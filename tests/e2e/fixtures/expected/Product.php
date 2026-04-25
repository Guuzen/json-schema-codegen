<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

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
        #[Assert\Type('string')]
        public $name,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        public $code,
        /**
         * @var string|null
         */
        #[Assert\Type('string')]
        public $nickname,
        /**
         * @var int
         */
        #[Assert\Type('integer')]
        public $stock,
        /**
         * @var int<0, 1000>
         */
        #[Assert\Type('integer')]
        public $quantity,
        /**
         * @var int|null
         */
        #[Assert\Type('integer')]
        public $rating,
        /**
         * @var float
         */
        #[Assert\Type('float')]
        public $price,
        /**
         * @var float|null
         */
        #[Assert\Type('float')]
        public $discount,
        /**
         * @var bool
         */
        #[Assert\Type('bool')]
        public $active,
        /**
         * @var bool|null
         */
        #[Assert\Type('bool')]
        public $featured,
        /**
         * @var list<string>
         */
        #[Assert\Type('array')]
        public $tags,
        /**
         * @var non-empty-list<string>
         */
        #[Assert\Type('array')]
        public $requiredTags,
        /**
         * @var list<mixed>
         */
        #[Assert\Type('array')]
        public $data,
        /**
         * @var int|string
         */
        public $externalId,
    ) {
    }
}
