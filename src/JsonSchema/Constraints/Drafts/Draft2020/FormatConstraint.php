<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\Entity\JsonPointer;

class FormatConstraint extends \JsonSchema\Constraints\Drafts\Draft2019\FormatConstraint
{
    /** @var Factory */
    private $factory;

    public function __construct(?Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        parent::__construct($this->factory);
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        // Format is an annotation by default, asserting it requires opting in
        // See https://json-schema.org/draft/2020-12/json-schema-validation#section-7.2.1
        if (!$this->factory->getConfig(Constraint::CHECK_MODE_FORMAT_ASSERTION)) {
            return;
        }

        parent::check($value, $schema, $path, $i);
    }
}
