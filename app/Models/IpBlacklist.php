<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * IP Blacklist Model
 *
 * Bu model, başarısız token denemeleri sonucu blackliste alınan
 * IP adreslerini yönetir.
 *
 * @property int $id
 * @property string $ip_address
 * @property int $attempt_count
 * @property Carbon|null $blocked_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property string|null $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class IpBlacklist extends Model {
    /**
     * Mass assignment için korunan alanlar
     */
    protected $fillable = [
        'ip_address',
        'attempt_count',
        'blocked_at',
        'expires_at',
        'is_active',
        'reason'
    ];

    /**
     * Cast edilecek alanlar
     */
    protected $casts = [
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'attempt_count' => 'integer'
    ];

    /**
     * Aktif blacklist kayıtları için scope
     */
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    /**
     * Belirli IP adresi için scope
     */
    public function scopeForIp($query, string $ipAddress) {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * IP adresinin hala blacklistte olup olmadığını kontrol eder
     * Bu method artık deprecated - middleware'de direkt kontrol yapılıyor
     */
    public function isStillBlocked(): bool {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            // Süresi dolmuş - tüm bloklanma verilerini temizle
            $this->update([
                'is_active' => false,
                'attempt_count' => 0,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
            return false;
        }

        return true;
    }

    /**
     * Kalan blok süresini dakika cinsinden döndürür
     */
    public function getRemainingMinutes(): int {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return now()->diffInMinutes($this->expires_at);
    }

    /**
     * IP adresinin blacklist geçmişini temizler
     *
     * @param string $ipAddress
     * @return bool
     */
    public static function clearHistory(string $ipAddress): bool {
        return self::where('ip_address', $ipAddress)
            ->update([
                'attempt_count' => 0,
                'is_active' => false,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
    }

    /**
     * Süresi dolmuş blacklist kayıtlarını temizler (CRON job için)
     *
     * @return int Temizlenen kayıt sayısı
     */
    public static function cleanupExpired(): int {
        return self::where('expires_at', '<', now())
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'attempt_count' => 0,
                'blocked_at' => null,
                'expires_at' => null,
                'reason' => null
            ]);
    }

    /**
     * Blacklist istatistiklerini döndürür
     *
     * @return array
     */
    public static function getStats(): array {
        return [
            'total_records' => self::count(),
            'active_blocks' => self::active()->count(),
            'expired_blocks' => self::where('expires_at', '<', now())->where('is_active', true)->count(),
            'high_attempt_ips' => self::where('attempt_count', '>=', 5)->count()
        ];
    }
}
