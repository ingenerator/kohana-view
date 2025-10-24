<?php

declare(strict_types=1);

namespace test\e2e;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use const DIRECTORY_SEPARATOR;

class EndToEndWithExampleFilesTest extends TestCase
{
    private static Filesystem $fs;
    private static string $examples_dir = __DIR__.'/examples';
    private static string $working_dir = __DIR__.'/build';
    private static array $files;

    public static function providerExampleFiles(): iterable
    {
        return self::$files ??= iterator_to_array(self::findExampleFiles());
    }

    private static function findExampleFiles(): iterable
    {
        $finder = new Finder()
            ->files()
            ->in(self::$examples_dir)
            ->name('*.php')
            ->notName('*.expected.php');

        foreach ($finder as $file) {
            $absolute_path = $file->getPathname();
            $relative_path = $file->getRelativePathname();
            yield $relative_path => [
                'source_path' => $absolute_path,
                'expected_path' => preg_replace('/\.php$/', '.expected.php', $absolute_path),
                'working_path' => self::$working_dir.DIRECTORY_SEPARATOR.$relative_path,
            ];
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::$fs = new Filesystem();
        self::initWorkingDirectory();
        self::runMigrationTool();
    }

    private static function initWorkingDirectory(): void
    {
        self::$working_dir = __DIR__.'/build';
        self::$fs->remove(self::$working_dir);
        foreach (self::providerExampleFiles() as $file) {
            self::$fs->copy($file['source_path'], $file['working_path']);
        }
    }

    private static function runMigrationTool(): void
    {
        new Process(
            [
                __DIR__.'/../../migrate',
            ],
            self::$working_dir,
        )->mustRun();
    }

    #[DataProvider('providerExampleFiles')]
    public function testItConvertsFileAsExpected(string $source_path, string $expected_path, string $working_path): void
    {
        try {
            $this->assertFileEquals($expected_path, $working_path, 'Should convert file as expected');
        } catch (ExpectationFailedException $e) {
            if (getenv('RE_RECORD_EXPECTATIONS')) {
                self::$fs->copy($working_path, $expected_path);
            }
            throw $e;
        }
    }
}
