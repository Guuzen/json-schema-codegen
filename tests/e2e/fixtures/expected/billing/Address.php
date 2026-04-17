<?php

declare(strict_types=1);

namespace App\Dto\billing;

class Address
{
    public function __construct(
        /**
         * @var string
         */
        public $street,
        /**
         * @var string
         */
        public $city,
        /**
         * @var string
         */
        public $country,
    ) {
    }
}
