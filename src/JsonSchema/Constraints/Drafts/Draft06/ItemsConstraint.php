<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Rfc3339;
use JsonSchema\Tool\Validator\RelativeReferenceValidator;
use JsonSchema\Tool\Validator\UriValidator;

class ItemsConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    /** @var \JsonSchema\Constraints\Drafts\Draft06\Factory */
    private $factory;
    public function __construct(?Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->initialiseErrorBag($this->factory);
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'items')) {
            return;
        }

        $properties = [];
        if (is_object($value)) {
            $properties = get_object_vars($value);
        }
        if (is_array($value)) {
            $properties = $value;
        }
        if (is_object($schema->items)) {
            foreach ($properties as $propertyName => $propertyValue) {
                $schemaConstraint = $this->factory->createInstanceFor('schema');
                $schemaConstraint->check($propertyValue, $schema->items, $path, $i);
                if ($schemaConstraint->isValid()) {
                    continue;
                }

                $this->addErrors($schemaConstraint->getErrors());
            }
        }
    }
}
