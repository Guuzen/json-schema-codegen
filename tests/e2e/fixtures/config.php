<?php

declare(strict_types=1);

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;

return new \Guuzen\JsonSchemaCodegen\Config(
    baseNamespace: 'App\Dto',
    schemaPath: new AbsoluteUnixDirectoryPath(__DIR__ . '/schemas'),
    outputPath: new AbsoluteUnixDirectoryPath(__DIR__ . '/schemas'),
    schemaSuffix: '.json',
    undefinedPath: new RelativeUnixPath('Undefined.json'),
    typeMappings: ['DateTimeImmutable.json' => DateTimeImmutable::class],
);
