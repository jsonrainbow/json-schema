<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

class Factory extends \JsonSchema\Constraints\Factory
{
    /**
     * @var array<string, class-string>
     */
    protected $constraintMap = [
        'schema' => Draft2019Constraint::class,
        'additionalProperties' => AdditionalPropertiesConstraint::class,
        'unevaluatedProperties' => UnevaluatedPropertiesConstraint::class,
        'additionalItems' => AdditionalItemsConstraint::class,
        'dependentSchemas' => DependentSchemasConstraint::class,
        'dependentRequired' => DependentRequiredConstraint::class,
        'type' => TypeConstraint::class,
        'const' => ConstConstraint::class,
        'enum' => EnumConstraint::class,
        'uniqueItems' => UniqueItemsConstraint::class,
        'minItems' => MinItemsConstraint::class,
        'minProperties' => MinPropertiesConstraint::class,
        'maxProperties' => MaxPropertiesConstraint::class,
        'minimum' => MinimumConstraint::class,
        'maximum' => MaximumConstraint::class,
        'exclusiveMinimum' => ExclusiveMinimumConstraint::class,
        'minLength' => MinLengthConstraint::class,
        'maxLength' => MaxLengthConstraint::class,
        'maxItems' => MaxItemsConstraint::class,
        'exclusiveMaximum' => ExclusiveMaximumConstraint::class,
        'multipleOf' => MultipleOfConstraint::class,
        'required' => RequiredConstraint::class,
        'format' => FormatConstraint::class,
        'anyOf' => AnyOfConstraint::class,
        'allOf' => AllOfConstraint::class,
        'oneOf' => OneOfConstraint::class,
        'not' => NotConstraint::class,
        'ifThenElse' => IfThenElseConstraint::class,
        'contains' => ContainsConstraint::class,
        'propertyNames' => PropertiesNamesConstraint::class,
        'patternProperties' => PatternPropertiesConstraint::class,
        'pattern' => PatternConstraint::class,
        'properties' => PropertiesConstraint::class,
        'items' => ItemsConstraint::class,
        'ref' => RefConstraint::class,
        'recursiveRef' => RecursiveRefConstraint::class,
        'content' => ContentConstraint::class,
    ];

    /** @var DynamicScope|null */
    private $dynamicScope;

    public function getDynamicScope(): DynamicScope
    {
        if ($this->dynamicScope === null) {
            $this->dynamicScope = new DynamicScope();
        }

        return $this->dynamicScope;
    }

    public function setDynamicScope(DynamicScope $dynamicScope): void
    {
        $this->dynamicScope = $dynamicScope;
    }

    /**
     * Resolve a $recursiveRef. Its initial target is resolved like a $ref, and when that target
     * declares "$recursiveAnchor": true, the outermost schema resource of the dynamic scope
     * reachable through resources that declare it as well is used instead.
     *
     * @return mixed
     */
    public function resolveRecursiveRef(string $reference)
    {
        $refSchema = $this->getSchemaStorage()->resolveRef($reference);
        if (!$this->hasRecursiveAnchor($refSchema)) {
            return $refSchema;
        }

        foreach ($this->getDynamicScope()->getResources() as $resource) {
            if (!$this->hasRecursiveAnchor($resource)) {
                break;
            }

            $refSchema = $resource;
        }

        return $refSchema;
    }

    /**
     * @param mixed $schema
     */
    private function hasRecursiveAnchor($schema): bool
    {
        return is_object($schema) && property_exists($schema, '$recursiveAnchor') && $schema->{'$recursiveAnchor'} === true;
    }
}
