<?php

declare(strict_types=1);

namespace App\Dto;

use Guuzen\JsonSchemaCodegen\Undefined;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A DTO with explicit default values
 */
final class Defaults
{
    public function __construct(
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $required,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
        public $status = 'active',
        /**
         * @var int
         */
        #[Assert\Type('integer')]
        #[Assert\NotNull]
        public $count = 0,
        /**
         * @var bool
         */
        #[Assert\Type('bool')]
        #[Assert\NotNull]
        public $enabled = true,
        /**
         * @var string|null
         */
        #[Assert\Type('string')]
        public $nickname = 'anon',
        /**
         * @var string|null|Undefined
         */
        #[Assert\Type(['string', Undefined::class])]
        public $comment = Undefined::Value,
        /**
         * @var Note
         */
        #[Assert\Type(Note::class)]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $note = null,
        /**
         * @var Note
         */
        #[Assert\Type(Note::class)]
        #[Assert\NotNull]
        #[Assert\Valid]
        public $pinnedNote = new Note(content: 'pinned'),
    ) {
    }
}
