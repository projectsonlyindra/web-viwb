<?php

namespace App\Http\Requests;

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use App\Models\Warga;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreWargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Warga::class);
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'digits:16', 'unique:warga,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:20'],
            'unit_id' => ['required', 'regex:/^[A-Z]\d{2}$/', 'unique:warga,unit_id'],
            'jenis_kendaraan' => ['required', new Enum(JenisKendaraan::class)],
            'status_warga' => ['required', new Enum(StatusWarga::class)],
            'ikut_hippam' => ['boolean'],
            'ikut_kebersihan' => ['boolean'],
        ];
    }
}
