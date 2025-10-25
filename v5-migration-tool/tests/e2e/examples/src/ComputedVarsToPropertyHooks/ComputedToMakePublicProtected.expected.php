<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ComputedToMakePublicProtected extends AbstractViewModel
{
    /**
     * Some info about the property
     */
    public protected(set) string $child_html;

    public function setBodyHtml(string $html): void
    {
        $this->child_html = $html;
    }

}
