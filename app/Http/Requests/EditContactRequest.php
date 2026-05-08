<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditContactRequest extends FormRequest
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
        $ignoreId = $this->route('contactCluster')?->id;
        return [
            'regional_id' => ['nullable', 'exists:regionals,id', 'required_without_all:area_id,branch_id', Rule::unique('contact_clusters', 'regional_id')->ignore($ignoreId)],
            'area_id' => ['nullable', 'exists:areas,id', 'required_without_all:regional_id,branch_id', Rule::unique('contact_clusters', 'area_id')->ignore($ignoreId),],
            'branch_id' => ['nullable', 'exists:branches,id', 'required_without_all:regional_id,area_id'], Rule::unique('contact_clusters', 'branch_id')->ignore($ignoreId),
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'regex:/^628\d{7,12}$/'],
            'avatar' => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'regional_id.exists' => 'Regional tidak valid',
            'regional_id.required_without_all' => 'Regional atau area atau cabang tidak sesuai',
            'area_id.exists' => 'Area tidak valid',
            'area_id.required_without_all' => 'Regional atau area atau cabang tidak sesuai',
            'branch_id.exists' => 'Cabang tidak valid',
            'branch_id.required_without_all' => 'Regional atau area atau cabang tidak sesuai',
            'name.required' => 'Nama kontak harus diisi',
            'name.string' => 'Nama kontak harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'nip.numeric' => 'NIP tidak valid',
            'phone.string' => 'Nomor telepon tidak valid',
            'phone.regex' => 'Nomor telepon tidak valid',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran file maksimal 1 MB',
            'avatar.mimes' => 'File harus berupa jpg, jpeg, atau png',
        ];
    }
}
