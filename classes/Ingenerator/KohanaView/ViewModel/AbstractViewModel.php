<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\ViewModel;
use ReflectionClass;
use ReflectionProperty;
use function array_diff;
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

    private array $expected_display_variables;

    /**
     * Set the data to be rendered in the view - note this does not actually render the view.
     *
     * @param array<string,mixed> $variables
     */
    public function display(array $variables): void
    {
        if ($errors = $this->validateDisplayVariables($variables)) {
            throw InvalidDisplayVariablesException::passedToDisplay(static::class, $errors);
        }

        $this->cache = [];
        try {
            foreach ($variables as $key => $value) {
                $this->$key = $value;
            }
        } catch (\Error $e) {
            throw new InvalidDisplayVariablesException($e->getMessage(), $e->getCode(), $e);
        }

//        // Reinstate default variables to ensure they are in expected state when using view in a loop
//        $variables = \array_merge($this->default_variables, $variables);
    }

    /**
     * @param array<string,mixed> $variables
     *
     * @return list<string> of errors
     */
    protected function validateDisplayVariables(array $variables): array
    {
        $this->expected_display_variables ??= $this->listExpectedDisplayVariables();

        $errors = [];
        $provided_variables = array_keys($variables);
        if ($unexpected = array_diff($provided_variables, $this->expected_display_variables)) {
            $errors[] = 'Unexpected vars: '.json_encode(array_values($unexpected));
        }

        if ($missing = array_diff($this->expected_display_variables, $provided_variables)) {
            $errors[] = 'Missing vars: '.json_encode(array_values($missing));
        }

        return $errors;
    }

    private function listExpectedDisplayVariables(): array
    {
        // @todo: Support optional caching of this metadata
        $refl = new ReflectionClass($this::class);

        $expected = array_filter(
            $refl->getProperties(),
            function (ReflectionProperty $property) {
                // If there is an explicit attribute on the property that always forces the treatment
                $attr = ($property->getAttributes(ViewModelProperty::class)[0] ?? null)?->newInstance();
                if ($attr instanceof ViewModelProperty) {
                    return $attr->is_displayable;
                }

                // Without an attribute, guess based on the property definition. Displayable properties are:
                // - public (at least for get)
                // - not a dependency that was injected as a constructor promoted property
                // - not virtual (e.g. with a get hook and no actual backing property).
                return $property->isPublic()
                    && !$property->isPromoted()
                    && !$property->isVirtual();
            },
        );

        return array_map(fn(ReflectionProperty $p) => $p->getName(), array_values($expected));
    }

    protected function getCached(string $key, \Closure $getter): mixed
    {
        return $this->cache[$key] ??= $getter();
    }

}
