<?php

namespace Ingenerator\KohanaView;

/**
 * Produces a string representation of a view for output to a user. In the most common case, this will be an
 * HTMLRenderer, which parses a template file with an anonymous scope containing just the view and a reference
 * to the renderer.
 *
 * @package Ingenerator\KohanaView
 */
interface Renderer
{
    /**
     *
     * @return string
     */
    public function render(ViewModel $view);
}
