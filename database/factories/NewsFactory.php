<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory {
	/**
	 * Türkçe haber başlıkları için örnekler
	 */
	private array $turkishTitles = [
		'Teknoloji Sektörü',
		'Eğitim Reformu',
		'Sağlık Sistemi',
		'Çevre Koruma',
		'Ekonomik Gelişmeler',
		'Spor Haberleri',
		'Kültür Sanat',
		'Bilim İnsanları',
		'Şehir Planlaması',
		'Tarih Araştırması',
		'Sosyal Medya',
		'İnovasyon Merkezi',
		'Girişimcilik',
		'Sürdürülebilirlik',
		'Dijital Dönüşüm',
		'Yapay Zeka'
	];

	/**
	 * Türkçe haber içerikleri için örnekler
	 */
	private array $turkishContents = [
		'Bu gelişme sektörde önemli değişikliklere yol açacak.',
		'Uzmanlar konuya ilişkin görüşlerini paylaştı.',
		'Yeni uygulama büyük ilgi gördü.',
		'Araştırma sonuçları dikkat çekici bulgular ortaya koydu.',
		'Proje kapsamında önemli adımlar atıldı.',
		'İnovasyona dayalı çözümler geliştirildi.',
		'Kullanıcı deneyimi ön planda tutuldu.',
		'Sürdürülebilir kalkınma hedefleri desteklendi.'
	];

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		$title = $this->faker->randomElement($this->turkishTitles) . ' ' .
			$this->faker->words(rand(2, 4), true) . ' ' .
			$this->faker->randomElement(['Gelişmeleri', 'Haberleri', 'Projesi', 'İncelemesi']);

		$content = $this->faker->randomElement($this->turkishContents) . ' ' .
			$this->faker->paragraph(rand(3, 8)) . ' ' .
			$this->faker->randomElement($this->turkishContents) . ' ' .
			$this->faker->paragraph(rand(2, 5));

		return [
			'title' => $title,
			'content' => $content,
			'slug' => Str::slug($title) . '-' . Str::random(5),
			'image' => null, // Görsellerle performans problemi olmasın diye null
			'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']), // %75 aktif
			'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
			'updated_at' => function (array $attributes) {
				return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
			},
		];
	}

	/**
	 * Sadece aktif haberler için state
	 */
	public function active() {
		return $this->state(function (array $attributes) {
			return [
				'status' => 'active',
			];
		});
	}

	/**
	 * Sadece inaktif haberler için state
	 */
	public function inactive() {
		return $this->state(function (array $attributes) {
			return [
				'status' => 'inactive',
			];
		});
	}

	/**
	 * Görsel ile birlikte haber için state
	 */
	public function withImage() {
		return $this->state(function (array $attributes) {
			return [
				'image' => 'images/' . date('Y/m/d') . '/' . Str::uuid() . '.webp',
			];
		});
	}
}
