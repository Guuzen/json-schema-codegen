<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * A DTO with explicit default values
 */
class Defaults
{
    public function __construct(
        /**
         * @var string
         */
        public $required,
        /**
         * @var string|null
         */
        public $comment,
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
