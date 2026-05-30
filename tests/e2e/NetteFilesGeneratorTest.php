<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\E2e;

use Guuzen\JsonSchemaCodegen\Nette\NetteFilesGeneratorFactory;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end test for the generate-dtos CLI command.
 *
 * Verifies that running the command against a directory of JSON Schema files
 * produces the correct PHP DTO classes next to each schema file.
 *
 * Together the fixture schemas cover:
 * - Product: all scalar property types (string, non-empty-string, int, int range,
 *            float, bool), nullable variants, array types (list<T>, non-empty-list<T>,
 *            untyped list), union type (int|string), class description, property description
 * - OrderStatus: string enum with class description
 * - CouponCode: standalone string schema referenced from Order
 * - Quantity: standalone integer schema referenced from Order
 * - Customer: $ref with title aliases, same class name from different sub-namespaces
 * - Order: $ref same namespace, $ref cross-namespace, nullable $ref (anyOf ref+null),
 *          list<$ref items>, anyOf two $ref branches
 * - Defaults: scalar defaults, $ref with null default, $ref with object literal default (new ClassName(...))
 * - Note, OrderItem, CreditCardPayment, BankTransferPayment: simple helper objects
 * - address/Address, billing/Address: schemas in subdirectories (namespace derivation)
 */
final class NetteFilesGeneratorTest extends TestCase
{
    private const string EXPECTED = __DIR__ . '/fixtures/expected/';

    private const string SCHEMAS = __DIR__ . '/fixtures/schemas/';

    private const array FILES = [
        // Product: all scalar types, array types, class description, property description
        self::EXPECTED . 'Product.php' => self::SCHEMAS . 'Product.php',
        // Customer: $ref with title aliases, same class name from different namespaces
        self::EXPECTED . 'Customer.php' => self::SCHEMAS . 'Customer.php',
        // Order: $ref same/cross namespace, scalar/enum refs as classes, nullable $ref, list<$ref>, anyOf two refs
        self::EXPECTED . 'Order.php' => self::SCHEMAS . 'Order.php',
        // Scalar/enum helper schemas, each generated as a single-value object
        self::EXPECTED . 'Quantity.php' => self::SCHEMAS . 'Quantity.php',
        self::EXPECTED . 'OrderStatus.php' => self::SCHEMAS . 'OrderStatus.php',
        self::EXPECTED . 'Cent.php' => self::SCHEMAS . 'Cent.php',
        self::EXPECTED . 'CouponCode.php' => self::SCHEMAS . 'CouponCode.php',
        // Simple helper schemas referenced by Order and Customer
        self::EXPECTED . 'Note.php' => self::SCHEMAS . 'Note.php',
        self::EXPECTED . 'OrderItem.php' => self::SCHEMAS . 'OrderItem.php',
        self::EXPECTED . 'CreditCardPayment.php' => self::SCHEMAS . 'CreditCardPayment.php',
        self::EXPECTED . 'BankTransferPayment.php' => self::SCHEMAS . 'BankTransferPayment.php',
        // Defaults: scalar defaults, null $ref default, object literal $ref default (new ClassName(...))
        self::EXPECTED . 'Defaults.php' => self::SCHEMAS . 'Defaults.php',
        // User: UUID format validation
        self::EXPECTED . 'User.php' => self::SCHEMAS . 'User.php',
        // Event: date format validation
        self::EXPECTED . 'Event.php' => self::SCHEMAS . 'Event.php',
        // UserList: Assert\All validation for array items with UUID format
        self::EXPECTED . 'UserList.php' => self::SCHEMAS . 'UserList.php',
        // Tags: Assert\All validation for array items with enum choices
        self::EXPECTED . 'Tags.php' => self::SCHEMAS . 'Tags.php',
        self::EXPECTED . 'Undefined.php' => self::SCHEMAS . 'Undefined.php',
        // Subdirectory schemas: namespace derived from path relative to baseUri
        self::EXPECTED . 'address/Address.php' => self::SCHEMAS . 'address/Address.php',
        self::EXPECTED . 'billing/Address.php' => self::SCHEMAS . 'billing/Address.php',
    ];

    protected function setUp(): void
    {
        $this->cleanGeneratedFiles();
    }

    public function testGeneratesDtosIntoTheSameFolderAsSchemas(): void
    {
        NetteFilesGeneratorFactory::create(
            baseNamespace: 'App\Dto',
            schemaPath: self::SCHEMAS,
            outputPath: self::SCHEMAS,
            schemaSuffix: '.json',
            undefinedPath: 'Undefined.json',
            typeMappings: ['DateTimeImmutable.json' => \DateTimeImmutable::class],
        )->run();

        foreach (self::FILES as $expectedPath => $actualPath) {
            self::assertFileExists($actualPath, sprintf('Expected output file "%s" was not generated', $actualPath));
            self::assertFileEquals(
                $expectedPath, $actualPath, sprintf('Content of "%s" does not match expected', $actualPath)
            );
        }

        // DateTimeImmutable.json is mapped to an existing type (see config), so it is
        // referenced (Event::$createdAt) but never generated as a class of its own.
        self::assertFileDoesNotExist(self::SCHEMAS . 'DateTimeImmutable.php');
    }

    private function cleanGeneratedFiles(): void
    {
        foreach (self::FILES as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
