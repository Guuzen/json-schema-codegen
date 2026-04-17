<?php

declare(strict_types=1);

namespace App\Dto;

class Note
{
    public function __construct(
        /**
         * @var string
         */
        public $content,
    ) {
    }
}
