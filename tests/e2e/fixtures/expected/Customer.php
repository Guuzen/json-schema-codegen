<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\address\Address as HomeAddress;
use App\Dto\billing\Address as BillingAddress;

final class Customer
{
    public function __construct(
        /**
         * @var HomeAddress
         */
        public $homeAddress,
        /**
         * @var BillingAddress
         */
        public $billingAddress,
    ) {
    }
}
