<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

class Factory extends \JsonSchema\Constraints\Factory
{
    /**
     * @var array<string, class-string>
     */
    protected $constraintMap = [
        'additionalProperties' => AdditionalPropertiesConstraint::class,
        'dependencies' => DependenciesConstraint::class,
        'type' => TypeConstraint::class,
        'const' => ConstConstraint::class,
        'enum' => EnumConstraint::class,
        'uniqueItems' => UniqueItemsConstraint::class,
        'minItems' => MinItemsConstraint::class,
        'minProperties' => MinPropertiesConstraint::class,
        'maxProperties' => MaxPropertiesConstraint::class,
        'minimum' => MinimumConstraint::class,
        'exclusiveMinimum' => ExclusiveMinimumConstraint::class,
        'minLength' => MinLengthConstraint::class,
        'maxLength' => MaxLengthConstraint::class,
        'maxItems' => MaxItemsConstraint::class,
        'exclusiveMaximum' => ExclusiveMaximumConstraint::class,
    ];
}
