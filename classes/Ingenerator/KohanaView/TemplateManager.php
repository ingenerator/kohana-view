<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView;


/**
 * The TemplateManager interfaces with the physical view template files provided by the project, managing compilation
 * where required and returning a full disk path to the compiled template where appropriate.
 *
 * @package Ingenerator\KohanaView
 */
interface TemplateManager
{

    /**
     * @param string $template_name Name of the template to use (can include path separators)
     *
     * @return string Path to the compiled template file
     */
    public function getPath($template_name);

}
