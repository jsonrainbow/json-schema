<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

/**
 * Proof-of-concept support for unevaluatedProperties.
 *
 * The current validator does not carry annotations between applicators, so this
 * first implementation derives the evaluated property names from properties,
 * patternProperties, and allOf branches in the current schema.
 */
class UnevaluatedPropertiesConstraint implements ConstraintInterface
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
        if (!is_object($schema) || !property_exists($schema, 'unevaluatedProperties') || !is_object($value)) {
            return;
        }

        if ($schema->unevaluatedProperties === true) {
            return;
        }

        $evaluated = $this->collectEvaluatedProperties($schema, $value);
        $unevaluated = array_diff_key(get_object_vars($value), array_flip($evaluated));
        if (!$unevaluated) {
            return;
        }

        $basePath = $path ?? new JsonPointer('');
        foreach ($unevaluated as $propertyName => $propertyValue) {
            $propertyPath = $basePath->withPropertyPaths(array_merge($basePath->getPropertyPaths(), [$propertyName]));

            if (is_object($schema->unevaluatedProperties)) {
                $propertyConstraint = $this->factory->createInstanceFor('schema');
                $propertyConstraint->check($propertyValue, $schema->unevaluatedProperties, $propertyPath, $i);
                if ($propertyConstraint->isValid()) {
                    continue;
                }

                $this->addErrors($propertyConstraint->getErrors());
                continue;
            }

            $this->addError(ConstraintError::UNEVALUATED_PROPERTIES(), $propertyPath, ['found' => $propertyName]);
        }
    }

    /**
     * @param object $schema
     * @param object $value
     *
     * @return array<int, string>
     */
    private function collectEvaluatedProperties($schema, object $value): array
    {
        if (!is_object($schema)) {
            return [];
        }

        $evaluated = [];

        if (isset($schema->properties) && is_object($schema->properties)) {
            $evaluated = array_merge($evaluated, array_keys(get_object_vars($schema->properties)));
        }

        if (isset($schema->patternProperties) && is_object($schema->patternProperties)) {
            foreach (get_object_vars($value) as $propertyName => $_) {
                foreach (array_keys(get_object_vars($schema->patternProperties)) as $pattern) {
                    if (preg_match($this->createPregMatchPattern($pattern), (string) $propertyName)) {
                        $evaluated[] = $propertyName;
                        break;
                    }
                }
            }
        }

        if (isset($schema->allOf) && is_array($schema->allOf)) {
            foreach ($schema->allOf as $branch) {
                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($branch, $value));
            }
        }

        return array_values(array_unique($evaluated));
    }

    private function createPregMatchPattern(string $pattern): string
    {
        $pattern = str_replace('\\p{digit}', '\\p{Nd}', $pattern);
        $pattern = str_replace('\\p{Letter}', '\\p{L}', $pattern);

        return '/' . str_replace('/', '\\/', $pattern) . '/u';
    }
}
