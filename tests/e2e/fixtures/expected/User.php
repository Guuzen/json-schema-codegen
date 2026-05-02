<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user with a UUID identifier
 */
final class User
{
    public function __construct(
        /**
         * The user's unique identifier
         *
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        #[Assert\Uuid]
        public $id,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $name,
    ) {
    }
}
