<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class DependenciesConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'dependencies')) {
            return;
        }

        if (!is_object($value)) {
            return;
        }

        foreach ($schema->dependencies as $dependant => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (property_exists($value, $dependant) && !property_exists($value, $dependency)) {
                    $this->addError(ConstraintError::DEPENDENCIES(), $path, ['dependant' => $dependant, 'dependency' => $dependency]);
                }
            }
        }
    }
}
