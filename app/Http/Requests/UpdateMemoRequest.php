<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoRequest extends StoreMemoRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'received_at' => ['sometimes', 'required', 'date', 'date_format:Y-m-d H:i:s'],
            'origin' => ['sometimes', 'max:255', 'string', 'nullable'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'completed_at' => ['nullable', 'date', 'date_format:Y-m-d H:i:s']
        ];
    }

    public function messages(): array
    {
        return [
            'received_at.required' => 'Tanggal masuk harus diisi',
            'received_at.date' => 'Tanggal masuk harus berupa tanggal',
            'origin.string' => 'Asal harus berupa teks',
            'origin.max' => 'Maksimal 255 karakter',
            'category_id.required' => 'Sifat harus dipilih',
            'category_id.exists' => 'Sifat tidak valid',
            'completed_at.date' => 'Tanggal selesai harus berupa tanggal',
        ];
    }
}
