<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\TemplateManager;

/**
 * Very simple wrapper around Kohana's cascading file system to allow injection (and mocking/stubbing) as required.
 *
 * @package Ingenerator\KohanaView\TemplateManager
 */
class CFSWrapper
{

    /**
     * @see \Kohana::find_file
     */
    public function find_file($dir, $file)
    {
        return \Kohana::find_file($dir, $file, NULL, FALSE);
    }
}
