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
    public function display($variables)
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomTemplateSpecifyingViewModel implements TemplateSpecifyingViewModel
{
    public function getTemplateName()
    {
        throw new BadMethodCallException(__METHOD__);
    }
}

abstract class CustomNestedChild implements NestedChildView
{
    public function getParentView()
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

abstract class CustomNestedParent implements NestedParentView
{
    public function setBodyHtml($html)
    {
        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomRenderer implements Renderer
{
    public function render($view)
    {

        throw new BadMethodCallException(__METHOD__);
    }

}

class CustomTemplateManager implements TemplateManager
{
    public function getPath($template_name)
    {
        throw new BadMethodCallException(__METHOD__);
    }

}
