<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoRequest extends FormRequest
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
            'users' => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],
            'document_link' => ['nullable', 'url', 'max:2048'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'due_date' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'origin' => ['sometimes', 'max:255', 'string', 'nullable'],
            'reference_number' => ['string', 'max:255', 'nullable'],
            'subject' => ['string', 'max:255', 'nullable'],
            'follow_up_note' => ['nullable', 'string', 'max:255'],
            'completed_at' => ['nullable', 'date', 'date_format:Y-m-d H:i:s']
        ];
    }

    public function messages(): array
    {
        return [
            'received_at.required' => 'Tanggal masuk harus diisi',
            'received_at.date' => 'Tanggal masuk harus berupa tanggal',
            'users.*.exists' => 'Pengguna tidak ditemukan',
            'document_link.url' => 'Link tidak valid',
            'document_link.max' => 'Maksimal 2048 karakter',
            'category_id.required' => 'Sifat harus dipilih',
            'category_id.exists' => 'Sifat tidak valid',
            'due_date.date' => 'Deadline harus berupa tanggal',
            'origin.string' => 'Asal harus berupa teks',
            'origin.max' => 'Maksimal 255 karakter',
            'reference_number.string' => 'Nomor harus berupa teks',
            'reference_number.max' => 'Maksimal 255 karakter',
            'subject.string' => 'Subjek harus berupa teks',
            'subject.max' => 'Maksimal 255 karakter',
            'follow_up_note.string' => 'Tindak lanjut harus berupa teks',
            'follow_up_note.max' => 'Maksimal 255 karakter',
            'completed_at.date' => 'Tanggal selesai harus berupa tanggal',
        ];
    }
}
