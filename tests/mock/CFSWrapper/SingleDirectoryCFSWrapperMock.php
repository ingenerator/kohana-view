<?php

namespace test\mock\CFSWrapper;

use Ingenerator\KohanaView\TemplateManager\CFSWrapper;
use Override;

use function file_exists;

/**
 * Works like a cascading filesystem with a single directory.
 */
class SingleDirectoryCFSWrapperMock extends CFSWrapper
{
    public function __construct(protected string $root_path)
    {
    }

    #[Override]
    public function find_file($dir, $file): string|false
    {
        $path = $this->root_path.'/'.$dir.'/'.$file.EXT;
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }
}
