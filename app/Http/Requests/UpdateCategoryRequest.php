<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends StoreCategoryRequest
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
        $rules = parent::rules();
        unset($rules['type']);
        $rules['order'] = ['nullable', 'integer', 'min:1'];
        return $rules;
    }

    public function messages(): array
    {
        return [
            'order.integer' => 'Nomor urut harus berupa angka',
            'order.min' => 'Nomor urut minimal 1'
        ];
    }
}
