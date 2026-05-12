<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreUserRequest extends FormRequest
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
            'position' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user,rlqh'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'position.required' => 'Jabatan harus diisi',
            'position.max' => 'Maksimal 255 karakter',
            'position.string' => 'Jabatan harus berupa teks',
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa teks',
            'email.email' => 'Email tidak valid',
            'email.max' => 'Maksimal 255 karakter',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role harus dipilih',
            'role.in' => 'Role tidak valid',
            'role.string' => 'Role harus berupa teks',
            'color_id.exists' => 'Warna tidak valid',
            'avatar.image' => 'Avatar harus berupa gambar',
            'avatar.max' => 'Maksimal 1 MB',
            'avatar.mimes' => 'Format avatar harus jpg, jpeg, atau png'
        ];
    }
}
