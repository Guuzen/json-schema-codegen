<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

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
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\All([new Assert\Type('string'), new Assert\NotNull(), new Assert\Uuid()])]
        public $userIds,
    ) {
    }
}
