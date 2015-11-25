<?php
/**
 * Acts as an object wrapper for output with embedded PHP, called "views".
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_View_Model {

	/**
	 * Captures the output that is generated when a view is included.
	 * The view data will be extracted to make local variables.
	 *
	 *     $output = $this->capture($file);
	 *
	 * @param   string  filename
	 * @return  string
	 */
	protected function capture($kohana_view_filename)
	{
		if ( ! in_array('kohana.view', stream_get_wrappers()))
		{
			stream_wrapper_register('kohana.view', 'View_Stream_Wrapper');
		}

		// Capture the view output
		ob_start();

		try
		{
			include 'kohana.view://'.$kohana_view_filename;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Magic method, returns the output of [View::render].
	 *
	 * @return  string
	 * @uses    View::render
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// Display the exception message
			Kohana_Exception::handler($e);

			return '';
		}
	}

	/**
	 * Sets the view filename.
	 *
	 *     $view->set_filename($file);
	 *
	 * @param   string  view filename
	 * @return  View
	 * @throws  Kohana_View_Exception
	 */
	public function set_filename($file)
	{
		if (($path = Kohana::find_file('views', $file)) === FALSE)
		{
			throw new Kohana_View_Exception('The requested view :file could not be found', [
				':file' => $file,
			]);
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	/**
	 * Renders the view object to a string. Global and local data are merged
	 * and extracted to create local variables within the view file.
	 *
	 *     $output = View::render();
	 *
	 * [!!] Global variables with the same key name as local variables will be
	 * overwritten by the local variable.
	 *
	 * @param    string  view filename
	 * @return   string
	 * @throws   Kohana_View_Exception
	 * @uses     View::capture
	 */
	public function render($file = NULL)
	{

		// Combine local and global data and capture the output
		return $this->capture($this->_file);
	}
} // End View
