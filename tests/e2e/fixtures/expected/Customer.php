<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @immutable
 */
final class Customer
{
    public function __construct(
        /**
         * @var address\Address
         */
        public $homeAddress,
        /**
         * @var billing\Address
         */
        public $billingAddress,
    ) {
    }
}
