<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

use JsonSchema\ConstraintError;
use JsonSchema\Constraints\ConstraintInterface;
use JsonSchema\Entity\ErrorBagProxy;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Tool\EcmaPatternConverter;

class PatternConstraint implements ConstraintInterface
{
    use ErrorBagProxy;

    /** @var Factory */
    private $factory;

    public function __construct(?Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->initialiseErrorBag($this->factory);
    }

    public function check(&$value, $schema = null, ?JsonPointer $path = null, $i = null): void
    {
        if (!property_exists($schema, 'pattern')) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        $matchPattern = EcmaPatternConverter::toPcre($schema->pattern);
        if (preg_match($matchPattern, $value) === 1) {
            return;
        }

        $this->addError(ConstraintError::PATTERN(), $path, ['found' => $value, 'pattern' => $schema->pattern]);
    }
}
