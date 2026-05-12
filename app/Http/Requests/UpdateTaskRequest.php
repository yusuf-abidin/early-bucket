<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scope' => [Rule::in([Task::SCOPE_CENTRAL, Task::SCOPE_RLQH])],
            'task_description' => ['sometimes', 'required', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'due_date' => ['sometimes', 'required'],
            'notes' => ['nullable', 'max:255'],
            'completed_at' => ['sometimes','nullable']
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
