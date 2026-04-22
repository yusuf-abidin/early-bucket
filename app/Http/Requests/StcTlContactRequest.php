<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StcTlContactRequest extends FormRequest
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
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'regex:/^628\d{7,12}$/'],
            'role' => ['required', 'string', 'in:STC,TL'],
        ];
    }

    public function messages()
    {
        return [
            'branch_id.required' => 'Cabang tidak valid',
            'branch_id.exists' => 'Cabang tidak valid',
            'name.required' => 'Nama kontak harus diisi',
            'name.string' => 'Nama kontak harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'nip.numeric' => 'NIP tidak valid',
            'phone.string' => 'Nomor telepon tidak valid',
            'phone.regex' => 'Nomor telepon tidak valid',
            'role.required' => 'Tipe Kontak harus valid',
            'role.in' => 'Tipe Kontak harus STC / TL',
        ];
    }
}
