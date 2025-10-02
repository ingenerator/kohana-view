<?php

namespace test\mock\ViewModel;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class FixedTemplateViewModelStub extends ViewModelDummy implements TemplateSpecifyingViewModel
{
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
