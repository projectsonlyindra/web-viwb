<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pembayaran::class);
    }

    public function rules(): array
    {
        return [
            'tagihan_ids' => ['required', 'array', 'min:1'],
            'tagihan_ids.*' => ['integer', 'exists:tagihan,id'],
            'bukti' => ['nullable', 'file', 'image', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()->role === Role::WARGA && ! $this->user()->warga_id) {
                $validator->errors()->add('tagihan_ids', 'Akun Anda belum terhubung dengan data profil warga.');
            }
        });
    }
}
