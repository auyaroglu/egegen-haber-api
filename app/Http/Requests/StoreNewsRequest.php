<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Haber oluşturma validation request'i
 * Türkçe hata mesajları ile birlikte
 */
class StoreNewsRequest extends FormRequest {
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
		return [
			'title' => [
				'required',
				'string',
				'min:5',
				'max:255',
				'unique:news,title' // Aynı başlıkta haber olmasın
			],
			'content' => [
				'required',
				'string',
				'min:50', // En az 50 karakter
				'max:10000' // Maksimum 10.000 karakter
			],
			'image' => [
				'nullable',
				'image', // Sadece görsel dosyaları
				'mimes:jpeg,jpg,png,webp,gif', // İzin verilen formatlar
				'max:5120' // Maksimum 5MB
			],
			'status' => [
				'sometimes',
				'string',
				Rule::in(['active', 'inactive'])
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
			'title.required' => 'Haber başlığı zorunludur.',
			'title.string' => 'Haber başlığı metin formatında olmalıdır.',
			'title.min' => 'Haber başlığı en az :min karakter olmalıdır.',
			'title.max' => 'Haber başlığı en fazla :max karakter olabilir.',
			'title.unique' => 'Bu başlıkta bir haber zaten mevcut.',

			'content.required' => 'Haber içeriği zorunludur.',
			'content.string' => 'Haber içeriği metin formatında olmalıdır.',
			'content.min' => 'Haber içeriği en az :min karakter olmalıdır.',
			'content.max' => 'Haber içeriği en fazla :max karakter olabilir.',

			'image.image' => 'Sadece görsel dosyaları yükleyebilirsiniz.',
			'image.mimes' => 'Görsel formatı jpeg, jpg, png, webp veya gif olmalıdır.',
			'image.max' => 'Görsel boyutu maksimum :max KB olabilir.',

			'status.string' => 'Durum metin formatında olmalıdır.',
			'status.in' => 'Durum active veya inactive olabilir.'
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

	/**
	 * Validation'dan sonra veriyi hazırla
	 */
	protected function prepareForValidation(): void {
		// Başlık ve içerikteki gereksiz boşlukları temizle
		if ($this->has('title')) {
			$this->merge([
				'title' => trim($this->title)
			]);
		}

		if ($this->has('content')) {
			$this->merge([
				'content' => trim($this->content)
			]);
		}

		// Status belirtilmemişse varsayılan olarak active yap
		if (!$this->has('status')) {
			$this->merge([
				'status' => 'active'
			]);
		}
	}
}
