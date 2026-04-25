<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Filesystem;

use Guuzen\JsonSchemaCodegen\Filesystem\PutContents;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use PHPUnit\Framework\TestCase;

final class PutContentsTest extends TestCase
{
    public function testDumpWritesFileIntoExistingNestedDirectories(): void
    {
        $baseDirectory = sys_get_temp_dir() . '/put_contents_nested_' . bin2hex(random_bytes(8));
        mkdir($baseDirectory . '/level-one/level-two', 0777, true);

        try {
            $path = new AbsoluteUnixPath($baseDirectory . '/level-one/level-two/Generated.php');

            new PutContents()->dump($path, '<?php');

            self::assertFileExists($path->value);
            self::assertSame('<?php', file_get_contents($path->value));
        } finally {
            @unlink($baseDirectory . '/level-one/level-two/Generated.php');
            @rmdir($baseDirectory . '/level-one/level-two');
            @rmdir($baseDirectory . '/level-one');
            @rmdir($baseDirectory);
        }
    }

    public function testDumpThrowsWhenOutputDirectoryCannotBeCreated(): void
    {
        $blockingFile = tempnam(sys_get_temp_dir(), 'put_contents_test_');
        if ($blockingFile === false) {
            self::fail('Failed to create a temporary file for the test');
        }

        try {
            $this->expectException(\RuntimeException::class);

            new PutContents()->dump(
                new AbsoluteUnixPath($blockingFile . '/Generated.php'),
                '<?php',
            );
        } finally {
            unlink($blockingFile);
        }
    }

    public function testDumpThrowsWhenOutputFileCannotBeWritten(): void
    {
        $outputDirectory = sys_get_temp_dir() . '/put_contents_dir_' . bin2hex(random_bytes(8));
        mkdir($outputDirectory);

        try {
            $this->expectException(\RuntimeException::class);

            new PutContents()->dump(
                new AbsoluteUnixPath($outputDirectory),
                '<?php',
            );
        } finally {
            rmdir($outputDirectory);
        }
    }
}
