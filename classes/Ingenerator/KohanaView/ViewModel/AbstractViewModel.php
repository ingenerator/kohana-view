<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

/**
 * The AbstractViewModel can be used as a base for all ViewModels within the system. It supports providing values
 * to the template either by providing read-only access to the values in the variables array, or by magically calling
 * a var_{variable_name} method.
 *
 * Values in the variables array always take precedence, so custom getters can cache calculated values for subsequent
 * reuse by simply assigning the value once they have it. For example:
 *
 *   class ViewThatDoesWork extends AbstractViewModel {
 *
 *     protected $variables = [];
 *
 *     protected var_calculated_id()
 *     {
 *        // This custom getter will only be called once for each view rendering cycle. Note that calls
 *        // to the `::display()` method will wipe out all calculated values.
 *        $this->variables['calculated_id'] = uniqid();
 *        return $this->variables['calculated_id'];
 *     }
 *   }
 *
 * By default, values are provided as an array to the display method - which will throw if any values are missing
 * or any unexpected variables are provided. This ensures that for views that may be rendered in loops etc, a call
 * to display will fully reset the state of the view.
 *
 * You can of course implement custom setters for fields that you want to be individually changed.
 */
abstract class AbstractViewModel implements ViewModel
{
    /**
     * @var array The actual view data
     */
    protected $variables = [];

    /**
     * @var string[] The names of the valid set of fields that must be passed to the display() method
     */
    protected $expect_var_names = [];

    public function __construct()
    {
        // Assign the expect_var_names to ensure that we don't accidentally start requiring compiled fields
        $this->expect_var_names = array_keys($this->variables);
    }

    /**
     * Get field values
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        } elseif (method_exists($this, 'var_'.$name)) {
            $method = 'var_'.$name;

            return $this->$method();
        } else {
            throw new \BadMethodCallException(static::class." does not define a '$name' field");
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws \BadMethodCallException values cannot be assigned except with the display method
     */
    public function __set($name, $value)
    {
        throw new \BadMethodCallException(static::class.' variables are read-only, cannot assign '.$name);
    }

    /**
     * Set the data to be rendered in the view - note this does not actually render the view.
     *
     * @param array $variables
     */
    public function display(array $variables)
    {
        if ($errors = $this->validateDisplayVariables($variables)) {
            throw new \InvalidArgumentException(
                "Invalid variables provided to ".static::class."::display()"
                ."\n - ".implode("\n - ", $errors)
            );
        }

        $this->variables = $variables;
    }

    /**
     * @param array $variables
     *
     * @return string[] of errors
     */
    protected function validateDisplayVariables(array $variables)
    {
        $errors             = [];
        $provided_variables = array_keys($variables);
        foreach (array_diff($provided_variables, $this->expect_var_names) as $unexpected_var) {
            if (method_exists($this, 'var_'.$unexpected_var)) {
                $errors[] = "'$unexpected_var' conflicts with ::var_$unexpected_var()";
            } else {
                $errors[] = "'$unexpected_var' is not expected";
            }
        }

        foreach (array_diff($this->expect_var_names, $provided_variables) as $missing_var) {
            $errors[] = "'$missing_var' is missing";
        }

        return $errors;
    }

}
