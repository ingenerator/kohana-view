<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use InvalidArgumentException;

/**
 * The TemplateCompiler takes a plain PHP template string and processes it to add automatic variable escaping within
 * PHP short echo tags, before returning the compiled template.
 *
 * For example, the template:
 *
 *    <h1><?=$view->title;?></h1>
 *
 * Will compile to:
 *
 *    <h1><?=$renderer->escape($view->title);?></h1>
 */
interface TemplateCompiler
{
    /**
     * Compile a string containing a PHP template, automatically escaping variables that are echoed in PHP short tags,
     * and return the compiled PHP string.
     *
     * @throws InvalidArgumentException if the template is empty or invalid
     */
    public function compile(string $source): string;
}
