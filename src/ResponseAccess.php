<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI;

use Countable;
use Iterator;

/**
 * Response data object for accessing.
 */
class ResponseAccess implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
    /**
     * The response data.
     */
    public $container;

    /**
     * Position of iterator.
     *
     * @var int
     */
    public $position = 0;

    /**
     * Setup resource.
     *
     * @param mixed $data the data to use for source
     *
     * @return self
     */
    final public function __construct($data)
    {
        $this->container = $data;
    }

    /**
     * Check if offset exists.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Get the value by offset.
     */
    public function offsetGet($offset): mixed
    {
        if ($offset === 'container') {
            return $this->container;
        }

        if (is_array($this->container[$offset])) {
            return new static($this->container[$offset]);
        }

        return $this->container[$offset];
    }

    /**
     * Set a value by offset.
     */
    public function offsetSet($offset, $value): void
    {
        $this->container[$offset] = $value;
    }

    /**
     * Remove by offset.
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Check if key exists in data.
     *
     * @param string $key
     */
    public function __isset($key): bool
    {
        return isset($this->container[$key]);
    }

    /**
     * Allows for accessing the underlying array as an object.
     * $response->shop->name will forward to $response['shop']['name'].
     *
     * @param string $key
     */
    public function __get($key)
    {
        if (isset($this->container[$key]) && is_array($this->container[$key])) {
            return new static($this->container[$key]);
        }

        return $this->container[$key];
    }

    /**
     * Set to array.
     *
     * @param string $key
     */
    public function __set($key, $value): void
    {
        $this->container[$key] = $value;
    }

    /**
     * Rewind iterator.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Get current position data.
     */
    public function current(): mixed
    {
        if (is_array($this->container[$this->position])) {
            return new static($this->container[$this->position]);
        }

        return $this->container[$this->position];
    }

    /**
     * Current position.
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move position forward.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Check if valid iterator.
     */
    public function valid(): bool
    {
        return isset($this->container[$this->position]);
    }

    /**
     * Countable.
     */
    public function count(): int
    {
        return count($this->container);
    }

    /**
     * Get keys for the array.
     */
    public function keys(): array
    {
        return array_keys($this->container);
    }

    /**
     * Get values for the array.
     */
    public function values(): array
    {
        return array_values($this->container);
    }

    /**
     * Return a JSON serializable array.
     */
    public function jsonSerialize(): array
    {
        return $this->container;
    }

    /**
     * To array, mainly for Laravel usage.
     */
    public function toArray(): array
    {
        return $this->container;
    }

    /**
     * Check if errors are in response.
     */
    public function hasErrors(): bool
    {
        return isset($this->container['errors']) || isset($this->container['error']);
    }

    /**
     * Get the errors.
     */
    public function getErrors()
    {
        if (!$this->hasErrors()) {
            return;
        }

        return $this->container['errors'] ?? $this->container['error'];
    }
}
