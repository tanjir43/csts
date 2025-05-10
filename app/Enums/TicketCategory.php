<?php

namespace App\Enums;

enum TicketCategory: string
{
    case TECHNICAL  = 'technical';
    case BILLING    = 'billing';
    case GENERAL    = 'general';
}
