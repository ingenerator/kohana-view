<?php

namespace My\Application\Controller;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class CompleteExample extends AbstractViewModel implements TemplateSpecifyingViewModel
{
    public function display($variables): void
    {
        $variables['foo'] = 'bar';
        parent::display($variables);
    }

    public function getTemplateName(): string
    {
        return 'foobar';
    }

}
