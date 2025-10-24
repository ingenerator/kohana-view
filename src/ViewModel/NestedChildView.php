<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

interface NestedChildView extends ViewModel
{
    public function getParentView(): NestedParentView;
}
