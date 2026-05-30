<?php

declare(strict_types=1);

namespace App\Dto;

final class Quantity
{
    public function __construct(
        /**
         * Quantity ordered
         *
         * @var int<1, 100>
         */
        public $value,
    ) {
    }
}
