<?php

declare(strict_types=1);

namespace App\Dto;

use Guuzen\JsonSchemaCodegen\Undefined;

/**
 * A DTO with explicit default values
 */
final class Defaults
{
    public function __construct(
        /**
         * @var string
         */
        public $required,
        /**
         * @var string
         */
        public $status = 'active',
        /**
         * @var int
         */
        public $count = 0,
        /**
         * @var bool
         */
        public $enabled = true,
        /**
         * @var string|null
         */
        public $nickname = 'anon',
        /**
         * @var string|null|Undefined
         */
        public $comment = Undefined::Value,
        /**
         * @var Note
         */
        public $note = null,
        /**
         * @var Note
         */
        public $pinnedNote = new Note(content: 'pinned'),
    ) {
    }
}
