<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidArgumentException;

/**
 * The SchemaConstraint Constraints, validates an element against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class SchemaConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function check(&$element, $schema = null, JsonPointer $path = null, $i = null)
    {
        if ($schema !== null) {
            // passed schema
            $this->checkUndefined($element, $schema, $path, $i);
        } elseif ($this->getTypeCheck()->propertyExists($element, $this->inlineSchemaProperty)) {
            $inlineSchema = $this->getTypeCheck()->propertyGet($element, $this->inlineSchemaProperty);
            if (is_array($inlineSchema)) {
                $inlineSchema = json_decode(json_encode($inlineSchema));
            }
            // inline schema
            $this->checkUndefined($element, $inlineSchema, $path, $i);
        } else {
            throw new InvalidArgumentException('no schema found to verify against');
        }
    }
}
