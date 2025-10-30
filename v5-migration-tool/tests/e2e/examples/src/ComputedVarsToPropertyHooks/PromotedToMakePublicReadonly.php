<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use My\Application\View\WidgetHTMLView;

/**
 * @property-read WidgetHTMLView $widgetHTMLView
 */
class PromotedToMakePublicReadonly extends AbstractViewModel
{

    public function __construct(
        protected WidgetHTMLView $widgetHTMLView
    )
    {

    }

    protected function var_widgetHTMLView(): WidgetHTMLView
    {
        return $this->widgetHTMLView;
    }

}
