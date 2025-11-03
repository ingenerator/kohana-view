<?php

declare(strict_types=1);

namespace test\e2e;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use function assert;

use const DIRECTORY_SEPARATOR;

class EndToEndWithExampleFilesTest extends TestCase
{
    private static Filesystem $fs;
    private static string $examples_dir = __DIR__.'/examples';
    private static array $files;

    private static function getWorkingDir(): string
    {
        static $workingDir;
        $workingDir ??= sys_get_temp_dir().'/kohana-view-v5-migration-tool/build';

        return $workingDir;
    }

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
            ->notName('*.expected.php')
        ->notName('rector.php');

        $workingDir = self::getWorkingDir();

        foreach ($finder as $file) {
            $absolute_path = $file->getPathname();
            $relative_path = $file->getRelativePathname();
            yield $relative_path => [
                'source_path' => $absolute_path,
                'expected_path' => preg_replace('/\.php$/', '.expected.php', $absolute_path),
                'working_path' => $workingDir.DIRECTORY_SEPARATOR.$relative_path,
            ];
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::$fs = new Filesystem();
        self::initWorkingDirectory();
        self::runInWorkingDir(['vendor/bin/rector', '--clear-cache']);
    }

    private static function initWorkingDirectory(): void
    {
        // self::$fs->remove(self::$working_dir);
        foreach (self::providerExampleFiles() as $file) {
            self::$fs->copy($file['source_path'], $file['working_path'], overwriteNewerFiles: true);
        }

        self::$fs->copy(
            self::$examples_dir.'/rector.php',
            self::getWorkingDir().'/rector.php',
            overwriteNewerFiles: true,
        );

        // Add the tool and dependencies to the working directory
        // We need to configure local repositories so that composer knows where to find the tools
        self::$fs->copy(
            self::$examples_dir.'/composer.json',
            self::getWorkingDir().'/composer.json',
            overwriteNewerFiles: true,
        );
        self::configureComposerRepository('kohana-view', __DIR__.'/../../../');
        self::runInWorkingDir(['composer', 'install']);
        $migration_tool_path = realpath(__DIR__.'/../../');
        self::runInWorkingDir([$migration_tool_path.'/configure']);
    }

    private static function configureComposerRepository(string $name, string $path): void
    {
        $realPath = realpath($path);
        assert($realPath !== false, "'$path' must resolve to a real path");
        self::runInWorkingDir([
            'composer',
            'config',
            'repositories.'.$name,
            json_encode(['type' => 'path', 'url' => $realPath]),
        ]);
    }

    private static function runInWorkingDir(array $command): void
    {
        new Process($command, self::getWorkingDir())->mustRun();
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
