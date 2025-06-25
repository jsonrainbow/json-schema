<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

class Factory extends \JsonSchema\Constraints\Factory
{
    /**
     * @var array<string, class-string>
     */
    protected $constraintMap = [
        'type' => TypeConstraint::class,
        'const' => ConstConstraint::class,
        'enum' => EnumConstraint::class,
        'number' => NumberConstraint::class,
        'uniqueItems' => UniqueItemsConstraint::class,
        'minItems' => MinItemsConstraint::class,
        'minProperties' => MinPropertiesConstraint::class,
        'minimum' => MinimumConstraint::class,
        'exclusiveMinimum' => ExclusiveMinimumConstraint::class,
        'minLength' => MinLengthConstraint::class,
        'maxItems' => MaxItemsConstraint::class,
    ];
}
