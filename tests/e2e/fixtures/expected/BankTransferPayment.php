<?php

declare(strict_types=1);

namespace App\Dto;

class BankTransferPayment
{
    public function __construct(
        /**
         * @var string
         */
        public $iban,
    ) {
    }
}
