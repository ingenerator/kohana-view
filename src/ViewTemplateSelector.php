<?php

namespace Ingenerator\KohanaView;

use Ingenerator\KohanaView\Exception\UnspecifiedTemplateNameException;
use UnexpectedValueException;

use function is_string;
use function preg_replace;
use function strtolower;

/**
 * The ViewTemplateSelector maps ViewModel classes to the appropriate template file. By default this is done by
 * converting the class name to a lowercased file path such that:
 *
 *   View_Model_Something_WithMixedCase => model/something/with_mixed_case
 *   \View\Stuff\In\NameSpace\Thing     => stuff/in/name_space/thing
 *   \Other\View\Hierarchy              => other/view/hierarchy
 *   \Other\View\HierarchyView          => other/view/hierarchy
 *   \Other\View\HierarchyViewModel     => other/view/hierarchy
 *
 * Views can also implement TemplateSpecifyingViewModel to provide a custom template file name when required.
 */
class ViewTemplateSelector
{
    public function getTemplateName(ViewModel $view): string
    {
        if ($view instanceof TemplateSpecifyingViewModel) {
            return $this->validateSpecifiedTemplateName($view);
        }

        return $this->calculateTemplateFromClassName($view);
    }

    /**
     * @throws UnexpectedValueException if no template is provided
     */
    protected function validateSpecifiedTemplateName(TemplateSpecifyingViewModel $view): string
    {
        $template = $view->getTemplateName();
        $view_class = $view::class;
        if ( ! $template) {
            throw UnspecifiedTemplateNameException::forEmptyValue($view_class);
        }

        if ( ! is_string($template)) {
            throw UnspecifiedTemplateNameException::forNonStringValue($view_class, $template);
        }

        return $template;
    }

    protected function calculateTemplateFromClassName(ViewModel $view): string
    {
        $template = $view::class;
        $template = preg_replace('/\\\\|_/', '/', $template);
        $template = preg_replace('#(^view/?(model)?/)|(?<!/)(view/?(model)?$)#i', '', (string) $template);
        $template = preg_replace('/([a-z])([A-Z])/', '\1_\2', (string) $template);
        $template = strtolower((string) $template);

        return $template;
    }
}
