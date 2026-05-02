<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A collection of tags
 */
final class Tags
{
    public function __construct(
        /**
         * List of tag enums
         *
         * @var list<'featured'|'sale'|'new'|'trending'>
         */
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotNull(),
            new Assert\Choice(choices: ['featured', 'sale', 'new', 'trending']),
        ])]
        public $tags,
    ) {
    }
}
