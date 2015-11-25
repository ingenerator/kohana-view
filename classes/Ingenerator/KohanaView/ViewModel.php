<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView;


/**
 * This is the basic interface for view models, which are responsible for holding and presenting data to
 * the template.
 *
 * @package Ingenerator\KohanaView
 */
interface ViewModel
{

    /**
     * @param  array $variables
     * @return void
     */
    public function display(array $variables);

}
