MethodException: 
Line |
   2 |  … pertiesConstraint.php'; $s=$s.Replace([char]13+[char]10,[char]10); $s
     |                            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     | Cannot convert argument "oldChar", with value: "
", for "Replace" to type "System.Char": "Cannot convert value "
" to type "System.Char". Error: "String must be exactly one character long.""
<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

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
                $propertyPath = ($path ?? new JsonPointer(''))->withPropertyPaths(
                    array_merge(($path ?? new JsonPointer(''))->getPropertyPaths(), [$key])
                );
                $schemaConstraint->check($additionalPropertiesValue, $schema->additionalProperties, $propertyPath, $i);
                if ($schemaConstraint->isValid()) {
                    unset($additionalProperties[$key]);
                }
            }
        }

        foreach ($additionalProperties as $key => $additionalPropertiesValue) {
            $propertyPath = ($path ?? new JsonPointer(''))->withPropertyPaths(
                array_merge(($path ?? new JsonPointer(''))->getPropertyPaths(), [$key])
            );
            $this->addError(ConstraintError::ADDITIONAL_PROPERTIES(), $propertyPath, ['found' => $key]);
        }
    }

    private function createPregMatchPattern(string $pattern): string
    {
        $replacements = [
//            '\D' => '[^0-9]',
//            '\d' => '[0-9]',
            '\p{digit}' => '\p{Nd}',
//            '\w' => '[A-Za-z0-9_]',
//            '\W' => '[^A-Za-z0-9_]',
//            '\s' => '[\s\x{200B}]' // Explicitly include zero width white space,
            '\p{Letter}' => '\p{L}', // Map ECMA long property name to PHP (PCRE) Unicode property abbreviations
        ];

        $pattern = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $pattern
        );

        return '/' . str_replace('/', '\/', $pattern) . '/u';
    }
}

