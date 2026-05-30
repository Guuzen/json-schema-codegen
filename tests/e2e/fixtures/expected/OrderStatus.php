<?php

declare(strict_types=1);

namespace App\Dto;

final class OrderStatus
{
    public function __construct(
        /**
         * The status of an order
         *
         * @var 'pending'|'processing'|'shipped'|'delivered'
         */
        public $value,
    ) {
    }
}
