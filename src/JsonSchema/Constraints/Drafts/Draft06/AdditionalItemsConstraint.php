<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;

class AdditionalItemsConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    public function __construct(?Factory $factory = null)
    {
        $this->initialiseErrorBag($factory ?: new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'additionalItems')) {
            return;
        }

        if ($schema->additionalItems === true) {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $additionalItems = array_diff_key($value, $schema->items);

        foreach ($additionalItems as $key => $_) {
            $this->addError(ConstraintError::ADDITIONAL_ITEMS(), $path, ['item' => $i, 'property' => $key, 'additionalItems' => $schema->additionalItems]);
        }


    }
}
