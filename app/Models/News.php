<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\CacheableTrait;

/**
 * News Model
 *
 * Bu model, haber içeriklerini yönetir.
 * WebP görsel desteği, slug oluşturma, full-text search ve cache özellikleri içerir.
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string|null $image
 * @property string $slug
 * @property string $status
 * @property string|null $summary
 * @property string|null $author
 * @property array|null $tags
 * @property int $view_count
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class News extends Model {
    /** @use HasFactory<\Database\Factories\NewsFactory> */
    use HasFactory, CacheableTrait;

    /**
     * Mass assignment için korunan alanlar
     */
    protected $fillable = [
        'title',
        'content',
        'image',
        'slug',
        'status',
        'summary',
        'author',
        'tags',
        'view_count',
        'published_at'
    ];

    /**
     * Cast edilecek alanlar
     */
    protected $casts = [
        'tags' => 'array',
        'view_count' => 'integer',
        'published_at' => 'datetime'
    ];

    /**
     * Status değerleri
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DRAFT = 'draft';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DRAFT
    ];

    /**
     * Aktif haberler için scope
     */
    public function scopeActive($query) {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Yayınlanmış haberler için scope
     */
    public function scopePublished($query) {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('published_at', '<=', now());
    }

    /**
     * Draft haberler için scope
     */
    public function scopeDraft($query) {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * En son haberler için scope
     */
    public function scopeLatest($query) {
        return $query->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * En popüler haberler için scope
     */
    public function scopeMostViewed($query) {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Slug ile arama için scope
     */
    public function scopeBySlug($query, string $slug) {
        return $query->where('slug', $slug);
    }

    /**
     * Yazar ile arama için scope
     */
    public function scopeByAuthor($query, string $author) {
        return $query->where('author', 'like', "%{$author}%");
    }

    /**
     * Belirli bir tarih aralığında yayınlanan haberler için scope
     */
    public function scopePublishedBetween($query, $startDate, $endDate) {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    /**
     * Full-text search için scope
     */
    public function scopeSearch($query, string $searchTerm) {
        return $query->whereFullText(['title', 'content', 'summary'], $searchTerm)
            ->orWhere('title', 'like', "%{$searchTerm}%")
            ->orWhere('content', 'like', "%{$searchTerm}%")
            ->orWhere('summary', 'like', "%{$searchTerm}%");
    }

    /**
     * Etiket ile arama için scope
     */
    public function scopeWithTag($query, string $tag) {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Model oluşturulurken otomatik slug oluşturma ve cache yönetimi
     */
    protected static function boot() {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = $news->generateUniqueSlug($news->title);
            }

            if (empty($news->published_at) && $news->status === self::STATUS_ACTIVE) {
                $news->published_at = now();
            }

            // Summary otomatik oluştur eğer boşsa
            if (empty($news->summary) && !empty($news->content)) {
                // Content alanının JSON string olup olmadığını kontrol et
                $contentText = $news->content;

                // Eğer content JSON string ise decode et
                if (is_string($contentText) && str_starts_with(trim($contentText), '{')) {
                    $jsonData = json_decode($contentText, true);
                    if (is_array($jsonData) && isset($jsonData['content'])) {
                        $contentText = $jsonData['content'];
                    }
                }

                $contentText = strip_tags($contentText);
                $news->summary = Str::limit($contentText, 200);
            }
        });

        static::updating(function ($news) {
            // Başlık değiştiğinde slug'ı güncelle
            if ($news->isDirty('title') && empty($news->slug)) {
                $news->slug = $news->generateUniqueSlug($news->title);
            }

            // Status active yapıldığında published_at set et
            if ($news->isDirty('status') && $news->status === self::STATUS_ACTIVE && empty($news->published_at)) {
                $news->published_at = now();
            }

            // Content değiştiğinde summary'i güncelle (eğer summary değiştirilmemişse)
            if ($news->isDirty('content') && !$news->isDirty('summary')) {
                // Content alanının JSON string olup olmadığını kontrol et
                $contentText = $news->content;

                // Eğer content JSON string ise decode et
                if (is_string($contentText) && str_starts_with(trim($contentText), '{')) {
                    $jsonData = json_decode($contentText, true);
                    if (is_array($jsonData) && isset($jsonData['content'])) {
                        $contentText = $jsonData['content'];
                    }
                }

                $contentText = strip_tags($contentText);
                $news->summary = Str::limit($contentText, 200);
            }
        });

        // Cache yönetimi - Model değiştiğinde cache'i temizle
        static::saved(function ($news) {
            $news->clearCache();
            static::clearGroupCache();
        });

        static::deleted(function ($news) {
            $news->clearCache();
            static::clearGroupCache();
        });
    }

    /**
     * Benzersiz slug oluşturur
     */
    private function generateUniqueSlug(string $title): string {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Görüntülenme sayısını artırır
     */
    public function incrementViewCount(): void {
        $this->increment('view_count');
    }

    /**
     * Görsel URL'ini döner
     */
    public function getImageUrlAttribute(): ?string {
        if (!$this->image) {
            return null;
        }

        // Eğer tam URL ise direkt döner
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // Storage'dan URL oluştur - image path zaten 'images/' prefix'i içeriyor
        return asset('storage/' . $this->image);
    }

    /**
     * Kısa özet döner (150 karakter)
     */
    public function getShortSummaryAttribute(): string {
        if ($this->summary) {
            return Str::limit($this->summary, 150);
        }

        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Okunma süresi tahmini (dakika)
     */
    public function getReadingTimeAttribute(): int {
        $wordCount = str_word_count(strip_tags($this->content));
        return ceil($wordCount / 200); // Dakikada ortalama 200 kelime
    }

    /**
     * Haberin aktif olup olmadığını kontrol eder
     */
    public function isActive(): bool {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Haberin yayınlanmış olup olmadığını kontrol eder
     */
    public function isPublished(): bool {
        return $this->isActive() &&
            $this->published_at &&
            $this->published_at->isPast();
    }

    /**
     * API için formatlanmış veri döner
     */
    public function toApiArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->short_summary,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'author' => $this->author,
            'tags' => $this->tags ?? [],
            'view_count' => $this->view_count,
            'reading_time' => $this->reading_time,
            'status' => $this->status,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
