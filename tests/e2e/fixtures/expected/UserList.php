<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * A list of user identifiers
 */
final class UserList
{
    public function __construct(
        /**
         * List of user UUIDs
         *
         * @var list<string>
         */
        public $userIds,
    ) {
    }
}
