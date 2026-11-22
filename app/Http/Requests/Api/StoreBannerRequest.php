<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var(
                    $this->input('is_active'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ) ?? false,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'image' => ['required_without:images', 'file', 'image', 'mimetypes:image/jpeg,image/jpg,image/png,image/webp', 'max:5120'],
            'images' => ['required_without:image', 'array', 'min:1', 'max:20'],
            'images.*' => ['file', 'image', 'mimetypes:image/jpeg,image/jpg,image/png,image/webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required_without' => 'Foto banner wajib diunggah.',
            'images.required_without' => 'Foto banner wajib diunggah.',
            'image.file' => 'Gambar gagal diunggah. Kompres file di bawah batas unggah PHP lalu coba lagi.',
            'image.uploaded' => 'Gambar gagal diunggah. Kompres file di bawah batas unggah PHP lalu coba lagi.',
            'images.*.file' => 'Gambar gagal diunggah. Kompres file di bawah batas unggah PHP lalu coba lagi.',
            'images.*.uploaded' => 'Gambar gagal diunggah. Kompres file di bawah batas unggah PHP lalu coba lagi.',
            'image.image' => 'File harus berupa gambar.',
            'images.*.image' => 'File harus berupa gambar.',
            'image.mimetypes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'images.*.mimetypes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
            'images.*.max' => 'Ukuran gambar maksimal 5 MB per foto.',
        ];
    }
}
