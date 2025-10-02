<?php

namespace Ingenerator\KohanaView\ViewModel;

interface NestedChildView extends PageContentView
{
    /**
     *
     * @return NestedParentView
     */
    public function getParentView();
}
