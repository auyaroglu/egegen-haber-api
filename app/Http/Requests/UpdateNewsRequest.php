<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Haber güncelleme validation request'i
 * Güncelleme için optimize edilmiş kurallar
 */
class UpdateNewsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 * Bearer token middleware tarafından kontrol edildiği için true
	 */
	public function authorize(): bool {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array {
		// Model binding ile gelen news ID'sini al
		$newsId = $this->news->id ?? null;

		return [
			'title' => [
				'sometimes',
				'string',
				'min:5',
				'max:255',
				Rule::unique('news', 'title')->ignore($newsId) // Kendi title'ı hariç unique kontrol
			],
			'content' => [
				'sometimes',
				'string',
				'min:50',
				'max:10000'
			],
			'image' => [
				'nullable',
				'image',
				'mimes:jpeg,jpg,png,webp,gif',
				'max:5120'
			],
			'status' => [
				'sometimes',
				'string',
				Rule::in(['published', 'draft'])
			]
		];
	}

	/**
	 * Türkçe hata mesajları
	 *
	 * @return array<string, string>
	 */
	public function messages(): array {
		return [
			'title.string' => 'Haber başlığı metin formatında olmalıdır.',
			'title.min' => 'Haber başlığı en az :min karakter olmalıdır.',
			'title.max' => 'Haber başlığı en fazla :max karakter olabilir.',
			'title.unique' => 'Bu başlıkta başka bir haber zaten mevcut.',

			'content.string' => 'Haber içeriği metin formatında olmalıdır.',
			'content.min' => 'Haber içeriği en az :min karakter olmalıdır.',
			'content.max' => 'Haber içeriği en fazla :max karakter olabilir.',

			'image.image' => 'Sadece görsel dosyaları yükleyebilirsiniz.',
			'image.mimes' => 'Görsel formatı jpeg, jpg, png, webp veya gif olmalıdır.',
			'image.max' => 'Görsel boyutu maksimum :max KB olabilir.',

			'status.string' => 'Durum metin formatında olmalıdır.',
			'status.in' => 'Durum published veya draft olabilir.'
		];
	}

	/**
	 * Alan adlarının Türkçe karşılıkları
	 *
	 * @return array<string, string>
	 */
	public function attributes(): array {
		return [
			'title' => 'başlık',
			'content' => 'içerik',
			'image' => 'görsel',
			'status' => 'durum'
		];
	}
}
