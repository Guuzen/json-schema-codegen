<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\address\Address;
use Guuzen\JsonSchemaCodegen\Undefined;
use Symfony\Component\Validator\Constraints as Assert;

final class Order
{
    public function __construct(
        /**
         * @var Customer
         */
        #[Assert\Type(Customer::class)]
        public $customer,
        /**
         * @var 'pending'|'processing'|'shipped'|'delivered'
         */
        public $status,
        /**
         * @var int<1, 100>
         */
        #[Assert\Type('integer')]
        public $quantity,
        /**
         * @var Address
         */
        #[Assert\Type(Address::class)]
        public $address,
        /**
         * @var list<OrderItem>
         */
        #[Assert\Type('array')]
        public $items,
        /**
         * @var CreditCardPayment|BankTransferPayment
         */
        public $payment,
        /**
         * @var non-empty-string|Undefined
         */
        #[Assert\Type('string')]
        public $couponCode = Undefined::Value,
        /**
         * @var Note|null|Undefined
         */
        #[Assert\Type(Note::class)]
        public $note = Undefined::Value,
    ) {
    }
}
