<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\Exception\UnassignedViewVarException;
use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class StaticPageContentView extends AbstractPageContentView implements TemplateSpecifyingViewModel
{
    public protected(set) mixed $page_path;

    public function getTemplateName(): string
    {
        if ( ! $this->page_path) {
            throw UnassignedViewVarException::forVariable(static::class, 'page_path', 'name/of/view');
        }

        return $this->page_path;
    }
}
