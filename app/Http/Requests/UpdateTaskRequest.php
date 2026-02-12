<?php

namespace App\Http\Requests;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_description' => ['sometimes', 'required', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'due_date' => ['sometimes', 'required'],
            'notes' => ['nullable', 'max:255'],
            'completed_at' => ['sometimes','nullable']
        ];
    }
}
