<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\mock\CFSWrapper;

use Ingenerator\KohanaView\TemplateManager\CFSWrapper;

/**
 * Works like a cascading filesystem with a single directory
 *
 * @package test\mock\CFSWrapper\SingleDirectoryCFSWrapper
 */
class SingleDirectoryCFSWrapperMock extends CFSWrapper
{
    protected $root_path;

    /**
     * @param string $root_path
     */
    public function __construct($root_path)
    {
        $this->root_path = $root_path;
    }

    public function find_file($dir, $file)
    {
        $path = $this->root_path.'/'.$dir.'/'.$file.EXT;
        if (\file_exists($path)) {
            return $path;
        } else {
            return FALSE;
        }
    }

}
