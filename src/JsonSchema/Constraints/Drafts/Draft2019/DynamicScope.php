<?php

declare(strict_types=1);

namespace JsonSchema\Constraints\Drafts\Draft2019;

/**
 * The schema resources entered during the evaluation, which $recursiveRef uses to find its
 * dynamic destination.
 */
class DynamicScope
{
    /** @var list<object> */
    private $resources = [];

    public function enter(object $resource): void
    {
        $this->resources[] = $resource;
    }

    public function leave(): void
    {
        array_pop($this->resources);
    }

    public function isEmpty(): bool
    {
        return $this->resources === [];
    }

    /**
     * @return list<object> the entered schema resources, the innermost first
     */
    public function getResources(): array
    {
        return array_reverse($this->resources);
    }
}
