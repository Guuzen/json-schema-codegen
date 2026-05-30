<?php

declare(strict_types=1);

namespace App\Dto;

final class Cent
{
    public function __construct(
        /**
         * @var int<0, max>
         */
        public $value,
    ) {
    }
}
