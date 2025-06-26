<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class MultipleOfConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'multipleOf')) {
            return;
        }

        if (!is_numeric($value)) {
            return;
        }

        if (fmod($value, $schema->multipleOf) === 0) {
            return;
        }

        $this->addError(ConstraintError::MULTIPLE_OF(), $path, ['multipleOf' => $schema->multipleOf, 'found' => $value]);


    }
}
