<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView;

class DeeplyDescendedView extends AbstractNestedChildView
{

    public string $some_var {
        get => 'whatever';
    }

}
