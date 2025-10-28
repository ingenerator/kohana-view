<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel;

use Closure;
use Error;
use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModelProperty;
use ReflectionClass;

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
     * @var array{expected_vars: list<string>, defaults: array{string, mixed}}
     */
    private array $display_var_schema;

    public function __construct()
    {
        // @todo remove the constructor when we have a rector to remove the parent::__construct call
    }

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
        $this->display_var_schema ??= $this->parseViewVarSchema();

        // Merge in defaults for any optional properties before validating
        $variables = [...$this->display_var_schema['defaults'], ...$variables];

        // Then validate they provided all / only properties that are expected
        $errors = [];
        $provided_variables = array_keys($variables);
        if ($unexpected = array_diff($provided_variables, $this->display_var_schema['expected_vars'])) {
            $errors[] = 'Unexpected vars: '.json_encode(array_values($unexpected));
        }

        if ($missing = array_diff($this->display_var_schema['expected_vars'], $provided_variables)) {
            $errors[] = 'Missing vars: '.json_encode(array_values($missing));
        }

        if ($errors !== []) {
            throw InvalidDisplayVariablesException::passedToDisplay(static::class, $errors);
        }

        return $variables;
    }

    private function parseViewVarSchema(): array
    {
        // @todo: Support optional caching of this metadata
        $refl = new ReflectionClass(static::class);

        $schema = [
            'expected_vars' => [],
            'defaults' => [],
        ];

        foreach ($refl->getProperties() as $property) {
            // If there is an explicit attribute on the property that always forces the treatment
            $attr = ($property->getAttributes(ViewModelProperty::class)[0] ?? null)?->newInstance();

            // Without an attribute, guess based on the property definition. Displayable properties are:
            // - public (at least for get)
            // - not a dependency that was injected as a constructor promoted property
            // - not virtual (e.g. with a get hook and no actual backing property).
            if ( ! $attr instanceof ViewModelProperty) {
                $attr = new ViewModelProperty(
                    is_displayable: $property->isPublic()
                    && ! $property->isPromoted()
                    && ! $property->isVirtual()
                );
            }

            if ($attr->is_displayable) {
                $schema['expected_vars'][] = $property->getName();
            }

            if ($attr->is_optional && $property->hasDefaultValue()) {
                $schema['defaults'][$property->getName()] = $property->getDefaultValue();
            }
            // @todo: Should we throw if they say it's optional but it has no default?
        }

        return $schema;
    }

    protected function getCached(string $key, Closure $getter): mixed
    {
        if ( ! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $getter();
        }

        return $this->cache[$key];
    }
}
