<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

class PropertiesNamesConstraint extends \JsonSchema\Constraints\Drafts\Draft2019\PropertiesNamesConstraint
{
    public function __construct(?Factory $factory = null)
    {
        parent::__construct($factory ?: new Factory());
    }
}
