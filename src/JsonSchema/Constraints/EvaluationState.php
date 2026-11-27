<?php

declare(strict_types=1);

namespace JsonSchema\Constraints;

/**
 * State shared by all constraints taking part in the evaluation of one instance, including the nested
 * factories the strict mode draft constraints create.
 *
 * @internal
 */
class EvaluationState
{
    /**
     * Whether a constraint may stop at its first error, because only the validity of the value is needed
     *
     * @var bool
     */
    public $failFast = false;
}
