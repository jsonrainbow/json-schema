<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class AdditionalPropertiesConstraint implements ConstraintInterface
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
        if (!property_exists($schema, 'additionalProperties')) {
            return;
        }

        if ($schema->additionalProperties === true) {
            return;
        }

        if (!is_object($value)) {
            return;
        }

        $additionalProperties = get_object_vars($value);

        if (isset($schema->properties)) {
            $additionalProperties = array_diff_key($additionalProperties, (array)$schema->properties);
        }

        if (isset($schema->patternProperties)) {
            $patterns = array_keys(get_object_vars($schema->patternProperties));

            foreach ($additionalProperties as $key => $_) {
                foreach ($patterns as $pattern) {
                    if (preg_match("/{$pattern}/", $key)) {
                        unset($additionalProperties[$key]);
                        break;
                    }
                }
            }
        }

        if (is_object($schema->additionalProperties)) {
            foreach ($additionalProperties as $key => $additionalPropertiesValue) {
                $schemaConstraint = $this->factory->createInstanceFor('schema');
                $schemaConstraint->check($additionalPropertiesValue, $schema->additionalProperties, $path, $i); // @todo increment path
                if ($schemaConstraint->isValid()) {
                    unset($additionalProperties[$key]);
                }
            }
        }

        foreach ($additionalProperties as $key => $additionalPropertiesValue) {
            $this->addError(ConstraintError::ADDITIONAL_PROPERTIES(), $path, ['found' => $additionalPropertiesValue]);
        }
    }
}
