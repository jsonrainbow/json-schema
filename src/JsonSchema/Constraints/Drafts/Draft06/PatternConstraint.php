<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class PatternConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    /** @var Factory */
    private $factory;

    public function __construct(?Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->initialiseErrorBag($this->factory);
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'pattern')) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        if (preg_match('/' . str_replace('/', '\/', $schema->pattern) . '/', $value) === 1) {
            return;
        }

        $this->addError(ConstraintError::PATTERN(), $path, ['found' => $value, 'pattern' => $schema->pattern]);
    }
}
