<?php

declare(strict_types=1);

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;

return new \Guuzen\JsonSchemaCodegen\Config(
    baseNamespace: 'App\Dto',
    schemaPath: new AbsoluteUnixDirectoryPath(__DIR__ . '/schemas'),
    outputPath: new AbsoluteUnixDirectoryPath(__DIR__ . '/schemas'),
    schemaSuffix: '.json',
);
