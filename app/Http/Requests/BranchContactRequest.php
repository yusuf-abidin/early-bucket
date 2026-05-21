<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'phone' => normalize_indo_phone($this->phone),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'regional_id' => ['required', 'exists:regionals,id'],
            'branch_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'regex:/^628\d{7,12}$/'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['regional_id'] = ['sometimes', 'required', 'exists:regionals,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'regional_id.required' => 'Regional tidak valid',
            'regional_id.exists' => 'Regional tidak valid',
            'branch_name.required' => 'Nama cabang harus diisi',
            'branch_name.max' => 'Maksimal 255 karakter',
            'branch_name.string' => 'Nama cabang harus berupa teks',
            'name.string' => 'Nama kontak harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'nip.numeric' => 'NIP tidak valid',
            'phone.string' => 'Nomor telepon tidak valid',
            'phone.regex' => 'Nomor telepon tidak valid',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran file maksimal 1MB',
            'avatar.mimes' => 'Format file harus jpg, jpeg, atau png',
        ];
    }
}
