<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Filesystem\PutContents;
use Guuzen\JsonSchemaCodegen\Filesystem\GetContents;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Schema\JsonDecoder;
use Guuzen\JsonSchemaCodegen\Schema\YamlDecoder;
use Guuzen\JsonSchemaCodegen\Filesystem\OutputPathTransformer;
use Guuzen\JsonSchemaCodegen\Filesystem\PathsWithSuffix;
use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Config;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FilesGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\AllConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ChoiceConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\DateConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\DateTimeConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\GreaterThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\LessThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\NotNullConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\RegexConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\TypeConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\UuidConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ValidConstraintGenerator;

final class NetteFilesGeneratorFactory
{
    public static function withJsonDecoder(Config $config): FilesGenerator
    {
        return self::create($config, new JsonDecoder());
    }

    public static function withYamlDecoder(Config $config): FilesGenerator
    {
        return self::create($config, new YamlDecoder());
    }

    private static function create(Config $config, SchemaDecoder $decoder): FilesGenerator
    {
        $fqcnResolver = new FqcnResolver(
            $config->schemaPath->toUri(),
            $config->baseNamespace,
            $config->schemaSuffix,
        );
        $schemaRegistry = new SchemaRegistry();

        $typeGenerator = new TypeGenerator($fqcnResolver, $schemaRegistry);

        return new FilesGenerator(
            pathCollector: PathsWithSuffix::create($config->schemaPath, $config->schemaSuffix),
            fileLoader: new GetContents(),
            pathTransformer: new OutputPathTransformer($config->schemaPath, $config->outputPath, $config->schemaSuffix),
            fileDumper: new PutContents(),
            schemaParser: new SchemaParser(),
            schemaFileDecoder: $decoder,
            registry: $schemaRegistry,
            generators: [
                new ClassGenerator(
                    printer: new NettePrinter(),
                    createPhpFile: new CreatePhpFile($fqcnResolver),
                    constructorParameterOrder: new ConstructorParameterOrder(),
                    modifiers: [
                        new CommentModifier(new DefaultCommentGenerator()),
                        new AnnotationModifier(
                            generator: new DefaultAnnotationGenerator(),
                            typeGenerator: $typeGenerator,
                        ),
                        new SymfonyValidationModifier(
                            typeGenerator: $typeGenerator,
                            generators: [
                                new TypeConstraintGenerator(new DefaultTypeRenderer()),
                                new NotNullConstraintGenerator(),
                                new UuidConstraintGenerator(),
                                new DateConstraintGenerator(),
                                new DateTimeConstraintGenerator(),
                                new RegexConstraintGenerator(),
                                new GreaterThanOrEqualConstraintGenerator(),
                                new LessThanOrEqualConstraintGenerator(),
                                new ChoiceConstraintGenerator(),
                                new AllConstraintGenerator([
                                    new TypeConstraintGenerator(new DefaultTypeRenderer()),
                                    new NotNullConstraintGenerator(),
                                    new UuidConstraintGenerator(),
                                    new DateConstraintGenerator(),
                                    new DateTimeConstraintGenerator(),
                                    new RegexConstraintGenerator(),
                                    new GreaterThanOrEqualConstraintGenerator(),
                                    new LessThanOrEqualConstraintGenerator(),
                                    new ChoiceConstraintGenerator(),
                                ]),
                                new ValidConstraintGenerator(),
                            ],
                        ),
                        new OptionalModifier(new DefaultDefaultGenerator($fqcnResolver)),
                    ],
                ),
            ],
        );
    }
}
