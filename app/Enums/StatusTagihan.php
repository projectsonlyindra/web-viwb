<?php

namespace App\Enums;

enum StatusTagihan: string
{
    case BELUM_BAYAR = 'BELUM_BAYAR';
    case SEBAGIAN = 'SEBAGIAN';
    case LUNAS = 'LUNAS';
}
