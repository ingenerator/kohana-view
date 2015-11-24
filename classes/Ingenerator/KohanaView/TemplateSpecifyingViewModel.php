<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView;

/**
 * Allows this view to specify a custom template file name at runtime, for example when the template may depend on
 * dynamic data from within the view itself.
 *
 * @package Ingenerator\KohanaView
 */
interface TemplateSpecifyingViewModel
{

    /**
     * @return string
     */
    public function getTemplateName();
}
