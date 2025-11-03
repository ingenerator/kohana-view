<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\OutputValue;

function raw(?string $value): UnescapedHtmlSafeString
{
    return new UnescapedHtmlSafeString($value ?? '');
}
