<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft07;

use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Tool\EcmaPatternConverter;

class PatternPropertiesConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'patternProperties')) {
            return;
        }

        if (!is_object($value)) {
            return;
        }

        $properties = get_object_vars($value);

        foreach ($properties as $propertyName => $propertyValue) {
            foreach ($schema->patternProperties as $patternPropertyRegex => $patternPropertySchema) {
                $matchPattern = EcmaPatternConverter::toPcre($patternPropertyRegex);
                if (preg_match($matchPattern, (string) $propertyName)) {
                    $schemaConstraint = $this->factory->createInstanceFor('schema');
                    $schemaConstraint->check($propertyValue, $patternPropertySchema, $path, $i);
                    if ($schemaConstraint->isValid()) {
                        continue;
                    }

                    $this->addErrors($schemaConstraint->getErrors());
                }
            }
        }
    }
}
