<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use My\Application\View\WidgetHTMLView;

class PromotedToMakePublicReadonly extends AbstractViewModel
{

    public function __construct(
        public readonly WidgetHTMLView $widgetHTMLView
    )
    {

    }

}
