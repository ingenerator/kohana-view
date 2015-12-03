<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Renderer;


use Ingenerator\KohanaView\Exception\TemplateNotFoundException;
use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\TemplateManager;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewTemplateSelector;

/**
 * Renders a ViewModel to an HTML string for return to the user. The template is rendered with an anonymous scope
 * which only has access to the ViewModel, the Renderer (for rendering any subviews) and the template file path.
 *
 * @package Ingenerator\KohanaView\Renderer
 */
class HTMLRenderer implements Renderer
{

    /**
     * @var TemplateManager
     */
    protected $template_manager;

    /**
     * @var ViewTemplateSelector
     */
    protected $template_selector;

    public function __construct(ViewTemplateSelector $template_selector, TemplateManager $template_manager)
    {
        $this->template_selector = $template_selector;
        $this->template_manager  = $template_manager;
    }


    /**
     * {@inheritdoc}
     */
    public function render(ViewModel $view)
    {
        $template_path = $this->getTemplatePath($view);

        ob_start();
        try {
            $this->includeWithAnonymousScope($view, $template_path);
        } finally {
            $output = ob_get_clean();
        }

        return $output;
    }

    /**
     * @param ViewModel $view
     *
     * @return string
     */
    protected function getTemplatePath(ViewModel $view)
    {
        $template_name = $this->template_selector->getTemplateName($view);
        $template      = $this->template_manager->getPath($template_name);

        return $template;
    }

    /**
     * @param ViewModel $view
     * @param string    $template_path
     */
    protected function includeWithAnonymousScope(ViewModel $view, $template_path)
    {
        /** @noinspection PhpUnusedParameterInspection */
        /** @noinspection PhpDocSignatureInspection */
        $bound_capture = function (ViewModel $view, Renderer $renderer, $template) {
            /** @noinspection PhpIncludeInspection */
            return include $template;
        };
        $anon_capture  = $bound_capture->bindTo(NULL);

        // A user's own error handler may throw an exception here if the include fails - which we will bubble as-is.
        // If they have not configured an error handler, we need to throw an exception of our own.
        if ($anon_capture($view, $this, $template_path) === FALSE) {
            throw TemplateNotFoundException::forFullPath($template_path);
        }
    }

}
