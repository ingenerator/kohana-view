<?php

/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;

class StaticPageContentView extends AbstractPageContentView implements TemplateSpecifyingViewModel
{
    protected $variables = [
        'page_path' => NULL,
    ];

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        if ( ! $this->variables['page_path']) {
            throw new \BadMethodCallException(
                'Call '.static::class.'::display(["page_path" => "name/of/view"]) before rendering static views'
            );
        }

        return $this->variables['page_path'];
    }


}
