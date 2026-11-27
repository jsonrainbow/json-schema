<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\ValidationException;

class AnyOfConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'anyOf')) {
            return;
        }

        // Only the validity of each branch is needed to know whether any of them matches. The errors of the
        // branches are collected afterwards, by evaluating them once more, only if the anyOf fails.
        $previousFailFast = $this->factory->startFailFast();
        if ($previousFailFast !== null) {
            try {
                foreach ($schema->anyOf as $anyOfSchema) {
                    $schemaConstraint = $this->factory->createInstanceFor('schema');
                    $schemaConstraint->check($value, $anyOfSchema, $path, $i);
                    if ($schemaConstraint->isValid()) {
                        return;
                    }
                }
            } finally {
                $this->factory->stopFailFast($previousFailFast);
            }

            if ($previousFailFast) {
                // Only the validity of this anyOf is needed as well
                $this->addError(ConstraintError::ANY_OF(), $path);

                return;
            }
        }

        foreach ($schema->anyOf as $anyOfSchema) {
            $schemaConstraint = $this->factory->createInstanceFor('schema');

            try {
                $schemaConstraint->check($value, $anyOfSchema, $path, $i);

                if ($schemaConstraint->isValid()) {
                    $this->errorBag()->reset();

                    return;
                }

                $this->addErrors($schemaConstraint->getErrors());
            } catch (ValidationException $e) {
            }
        }

        $this->addError(ConstraintError::ANY_OF(), $path);
    }
}
