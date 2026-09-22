<?php

namespace App\Enums;

enum Role: string
{
    case SUPERADMIN = 'SUPERADMIN';
    case KETUA_RT = 'KETUA_RT';
    case BENDAHARA = 'BENDAHARA';
    case TIM_DIVISI = 'TIM_DIVISI';
    case WARGA = 'WARGA';
}
