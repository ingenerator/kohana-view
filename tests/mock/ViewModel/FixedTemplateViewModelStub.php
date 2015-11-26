<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\mock\ViewModel;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class FixedTemplateViewModelStub extends ViewModelDummy implements TemplateSpecifyingViewModel
{
    /**
     * @var
     */
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template;
    }

}
