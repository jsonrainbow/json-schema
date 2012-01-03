<?php

namespace JsonSchema\Constraints;

/**
 * The Schema Constraints, validates an element against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Schema extends Constraint
{
    /**
     * {inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        if ($schema !== null) {
            // passed schema
            $this->checkUndefined($element, $schema, '', '');
        } elseif (isset($element->{$this->inlineSchemaProperty})) {
            // inline schema
            $this->checkUndefined($element, $element->{$this->inlineSchemaProperty}, '', '');
        } else {
            throw new \InvalidArgumentException('no schema found to verify against');
        }
    }
}