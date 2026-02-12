<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.max' => 'Maksimal 255 karakter',
            'name.string' => 'Nama harus berupa teks',
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa teks',
            'email.lowercase' => 'Email harus berupa huruf kecil',
            'email.email' => 'Email tidak valid',
            'email.max' => 'Maksimal 255 karakter',
            'email.unique' => 'Email sudah digunakan'
        ];
    }
}
