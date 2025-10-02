<?php

namespace test\mock\ViewModel;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class FixedTemplateViewModelStub extends ViewModelDummy implements TemplateSpecifyingViewModel
{
    public function __construct(private $template)
    {
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template;
    }
}
