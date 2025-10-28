<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

interface NestedParentView extends ViewModel
{
    public function setBodyHtml(string $html): void;
}
