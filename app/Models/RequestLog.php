<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Request Log Model
 * 
 * Bu model, tüm API isteklerinin loglanması için kullanılır.
 * Güvenlik ve monitoring amaçlı detaylı log kaydı tutar.
 * 
 * @property int $id
 * @property string $ip_address
 * @property string $method
 * @property string $url
 * @property string|null $user_agent
 * @property array|null $headers
 * @property array|null $request_data
 * @property int|null $response_status
 * @property string|null $response_message
 * @property bool $has_bearer_token
 * @property float|null $execution_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RequestLog extends Model {
	/**
	 * Mass assignment için korunan alanlar
	 */
	protected $fillable = [
		'ip_address',
		'method',
		'url',
		'user_agent',
		'headers',
		'request_data',
		'response_status',
		'response_message',
		'has_bearer_token',
		'execution_time'
	];

	/**
	 * Cast edilecek alanlar
	 */
	protected $casts = [
		'headers' => 'array',
		'request_data' => 'array',
		'has_bearer_token' => 'boolean',
		'response_status' => 'integer',
		'execution_time' => 'decimal:3'
	];

	/**
	 * Bearer token olmayan istekler için scope
	 */
	public function scopeWithoutBearerToken($query) {
		return $query->where('has_bearer_token', false);
	}

	/**
	 * Belirli IP adresi için scope
	 */
	public function scopeForIp($query, string $ipAddress) {
		return $query->where('ip_address', $ipAddress);
	}

	/**
	 * Belirli HTTP metodu için scope
	 */
	public function scopeWithMethod($query, string $method) {
		return $query->where('method', strtoupper($method));
	}

	/**
	 * Başarısız istekler için scope (4xx ve 5xx)
	 */
	public function scopeFailedRequests($query) {
		return $query->where('response_status', '>=', 400);
	}

	/**
	 * Başarılı istekler için scope (2xx)
	 */
	public function scopeSuccessfulRequests($query) {
		return $query->whereBetween('response_status', [200, 299]);
	}

	/**
	 * Son N dakika içindeki istekler için scope
	 */
	public function scopeInLastMinutes($query, int $minutes) {
		return $query->where('created_at', '>=', now()->subMinutes($minutes));
	}

	/**
	 * Belirli bir IP adresinin son N dakikadaki istek sayısını döner
	 */
	public static function getRequestCountForIp(string $ipAddress, int $minutes = 10): int {
		return self::forIp($ipAddress)
			->inLastMinutes($minutes)
			->count();
	}

	/**
	 * Belirli bir IP adresinin son N dakikadaki başarısız istek sayısını döner
	 */
	public static function getFailedRequestCountForIp(string $ipAddress, int $minutes = 10): int {
		return self::forIp($ipAddress)
			->withoutBearerToken()
			->inLastMinutes($minutes)
			->count();
	}

	/**
	 * Request log kaydı oluşturur
	 */
	public static function logRequest(
		string $ipAddress,
		string $method,
		string $url,
		?string $userAgent = null,
		?array $headers = null,
		?array $requestData = null,
		?int $responseStatus = null,
		?string $responseMessage = null,
		bool $hasBearerToken = false,
		?float $executionTime = null
	): self {
		return self::create([
			'ip_address' => $ipAddress,
			'method' => strtoupper($method),
			'url' => $url,
			'user_agent' => $userAgent,
			'headers' => $headers,
			'request_data' => $requestData,
			'response_status' => $responseStatus,
			'response_message' => $responseMessage,
			'has_bearer_token' => $hasBearerToken,
			'execution_time' => $executionTime
		]);
	}

	/**
	 * Request'in detaylı bilgilerini formatlanmış şekilde döner
	 */
	public function getFormattedInfo(): array {
		return [
			'id' => $this->id,
			'ip_address' => $this->ip_address,
			'method' => $this->method,
			'url' => $this->url,
			'user_agent' => $this->user_agent,
			'response_status' => $this->response_status,
			'has_bearer_token' => $this->has_bearer_token,
			'execution_time' => $this->execution_time . 'ms',
			'created_at' => $this->created_at->format('Y-m-d H:i:s')
		];
	}
}
