## The Problem

When your application communicates with external systems - APIs, message queues, event streams - you typically define data contracts using JSON Schema. On the PHP side, you represent those contracts as DTO classes with typed properties.

The problem is keeping both in sync. Every time the schema changes, you have to manually update the corresponding PHP class: rename properties, adjust types, add or remove fields. This is repetitive, error-prone, and easy to forget. The schema and the code silently drift apart, and type mismatches only surface at runtime.

## The Solution

`JSON Schema Codegen` generates PHP DTO classes directly from your JSON Schema files. Run the generator after any schema change and your PHP classes are always up to date - no manual editing required.

The generator maps JSON Schema types to precise PHPDoc annotations that static analysis tools like PHPStan understand:

- `"type": "string"` → `@var string`
- `"type": "string", "minLength": 1` → `@var non-empty-string`
- `"type": "integer", "minimum": 0, "maximum": 100"` → `@var int<0, 100>`
- `"type": ["string", "null"]` → `@var string|null`
- `"type": "array", "items": {"type": "string"}` → `@var list<string>`
- `"oneOf": [{"$ref": "A.json"}, {"$ref": "B.json"}]` → `@var A|B`
- `"$ref": "Other.json"` → `@var Other`

## Requirements

- PHP 8.4+
- `nette/php-generator: ^4.2` - used by the built-in generator implementation
- `symfony/yaml: ^8.0` - optional, required only for YAML schema support

## Installation

```bash
composer require guuzen/json-schema-codegen
composer require nette/php-generator
```

## Quick Start

Create a PHP script that configures and runs the generator:

```php
<?php

require 'vendor/autoload.php';

use Guuzen\JsonSchemaCodegen\Config;
use Guuzen\JsonSchemaCodegen\Nette\NetteFilesGeneratorFactory;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

$config = new Config(
    baseNamespace: 'App\Dto',
    schemaPath: new AbsoluteUnixDirectoryPath(new AbsoluteUnixPath('/path/to/schemas')),
    outputPath: new AbsoluteUnixDirectoryPath(new AbsoluteUnixPath('/path/to/output')),
    schemaSuffix: '.json',
);

NetteFilesGeneratorFactory::default($config)->run();
```

Run it with `php generate.php` whenever your schemas change.

## Configuration

| Option | Type | Description |
|---|---|---|
| `baseNamespace` | `string` | Root namespace for all generated classes |
| `schemaPath` | `AbsoluteUnixDirectoryPath` | Directory containing your schema files |
| `outputPath` | `AbsoluteUnixDirectoryPath` | Directory where generated PHP files are written |
| `schemaSuffix` | `string` | File extension used to identify schema files (e.g. `.json`) |

Schema files in subdirectories of `schemaPath` produce classes in sub-namespaces. For example, `schemas/billing/Address.json` generates `App\Dto\billing\Address`.

## Example

Given this schema at `schemas/Product.json`:

```json
{
    "description": "A sellable product",
    "type": "object",
    "properties": {
        "name":      {"type": "string", "minLength": 1, "description": "The product name"},
        "stock":     {"type": "integer"},
        "quantity":  {"type": "integer", "minimum": 0, "maximum": 1000},
        "rating":    {"type": ["integer", "null"]},
        "price":     {"type": "number"},
        "tags":      {"type": "array", "items": {"type": "string"}},
        "externalId":{"type": ["integer", "string"]}
    }
}
```

The generator produces `output/Product.php`:

```php
<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * A sellable product
 */
class Product
{
    public function __construct(
        /**
         * The product name
         *
         * @var non-empty-string
         */
        public $name,
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
         * @var list<string>
         */
        public $tags,
        /**
         * @var int|string
         */
        public $externalId,
    ) {
    }
}
```

Cross-file references via `$ref` are also resolved. Given `schemas/Order.json`:

```json
{
    "type": "object",
    "properties": {
        "customer": {"$ref": "Customer.json"},
        "note":     {"oneOf": [{"$ref": "Note.json"}, {"type": "null"}]},
        "items":    {"type": "array", "items": {"$ref": "OrderItem.json"}},
        "payment":  {"oneOf": [{"$ref": "CreditCardPayment.json"}, {"$ref": "BankTransferPayment.json"}]}
    }
}
```

The generator produces:

```php
class Order
{
    public function __construct(
        /** @var Customer */
        public $customer,
        /** @var Note|null */
        public $note,
        /** @var list<OrderItem> */
        public $items,
        /** @var CreditCardPayment|BankTransferPayment */
        public $payment,
    ) {
    }
}
```

## YAML Support

To use YAML schema files, install `symfony/yaml` and use `YamlDecoder`:

```bash
composer require symfony/yaml
```

```php
use Guuzen\JsonSchemaCodegen\Nette\NetteFilesGeneratorFactory;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder\YamlDecoder;

NetteFilesGeneratorFactory::withDecoder($config, new YamlDecoder())->run();
```

## Running Tests

```bash
composer install
./vendor/bin/phpunit
```
