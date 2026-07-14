<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * A collection of tags
 *
 * @immutable
 */
final class Tags
{
    public function __construct(
        /**
         * List of tag enums
         *
         * @var list<'featured'|'sale'|'new'|'trending'>
         */
        public $tags,
        /**
         * List of tag enums or null
         *
         * Tag
         *
         * @var Tag|null
         */
        public $tag,
    ) {
    }
}
