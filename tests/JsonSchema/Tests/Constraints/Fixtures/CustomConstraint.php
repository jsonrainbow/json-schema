<?php
namespace JsonSchema\Tests\Constraints\Fixtures;

use \JsonSchema\Constraints\Constraint;

class CustomConstraint extends Constraint
{
    public function check($value, $schema = null, $path = null, $i = null)
    {
    }

}