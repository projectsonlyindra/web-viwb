<?php

namespace App\Http\Requests;

use App\Models\Pengeluaran;
use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pengeluaran::class);
    }

    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'max:255'],
            'keterangan' => ['required', 'string'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'bukti' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }
}
