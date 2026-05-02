<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\address\Address as HomeAddress;
use App\Dto\billing\Address as BillingAddress;
use Symfony\Component\Validator\Constraints as Assert;

final class Customer
{
    public function __construct(
        /**
         * @var HomeAddress
         */
        #[Assert\Type(HomeAddress::class)]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $homeAddress,
        /**
         * @var BillingAddress
         */
        #[Assert\Type(BillingAddress::class)]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $billingAddress,
    ) {
    }
}
