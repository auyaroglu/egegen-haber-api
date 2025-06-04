<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

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
     * Model grubunun cache'ini temizle (tag kullanarak)
     */
    public static function clearGroupCache(string|null $tag = null): bool {
        $tag = $tag ?: strtolower(class_basename(static::class));
        return Cache::tags([$tag])->flush();
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
