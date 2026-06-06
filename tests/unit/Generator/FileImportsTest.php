<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\FileImportsFactory;
use Guuzen\JsonSchemaCodegen\Generator\TypeMappings;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\TestCase;

final class FileImportsTest extends TestCase
{
    /**
     * A single reference whose short name nothing else competes
     */
    public function testImportsAUniqueReferenceUnderItsShortName(): void
    {
        $address = new Fqcn('App\\Dto\\billing\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties(['address' => 'billing/Address.json'])
            )
        );

        self::assertSame(
            ['App\\Dto\\billing\\Address'],
            self::fqcns($imports->uses())
        );
        self::assertSame('Address', $imports->name($address));
    }

    /**
     * Two distinct FQCNs that end in the same short name (e.g. address/Address and
     * billing/Address) cannot both own the bare name, so neither is imported and each
     * is rendered in its shortest unambiguous qualified form.
     */
    public function testQualifiesBothReferencesThatShareAShortName(): void
    {
        $home = new Fqcn('App\\Dto\\address\\Address');
        $billing = new Fqcn('App\\Dto\\billing\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties([
                    'home' => 'address/Address.json',
                    'billing' => 'billing/Address.json',
                ])
            )
        );

        self::assertSame([], self::fqcns($imports->uses()));
        self::assertSame('address\\Address', $imports->name($home));
        self::assertSame('billing\\Address', $imports->name($billing));
    }

    /**
     * The file's own class already owns its short name, so a reference resolving to the
     * same short name must also be treated as a collision and qualified, even though it
     * is the only reference with that name.
     */
    public function testQualifiesAReferenceCollidingWithTheOwnClassName(): void
    {
        $billing = new Fqcn('App\\Dto\\billing\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Address.json'),
            schema: new Schema(
                properties: self::schemaProperties(['billing' => 'billing/Address.json'])
            )
        );

        self::assertSame([], self::fqcns($imports->uses()));
        self::assertSame('billing\\Address', $imports->name($billing));
    }

    /**
     * The same FQCN referenced several times in one file is not a collision: it is
     * imported exactly once and every occurrence is rendered under its short name.
     */
    public function testImportsAReferenceUsedSeveralTimesOnlyOnce(): void
    {
        $billing = new Fqcn('App\\Dto\\billing\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties([
                    'home' => 'billing/Address.json',
                    'work' => 'billing/Address.json',
                ])
            )
        );

        self::assertSame(['App\\Dto\\billing\\Address'], self::fqcns($imports->uses()));
        self::assertSame('Address', $imports->name($billing));
    }

    /**
     * When a collision involves a class outside the file's namespace (e.g. a mapped
     * type such as DateTimeImmutable), the un-importable side is rendered as a
     * leading-backslash fully-qualified name, while a co-colliding class that lives in
     * the file's own namespace renders bare — neither claims the importable short name.
     */
    public function testQualifiesACollidingReferenceFromAnotherNamespaceWithLeadingBackslash(): void
    {
        $local = new Fqcn('App\\Dto\\Address');
        $foreign = new Fqcn('Some\\Other\\Address');

        $factory = self::factory([
            self::schemaUri('foreign/Address.json')->value => $foreign,
        ]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties([
                    'local' => 'Address.json',
                    'foreign' => 'foreign/Address.json',
                ])
            )
        );

        self::assertSame([], self::fqcns($imports->uses()));
        self::assertSame('Address', $imports->name($local));
        self::assertSame('\\Some\\Other\\Address', $imports->name($foreign));
    }

    /**
     * A reference that already lives in the file's own namespace needs no `use` at all,
     * yet must still render under its bare short name.
     */
    public function testRendersASameNamespaceReferenceBareWithoutAUse(): void
    {
        $note = new Fqcn('App\\Dto\\Note');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties(['note' => 'Note.json'])
            )
        );

        self::assertSame([], self::fqcns($imports->uses()));
        self::assertSame('Note', $imports->name($note));
    }

    /**
     * The collision rule is not limited to two sides: when three or more distinct FQCNs
     * share a short name, every one of them is qualified and none is imported.
     */
    public function testQualifiesEveryReferenceInACollisionOfMoreThanTwo(): void
    {
        $a = new Fqcn('App\\Dto\\a\\Address');
        $b = new Fqcn('App\\Dto\\b\\Address');
        $c = new Fqcn('App\\Dto\\c\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties([
                    'a' => 'a/Address.json',
                    'b' => 'b/Address.json',
                    'c' => 'c/Address.json',
                ])
            )
        );

        self::assertSame([], self::fqcns($imports->uses()));
        self::assertSame('a\\Address', $imports->name($a));
        self::assertSame('b\\Address', $imports->name($b));
        self::assertSame('c\\Address', $imports->name($c));
    }

    /**
     * Collisions are scoped per short name: a clash on one short name (Address) must not
     * prevent an unrelated, uniquely named reference (Note) from being imported normally
     * in the same file.
     */
    public function testImportsAUniqueReferenceAlongsideAnUnrelatedCollision(): void
    {
        $note = new Fqcn('App\\Dto\\common\\Note');
        $home = new Fqcn('App\\Dto\\address\\Address');
        $billing = new Fqcn('App\\Dto\\billing\\Address');

        $factory = self::factory([]);
        $imports = $factory->forFile(
            schemaUri: self::schemaUri('Customer.json'),
            schema: new Schema(
                properties: self::schemaProperties([
                    'note' => 'common/Note.json',
                    'home' => 'address/Address.json',
                    'billing' => 'billing/Address.json',
                ])
            )
        );

        self::assertSame(['App\\Dto\\common\\Note'], self::fqcns($imports->uses()));
        self::assertSame('Note', $imports->name($note));
        self::assertSame('address\\Address', $imports->name($home));
        self::assertSame('billing\\Address', $imports->name($billing));
    }

    /**
     * @param list<Fqcn> $uses
     *
     * @return list<string>
     */
    private static function fqcns(array $uses): array
    {
        return array_map(static fn (Fqcn $fqcn) => $fqcn->fqcn, $uses);
    }

    /**
     * @param array<string, Fqcn> $mappings
     */
    private static function factory(array $mappings): FileImportsFactory
    {
        return new FileImportsFactory(
            fqcnResolver: new FqcnResolver(self::schemaUri(''), 'App\\Dto', '.json'),
            typeMappings: new TypeMappings($mappings),
        );
    }

    private static function schemaUri(string $path): AbsoluteUri
    {
        return new AbsoluteUri("file:///schemas/$path");
    }

    /**
     * @param array<string, string> $properties
     *
     * @return array<string, Schema>
     */
    private static function schemaProperties(array $properties): array
    {
        $schemaProperties = [];
        foreach ($properties as $name => $property) {
            $schemaProperties[$name] = new Schema(ref: new Ref(self::schemaUri($property)));
        }

        return $schemaProperties;
    }
}
