<?php

namespace App\Http\Requests;

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateWargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('warga'));
    }

    public function rules(): array
    {
        $warga = $this->route('warga');

        return [
            'nik' => ['required', 'digits:16', Rule::unique('warga', 'nik')->ignore($warga)],
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:20'],
            'unit_id' => ['required', 'regex:/^[A-Z]\d{2}$/', Rule::unique('warga', 'unit_id')->ignore($warga)],
            'jenis_kendaraan' => ['required', new Enum(JenisKendaraan::class)],
            'status_warga' => ['required', new Enum(StatusWarga::class)],
            'ikut_hippam' => ['boolean'],
            'ikut_kebersihan' => ['boolean'],
        ];
    }
}
