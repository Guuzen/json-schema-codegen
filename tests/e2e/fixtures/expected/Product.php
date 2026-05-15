<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * A sellable product
 */
final class Product
{
    public function __construct(
        /**
         * The product name
         *
         * @var non-empty-string
         */
        public $name,
        /**
         * @var string
         */
        public $code,
        /**
         * @var string|null
         */
        public $nickname,
        /**
         * @var int
         */
        public $stock,
        /**
         * @var int<0, 1000>
         */
        public $quantity,
        /**
         * @var int|null
         */
        public $rating,
        /**
         * @var float
         */
        public $price,
        /**
         * @var float|null
         */
        public $discount,
        /**
         * @var bool
         */
        public $active,
        /**
         * @var bool|null
         */
        public $featured,
        /**
         * @var list<string>
         */
        public $tags,
        /**
         * @var non-empty-list<string>
         */
        public $requiredTags,
        /**
         * @var list<mixed>
         */
        public $data,
        /**
         * @var int|string
         */
        public $externalId,
    ) {
    }
}
