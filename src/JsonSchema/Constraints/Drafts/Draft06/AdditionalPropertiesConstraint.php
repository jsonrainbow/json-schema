<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class AdditionalPropertiesConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'additionalProperties')) {
            return;
        }

        if ($schema->additionalProperties === true) {
            return;
        }

        if (!is_object($value)) {
            return;
        }

        $additionalProperties = array_diff_key(get_object_vars($value), (array) $schema->properties);
        // @todo additional properties should bechecked against the patternProperties
        if ($schema->additionalProperties === false && $additionalProperties !== []) {
            $this->addError(ConstraintError::ADDITIONAL_PROPERTIES(), $path, ['additionalProperties' => array_keys($additionalProperties)]);
        }
    }
}
