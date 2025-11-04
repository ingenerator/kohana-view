<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel;

use Closure;
use Error;
use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\DisplaySchema\ViewDisplaySchemaProviderInstance;

use function array_diff;
use function array_key_exists;
use function array_keys;

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
    private array $cache = [];

    /**
     * Set the data to be rendered in the view - note this does not actually render the view.
     */
    public function display(array $variables): void
    {
        $variables = $this->mergeDefaultsAndValidateVariables($variables);

        // Clear any cached computed properties
        $this->cache = [];
        try {
            foreach ($variables as $key => $value) {
                $this->$key = $value;
            }
        } catch (Error $e) {
            throw new InvalidDisplayVariablesException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function mergeDefaultsAndValidateVariables(array $variables): array
    {
        $schema = ViewDisplaySchemaProviderInstance::getDisplaySchema(static::class);

        // Merge in defaults for any optional properties before validating
        $variables = [...$schema->defaults, ...$variables];

        // Then validate they provided all / only properties that are expected
        $provided_variables = array_keys($variables);
        $errors = array_filter([
            'unexpected' => array_values(array_diff($provided_variables, $schema->expected_vars)),
            'missing' => array_values(array_diff($schema->expected_vars, $provided_variables)),
        ]);

        if ($errors !== []) {
            throw InvalidDisplayVariablesException::passedToDisplay(static::class, $errors);
        }

        return $variables;
    }

    protected function getCached(string $key, Closure $getter): mixed
    {
        if ( ! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $getter();
        }

        return $this->cache[$key];
    }
}
