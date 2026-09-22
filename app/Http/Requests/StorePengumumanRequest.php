<?php

namespace App\Http\Requests;

use App\Models\Pengumuman;
use Illuminate\Foundation\Http\FormRequest;

class StorePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pengumuman::class);
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'target_blok' => ['nullable', 'string', 'regex:/^[A-Z]$/'],
        ];
    }
}
