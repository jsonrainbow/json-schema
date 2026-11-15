<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class PrefixItemsConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'prefixItems')) {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $basePath = $path ?? new JsonPointer('');
        foreach ($schema->prefixItems as $index => $itemSchema) {
            if (!array_key_exists($index, $value)) {
                break;
            }

            $incrementedPath = $basePath->withPropertyPaths(array_merge($basePath->getPropertyPaths(), [$index]));
            $schemaConstraint = $this->factory->createInstanceFor('schema');
            $schemaConstraint->check($value[$index], $itemSchema, $incrementedPath, $i);
            if ($schemaConstraint->isValid()) {
                continue;
            }

            $this->addErrors($schemaConstraint->getErrors());
        }
    }
}
