<?php

namespace App\Http\Requests\Api;

use App\Enums\PopupDisplayFrequency;
use App\Support\JakartaDateTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePopupBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizedPayload());
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'file', 'image', 'mimetypes:image/jpeg,image/jpg,image/png,image/webp', 'max:5120'],
            'description' => ['nullable', 'string'],
            'link_url' => ['nullable', 'string', 'max:2048', 'url'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'display_order' => ['integer', 'min:0'],
            'display_frequency' => ['required', Rule::enum(PopupDisplayFrequency::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Gambar popup wajib diunggah.',
            'image.file' => 'Gambar gagal diunggah. Kompres file di bawah 2 MB (batas PHP upload_max_filesize) lalu coba lagi.',
            'image.uploaded' => 'Gambar gagal diunggah. Kompres file di bawah 2 MB (batas PHP upload_max_filesize) lalu coba lagi.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimetypes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
            'end_at.after_or_equal' => 'Waktu berakhir harus setelah atau sama dengan waktu mulai.',
            'link_url.url' => 'URL tujuan tidak valid.',
        ];
    }

    private function normalizedPayload(): array
    {
        $payload = [];

        foreach (['title', 'description', 'link_url', 'button_text', 'start_at', 'end_at'] as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $payload[$field] = null;
            }
        }

        $link = trim((string) ($this->input('link_url') ?? ''));
        if ($link === '' || $link === 'https://' || $link === 'http://') {
            $payload['link_url'] = null;
        }

        if ($this->has('is_active')) {
            $payload['is_active'] = filter_var(
                $this->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ) ?? false;
        }

        if ($this->input('display_order') === '' || $this->input('display_order') === 'NaN') {
            $payload['display_order'] = 0;
        }

        if (array_key_exists('start_at', $payload) || $this->filled('start_at')) {
            $payload['start_at'] = JakartaDateTime::fromLocalInput($payload['start_at'] ?? $this->input('start_at'));
        }
        if (array_key_exists('end_at', $payload) || $this->filled('end_at')) {
            $payload['end_at'] = JakartaDateTime::fromLocalInput($payload['end_at'] ?? $this->input('end_at'));
        }

        return $payload;
    }
}
