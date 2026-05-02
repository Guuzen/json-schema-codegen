<?php

declare(strict_types=1);

namespace App\Dto\address;

use Symfony\Component\Validator\Constraints as Assert;

final class Address
{
    public function __construct(
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $street,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $city,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $country,
    ) {
    }
}
