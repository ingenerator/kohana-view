<?php

namespace test\mock\ViewModel;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class FixedTemplateViewModelStub extends ViewModelDummy implements TemplateSpecifyingViewModel
{
    public function __construct(private $template)
    {
    }

    public function getTemplateName(): string
    {
        return $this->template;
    }
}
