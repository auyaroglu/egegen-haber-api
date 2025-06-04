<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 * News tablosu - Haber içerikleri için ana tablo
	 */
	public function up(): void {
		Schema::create('news', function (Blueprint $table) {
			$table->id();
			$table->string('title'); // Haber başlığı
			$table->text('content'); // Haber içeriği
			$table->string('image')->nullable(); // Haber görseli (WebP formatında)
			$table->string('slug')->unique(); // SEO dostu URL slug
			$table->enum('status', ['active', 'inactive', 'draft'])->default('active'); // Haber durumu
			$table->text('summary')->nullable(); // Haber özeti (isteğe bağlı)
			$table->string('author')->nullable(); // Yazar adı
			$table->json('tags')->nullable(); // Etiketler (JSON array)
			$table->integer('view_count')->default(0); // Görüntülenme sayısı
			$table->timestamp('published_at')->nullable(); // Yayınlanma tarihi
			$table->timestamps();

			// Index'ler performans ve arama için
			$table->index('status');
			$table->index('published_at');
			$table->index('slug');
			$table->index(['status', 'published_at']);

			// Full-text search için index (MySQL için)
			$table->fullText(['title', 'content', 'summary']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('news');
	}
};
