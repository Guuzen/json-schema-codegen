<?php

declare(strict_types=1);

namespace App\Dto;

final class CreditCardPayment
{
    public function __construct(
        /**
         * @var string
         */
        public $cardNumber,
    ) {
    }
}
