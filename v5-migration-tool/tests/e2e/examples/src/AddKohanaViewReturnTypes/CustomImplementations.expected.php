<?php

namespace My\Application\Controller;

use BadMethodCallException;
use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\TemplateManager;
use Ingenerator\KohanaView\TemplateSpecifyingViewModel;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;

class CustomView implements ViewModel
{
    public function display($variables): void
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomTemplateSpecifyingViewModel implements TemplateSpecifyingViewModel
{
    public function getTemplateName(): string
    {
        throw new BadMethodCallException(__METHOD__);
    }
}

abstract class CustomNestedChild implements NestedChildView
{
    public function getParentView(): NestedParentView
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

abstract class CustomNestedParent implements NestedParentView
{
    public function setBodyHtml($html): void
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomRenderer implements Renderer
{
    public function render($view): string
    {

        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomTemplateManager implements TemplateManager
{
    public function getPath($template_name): string
    {
        throw new BadMethodCallException(__METHOD__);
    }

}
