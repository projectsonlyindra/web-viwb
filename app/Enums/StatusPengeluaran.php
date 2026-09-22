<?php

namespace App\Enums;

enum StatusPengeluaran: string
{
    case DRAFT = 'DRAFT';
    case MENUNGGU_APPROVAL = 'MENUNGGU_APPROVAL';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
}
