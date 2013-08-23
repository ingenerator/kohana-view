<?php
/**
 * The ViewFactory creates view classes, to allow stubbing of views for testing and easier extension in applications.
 *
 * @package    ViewModel
 * @category   Factory
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  (c) 2013 inGenerator Ltd
 * @license    BSD
 */

namespace Ingenerator;

/**
 * The ViewFactory creates view classes, to allow stubbing of views for testing and easier extension in applications.
 *
 * @package Ingenerator
 */
class ViewFactory {

	/**
	 * Create and return a view of the specified type
	 *
	 * @param string $class name of the view class to create
	 * @param array  $data  array of data to pass to the view
	 *
	 * @return \View
	 * @throws \View_Exception if the class is not defined
	 */
	public function create($class, $data = array())
	{
		// Check the view class exists
		if ( ! class_exists($class))
		{
			throw new \View_Exception("View class $class does not exist");
		}

		// Temporary fix to map template names for namespaced classes
		//@todo: Refactor the existing factory methods and classes to support namespaces
		if ('\\View\\' === substr($class, 0, 6))
		{
			// Strip the \View\ prefix
			$template = substr($class, 6);

			// Exchange namespace separators for directory separators
			$template = str_replace('\\', DIRECTORY_SEPARATOR, $template);

			// Lowercase the View file name
			$template = strtolower($template);
		}
		else
		{
			$template = NULL;
		}

		return new $class($template, $data);
	}

}