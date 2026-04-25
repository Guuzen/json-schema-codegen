<?php

declare(strict_types=1);

namespace App\Dto;

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
        public $status,
        /**
         * @var int
         */
        public $count,
        /**
         * @var bool
         */
        public $enabled,
        /**
         * @var string|null
         */
        public $nickname,
        /**
         * @var string|null
         */
        public $comment,
        /**
         * @var Note
         */
        public $note,
        /**
         * @var Note
         */
        public $pinnedNote,
    ) {
    }
}
