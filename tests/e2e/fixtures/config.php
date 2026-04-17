<?php

declare(strict_types=1);

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

return new \Guuzen\JsonSchemaCodegen\Config(
    baseNamespace: 'App\Dto',
    schemaPath: new AbsoluteUnixDirectoryPath(new AbsoluteUnixPath(__DIR__ . '/schemas')),
    outputPath: new AbsoluteUnixDirectoryPath(new AbsoluteUnixPath(__DIR__ . '/schemas')),
    schemaSuffix: '.json',
);
