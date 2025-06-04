<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Görsel işleme servisi
 * WebP formatına çevirme ve boyutlandırma işlemlerini yönetir
 */
class ImageService {
	private ImageManager $manager;
	private const MAX_WIDTH = 800;
	private const MAX_HEIGHT = 800;
	private const QUALITY = 85;

	public function __construct() {
		// GD driver ile Image Manager'i başlat
		$this->manager = new ImageManager(new Driver());
	}

	/**
	 * Yüklenen görseli işle ve WebP formatında kaydet
	 *
	 * @param UploadedFile $file Yüklenen görsel dosyası
	 * @param string $directory Kaydedilecek klasör (varsayılan: images)
	 * @return string Kaydedilen dosyanın yolu
	 * @throws \Exception
	 */
	public function processAndStore(UploadedFile $file, string $directory = 'images'): string {
		try {
			// Benzersiz dosya adı oluştur
			$filename = $this->generateUniqueFilename();

			// Görseli yükle ve işle
			$image = $this->manager->read($file->getRealPath());

			// Boyutları kontrol et ve gerekirse yeniden boyutlandır
			$image = $this->resizeIfNeeded($image);

			// WebP formatına çevir
			$webpImage = $image->toWebp(self::QUALITY);

			// Dosyayı kaydet
			$path = $directory . '/' . $filename . '.webp';
			Storage::disk('public')->put($path, $webpImage);

			return $path;
		} catch (\Exception $e) {
			throw new \Exception('Görsel işleme hatası: ' . $e->getMessage());
		}
	}

	/**
	 * Görseli gerekirse yeniden boyutlandır
	 * 800px'i geçmeyecek şekilde orantılı olarak küçült
	 *
	 * @param \Intervention\Image\Interfaces\ImageInterface $image
	 * @return \Intervention\Image\Interfaces\ImageInterface
	 */
	private function resizeIfNeeded($image) {
		$width = $image->width();
		$height = $image->height();

		// Eğer görsel maksimum boyutları geçiyorsa yeniden boyutlandır
		if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
			// Orantıyı koruyarak boyutlandır
			$image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);
		}

		return $image;
	}

	/**
	 * Benzersiz dosya adı oluştur
	 * Tarih bazlı klasör yapısı: YYYY/MM/DD/UUID.webp
	 *
	 * @return string
	 */
	private function generateUniqueFilename(): string {
		return date('Y/m/d') . '/' . time() . '_' . Str::random(10);
	}

	/**
	 * Eski görseli sil
	 * Hata durumunda log kaydı tutar ancak işlemi durdurmaz
	 *
	 * @param string|null $imagePath Silinecek görsel yolu
	 * @return bool
	 */
	public function deleteImage(?string $imagePath): bool {
		if (!$imagePath) {
			return true;
		}

		try {
			if (Storage::disk('public')->exists($imagePath)) {
				$deleted = Storage::disk('public')->delete($imagePath);

				if ($deleted) {
					Log::info('Görsel başarıyla silindi: ' . $imagePath);
				}

				return $deleted;
			}

			Log::warning('Silinmek istenen görsel bulunamadı: ' . $imagePath);
			return true; // Dosya zaten yok, başarılı kabul et
		} catch (\Exception $e) {
			// Log hatası ancak işlemi durdurma
			Log::error('Görsel silme hatası: ' . $e->getMessage(), [
				'image_path' => $imagePath,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return false;
		}
	}

	/**
	 * Görsel URL'sini oluştur
	 *
	 * @param string|null $imagePath
	 * @return string|null
	 */
	public function getImageUrl(?string $imagePath): ?string {
		if (!$imagePath) {
			return null;
		}

		return url('storage/' . $imagePath);
	}

	/**
	 * Görselin mevcut olup olmadığını kontrol et
	 *
	 * @param string|null $imagePath
	 * @return bool
	 */
	public function imageExists(?string $imagePath): bool {
		if (!$imagePath) {
			return false;
		}

		return Storage::disk('public')->exists($imagePath);
	}
}
