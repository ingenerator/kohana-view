<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * The View_Layout class provides a view model for views that are designed to be
 * rendered within a consistent page layout - similar to Kohana's [Controller_Template]
 * but maintaining the template/layout implementation within the View layer.
 *
 * By default, the layout is not rendered for an AJAX request, but this can be
 * changed either by manually setting the use_template property, or by extending
 * [View_Layout::use_template()] to test some other logic.
 *
 * Whether or not the layout will be rendered, it is available to child view
 * models under [View_Layout::var_page()] and within view files directly as the
 * page() variable. This allows easy and consistent injection of title, meta, and
 * navigational elements into the parent template by a child view.
 *
 * @package    ViewModel
 * @category   Template
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  (c) 2011-13 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
abstract class Ingenerator_View_Layout extends View_Model
{
    // The name of the template model to use
    public $template = 'Global';

    // Whether to render the view within the template or not, null for auto
    protected $_use_template = null;

    /**
     * Sets a new template to use for this view model. By default template names
     * become View_Template_$template classes.
     *
     * @param string $template
     */
    public function set_template($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the View_Model representing the template layout (instantiating it
     * if it has not previously been created).
     *
     * @return View_Model
     */
    public function var_page()
    {
        if ( is_string($this->template))
        {
            $class = 'View_Template_'.$this->template;
            if ( ! class_exists($class))
            {
                $class = 'View';
            }
            $this->template = new $class();
        }
        return $this->template;
    }

    /**
     * Hook called just before the body content is rendered, as a useful extension
     * point for transforming any data or setting variables on the page template.
     *
     *     class View_Index extends View_Layout
     *     {
     *         public function pre_render()
     *         {
     *             $page = $this->var_page();
     *             $page->title = 'Index';
     *         }
     *     }
     */
    public function pre_render()
    {
    }

    /**
     * Hook called just after the body content is rendered, as a useful extension
     * point for transforming any data or setting variables on the page template.
     *
     * @see View_Layout::pre_render()
     */
    public function post_render()
    {
    }


    /**
     * Renders the body content (calling [View_Layout::pre_render()] and
     * [View_Layout::post_render()] in the process).
     *
     * If required, the body content will then be passed to the template view
     * for rendering within the page layout and the fully templated page returned.
     * Otherwise, the body fragment is returned alone and the page template is
     * never rendered.
     *
     * @param string $file If wishing to generate a view to a file
     * @return string The formatted view content
     */
    public function render($file = null)
    {
        $this->pre_render();

        // Generate the body content
        $body = parent::render($file);

        $this->post_render();

        // Render the template if required
        if ($this->use_template())
        {
            $template = $this->var_page();
            $template->set('var_body', $body);
            return $template->render();
        }

        return $body;
    }

    /**
     * Setter/Getter for the use_template setting determining whether or not to
     * render the page template or just the body fragment.
     *
     * By default, $this-_use_template is null, meaning the class should determine
     * automatically based on whether the request is an AJAX request.
     *
     * If manually set true or false, the class will respect this value.
     *
     * @uses Request::is_ajax
     * @uses Request::current()
     *
     * @param boolean $use_template
     * @return boolean If called as getter
     * @return View_Layout If called as setter
     */
    public function use_template($use_template = null)
    {
        // If we're being called as a setter
        if ($use_template !== null)
        {
            $this->_use_template = $use_template;
            return $this;
        }

        // If an explicit value was set
        if ($this->_use_template !== null)
        {
            return $this->_use_template;
        }

        // Guess based on is_ajax
        if (Request::current())
        {
            if (Request::current()->is_ajax())
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

}
