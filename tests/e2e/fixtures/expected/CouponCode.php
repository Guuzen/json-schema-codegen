<?php

declare(strict_types=1);

namespace App\Dto;

final class CouponCode
{
    public function __construct(
        /**
         * A promotional coupon code
         *
         * @var non-empty-string
         */
        public $value,
    ) {
    }
}
