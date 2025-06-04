<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'summary' => [
                'nullable',
                'string',
                'max:500' // Maksimum 500 karakter özet
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
            'title.string' => 'Haber başlığı metin formatında olmalıdır.',
            'title.min' => 'Haber başlığı en az :min karakter olmalıdır.',
            'title.max' => 'Haber başlığı en fazla :max karakter olabilir.',
            'title.unique' => 'Bu başlıkta başka bir haber zaten mevcut.',

            'content.string' => 'Haber içeriği metin formatında olmalıdır.',
            'content.min' => 'Haber içeriği en az :min karakter olmalıdır.',
            'content.max' => 'Haber içeriği en fazla :max karakter olabilir.',

            'summary.string' => 'Özet metin formatında olmalıdır.',
            'summary.max' => 'Özet en fazla :max karakter olabilir.',

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
            'summary' => 'özet',
            'image' => 'görsel',
            'status' => 'durum'
        ];
    }

    /**
     * Validation'dan sonra veriyi hazırla
     */
    protected function prepareForValidation(): void {
        // JSON request body'si yanlış parse edilmiş olabilir, düzeltelim
        /** @var \Illuminate\Http\Request $this */
        if ($this->isJson()) {
            $rawContent = $this->getContent();

            // Raw content'i decode et
            $jsonData = json_decode($rawContent, true);

            // Eğer JSON doğru decode edilmişse
            if (is_array($jsonData) && json_last_error() === JSON_ERROR_NONE) {
                // Sadece gerekli field'ları al
                $cleanData = [];

                if (isset($jsonData['title']) && is_string($jsonData['title'])) {
                    $cleanData['title'] = trim($jsonData['title']);
                }

                if (isset($jsonData['content']) && is_string($jsonData['content'])) {
                    $cleanData['content'] = trim($jsonData['content']);
                }

                if (isset($jsonData['status']) && is_string($jsonData['status'])) {
                    $cleanData['status'] = $jsonData['status'];
                }

                if (isset($jsonData['summary']) && is_string($jsonData['summary'])) {
                    $cleanData['summary'] = trim($jsonData['summary']);
                }

                // Request data'sını temiz data ile değiştir
                $this->replace($cleanData);
            }
        }

        // Summary field'ını otomatik oluştur eğer content varsa ama summary yoksa
        if ($this->has('content') && !$this->has('summary') && is_string($this->input('content'))) {
            $contentText = strip_tags($this->input('content'));
            $summary = Str::limit($contentText, 200);
            $this->merge([
                'summary' => $summary
            ]);
        }
    }
}
