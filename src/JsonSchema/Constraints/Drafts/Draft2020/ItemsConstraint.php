<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class ItemsConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'items')) {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        // Items already covered by prefixItems are not evaluated by items
        $prefixItemsCount = property_exists($schema, 'prefixItems') ? count($schema->prefixItems) : 0;
        $basePath = $path ?? new JsonPointer('');
        foreach (array_slice($value, $prefixItemsCount, null, true) as $propertyName => $propertyValue) {
            $incrementedPath = $basePath->withPropertyPaths(array_merge($basePath->getPropertyPaths(), [$propertyName]));
            $schemaConstraint = $this->factory->createInstanceFor('schema');
            $schemaConstraint->check($propertyValue, $schema->items, $incrementedPath, $i);
            if ($schemaConstraint->isValid()) {
                continue;
            }

            $this->addErrors($schemaConstraint->getErrors());
        }
    }
}
