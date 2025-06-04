<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IpBlacklist;
use App\Models\RequestLog;

/**
 * Bearer Token Middleware
 *
 * Bu middleware, gelen isteklerdeki bearer token'ı kontrol eder.
 * Geçerli token: "2BH52wAHrAymR7wP3CASt"
 *
 * Token yoksa veya geçersizse:
 * - IP adresinin blacklist durumunu kontrol eder
 * - Bloklanmış ve süresi dolmamışsa 403 döndürür
 * - Değilse başarısız deneme sayısını artırır
 * - 10 başarısız deneme sonrası IP'yi blackliste alır
 */
class BearerTokenMiddleware {
    /**
     * Geçerli bearer token
     */
    private const VALID_TOKEN = '2BH52wAHrAymR7wP3CASt';

    /**
     * Maksimum başarısız deneme sayısı
     */
    private const MAX_FAILED_ATTEMPTS = 10;

    /**
     * Blacklist süresi (dakika)
     */
    private const BLACKLIST_DURATION = 10;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $ipAddress = $request->ip();

        // ADIM 1: İlk olarak IP adresinin mevcut blacklist durumunu kontrol et
        $blacklistStatus = $this->checkBlacklistStatus($ipAddress);

        if ($blacklistStatus['is_blocked']) {
            // IP şu anda bloklu - direkt 403 dön
            return $this->respondBlacklisted($request, $blacklistStatus['entry']);
        }

        // ADIM 2: Bearer token'ı kontrol et
        $token = $request->bearerToken();

        if (!$token || $token !== self::VALID_TOKEN) {
            // Token geçersiz - başarısız deneme işle
            $newStatus = $this->handleFailedAttempt($ipAddress);

            // Log kaydı oluştur
            RequestLog::logRequest(
                $ipAddress,
                $request->method(),
                $request->fullUrl(),
                $request->userAgent(),
                $this->sanitizeHeaders($request->headers->all()),
                $this->sanitizeRequestData($request->all()),
                401,
                $token ? 'Invalid bearer token' : 'Missing bearer token',
                !empty($token)
            );

            // Eğer bu deneme ile IP bloklandıysa farklı mesaj dön
            if ($newStatus['newly_blocked']) {
                return response()->json([
                    'success' => false,
                    'message' => "IP adresiniz çok fazla başarısız deneme nedeniyle " . self::BLACKLIST_DURATION . " dakika süreyle engellenmiştir.",
                    'error_code' => 'IP_NEWLY_BLACKLISTED',
                    'details' => [
                        'attempt_count' => $newStatus['attempt_count'],
                        'max_attempts' => self::MAX_FAILED_ATTEMPTS,
                        'blocked_until' => $newStatus['blocked_until'],
                        'block_duration' => self::BLACKLIST_DURATION . ' dakika'
                    ]
                ], 403);
            }

            // Henüz bloklanmadı - normal token hatası dön
            return response()->json([
                'success' => false,
                'message' => 'Yetkisiz erişim. Geçerli bir bearer token gerekmektedir.',
                'error_code' => $token ? 'INVALID_TOKEN' : 'MISSING_TOKEN',
                'warning' => [
                    'attempt_count' => $newStatus['attempt_count'],
                    'remaining_attempts' => self::MAX_FAILED_ATTEMPTS - $newStatus['attempt_count'],
                    'message' => 'Çok fazla başarısız deneme IP adresinizi geçici olarak bloklatabilir.'
                ]
            ], 401);
        }

        // ADIM 3: Token geçerli - başarılı giriş işlemleri
        $this->handleSuccessfulAuthentication($ipAddress);

        // Request'e token bilgisini ekle
        $request->attributes->add(['has_valid_token' => true]);

