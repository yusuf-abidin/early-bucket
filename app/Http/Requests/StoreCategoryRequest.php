<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'color_id' => ['nullable', 'exists:colors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi',
            'name.string' => 'Nama kategori harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'type.required' => 'Tipe kategori harus dipilih',
            'type.string' => 'Tipe kategori harus berupa teks',
            'type.max' => 'Maksimal 255 karakter',
            'color_id.exists' => 'Warna tidak valid'
        ];
    }
}
