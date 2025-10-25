<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $child_html Some info about the property
 */
class ComputedToMakePublicProtected extends AbstractViewModel
{
    protected string $child_html;

    public function setBodyHtml(string $html): void
    {
        $this->child_html = $html;
    }

    protected function var_child_html()
    {
        return $this->child_html;
    }

}
