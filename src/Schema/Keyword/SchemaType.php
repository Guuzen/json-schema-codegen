<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema\Keyword;

enum SchemaType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Number = 'number';
    case Boolean = 'boolean';
    case Object = 'object';
    case Array = 'array';
    case Null = 'null';
}
