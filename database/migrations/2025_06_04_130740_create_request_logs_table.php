<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 * Request logs tablosu - Tüm API isteklerinin loglanması
	 */
	public function up(): void {
		Schema::create('request_logs', function (Blueprint $table) {
			$table->id();
			$table->string('ip_address', 45); // İstek yapılan IP adresi
			$table->string('method', 10); // HTTP metodu (GET, POST, PUT, DELETE)
			$table->text('url'); // İstek yapılan URL
			$table->text('user_agent')->nullable(); // Kullanıcı aracısı
			$table->json('headers')->nullable(); // Request headers
			$table->json('request_data')->nullable(); // Request body/parameters
			$table->integer('response_status')->nullable(); // HTTP response kodu
			$table->text('response_message')->nullable(); // Response mesajı
			$table->boolean('has_bearer_token')->default(false); // Bearer token var mı?
			$table->decimal('execution_time', 8, 3)->nullable(); // İşlem süresi (ms)
			$table->timestamps();

			// Index'ler performans için
			$table->index('ip_address');
			$table->index('method');
			$table->index(['ip_address', 'created_at']);
			$table->index('has_bearer_token');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('request_logs');
	}
};
