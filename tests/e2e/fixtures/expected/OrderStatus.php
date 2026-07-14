<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * The status of an order
 */
enum OrderStatus: string
{
    case pending = 'pending';
    case processing = 'processing';
    case shipped = 'shipped';
    case delivered = 'delivered';
}
