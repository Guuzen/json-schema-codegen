<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event with a date
 */
final class Event
{
    public function __construct(
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $name,
        /**
         * Date in YYYY-MM-DD format
         *
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        #[Assert\Date]
        public $date,
    ) {
    }
}
