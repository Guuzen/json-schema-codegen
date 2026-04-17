<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\E2e;

use Guuzen\JsonSchemaCodegen\Config;
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
 * - Order: $ref same namespace, $ref cross-namespace, nullable $ref (oneOf ref+null),
 *          list<$ref items>, oneOf two $ref branches
 * - Defaults: scalar defaults, $ref with null default, $ref with object literal default (new ClassName(...))
 * - Note, OrderItem, CreditCardPayment, BankTransferPayment: simple helper objects
 * - address/Address, billing/Address: schemas in subdirectories (namespace derivation)
 */
final class NetteFilesGeneratorTest extends TestCase
{
    private const string CONFIG_FILE = __DIR__ . '/fixtures/config.php';

    private const string EXPECTED = __DIR__ . '/fixtures/expected/';

    private const string SCHEMAS = __DIR__ . '/fixtures/schemas/';

    private const array FILES = [
        // Product: all scalar types, array types, class description, property description
        self::EXPECTED . 'Product.php' => self::SCHEMAS . 'Product.php',
        // Customer: $ref with title aliases, same class name from different namespaces
        self::EXPECTED . 'Customer.php' => self::SCHEMAS . 'Customer.php',
        // Order: $ref same/cross namespace, inline scalar refs, nullable $ref, list<$ref>, oneOf two refs
        self::EXPECTED . 'Order.php' => self::SCHEMAS . 'Order.php',
        // Simple helper schemas referenced by Order and Customer
        self::EXPECTED . 'Note.php' => self::SCHEMAS . 'Note.php',
        self::EXPECTED . 'OrderItem.php' => self::SCHEMAS . 'OrderItem.php',
        self::EXPECTED . 'CreditCardPayment.php' => self::SCHEMAS . 'CreditCardPayment.php',
        self::EXPECTED . 'BankTransferPayment.php' => self::SCHEMAS . 'BankTransferPayment.php',
        // Defaults: scalar defaults, null $ref default, object literal $ref default (new ClassName(...))
        self::EXPECTED . 'Defaults.php' => self::SCHEMAS . 'Defaults.php',
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
        /** @var Config $config */
        $config = require self::CONFIG_FILE;
        NetteFilesGeneratorFactory::withJsonDecoder($config)->run();

        foreach (self::FILES as $expectedPath => $actualPath) {
            self::assertFileExists($actualPath, sprintf('Expected output file "%s" was not generated', $actualPath));
            self::assertFileEquals(
                $expectedPath, $actualPath, sprintf('Content of "%s" does not match expected', $actualPath)
            );
        }
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
