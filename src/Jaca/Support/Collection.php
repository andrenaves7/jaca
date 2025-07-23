<?php
namespace Jaca\Support;

use ArrayIterator;
use IteratorAggregate;
use Countable;

/**
 * Class Collection
 *
 * A simple implementation of a fluent, iterable collection of items.
 *
 * @package Jaca\Support
 */
class Collection implements IteratorAggregate, Countable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Collection constructor.
     *
     * @param array $items Optional initial items.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Create a new collection instance.
     *
     * @param array $items
     * @return static
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Get all items from the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Execute a callback over each item.
     *
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $value) {
            $callback($value, $key);
        }
        return $this;
    }

    /**
     * Apply a callback to each item and return a new collection.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Filter items using a callback.
     *
     * @param callable|null $callback
     * @return static
     */
    public function filter(callable $callback = null): static
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Extract a single column from each item.
     *
     * @param string $key
     * @return static
     */
    public function pluck(string $key): static
    {
        return new static(array_map(fn($item) => $item[$key] ?? null, $this->items));
    }

    /**
     * Retrieve an external iterator.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}
