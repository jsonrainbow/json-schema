<?php

declare(strict_types=1);

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\DraftIdentifiers;
use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\SchemaStorage;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriRetrieverInterface;
use JsonSchema\Validator;

/**
 * Factory for centralize constraint initialization.
 */
class Factory
{
    /**
     * @var SchemaStorageInterface
     */
    protected $schemaStorage;

    /**
     * @var UriRetriever
     */
    protected $uriRetriever;

    /**
     * @var int
     * @phpstan-var int-mask-of<Constraint::CHECK_MODE_*>
     */
    private $checkMode = Constraint::CHECK_MODE_NORMAL;

    /**
     * @var array<int, TypeCheck\TypeCheckInterface>
     * @phpstan-var array<int-mask-of<Constraint::CHECK_MODE_*>, TypeCheck\TypeCheckInterface>
     */
    private $typeCheck = [];

    /**
     * @var int-mask-of<Validator::ERROR_*> Validation context
     */
    protected $errorContext = Validator::ERROR_DOCUMENT_VALIDATION;

    /**
     * The default dialect used for strict mode (Constraint::CHECK_MODE_STRICT) when the schema is without a schema property
     *
     * @var string
     */
    private $defaultDialect = DraftIdentifiers::DRAFT_6;

    /**
     * @var array
     */
    protected $constraintMap = [
        'array' => 'JsonSchema\Constraints\CollectionConstraint',
        'collection' => 'JsonSchema\Constraints\CollectionConstraint',
        'object' => 'JsonSchema\Constraints\ObjectConstraint',
        'type' => 'JsonSchema\Constraints\TypeConstraint',
        'undefined' => 'JsonSchema\Constraints\UndefinedConstraint',
        'string' => 'JsonSchema\Constraints\StringConstraint',
        'number' => 'JsonSchema\Constraints\NumberConstraint',
        'enum' => 'JsonSchema\Constraints\EnumConstraint',
        'const' => 'JsonSchema\Constraints\ConstConstraint',
        'format' => 'JsonSchema\Constraints\FormatConstraint',
        'schema' => 'JsonSchema\Constraints\SchemaConstraint',
        'validator' => 'JsonSchema\Validator',
        'draft06' => Drafts\Draft06\Draft06Constraint::class,
        'draft07' => Drafts\Draft07\Draft07Constraint::class,
        'draft2019-09' => Drafts\Draft2019\Draft2019Constraint::class,
    ];

    /**
     * @var array<ConstraintInterface>
     */
    private $instanceCache = [];

    /**
     * @var EvaluationState|null
     */
    private $evaluationState;

    /**
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function __construct(
        ?SchemaStorageInterface $schemaStorage = null,
        ?UriRetrieverInterface $uriRetriever = null,
        int $checkMode = Constraint::CHECK_MODE_NORMAL
    ) {
        // set provided config options
        $this->setConfig($checkMode);

        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->schemaStorage = $schemaStorage ?: new SchemaStorage($this->uriRetriever);
    }

    /**
     * Set config values
     *
     * @param int $checkMode Set checkMode options - does not preserve existing flags
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function setConfig(int $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        $this->checkMode = $checkMode;
    }

    /**
     * Enable checkMode flags
     *
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $options
     */
    public function addConfig(int $options): void
    {
        $this->checkMode |= $options;
    }

    /**
     * Disable checkMode flags
     *
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $options
     */
    public function removeConfig(int $options): void
    {
        $this->checkMode &= ~$options;
    }

    /**
     * Get checkMode option
     *
     * @param int|null $options Options to get, if null then return entire bitmask
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*>|null $options Options to get, if null then return entire bitmask
     *
     * @phpstan-return int-mask-of<Constraint::CHECK_MODE_*>
     */
    public function getConfig(?int $options = null): int
    {
        if ($options === null) {
            return $this->checkMode;
        }

        return $this->checkMode & $options;
    }

    /**
     * @internal
     */
    public function getEvaluationState(): EvaluationState
    {
        if ($this->evaluationState === null) {
            $this->evaluationState = new EvaluationState();
        }

        return $this->evaluationState;
    }

    /**
     * Shares the evaluation state of another factory, so that nested factories take part in the same evaluation
     *
     * @internal
     */
    public function setEvaluationState(EvaluationState $evaluationState): void
    {
        $this->evaluationState = $evaluationState;
    }

    /**
     * Whether a constraint may stop at its first error, because only the validity of the value is needed
     *
     * @internal
     */
    public function isFailFast(): bool
    {
        return $this->getEvaluationState()->failFast;
    }

    /**
     * Lets the constraints evaluating a subschema stop at its first error, when only its validity is needed.
     * Returns the previous state, to be passed to stopFailFast(), or null if the check mode needs every
     * constraint to be evaluated, because it modifies the value or throws on the first error.
     *
     * @internal
     */
    public function startFailFast(): ?bool
    {
        if ($this->getConfig(Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_EXCEPTIONS)) {
            return null;
        }

        $evaluationState = $this->getEvaluationState();
        $previous = $evaluationState->failFast;
        $evaluationState->failFast = true;

        return $previous;
    }

    /**
     * @internal
     */
    public function stopFailFast(bool $previous): void
    {
        $this->getEvaluationState()->failFast = $previous;
    }

    public function getUriRetriever(): UriRetrieverInterface
    {
        return $this->uriRetriever;
    }

    public function getSchemaStorage(): SchemaStorageInterface
    {
        return $this->schemaStorage;
    }

    public function getTypeCheck(): TypeCheck\TypeCheckInterface
    {
        if (!isset($this->typeCheck[$this->checkMode])) {
            $this->typeCheck[$this->checkMode] = ($this->checkMode & Constraint::CHECK_MODE_TYPE_CAST)
                ? new TypeCheck\LooseTypeCheck()
                : new TypeCheck\StrictTypeCheck();
        }

        return $this->typeCheck[$this->checkMode];
    }

    public function setConstraintClass(string $name, string $class): Factory
    {
        // Ensure class exists
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Unknown constraint ' . $name);
        }
        // Ensure class is appropriate
        if (!in_array('JsonSchema\Constraints\ConstraintInterface', class_implements($class))) {
            throw new InvalidArgumentException('Invalid class ' . $name);
        }
        $this->constraintMap[$name] = $class;

        return $this;
    }

    /**
     * Create a constraint instance for the given constraint name.
     *
     * @param string $constraintName
     *
     * @throws InvalidArgumentException if is not possible create the constraint instance
     *
     * @return ConstraintInterface&BaseConstraint
     * @phpstan-return ConstraintInterface&BaseConstraint
     */
    public function createInstanceFor($constraintName)
    {
        if (!isset($this->constraintMap[$constraintName])) {
            throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
        }

        if (!isset($this->instanceCache[$constraintName])) {
            $this->instanceCache[$constraintName] = new $this->constraintMap[$constraintName]($this);
        }

        return clone $this->instanceCache[$constraintName];
    }

    /**
     * Get the error context
     *
     * @return int-mask-of<Validator::ERROR_*>
     */
    public function getErrorContext(): int
    {
        return $this->errorContext;
    }

    /**
     * Set the error context
     *
     * @param int-mask-of<Validator::ERROR_*> $errorContext
     */
    public function setErrorContext(int $errorContext): void
    {
        $this->errorContext = $errorContext;
    }

    public function getDefaultDialect(): string
    {
        return $this->defaultDialect;
    }

    public function setDefaultDialect(string $defaultDialect): void
    {
        $this->defaultDialect = $defaultDialect;
    }
}
