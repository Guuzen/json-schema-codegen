<?php

declare(strict_types=1);

namespace App\Dto;

class CreditCardPayment
{
    public function __construct(
        /**
         * @var string
         */
        public $cardNumber,
    ) {
    }
}
