<?php

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

interface NestedParentView extends ViewModel
{
    /**
     * @return void
     */
    public function setBodyHtml(string $html);
}
