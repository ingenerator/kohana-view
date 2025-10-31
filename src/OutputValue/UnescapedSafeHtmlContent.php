<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\OutputValue;

/**
 * Marks a DTO or similar that should be rendered unescaped into the final HTML.
 *
 * Implementers are responsible for ensuring that any content within the provided
 * string is escaped as appropriate.
 */
interface UnescapedSafeHtmlContent
{
    public function renderSafeHtml(): string;
}
