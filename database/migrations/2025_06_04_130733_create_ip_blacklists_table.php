<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 * IP blacklist tablosu - Başarısız token denemeleri için IP bloklama
	 */
	public function up(): void {
		Schema::create('ip_blacklists', function (Blueprint $table) {
			$table->id();
			$table->string('ip_address', 45)->unique(); // IPv4 ve IPv6 desteği için
			$table->integer('attempt_count')->default(0); // Başarısız deneme sayısı
			$table->timestamp('blocked_at')->nullable(); // Bloklanma zamanı
			$table->timestamp('expires_at')->nullable(); // Bloklama sona erme zamanı
			$table->boolean('is_active')->default(true); // Aktif durum
			$table->text('reason')->nullable(); // Bloklanma sebebi
			$table->timestamps();

			// Index'ler performans için
			$table->index('ip_address');
			$table->index(['is_active', 'expires_at']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('ip_blacklists');
	}
};
