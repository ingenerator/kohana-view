<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView;

class DeeplyDescendedView extends AbstractNestedChildView
{

    protected function var_some_var(): string
    {
        return 'whatever';
    }

}
