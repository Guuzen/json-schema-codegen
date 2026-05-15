<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\address\Address;
use Guuzen\JsonSchemaCodegen\Undefined;

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
         * @var int<1, 100>
         */
        public $quantity,
        /**
         * @var Address
         */
        public $address,
        /**
         * @var list<OrderItem>
         */
        public $items,
        /**
         * @var CreditCardPayment|BankTransferPayment
         */
        public $payment,
        /**
         * @var int<0, max>|null
         */
        public $amount,
        /**
         * @var non-empty-string|Undefined
         */
        public $couponCode = Undefined::Value,
        /**
         * @var Note|null|Undefined
         */
        public $note = Undefined::Value,
    ) {
    }
}
