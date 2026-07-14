<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Available tag types
 */
enum Tag: string
{
    case featured = 'featured';
    case sale = 'sale';
    case new = 'new';
    case trending = 'trending';
}
