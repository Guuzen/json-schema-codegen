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
        #[Assert\NotNull]
        public $name,
        /**
         * @var string
         */
        #[Assert\Type('string')]
        #[Assert\NotNull]
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
        #[Assert\NotNull]
        public $stock,
        /**
         * @var int<0, 1000>
         */
        #[Assert\Type('integer')]
        #[Assert\NotNull]
        #[Assert\GreaterThanOrEqual(0)]
        #[Assert\LessThanOrEqual(1000)]
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
        #[Assert\NotNull]
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
        #[Assert\NotNull]
        public $active,
        /**
         * @var bool|null
         */
        #[Assert\Type('bool')]
        public $featured,
        /**
         * @var list<string>
         */
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\All([new Assert\Type('string'), new Assert\NotNull()])]
        public $tags,
        /**
         * @var non-empty-list<string>
         */
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\All([new Assert\Type('string'), new Assert\NotNull()])]
        public $requiredTags,
        /**
         * @var list<mixed>
         */
        #[Assert\Type('list')]
        #[Assert\NotNull]
        #[Assert\All([new Assert\NotNull()])]
        public $data,
        /**
         * @var int|string
         */
        #[Assert\Type(['integer', 'string'])]
        #[Assert\NotNull]
        public $externalId,
    ) {
    }
}
