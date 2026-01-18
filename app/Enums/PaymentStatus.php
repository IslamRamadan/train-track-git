<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = '1';
    case PAID = '2';
    case REFUNDED = '3';
}
