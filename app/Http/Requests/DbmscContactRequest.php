<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DbmscContactRequest extends FormRequest
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
            'branch_contact_id' => ['required', 'exists:branch_contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'regex:/^628\d{7,12}$/'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['branch_contact_id'] = ['sometimes', 'required', 'exists:branch_contacts,id'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'branch_contact_id.required' => 'Kontak cabang tidak valid',
            'branch_contact_id.exists' => 'Kontak cabang tidak valid',
            'name.required' => 'Nama harus diisi',
            'name.max' => 'Maksimal 255 karakter',
            'name.string' => 'Nama harus berupa teks',
            'nip.numeric' => 'NIP tidak valid',
            'phone.regex' => 'Nomor telepon tidak valid',
            'phone.string' => 'Nomor telepon tidak valid',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.mimes' => 'File harus berupa gambar dengan format jpg, jpeg, atau png',
            'avatar.max' => 'Maksimal ukuran file 1MB',
        ];
    }
}