        return $next($request);
    }

    /**
     * IP adresinin mevcut blacklist durumunu kontrol eder
     *
     * @param string $ipAddress
     * @return array ['is_blocked' => bool, 'entry' => IpBlacklist|null]
     */
    private function checkBlacklistStatus(string $ipAddress): array {
        $entry = IpBlacklist::where('ip_address', $ipAddress)->first();

        if (!$entry) {
            return ['is_blocked' => false, 'entry' => null];
        }

        // Eğer aktif değilse bloklu değil
        if (!$entry->is_active) {
            return ['is_blocked' => false, 'entry' => $entry];
        }

        // Süre kontrolü yap
        if ($entry->expires_at && $entry->expires_at->isPast()) {
            // Süresi dolmuş - tüm bloklanma verilerini temizle
            $entry->update([
                'is_active' => false,
                'attempt_count' => 0,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
            return ['is_blocked' => false, 'entry' => $entry];
        }

        // Hala bloklu
        return ['is_blocked' => true, 'entry' => $entry];
    }

    /**
     * Başarısız deneme işlemlerini yönetir
     *
     * @param string $ipAddress
     * @return array Deneme sonrası durum bilgisi
     */
    private function handleFailedAttempt(string $ipAddress): array {
        $entry = IpBlacklist::firstOrCreate(
            ['ip_address' => $ipAddress],
            ['attempt_count' => 0, 'is_active' => false]
        );

        // Eğer süre dolmuş bir kayıtsa tamamen temizle
        if ($entry->expires_at && $entry->expires_at->isPast()) {
            $entry->update([
                'attempt_count' => 0,
                'is_active' => false,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
        }

        // Deneme sayısını artır
        $entry->attempt_count += 1;

        // Maksimum deneme sayısına ulaştı mı?
        $newlyBlocked = false;
        $blockedUntil = null;

        if ($entry->attempt_count >= self::MAX_FAILED_ATTEMPTS) {
            $blockedUntil = now()->addMinutes(self::BLACKLIST_DURATION);
            $entry->update([
                'blocked_at' => now(),
                'expires_at' => $blockedUntil,
                'is_active' => true,
                'reason' => "Çok fazla başarısız bearer token denemesi ({$entry->attempt_count} deneme)"
            ]);
            $newlyBlocked = true;
        } else {
            $entry->save();
        }

        return [
            'attempt_count' => $entry->attempt_count,
            'newly_blocked' => $newlyBlocked,
            'blocked_until' => $blockedUntil?->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Başarılı authentication sonrası işlemler
     */
    private function handleSuccessfulAuthentication(string $ipAddress): void {
        $entry = IpBlacklist::where('ip_address', $ipAddress)->first();

        if ($entry && $entry->attempt_count > 0) {
            // Başarılı giriş - tüm bloklanma verilerini temizle
            $entry->update([
                'attempt_count' => 0,
                'is_active' => false,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
        }
    }

    /**
     * Bloklanmış IP için response döndürür
     */
    private function respondBlacklisted(Request $request, IpBlacklist $entry): Response {
        // Log kaydı oluştur
        RequestLog::logRequest(
            $request->ip(),
            $request->method(),
            $request->fullUrl(),
            $request->userAgent(),
            $this->sanitizeHeaders($request->headers->all()),
            $this->sanitizeRequestData($request->all()),
            403,
            'IP address is blacklisted due to multiple failed authentication attempts',
            false
        );

        return response()->json([
            'success' => false,
            'message' => 'IP adresiniz güvenlik nedeniyle geçici süreyle engellenmiştir.',
            'error_code' => 'IP_BLACKLISTED',
            'details' => [
                'blocked_at' => $entry->blocked_at?->format('Y-m-d H:i:s'),
                'expires_at' => $entry->expires_at?->format('Y-m-d H:i:s'),
                'reason' => $entry->reason,
                'remaining_time' => $this->getRemainingBlockTime($entry)
            ]
        ], 403);
    }

    /**
     * Kalan bloklanma süresini hesaplar
     */
    private function getRemainingBlockTime(IpBlacklist $entry): ?string {
        if (!$entry->expires_at) {
            return null;
        }

        $now = now();
        $expiresAt = $entry->expires_at;

        if ($expiresAt->isPast()) {
            return '0 dakika';
        }

        $diffInMinutes = $now->diffInMinutes($expiresAt);
        $diffInSeconds = $now->diffInSeconds($expiresAt) % 60;

        if ($diffInMinutes > 0) {
            return "{$diffInMinutes} dakika " . ($diffInSeconds > 0 ? "{$diffInSeconds} saniye" : "");
        }

        return "{$diffInSeconds} saniye";
    }

    /**
     * Request headers'ları güvenli hale getirir (hassas bilgileri temizler)
     */
    private function sanitizeHeaders(array $headers): array {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***FILTERED***'];
            }
        }

        return $headers;
    }

    /**
     * Request data'sını güvenli hale getirir (hassas bilgileri temizler)
     */
    private function sanitizeRequestData(array $requestData): array {
        $sensitiveFields = ['password', 'token', 'api_key', 'secret'];

        foreach ($sensitiveFields as $field) {
            if (isset($requestData[$field])) {
                $requestData[$field] = '***FILTERED***';
            }
        }

        return $requestData;
    }
}
