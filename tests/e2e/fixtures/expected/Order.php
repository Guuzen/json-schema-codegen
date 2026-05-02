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
        #[Assert\NotNull]
        #[Assert\Valid]
        public $customer,
        /**
         * @var 'pending'|'processing'|'shipped'|'delivered'
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        #[Assert\Choice(choices: ['pending', 'processing', 'shipped', 'delivered'])]
        public $status,
        /**
         * @var int<1, 100>
         */
        #[Assert\Type('integer')]
        #[Assert\NotNull]
        public $quantity,
        /**
         * @var Address
         */
        #[Assert\Type(Address::class)]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $address,
        /**
         * @var list<OrderItem>
         */
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $items,
        /**
         * @var CreditCardPayment|BankTransferPayment
         */
        #[Assert\Type([CreditCardPayment::class, BankTransferPayment::class])]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $payment,
        /**
         * @var non-empty-string|Undefined
         */
        #[Assert\Type(['string', Undefined::class])]
        #[Assert\NotNull]
        public $couponCode = Undefined::Value,
        /**
         * @var Note|null|Undefined
         */
        #[Assert\Type([Note::class, Undefined::class])]
        #[Assert\Valid]
        public $note = Undefined::Value,
    ) {
    }
}
