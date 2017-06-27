<?php

namespace JsonSchema;

/**
 * Alias class for \MabeEnum\Enum, so that the underlying class can be easily changed or extended.
 *
 * This library currently contains no custom functionality, but defines this class so that
 * the Enum interface can be preserved if the upstream API changes, or a different Enum package
 * is used.
 *
 * @package justinrainbow\json-schema
 *
 * @license MIT
 */
abstract class Enum extends \MabeEnum\Enum
{
}
