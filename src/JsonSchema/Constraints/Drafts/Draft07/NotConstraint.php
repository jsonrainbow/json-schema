<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft07;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class NotConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'not')) {
            return;
        }

        // Only the validity of the subschema is needed, its errors are never reported
        $previousFailFast = $this->factory->startFailFast();
        try {
            $schemaConstraint = $this->factory->createInstanceFor('schema');
            $schemaConstraint->check($value, $schema->not, $path, $i);
        } finally {
            if ($previousFailFast !== null) {
                $this->factory->stopFailFast($previousFailFast);
            }
        }

        if (!$schemaConstraint->isValid()) {
            return;
        }

        $this->addError(ConstraintError::NOT(), $path);
    }
}
