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
            $additionalProperties = array_diff_key($additionalProperties, (array) $schema->properties);
        }

        if (isset($schema->patternProperties)) {
            $patterns = array_keys(get_object_vars($schema->patternProperties));

            foreach ($additionalProperties as $key => $_) {
                foreach ($patterns as $pattern) {
                    if (preg_match($this->createPregMatchPattern($pattern), (string) $key)) {
                        unset($additionalProperties[$key]);
                        break;
                    }
                }
            }
        }

        if (is_object($schema->additionalProperties)) {
            foreach ($additionalProperties as $key => $additionalPropertiesValue) {
                $schemaConstraint = $this->factory->createInstanceFor('schema');
                $schemaConstraint->check($additionalPropertiesValue, $schema->additionalProperties, ($path ?? new JsonPointer(''))->withAppendedPath($key), $i);

                // The property is permitted by the schema, so it is never an "additional property"
                // error; what it failed is the sub-schema, and those errors carry the reason.
                if (!$schemaConstraint->isValid()) {
                    $this->addErrors($schemaConstraint->getErrors());
                }

                unset($additionalProperties[$key]);
            }
        }

        foreach ($additionalProperties as $key => $additionalPropertiesValue) {
            $this->addError(ConstraintError::ADDITIONAL_PROPERTIES(), ($path ?? new JsonPointer(''))->withAppendedPath($key), ['found' => $key]);
        }
    }

    private function createPregMatchPattern(string $pattern): string
    {
        $replacements = [
            // PCRE with /u makes \d, \D, \w and \W Unicode aware, while ECMA-262 defines
            // them over ASCII only, so they are narrowed back to their ECMA meaning.
            '\\D' => '[^0-9]',
            '\\d' => '[0-9]',
            '\\w' => '[A-Za-z0-9_]',
            '\\W' => '[^A-Za-z0-9_]',
            '\\s' => '[\\s\\x{200B}]', // Explicitly include zero width white space
            // PCRE rejects the ECMA long property names, so they are mapped to its abbreviations.
            '\\p{digit}' => '\\p{Nd}',
            '\\p{Letter}' => '\\p{L}',
        ];

        $pattern = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $pattern
        );

        return '/' . str_replace('/', '\/', $pattern) . '/u';
    }
}
