<?php

declare(strict_types=1);

namespace App\Dto;

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
        public $id,
        /**
         * @var string
         */
        public $name,
    ) {
    }
}
