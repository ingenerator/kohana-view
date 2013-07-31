<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * A basic view model for the global page template, used by [View_Layout] as
 * the page container. You can extend this class in your application, or indeed
 * use an entire replacement if you don't need the functionality here.
 *
 * Variables can only be passed to the template view by the content view, or by
 * extending this class - the controller has no direct access.
 *
 * @package    ViewModel
 * @category   Template
 * @author     Andrew Coulton <andrewnfcoulton@gmail.com>
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaphp.com/license
 */
abstract class AndrewC_View_Template_Global extends View_Model
{
    public $var_title = 'Page title';
}