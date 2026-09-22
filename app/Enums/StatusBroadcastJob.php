<?php

namespace App\Enums;

enum StatusBroadcastJob: string
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case DONE = 'DONE';
    case FAILED = 'FAILED';
}
