<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JsonSchema\Constraints;

use JsonSchema\Uri\UriRetriever;

/**
 * CallableConstraint
 *
 * Used internally to register a custom callable constraint via:
 *    ($validator|$factory)->addConstraint('name', \Callable)
 *
 */
class CallableConstraint extends Constraint
{

    /**
     * @var \Callable
     */
    private $callable;

    /**
     * @param int $checkMode
     * @param UriRetriever $uriRetriever
     * @param Factory $factory
     * @param Callable $callable
     */
    public function __construct(
        $checkMode = self::CHECK_MODE_NORMAL,
        UriRetriever $uriRetriever = null,
        Factory $factory = null,
        $callable
    ) {
        $this->callable = $callable;
        parent::__construct($checkMode, $uriRetriever, $factory);
    }

    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        if ( ! is_callable($this->callable)) {
            return;
        }

        $result = call_user_func($this->callable, $element, $schema, $path, $i);

        if ($result) {
            $this->addError($path, $result);
        }
    }
}