<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

/**
 * @template Context
 */
interface DefaultValueFactory
{
    /**
     * @return AddDefaultValue<Context>
     */
    public function defaultValue(mixed $value): AddDefaultValue;

    /**
     * @param array<array-key, mixed> $args
     *
     * @return AddDefaultValue<Context>
     */
    public function newObjectDefaultValue(string $className, array $args): AddDefaultValue;

    /**
     * @return AddDefaultValue<Context>
     */
    public function noDefaultValue(): AddDefaultValue;
}
