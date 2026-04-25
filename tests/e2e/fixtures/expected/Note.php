<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class Note
{
    public function __construct(
        /**
         * @var string
         */
        #[Assert\Type('string')]
        public $content,
    ) {
    }
}
