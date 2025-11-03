<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel;

use Closure;
use Error;
use Ingenerator\KohanaView\Attribute\DisplayVariableAttribute;
use Ingenerator\KohanaView\Attribute\InternalDisplayVariable;
use Ingenerator\KohanaView\Attribute\RequiredDisplayVariable;
use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\Exception\InvalidViewDefinitionException;
use Ingenerator\KohanaView\ViewModel;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function assert;
use function count;
use function sprintf;

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
        $schema = $this->parseViewVarSchema();

        // Merge in defaults for any optional properties before validating
        $variables = [...$schema['defaults'], ...$variables];

        // Then validate they provided all / only properties that are expected
        $provided_variables = array_keys($variables);
        $errors = array_filter([
            'unexpected' => array_values(array_diff($provided_variables, $schema['expected_vars'])),
            'missing' => array_values(array_diff($schema['expected_vars'], $provided_variables)),
        ]);

        if ($errors !== []) {
            throw InvalidDisplayVariablesException::passedToDisplay(static::class, $errors);
        }

        return $variables;
    }

    /**
     * @return array{expected_vars: list<string>, defaults: array{string, mixed}}
     */
    private function parseViewVarSchema(): array
    {
        // @todo: Support optional caching of this metadata
        $refl = new ReflectionClass(static::class);

        $schema = [
            'expected_vars' => [],
            'defaults' => [],
        ];

        foreach ($refl->getProperties() as $property) {
            $attr = $this->getOrDefaultSingleDisplayVariableAttributeForProperty($property);
            $this->validatePropertyAttributeDefinition($attr, $property);

            if ($attr->canPassToDisplay()) {
                $schema['expected_vars'][] = $property->getName();
                if ( ! $attr->mustPassToDisplay()) {
                    $schema['defaults'][$property->getName()] = $property->getDefaultValue();
                }
            }
        }

        return $schema;
    }

    private function getOrDefaultSingleDisplayVariableAttributeForProperty(ReflectionProperty $property): DisplayVariableAttribute
    {
        // If there is an explicit attribute on the property that always forces the treatment
        $attributes = $property->getAttributes(DisplayVariableAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) > 1) {
            throw new InvalidViewDefinitionException(
                sprintf(
                    'Expected only one %s per property but got %s on %s->$%s (%s)',
                    DisplayVariableAttribute::class,
                    count($attributes),
                    static::class,
                    $property->getName(),
                    implode(',', array_map(fn (ReflectionAttribute $a): string => $a->getName(), $attributes))
                )
            );
        }

        if ($attributes !== []) {
            $attr = $attributes[0]->newInstance();
            assert($attr instanceof DisplayVariableAttribute);

            return $attr;
        }

        // Without an attribute, guess based on the property definition. Displayable properties are:
        // - public (at least for get)
        // - not a dependency that was injected as a constructor promoted property
        // - not virtual (e.g. with a get hook and no actual backing property).
        if ($property->isPublic() && ! $property->isPromoted() && ! $property->isVirtual()) {
            return new RequiredDisplayVariable();
        }

        // Otherwise, assume this is an internal property that should not be passed to display
        return new InternalDisplayVariable();
    }

    private function validatePropertyAttributeDefinition(DisplayVariableAttribute $attr, ReflectionProperty $property): void
    {
        if ($attr->mustPassToDisplay() && ! $attr->canPassToDisplay()) {
            throw new InvalidViewDefinitionException(
                sprintf(
                    'Attribute %s marked that property must be provided but can not be provided (on %s:%s)',
                    $attr::class,
                    static::class,
                    $property->getName()
                )
            );
        }

        if ($attr->canPassToDisplay() && ! $attr->mustPassToDisplay() && ! $property->hasDefaultValue()) {
            throw new InvalidViewDefinitionException(
                sprintf(
                    '%s->$%s was tagged as %s, but has no default value',
                    static::class,
                    $property->getName(),
                    $attr::class
                )
            );
        }
    }

    protected function getCached(string $key, Closure $getter): mixed
    {
        if ( ! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $getter();
        }

        return $this->cache[$key];
    }
}
