<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\DisplaySchema;

use Ingenerator\KohanaView\Attribute\DisplayVariableAttribute;
use Ingenerator\KohanaView\Attribute\InternalDisplayVariable;
use Ingenerator\KohanaView\Attribute\RequiredDisplayVariable;
use Ingenerator\KohanaView\Exception\InvalidViewDefinitionException;
use Ingenerator\KohanaView\ViewModelDisplaySchemaProvider;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

use function assert;
use function count;
use function sprintf;

final class CoreDisplaySchemaProvider implements ViewModelDisplaySchemaProvider
{
    public function getSchema(string $model_class): ViewModelDisplaySchema
    {
        $refl = new ReflectionClass($model_class);

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

        return new ViewModelDisplaySchema(...$schema);
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
                    $property->getDeclaringClass()->getName(),
                    $property->getName(),
                    implode(',', array_map(fn (ReflectionAttribute $a): string => $a->getName(), $attributes)),
                ),
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
                    $property->getDeclaringClass()->getName(),
                    $property->getName(),
                ),
            );
        }

        if ($attr->canPassToDisplay() && ! $attr->mustPassToDisplay() && ! $property->hasDefaultValue()) {
            throw new InvalidViewDefinitionException(
                sprintf(
                    '%s->$%s was tagged as %s, but has no default value',
                    $property->getDeclaringClass()->getName(),
                    $property->getName(),
                    $attr::class,
                ),
            );
        }
    }
}
