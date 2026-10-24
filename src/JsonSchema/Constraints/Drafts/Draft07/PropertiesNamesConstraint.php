<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft07;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class PropertiesNamesConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'propertyNames')) {
            return;
        }

        if (!is_object($value)) {
            return;
        }
        if ($schema->propertyNames === true) {
            return;
        }

        // PHP turns numeric property names into integer keys, while property names are always strings
        $propertyNames = array_map('strval', array_keys(get_object_vars($value)));

        foreach ($propertyNames as $propertyName) {
            $nameConstraint = $this->factory->createInstanceFor('schema');
            $nameConstraint->check($propertyName, $schema->propertyNames, $path, $i);
            if ($nameConstraint->isValid()) {
                continue;
            }

            $violatedKeywords = array_unique(array_map(static function (array $error): string {
                return $error['constraint']['name'];
            }, $nameConstraint->getErrors()));
            foreach ($violatedKeywords as $violatedKeyword) {
                $this->addError(ConstraintError::PROPERTY_NAMES(), $path, ['name' => $propertyName, 'violating' => $violatedKeyword, 'propertyNames' => $schema->propertyNames]);
            }
        }
    }
}
