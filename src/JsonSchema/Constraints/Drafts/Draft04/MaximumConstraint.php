<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft04;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

/**
 * In Draft-04, exclusiveMaximum is a boolean modifier of maximum, not a keyword of its own.
 */
class MaximumConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'maximum')) {
            return;
        }

        if (!is_numeric($value)) {
            return;
        }

        $exclusive = property_exists($schema, 'exclusiveMaximum') && $schema->exclusiveMaximum === true;
        if ($exclusive ? $value < $schema->maximum : $value <= $schema->maximum) {
            return;
        }

        $this->addError(
            $exclusive ? ConstraintError::EXCLUSIVE_MAXIMUM() : ConstraintError::MAXIMUM(),
            $path,
            ['maximum' => $schema->maximum, 'found' => $value]
        );
    }
}
