<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\TemplateManager;

use Kohana;

/**
 * Very simple wrapper around Kohana's cascading file system to allow injection (and mocking/stubbing) as required.
 */
class CFSWrapper
{
    /**
     * @see Kohana::find_file
     */
    public function find_file($dir, $file)
    {
        return Kohana::find_file($dir, $file, null, false);
    }
}
