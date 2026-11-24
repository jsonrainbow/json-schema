<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft04;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

/**
 * In Draft-04, exclusiveMinimum is a boolean modifier of minimum, not a keyword of its own.
 */
class MinimumConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'minimum')) {
            return;
        }

        if (!is_numeric($value)) {
            return;
        }

        $exclusive = property_exists($schema, 'exclusiveMinimum') && $schema->exclusiveMinimum === true;
        if ($exclusive ? $value > $schema->minimum : $value >= $schema->minimum) {
            return;
        }

        $this->addError(
            $exclusive ? ConstraintError::EXCLUSIVE_MINIMUM() : ConstraintError::MINIMUM(),
            $path,
            ['minimum' => $schema->minimum, 'found' => $value]
        );
    }
}
