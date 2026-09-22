<?php

namespace App\Enums;

enum StatusWarga: string
{
    case AKTIF = 'AKTIF';
    case PINDAH = 'PINDAH';
    case KONTRAK = 'KONTRAK';
}
