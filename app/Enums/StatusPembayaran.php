<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';
    case DIKONFIRMASI = 'DIKONFIRMASI';
    case DITOLAK = 'DITOLAK';
}
