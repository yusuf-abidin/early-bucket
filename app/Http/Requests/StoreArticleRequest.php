<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'status' => ['required', 'in:draft,published'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul news harus diisi',
            'title.max' => 'Judul news tidak boleh lebih dari 255 karakter',
            'content.required' => 'Konten news harus diisi',
            'image.image' => 'File yang diunggah harus berupa gambar',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 1 MB',
            'status.in' => 'Status tidak valid'
        ];
    }
}
