<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\address\Address;

final class Order
{
    public function __construct(
        /**
         * @var Customer
         */
        public $customer,
        /**
         * @var 'pending'|'processing'|'shipped'|'delivered'
         */
        public $status,
        /**
         * @var non-empty-string
         */
        public $couponCode,
        /**
         * @var int<1, 100>
         */
        public $quantity,
        /**
         * @var Address
         */
        public $address,
        /**
         * @var Note|null
         */
        public $note,
        /**
         * @var list<OrderItem>
         */
        public $items,
        /**
         * @var CreditCardPayment|BankTransferPayment
         */
        public $payment,
    ) {
    }
}
