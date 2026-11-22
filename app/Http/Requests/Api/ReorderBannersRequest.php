<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReorderBannersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:banners,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Daftar urutan banner wajib dikirim.',
            'ids.*.exists' => 'Salah satu banner tidak ditemukan.',
        ];
    }
}
