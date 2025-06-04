<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use Illuminate\Support\Facades\DB;

/**
 * News Seeder - 250.000 haber kaydı oluşturur
 * Performans için chunk işlemleri kullanır
 */
class NewsSeeder extends Seeder {
	use WithoutModelEvents;

	private const TOTAL_RECORDS = 250000;
	private const CHUNK_SIZE = 1000; // Her seferinde 1000 kayıt

	/**
	 * Run the database seeds.
	 * 250.000 haber kaydını chunk'lar halinde oluşturur
	 */
	public function run(): void {
		$this->command->info('📰 250.000 haber kaydı oluşturuluyor...');
		$this->command->info('🔄 Bu işlem biraz zaman alabilir, lütfen bekleyiniz.');

		// Foreign key kontrollerini geçici olarak kapat (performans için)
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		// Mevcut kayıtları temizle (varsa)
		DB::table('news')->truncate();

		$totalChunks = ceil(self::TOTAL_RECORDS / self::CHUNK_SIZE);
		$this->command->info("📊 Toplam {$totalChunks} chunk ile işlem yapılacak");

		// Chunk'lar halinde kayıt oluştur
		for ($i = 0; $i < $totalChunks; $i++) {
			$currentChunk = $i + 1;
			$recordsToCreate = min(self::CHUNK_SIZE, self::TOTAL_RECORDS - ($i * self::CHUNK_SIZE));

			$this->command->info("⏳ Chunk {$currentChunk}/{$totalChunks} işleniyor... ({$recordsToCreate} kayıt)");

			// Memory kullanımını optimize etmek için her chunk'tan sonra temizle
			if ($i > 0 && $i % 10 == 0) {
				$this->command->info("🧹 Memory temizleme yapılıyor...");
				gc_collect_cycles();
			}

			try {
				// Factory ile kayıtları oluştur
				News::factory()
					->count($recordsToCreate)
					->create();

				$processedRecords = ($i + 1) * self::CHUNK_SIZE;
				$actualProcessed = min($processedRecords, self::TOTAL_RECORDS);
				$percentage = round(($actualProcessed / self::TOTAL_RECORDS) * 100, 1);

				$this->command->info("✅ İlerleme: {$actualProcessed}/" . self::TOTAL_RECORDS . " (%{$percentage})");
			} catch (\Exception $e) {
				$this->command->error("❌ Chunk {$currentChunk} işlenirken hata: " . $e->getMessage());
				break;
			}
		}

		// Foreign key kontrollerini tekrar aç
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

		// Final kontrol
		$totalCreated = News::count();
		$this->command->info("🎉 Toplam {$totalCreated} haber kaydı başarıyla oluşturuldu!");

		// İstatistikler
		$this->displayStatistics();
	}

	/**
	 * Oluşturulan kayıtların istatistiklerini göster
	 */
	private function displayStatistics(): void {
		$this->command->info('📈 HABER İSTATİSTİKLERİ:');

		$activeCount = News::where('status', 'active')->count();
		$inactiveCount = News::where('status', 'inactive')->count();
		$withImageCount = News::whereNotNull('image')->count();
		$withoutImageCount = News::whereNull('image')->count();

		$this->command->table([
			['Durum', 'Sayı', 'Yüzde'],
		], [
			['Aktif Haberler', number_format($activeCount), round(($activeCount / self::TOTAL_RECORDS) * 100, 1) . '%'],
			['İnaktif Haberler', number_format($inactiveCount), round(($inactiveCount / self::TOTAL_RECORDS) * 100, 1) . '%'],
			['Görselli Haberler', number_format($withImageCount), round(($withImageCount / self::TOTAL_RECORDS) * 100, 1) . '%'],
			['Görselsiz Haberler', number_format($withoutImageCount), round(($withoutImageCount / self::TOTAL_RECORDS) * 100, 1) . '%'],
		]);

		// En son oluşturulan 5 haber
		$this->command->info('🔍 Son oluşturulan 5 haber:');
		$latestNews = News::latest()->take(5)->get(['id', 'title', 'status', 'created_at']);

		foreach ($latestNews as $news) {
			$this->command->line("• #{$news->id} - {$news->title} ({$news->status}) - {$news->created_at}");
		}
	}
}
