<?php

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

interface NestedParentView extends ViewModel
{
    public function setBodyHtml(string $html): void;
}
