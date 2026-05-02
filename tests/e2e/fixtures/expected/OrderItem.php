<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderItem
{
    public function __construct(
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $productId,
        /**
         * @var int
         */
        #[Assert\Type('integer')]
        #[Assert\NotNull]
        public $quantity,
    ) {
    }
}
