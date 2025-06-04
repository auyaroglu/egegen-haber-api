<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IpBlacklist;

/**
 * IP Blacklist Middleware
 * 
 * Bu middleware, blacklistteki IP adreslerini kontrol eder.
 * Blacklistteki IP'lerden gelen istekleri bloklar.
 * 
 * Not: Bu middleware genellikle BearerTokenMiddleware ile birlikte kullanılır.
 * Ancak gerektiğinde tek başına da kullanılabilir.
 */
class IpBlacklistMiddleware {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response {
		$ipAddress = $request->ip();

		// IP adresinin blacklistte olup olmadığını kontrol et
		$blacklistEntry = IpBlacklist::forIp($ipAddress)
			->active()
			->notExpired()
			->first();

		if ($blacklistEntry && $blacklistEntry->isStillBlocked()) {
			return response()->json([
				'success' => false,
				'message' => 'IP adresiniz güvenlik nedeniyle geçici süreyle engellenmiştir.',
				'error_code' => 'IP_BLACKLISTED',
				'details' => [
					'blocked_at' => $blacklistEntry->blocked_at?->format('Y-m-d H:i:s'),
					'expires_at' => $blacklistEntry->expires_at?->format('Y-m-d H:i:s'),
					'reason' => $blacklistEntry->reason,
					'remaining_time' => $this->getRemainingBlockTime($blacklistEntry)
				]
			], 403);
		}

		return $next($request);
	}

	/**
	 * Kalan bloklanma süresini hesaplar
	 */
	private function getRemainingBlockTime(IpBlacklist $blacklistEntry): ?string {
		if (!$blacklistEntry->expires_at) {
			return null;
		}

		$now = now();
		$expiresAt = $blacklistEntry->expires_at;

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
}
