<?php

declare(strict_types=1);

namespace App\Dto;

final class OrderItem
{
    public function __construct(
        /**
         * @var string
         */
        public $productId,
        /**
         * @var int
         */
        public $quantity,
    ) {
    }
}
