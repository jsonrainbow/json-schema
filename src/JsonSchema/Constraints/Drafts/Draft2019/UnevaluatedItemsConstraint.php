<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class UnevaluatedItemsConstraint implements ConstraintInterface
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
        if (!is_object($schema) || !property_exists($schema, 'unevaluatedItems') || !is_array($value)) {
            return;
        }

        if ($schema->unevaluatedItems === true) {
            return;
        }

        $evaluatedCount = $this->countEvaluatedItems($schema, $value, $path);
        $unevaluated = array_slice($value, $evaluatedCount, null, true);
        if (!$unevaluated) {
            return;
        }

        $basePath = $path ?? new JsonPointer('');
        foreach ($unevaluated as $index => $itemValue) {
            $itemPath = $basePath->withPropertyPaths(array_merge($basePath->getPropertyPaths(), [$index]));

            if (is_object($schema->unevaluatedItems)) {
                $itemConstraint = $this->factory->createInstanceFor('schema');
                $itemConstraint->check($itemValue, $schema->unevaluatedItems, $itemPath, $i);
                if ($itemConstraint->isValid()) {
                    continue;
                }

                $this->addErrors($itemConstraint->getErrors());
                continue;
            }

            $this->addError(ConstraintError::UNEVALUATED_ITEMS(), $itemPath, ['index' => $index]);
        }
    }

    /**
     * In Draft 2019-09 the evaluated items always form a prefix of the array, so it is
     * sufficient to derive how many leading items are evaluated by the supported schema branches.
     *
     * @param array<int, mixed>  $value
     * @param array<int, string> $visitedRefs
     */
    private function countEvaluatedItems(object $schema, array $value, ?JsonPointer $path = null, array $visitedRefs = []): int
    {
        $itemCount = count($value);
        $evaluated = 0;

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            $reference = $schema->{'$ref'};
            if (in_array($reference, $visitedRefs, true)) {
                return 0;
            }

            try {
                $visitedRefs[] = $reference;
                $resolvedSchema = $this->factory->getSchemaStorage()->resolveRefSchema($schema);
                if (is_object($resolvedSchema)) {
                    $evaluated = max($evaluated, $this->countEvaluatedItems($resolvedSchema, $value, $path, $visitedRefs));
                }
            } catch (\Exception $e) {
                // Let the normal reference validation report resolution errors.
            }
        }

        if (property_exists($schema, 'unevaluatedItems') && $schema->unevaluatedItems === true) {
            return $itemCount;
        }

        if (property_exists($schema, 'items')) {
            if (is_array($schema->items)) {
                $evaluated = max($evaluated, min(count($schema->items), $itemCount));
                if (property_exists($schema, 'additionalItems')) {
                    return $itemCount;
                }
            } else {
                return $itemCount;
            }
        }

        foreach (['allOf', 'anyOf'] as $keyword) {
            if (!isset($schema->{$keyword}) || !is_array($schema->{$keyword})) {
                continue;
            }

            foreach ($schema->{$keyword} as $branch) {
                if (!is_object($branch) || !$this->schemaIsValid($branch, $value, $path)) {
                    continue;
                }

                $evaluated = max($evaluated, $this->countEvaluatedItems($branch, $value, $path, $visitedRefs));
            }
        }

        if (isset($schema->oneOf) && is_array($schema->oneOf)) {
            $validBranchCount = 0;
            $validBranches = [];
            foreach ($schema->oneOf as $branch) {
                if ($this->schemaIsValid($branch, $value, $path)) {
                    ++$validBranchCount;
                    if (is_object($branch)) {
                        $validBranches[] = $branch;
                    }
                }
            }

            if ($validBranchCount === 1 && count($validBranches) === 1) {
                $evaluated = max($evaluated, $this->countEvaluatedItems($validBranches[0], $value, $path, $visitedRefs));
            }
        }

        if (property_exists($schema, 'if')) {
            if ($this->schemaIsValid($schema->if, $value, $path)) {
                if (is_object($schema->if)) {
                    $evaluated = max($evaluated, $this->countEvaluatedItems($schema->if, $value, $path, $visitedRefs));
                }
                if (property_exists($schema, 'then') && is_object($schema->then) && $this->schemaIsValid($schema->then, $value, $path)) {
                    $evaluated = max($evaluated, $this->countEvaluatedItems($schema->then, $value, $path, $visitedRefs));
                }
            } elseif (property_exists($schema, 'else') && is_object($schema->else) && $this->schemaIsValid($schema->else, $value, $path)) {
                $evaluated = max($evaluated, $this->countEvaluatedItems($schema->else, $value, $path, $visitedRefs));
            }
        }

        return min($evaluated, $itemCount);
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
}
