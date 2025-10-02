<?php

namespace Ingenerator\KohanaView\ViewModel;

interface NestedParentView extends PageLayoutView
{
    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHtml($html);
}
