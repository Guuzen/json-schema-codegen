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
         * @var OrderStatus
         */
        public $status,
        /**
         * @var Quantity
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
         * @var Cent|null
         */
        public $amount,
        /**
         * @var CouponCode|Undefined
         */
        public $couponCode = new Undefined(),
        /**
         * @var Note|null|Undefined
         */
        public $note = new Undefined(),
    ) {
    }
}
