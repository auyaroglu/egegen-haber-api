<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Arama performansını artırmak için index'ler ekler:
     * - title alanı için index - başlık araması hızlandırmak için
     * - content alanı için fulltext index - içerik araması için (MySQL 5.7+)
     * - status alanı için index - aktif/inaktif filtrelemesi için
     * - created_at alanı için index - tarih sıralama için
     * - composite index (status + created_at birlikte sık kullanılır)
     */
    public function up(): void {
        Schema::table('news', function (Blueprint $table) {
            // Title için normal index - başlık araması hızlandırmak için
            $table->index('title', 'idx_news_title');

            // Content için fulltext index - içerik araması için (MySQL 5.7+)
            $table->fullText(['title', 'content'], 'idx_news_search_fulltext');

            // Status için index - aktif/inaktif filtrelemesi için
            $table->index('status', 'idx_news_status');

            // Created_at için index - tarih sıralama için
            $table->index('created_at', 'idx_news_created_at');

            // Composite index - status ve created_at birlikte sık kullanılır
            $table->index(['status', 'created_at'], 'idx_news_status_created_at');

            // Slug için unique index - SEO URL'leri için
            $table->index('slug', 'idx_news_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('news', function (Blueprint $table) {
            // Index'leri sil
            $table->dropIndex('idx_news_title');
            $table->dropFullText('idx_news_search_fulltext');
            $table->dropIndex('idx_news_status');
            $table->dropIndex('idx_news_created_at');
            $table->dropIndex('idx_news_status_created_at');
            $table->dropIndex('idx_news_slug');
        });
    }
};
