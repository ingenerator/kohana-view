<?php

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

interface NestedParentView extends ViewModel
{
    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHtml($html);
}
