<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $bar Some property defined in a method
 */
class SomeChildView extends AbstractViewModel
{

    protected $variables = [
        'foo' => null,
    ];

    public function __construct()
    {
        // Note we'll leave the assignment in (for now at least) so users can see what it was
        $this->variables['bar'] = null;
    }
}
