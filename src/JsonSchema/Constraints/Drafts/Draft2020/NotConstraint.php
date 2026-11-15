<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2020;

class NotConstraint extends \JsonSchema\Constraints\Drafts\Draft2019\NotConstraint
{
    public function __construct(?Factory $factory = null)
    {
        parent::__construct($factory ?: new Factory());
    }
}
