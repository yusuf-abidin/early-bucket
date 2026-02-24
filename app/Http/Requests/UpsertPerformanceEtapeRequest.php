<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPerformanceEtapeRequest extends FormRequest
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
            'branch_id' => ['required', 'exists:branches,id'],
            'etape_no' => ['required', Rule::in(['1', '2', '3', 'eom', 'program_khusus'])],
            'year' => ['required', 'integer', 'min:2020', 'max:' . (now()->year + 10)],
            'month' => ['required', 'integer', 'between:1,12'],
            'user_id' => ['nullable', 'exists:users,id'],
            'komitmen_etape_bc_id' => ['nullable', 'exists:categories,id'],
            'komitmen_etape_bm_id' => ['nullable', 'exists:categories,id'],
            'prognosa_akhir_bulan' => ['nullable', 'numeric'],
            'kendala' => ['nullable', 'string'],

        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang harus dipilih',
            'branch_id.exists' => 'Cabang tidak valid',
            'etape_no.in' => 'Etap tidak valid',
            'year.min' => 'Tahun minimal 2020',
            'year.max' => 'Tahun maksimal ' . (now()->year + 10),
            'year.integer' => 'Tahun harus berupa angka',
            'month.integer' => 'Bulan harus berupa angka',
            'month.between' => 'Bulan harus antara 1 sampai 12',
            'user_id.exists' => 'Pengguna tidak valid',
            'komitmen_etape_bc_id.exists' => 'Komitmen EOM BC tidak valid',
            'komitmen_etape_bm_id.exists' => 'Komitmen EOM BM tidak valid',
            'prognosa_akhir_bulan.numeric' => 'Prognosa akhir bulan harus berupa angka',
            'kendala.string' => 'Kendala harus berupa teks'
        ];
    }
}
