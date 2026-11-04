<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class ContentConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        // See https://json-schema.org/draft/2020-12/json-schema-validation#section-8
    }
}
