<?php
namespace Ingenerator\KohanaView;

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
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
class ViewTemplateSelector
{

    /**
     * @param ViewModel $view
     *
     * @return string
     */
    public function getTemplateName(ViewModel $view)
    {
        if ($view instanceof TemplateSpecifyingViewModel) {
            return $this->validateSpecifiedTemplateName($view);
        } else {
            return $this->calculateTemplateFromClassName($view);
        }
    }

    /**
     * @param TemplateSpecifyingViewModel $view
     *
     * @return mixed
     * @throws \UnexpectedValueException if no template is provided
     */
    protected function validateSpecifiedTemplateName(TemplateSpecifyingViewModel $view)
    {
        $template   = $view->getTemplateName();
        $view_class = get_class($view);
        if ( ! $template) {
            throw new \UnexpectedValueException(
                "$view_class::getTemplateName() must return a template name, empty value returned"
            );
        }

        if ( ! is_string($template)) {
            $type = is_object($template) ? get_class($template) : gettype($template);
            throw new \UnexpectedValueException(
                "$view_class::getTemplateName() must return a string template name, $type value returned"
            );
        }

        return $template;
    }

    /**
     * @param ViewModel $view
     *
     * @return string
     */
    protected function calculateTemplateFromClassName(ViewModel $view)
    {
        $template = get_class($view);
        $template = preg_replace('/\\\\|_/', '/', $template);
        $template = preg_replace('#(^view/?(model)?/)|(?<!/)(view/?(model)?$)#i', '', $template);
        $template = preg_replace('/([a-z])([A-Z])/', '\1_\2', $template);
        $template = strtolower($template);

        return $template;
    }

}
