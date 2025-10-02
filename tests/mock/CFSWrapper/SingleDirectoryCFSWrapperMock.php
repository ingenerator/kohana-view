<?php

namespace test\mock\CFSWrapper;

use Ingenerator\KohanaView\TemplateManager\CFSWrapper;

use function file_exists;

/**
 * Works like a cascading filesystem with a single directory.
 */
class SingleDirectoryCFSWrapperMock extends CFSWrapper
{
    /**
     * @param string $root_path
     */
    public function __construct(protected $root_path)
    {
    }

    public function find_file($dir, $file)
    {
        $path = $this->root_path.'/'.$dir.'/'.$file.EXT;
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }
}
