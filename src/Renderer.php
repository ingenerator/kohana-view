<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

/**
 * Produces a string representation of a view for output to a user. In the most common case, this will be an
 * HTMLRenderer, which parses a template file with an anonymous scope containing just the view and a reference
 * to the renderer.
 */
interface Renderer
{
    public function render(ViewModel $view): string;
}
