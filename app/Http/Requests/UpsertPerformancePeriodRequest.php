<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertPerformancePeriodRequest extends FormRequest
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
            'id' => ['nullable', 'exists:performance_periods,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer'],
            'performance_type' => ['required', 'in:etape_1,etape_2,etape_3,eom'],
            'start_date' => ['nullable', 'integer', 'min:1', 'max:31'],
            'end_date' => ['nullable', 'integer', 'min:1', 'max:31'],
        ];
    }

    public function messages()
    {
        return [
            'id.exists' => 'Periode yang dipilih tidak ditemukan.',
            'month.required' => 'Bulan harus diisi jika periode tidak dipilih',
            'month.integer' => 'Bulan harus berupa angka.',
            'month.min' => 'Bulan harus antara 1 sampai 12.',
            'month.max' => 'Bulan harus antara 1 sampai 12.',
            'year.required' => 'Tahun harus diisi jika periode tidak dipilih',
            'year.integer' => 'Tahun harus berupa angka.',
            'performance_type.required' => 'Tipe performa harus diisi jika periode belum ditentukan',
            'performance_type.in' => 'Tipe performa harus salah satu dari: etape_1, etape_2, etape_3, eom.',
            'start_date.integer' => 'Tanggal mulai harus berupa angka.',
            'start_date.min' => 'Tanggal mulai harus antara 1 sampai 31.',
            'start_date.max' => 'Tanggal mulai harus antara 1 sampai 31.',
            'end_date.integer' => 'Tanggal selesai harus berupa angka.',
            'end_date.min' => 'Tanggal selesai harus antara 1 sampai 31.',
            'end_date.max' => 'Tanggal selesai harus antara 1 sampai 31.',
        ];
    }
}
