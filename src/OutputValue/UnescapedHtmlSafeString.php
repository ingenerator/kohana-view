<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\OutputValue;

use Stringable;

/**
 * The string wrapped by this class will be rendered unescaped into the HTML.
 */
final readonly class UnescapedHtmlSafeString implements UnescapedSafeHtmlContent, Stringable
{
    public function __construct(public string $content)
    {
    }

    public function renderSafeHtml(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
