<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft06;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Entity\JsonPointer;

class Draft06Constraint extends Constraint
{
    public function __construct()
    {
        parent::__construct(new Factory());
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        // Apply defaults
        // Required keyword
        $this->checkForKeyword('type', $value, $schema, $path, $i);
        // Not
        $this->checkForKeyword('dependencies', $value, $schema, $path, $i);
        // allof
        // anyof
        // oneof

        // array
        // object
        // string
        $this->checkForKeyword('additionalProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('uniqueItems', $value, $schema, $path, $i);
        $this->checkForKeyword('minItems', $value, $schema, $path, $i);
        $this->checkForKeyword('minProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('maxProperties', $value, $schema, $path, $i);
        $this->checkForKeyword('minimum', $value, $schema, $path, $i);
        $this->checkForKeyword('minLength', $value, $schema, $path, $i);
        $this->checkForKeyword('exclusiveMinimum', $value, $schema, $path, $i);
        $this->checkForKeyword('maxItems', $value, $schema, $path, $i);
        $this->checkForKeyword('maxLength', $value, $schema, $path, $i);
        $this->checkForKeyword('exclusiveMaximum', $value, $schema, $path, $i);
        $this->checkForKeyword('enum', $value, $schema, $path, $i);
        $this->checkForKeyword('const', $value, $schema, $path, $i);
    }

    protected function checkForKeyword(string $keyword, $value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        $validator = $this->factory->createInstanceFor($keyword);
        $validator->check($value, $schema, $path, $i);

        $this->addErrors($validator->getErrors());
    }
}
