<?php

namespace RebelCode\WpSdk\Wp;

use ArrayIterator;
use Countable;
use Dhii\Services\Factory;
use Iterator;
use IteratorAggregate;
use WP_Post;

class PostCollection implements Countable, IteratorAggregate
{
    /** @var array */
    protected $query;

    /** @var null|WP_Post[] */
    protected $cache;

    /**
     * Constructor.
     *
     * @param array $query The initial query to use for the collection.
     */
    public function __construct(array $query = [])
    {
        $this->query = $query;
        $this->cache = null;
    }

    /** Retrieves the total number of posts in the collection. */
    public function count(): int
    {
        return count($this->get());
    }

    /** Retrieves an iterator for the posts in the collection. */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->get());
    }

    /**
     * Retrieves all the WordPress posts in the collection.
     *
     * @return WP_Post[] A list of WordPress posts.
     */
    public function get(): array
    {
        if ($this->cache === null) {
            $this->cache = get_posts($this->query);
        }

        return $this->cache;
    }

    /**
     * Retrieves a specific post by ID from the collection.
     *
     * This method will **not** retrieve posts that do not match the collection's query. A post MAY exist for the
     * given ID, but may not satisfy the query. In that case, this method will return null.
     *
     * @param string|int $id The ID of the post to retrieve.
     *
     * @return WP_Post|null A WordPress post instance, or null if no post was found with the given ID in the collection.
     */
    public function getById(int $id): ?WP_Post
    {
        $posts = $this->with(['p' => $id])->limit(1)->get();

        return count($posts) === 0 ? null : $posts[0];
    }

    /**
     * Retrieves the first post in the collection.
     *
     * @return WP_Post|null The first post in the collection, or null if the collection is empty.
     */
    public function first(): ?WP_Post
    {
        $posts = $this->get();
        $first = reset($posts);

        return $first ? : null;
    }

    /**
     * Creates a copy of the collection with additional query parameters.
     *
     * @param array<string, mixed> $query The query parameters to add to the collection.
     * @return self The new collection instance.
     */
    public function with(array $query): self
    {
        return new self(array_merge($this->query, $query));
    }

    /**
     * Creates a copy of the collection that is limited to a specific page.
     *
     * @param int $page The page number, 1-based.
     * @return self The new collection instance.
     */
    public function page(int $page): self
    {
        return $this->with(['page' => $page]);
    }

    /**
     * Creates a copy of the collection that is limited to a specific number of posts.
     *
     * @param int|null $num The number of posts, or null to use no limit.
     * @return self The new collection instance.
     */
    public function limit(?int $num): self
    {
        return $this->with([
            'posts_per_page' => max(-1, $num ?? -1),
        ]);
    }

    /**
     * Creates a copy of the collection that is offset by a specific number of posts.
     *
     * @param int $offset The number of posts to skip.
     * @return self The new collection instance.
     */
    public function offset(int $offset): self
    {
        return $this->with([
            'offset' => max(0, $offset),
        ]);
    }

    /**
     * Creates a new copy of the collection that is sorted by a specific field.
     *
     * @param string $orderBy The post field to sort by.
     * @param string $orderDir The sort direction, either "ASC" or "DESC".
     * @return self The new collection instance.
     */
    public function sort(string $orderBy, string $orderDir): self
    {
        return $this->with([
            'orderby' => $orderBy,
            'order' => strtoupper($orderDir),
        ]);
    }

    /**
     * Applies a callback to each post in the collection.
     *
     * @param callable $fn A function that takes a WP_Post instance and returns a new value.
     * @return array A list containing the mapped values.
     */
    public function map(callable $fn): array
    {
        return array_map($fn, $this->get());
    }

    /**
     * Filters the posts in the collection.
     *
     * @param callable $fn A function that takes a WP_Post instance and returns a boolean.
     * @return array A list containing the posts for which the callable returned true.
     */
    public function filter(callable $fn): array
    {
        return array_values(array_filter($this->get(), $fn));
    }

    /**
     * Reduces the list of posts in the collection to a single value.
     *
     * @param mixed $initial The initial value to use for the reduction.
     * @param callable $fn A function that takes the reduction value and a WP_Post instance, and returns a new value.
     * @return mixed The final reduction value.
     */
    public function reduce($initial, callable $fn)
    {
        return array_reduce($this->get(), $fn, $initial);
    }

    /** Creates a factory for a post collection, for use in modules. */
    public static function factory(array $query): Factory
    {
        return new Factory([], function () use ($query) {
            return new self($query);
        });
    }
}
