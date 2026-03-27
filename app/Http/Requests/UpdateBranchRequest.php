<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'area_id' => ['sometimes', 'nullable', 'exists:areas,id'],
            'regional_id' => ['sometimes', 'required', 'exists:regionals,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama cabang harus diisi',
            'name.string' => 'Nama cabang harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
            'regional_id.required' => 'Regional harus dipilih',
            'regional_id.exists' => 'Regional tidak valid',
            'area_id.exists' => 'Area tidak valid'
        ];
    }
}
