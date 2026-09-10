<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

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

        $evaluated = $this->collectEvaluatedProperties($schema, $value, $path);
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
     * @param object             $schema
     * @param object             $value
     * @param array<int, string> $visitedRefs
     *
     * @return array<int, string>
     */
    private function collectEvaluatedProperties($schema, object $value, ?JsonPointer $path = null, array $visitedRefs = []): array
    {
        if (!is_object($schema)) {
            return [];
        }

        $evaluated = [];
        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $reference = $schema->{'$ref'};
            if (in_array($reference, $visitedRefs, true)) {
                return [];
            }

            try {
                $visitedRefs[] = $reference;
                $resolvedSchema = $this->factory->getSchemaStorage()->resolveRefSchema($schema);
                if (is_object($resolvedSchema)) {
                    $evaluated = array_merge(
                        $evaluated,
                        $this->collectEvaluatedProperties($resolvedSchema, $value, $path, $visitedRefs)
                    );
                }
            } catch (\Exception $e) {
                // Let the normal reference validation report resolution errors.
            }
        }

        $properties = get_object_vars($value);

        if (property_exists($schema, 'unevaluatedProperties') && $schema->unevaluatedProperties === true) {
            $evaluated = array_merge($evaluated, array_keys($properties));
        }

        if (isset($schema->properties) && is_object($schema->properties)) {
            $evaluated = array_merge(
                $evaluated,
                array_intersect(array_keys(get_object_vars($schema->properties)), array_keys($properties))
            );
        }

        if (isset($schema->patternProperties) && is_object($schema->patternProperties)) {
            foreach ($properties as $propertyName => $_) {
                foreach (array_keys(get_object_vars($schema->patternProperties)) as $pattern) {
                    if (preg_match($this->createPregMatchPattern($pattern), (string) $propertyName)) {
                        $evaluated[] = $propertyName;
                        break;
                    }
                }
            }
        }

        if (property_exists($schema, 'additionalProperties')) {
            if ($schema->additionalProperties === true) {
                $evaluated = array_merge($evaluated, array_keys($properties));
            } elseif (is_object($schema->additionalProperties)) {
                foreach (array_diff(array_keys($properties), $evaluated) as $propertyName) {
                    $propertyPath = $this->propertyPath($path, $propertyName);
                    if ($this->schemaIsValid($schema->additionalProperties, $properties[$propertyName], $propertyPath)) {
                        $evaluated[] = $propertyName;
                    }
                }
            }
        }

        if (isset($schema->allOf) && is_array($schema->allOf)) {
            foreach ($schema->allOf as $branch) {
                if (!$this->schemaIsValid($branch, $value, $path)) {
                    continue;
                }

                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($branch, $value, $path, $visitedRefs));
            }
        }

        if (isset($schema->anyOf) && is_array($schema->anyOf)) {
            foreach ($schema->anyOf as $branch) {
                if (!$this->schemaIsValid($branch, $value, $path)) {
                    continue;
                }

                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($branch, $value, $path, $visitedRefs));
            }
        }

        if (isset($schema->oneOf) && is_array($schema->oneOf)) {
            $validBranches = [];
            foreach ($schema->oneOf as $branch) {
                if ($this->schemaIsValid($branch, $value, $path)) {
                    $validBranches[] = $branch;
                }
            }

            if (count($validBranches) === 1) {
                $evaluated = array_merge(
                    $evaluated,
                    $this->collectEvaluatedProperties($validBranches[0], $value, $path, $visitedRefs)
                );
            }
        }

        if (property_exists($schema, 'if')) {
            $ifMatches = $this->schemaIsValid($schema->if, $value, $path);
            if ($ifMatches) {
                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($schema->if, $value, $path, $visitedRefs));
                if (property_exists($schema, 'then') && $this->schemaIsValid($schema->then, $value, $path)) {
                    $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($schema->then, $value, $path, $visitedRefs));
                }
            } elseif (property_exists($schema, 'else') && $this->schemaIsValid($schema->else, $value, $path)) {
                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($schema->else, $value, $path, $visitedRefs));
            }
        }

        if (isset($schema->dependentSchemas) && is_object($schema->dependentSchemas)) {
            foreach (get_object_vars($schema->dependentSchemas) as $propertyName => $dependentSchema) {
                if (!array_key_exists($propertyName, $properties) || !$this->schemaIsValid($dependentSchema, $value, $path)) {
                    continue;
                }

                $evaluated = array_merge($evaluated, $this->collectEvaluatedProperties($dependentSchema, $value, $path, $visitedRefs));
            }
        }

        return array_values(array_unique($evaluated));
    }

    /**
     * @param mixed $schema
     * @param mixed $value
     */
    private function schemaIsValid($schema, $value, ?JsonPointer $path = null): bool
    {
        $schemaConstraint = $this->factory->createInstanceFor('schema');
        $schemaConstraint->check($value, $schema, $path);

        return $schemaConstraint->isValid();
    }

    private function propertyPath(?JsonPointer $path, string $propertyName): JsonPointer
    {
        $basePath = $path ?? new JsonPointer('');

        return $basePath->withPropertyPaths(array_merge($basePath->getPropertyPaths(), [$propertyName]));
    }

    private function createPregMatchPattern(string $pattern): string
    {
        $pattern = str_replace('\\p{digit}', '\\p{Nd}', $pattern);
        $pattern = str_replace('\\p{Letter}', '\\p{L}', $pattern);

        return '/' . str_replace('/', '\\/', $pattern) . '/u';
    }
}
