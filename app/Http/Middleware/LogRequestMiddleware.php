<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Log;

/**
 * Log Request Middleware
 * 
 * Bu middleware, tüm gelen API isteklerini detaylı şekilde loglar.
 * İstek bilgileri, response durumu ve performans ölçümleri kaydedilir.
 * 
 * Kaydedilen bilgiler:
 * - IP adresi
 * - HTTP metodu ve URL
 * - User Agent
 * - Request headers ve body
 * - Response status ve mesajı
 * - Bearer token varlığı
 * - İşlem süresi
 */
class LogRequestMiddleware {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response {
		// İşlem süresini ölçmek için başlangıç zamanını kaydet
		$startTime = microtime(true);

		// Request'in bearer token içerip içermediğini kontrol et
		$hasBearerToken = !empty($request->bearerToken());

		// Request'i işle
		$response = $next($request);

		// İşlem süresini hesapla (milisaniye cinsinden)
		$executionTime = (microtime(true) - $startTime) * 1000;

		// Response mesajını belirle
		$responseMessage = $this->getResponseMessage($response);

		// Request log kaydını oluştur
		try {
			RequestLog::logRequest(
				$request->ip(),
				$request->method(),
				$request->fullUrl(),
				$request->userAgent(),
				$this->sanitizeHeaders($request->headers->all()),
				$this->sanitizeRequestData($request->all()),
				$response->getStatusCode(),
				$responseMessage,
				$hasBearerToken,
				round($executionTime, 3)
			);
		} catch (\Exception $e) {
			// Log kaydında hata olursa uygulamanın çalışmasını etkilemesin
			Log::error('Request log kaydında hata: ' . $e->getMessage());
		}

		return $response;
	}

	/**
	 * Response'dan mesaj çıkarır
	 */
	private function getResponseMessage(Response $response): ?string {
		$content = $response->getContent();

		// JSON response ise mesajı çıkar
		if ($this->isJsonResponse($response)) {
			$data = json_decode($content, true);

			if (is_array($data)) {
				// Laravel validation hatası formatı
				if (isset($data['message'])) {
					return $data['message'];
				}

				// Custom error formatı
				if (isset($data['error'])) {
					return is_string($data['error']) ? $data['error'] : 'API Error';
				}

				// Success mesajı
				if (isset($data['success']) && $data['success'] === true) {
					return 'Request successful';
				}
			}
		}

		// HTTP status mesajları
		return match ($response->getStatusCode()) {
			200 => 'OK',
			201 => 'Created',
			204 => 'No Content',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			422 => 'Validation Error',
			500 => 'Internal Server Error',
			default => 'HTTP ' . $response->getStatusCode()
		};
	}

	/**
	 * Response'un JSON olup olmadığını kontrol eder
	 */
	private function isJsonResponse(Response $response): bool {
		$contentType = $response->headers->get('Content-Type', '');
		return str_contains($contentType, 'application/json');
	}

	/**
	 * Request headers'ları güvenli hale getirir (hassas bilgileri temizler)
	 */
	private function sanitizeHeaders(array $headers): array {
		$sensitiveHeaders = [
			'authorization',
			'cookie',
			'x-api-key',
			'x-auth-token',
			'x-csrf-token',
			'x-xsrf-token'
		];

		foreach ($sensitiveHeaders as $header) {
			$headerLower = strtolower($header);
			foreach ($headers as $key => $value) {
				if (strtolower($key) === $headerLower) {
					$headers[$key] = ['***FILTERED***'];
				}
			}
		}

		return $headers;
	}

	/**
	 * Request verilerini güvenli hale getirir (hassas bilgileri temizler)
	 */
	private function sanitizeRequestData(array $requestData): array {
		$sensitiveFields = [
			'password',
			'password_confirmation',
			'token',
			'api_key',
			'secret',
			'client_secret',
			'access_token',
			'refresh_token'
		];

		return $this->recursivelyFilterSensitiveData($requestData, $sensitiveFields);
	}

	/**
	 * Nested array'lerde de hassas verileri temizler
	 */
	private function recursivelyFilterSensitiveData(array $data, array $sensitiveFields): array {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->recursivelyFilterSensitiveData($value, $sensitiveFields);
			} elseif (in_array(strtolower($key), array_map('strtolower', $sensitiveFields))) {
				$data[$key] = '***FILTERED***';
			}
		}

		return $data;
	}

	/**
	 * Request boyutunu kontrol eder (çok büyük request'leri kısaltır)
	 */
	private function limitRequestSize(array $requestData, int $maxSize = 10000): array {
		$serialized = json_encode($requestData);

		if (strlen($serialized) > $maxSize) {
			return [
				'_truncated' => true,
				'_original_size' => strlen($serialized),
				'_note' => 'Request data too large, truncated for logging',
				'partial_data' => array_slice($requestData, 0, 10, true)
			];
		}

		return $requestData;
	}
}
