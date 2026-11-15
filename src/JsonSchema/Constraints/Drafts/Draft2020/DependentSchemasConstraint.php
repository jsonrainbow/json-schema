<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

class DependentSchemasConstraint extends \JsonSchema\Constraints\Drafts\Draft2019\DependentSchemasConstraint
{
    public function __construct(?Factory $factory = null)
    {
        parent::__construct($factory ?: new Factory());
    }
}
