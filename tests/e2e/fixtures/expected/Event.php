<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeImmutable;

/**
 * An event with a date
 */
final class Event
{
    public function __construct(
        /**
         * @var string
         */
        public $name,
        /**
         * Date in YYYY-MM-DD format
         *
         * @var string
         */
        public $date,
        /**
         * Start timestamp in ATOM format
         *
         * @var string
         */
        public $startsAt,
        /**
         * Slug like 123-456
         *
         * @var string
         */
        public $slug,
        /**
         * @var DateTimeImmutable
         */
        public $createdAt,
    ) {
    }
}
