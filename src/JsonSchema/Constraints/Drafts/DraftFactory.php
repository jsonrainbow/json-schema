<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\UriRetrieverInterface;

/**
 * Creates the constraints of a draft, reading and writing the check mode of the factory it was created from.
 *
 * The draft constraints are cached by that factory, so a check mode copied when they are created would ignore the
 * check mode of every later validation.
 */
abstract class DraftFactory extends Factory
{
    /**
     * @var Factory|null
     */
    private $parentFactory;

    /**
     * @phpstan-param int-mask-of<Constraint::CHECK_MODE_*> $checkMode
     */
    public function __construct(
        ?SchemaStorageInterface $schemaStorage = null,
        ?UriRetrieverInterface $uriRetriever = null,
        int $checkMode = Constraint::CHECK_MODE_NORMAL,
        ?Factory $parentFactory = null
    ) {
        parent::__construct($schemaStorage, $uriRetriever, $checkMode);

        $this->parentFactory = $parentFactory;
    }

    public function setConfig(int $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        if ($this->parentFactory === null) {
            parent::setConfig($checkMode);

            return;
        }

        $this->parentFactory->setConfig($checkMode);
    }

    public function addConfig(int $options): void
    {
        if ($this->parentFactory === null) {
            parent::addConfig($options);

            return;
        }

        $this->parentFactory->addConfig($options);
    }

    public function removeConfig(int $options): void
    {
        if ($this->parentFactory === null) {
            parent::removeConfig($options);

            return;
        }

        $this->parentFactory->removeConfig($options);
    }

    public function getConfig(?int $options = null): int
    {
        if ($this->parentFactory === null) {
            return parent::getConfig($options);
        }

        return $this->parentFactory->getConfig($options);
    }
}
