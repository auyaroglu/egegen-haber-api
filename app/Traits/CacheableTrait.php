<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Cacheable Trait
 * Model'ler için cache işlemlerini kolaylaştıran trait
 */
trait CacheableTrait {
	/**
	 * Cache key prefix'i
	 */
	protected function getCachePrefix(): string {
		return strtolower(class_basename($this)) . '_';
	}

	/**
	 * Model için cache key oluştur
	 */
	public function getCacheKey(string $suffix = ''): string {
		$key = $this->getCachePrefix() . $this->getKey();

		if ($suffix) {
			$key .= '_' . $suffix;
		}

		return $key;
	}

	/**
	 * Model'i cache'e koy
	 */
	public function putInCache(int $minutes = 60, string $suffix = ''): self {
		$key = $this->getCacheKey($suffix);
		Cache::put($key, $this, now()->addMinutes($minutes));

		return $this;
	}

	/**
	 * Model'i cache'den getir
	 */
	public static function getFromCache(mixed $id, string $suffix = ''): ?static {
		$instance = new static;
		$key = $instance->getCachePrefix() . $id;

		if ($suffix) {
			$key .= '_' . $suffix;
		}

		return Cache::get($key);
	}

	/**
	 * Model'in cache'ini temizle
	 */
	public function clearCache(string $suffix = ''): bool {
		$key = $this->getCacheKey($suffix);
		return Cache::forget($key);
	}

	/**
	 * Model grubunun cache'ini temizle
	 * Tagging desteği olmayan cache driver'lar için manuel key temizliği
	 */
	public static function clearGroupCache(string|null $tag = null): bool {
		try {
			// Cache driver tagging destekliyorsa normal yöntem
			if (self::cacheDriverSupportsTagging()) {
				$tag = $tag ?: strtolower(class_basename(static::class));
				return Cache::tags([$tag])->flush();
			} else {
				// Tagging desteklemiyorsa alternatif yöntem
				return self::clearGroupCacheWithoutTags();
			}
		} catch (\Exception $e) {
			// Hata olursa sessizce devam et
			Log::warning('Cache clearing failed: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Cache driver'ın tagging desteği olup olmadığını kontrol et
	 */
	private static function cacheDriverSupportsTagging(): bool {
		$driver = config('cache.default');
		$supportedDrivers = ['redis', 'memcached'];

		return in_array($driver, $supportedDrivers);
	}

	/**
	 * Tagging olmadan cache temizliği (Database/File cache için)
	 */
	private static function clearGroupCacheWithoutTags(): bool {
		try {
			// Genel cache temizliği pattern'leri
			$prefix = strtolower(class_basename(static::class));

			// Cache store'a göre farklı stratejiler uygulanabilir
			$driver = config('cache.default');

			if ($driver === 'database') {
				// Database cache için manuel temizlik
				DB::table(config('cache.stores.database.table', 'cache'))
					->where('key', 'like', '%' . $prefix . '%')
					->delete();
				return true;
			} elseif ($driver === 'file') {
				// File cache için cache'i tamamen temizle
				Cache::flush();
				return true;
			}

			return true;
		} catch (\Exception $e) {
			Log::warning('Manual cache clearing failed: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Cache'li query builder
	 */
	public function scopeCached($query, int $minutes = 60, string|null $cacheKey = null) {
		if (!$cacheKey) {
			$cacheKey = md5($query->toSql() . serialize($query->getBindings()));
		}

		return Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($query) {
			return $query->get();
		});
	}
}
