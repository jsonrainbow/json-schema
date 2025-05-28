<?php

declare(strict_types=1);

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Entity;

use JsonSchema\Exception\InvalidArgumentException;

/**
 * @package JsonSchema\Entity
 *
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 */
class JsonPointer
{
    /** @var string */
    private $filename;

    /** @var string[] */
    private $propertyPaths = [];

    /**
     * @var bool Whether the value at this path was set from a schema default
     */
    private $fromDefault = false;

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException when $value is not a string
     */
    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Ref value must be a string');
        }

        $splitRef = explode('#', $value, 2);
        $this->filename = $splitRef[0];
        if (array_key_exists(1, $splitRef)) {
            $this->propertyPaths = $this->decodePropertyPaths($splitRef[1]);
        }
    }

    /**
     * @return string[]
     */
    private function decodePropertyPaths(string $propertyPathString): array
    {
        $paths = [];
        foreach (explode('/', trim($propertyPathString, '/')) as $path) {
            $path = $this->decodePath($path);
            if (is_string($path) && '' !== $path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function encodePropertyPaths(): array
    {
        return array_map(
            [$this, 'encodePath'],
            $this->getPropertyPaths()
        );
    }

    
    private function decodePath(string $path): string
    {
        return strtr($path, ['~1' => '/', '~0' => '~', '%25' => '%']);
    }

    /**
     * @param string $path
     */
    private function encodePath($path): string
    {
        return strtr($path, ['/' => '~1', '~' => '~0', '%' => '%25']);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string[]
     */
    public function getPropertyPaths()
    {
        return $this->propertyPaths;
    }

    
    public function withPropertyPaths(array $propertyPaths): self
    {
        $new = clone $this;
        $new->propertyPaths = array_map(function ($p): string { return (string) $p; }, $propertyPaths);

        return $new;
    }

    public function getPropertyPathAsString(): string
    {
        return rtrim('#/' . implode('/', $this->encodePropertyPaths()), '/');
    }

    public function __toString(): string
    {
        return $this->getFilename() . $this->getPropertyPathAsString();
    }

    /**
     * Mark the value at this path as being set from a schema default
     */
    public function setFromDefault(): void
    {
        $this->fromDefault = true;
    }

    /**
     * Check whether the value at this path was set from a schema default
     *
     * @return bool
     */
    public function fromDefault()
    {
        return $this->fromDefault;
    }
}
