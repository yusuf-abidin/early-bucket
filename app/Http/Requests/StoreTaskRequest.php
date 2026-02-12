<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'task_description' => ['required', 'max:255'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'due_date' => ['required'],
            'notes' => ['nullable', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'task_description.required' => 'Agenda tidak boleh kosong',
            'task_description.max' => 'Maksimal 255 karakter',
            'users.*.exists' => 'Pengguna tidak ditemukan',
            'category_id.required' => 'Kategori harus dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'due_date.required' => 'Tanggal harus dipilih',
            'notes.max' => 'Maksimal 255 karakter'
        ];
    }
}
