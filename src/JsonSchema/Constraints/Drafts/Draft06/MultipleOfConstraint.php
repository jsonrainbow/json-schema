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

        if (fmod($value, $schema->multipleOf) === 0.0 || $this->isMultipleOf((string) $value, (string) $schema->multipleOf)) {
            return;
        }

        $this->addError(ConstraintError::MULTIPLE_OF(), $path, ['multipleOf' => $schema->multipleOf, 'found' => $value]);
    }

    private function isMultipleOf(string $value, string $multipleOf): bool
    {
        if (bccomp($multipleOf, '0', 20) === 0) {
            return false;
        }

        $div = bcdiv($value, $multipleOf, 20);

        return bccomp(bcmod($div, '1', 20), '0', 20) === 0;
    }
}
