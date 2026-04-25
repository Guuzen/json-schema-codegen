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
        public $required,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        public $status = 'active',
        /**
         * @var int
         */
        #[Assert\Type('integer')]
        public $count = 0,
        /**
         * @var bool
         */
        #[Assert\Type('bool')]
        public $enabled = true,
        /**
         * @var string|null
         */
        #[Assert\Type('string')]
        public $nickname = 'anon',
        /**
         * @var string|null|Undefined
         */
        #[Assert\Type('string')]
        public $comment = Undefined::Value,
        /**
         * @var Note
         */
        #[Assert\Type(Note::class)]
        public $note = null,
        /**
         * @var Note
         */
        #[Assert\Type(Note::class)]
        public $pinnedNote = new Note(content: 'pinned'),
    ) {
    }
}
