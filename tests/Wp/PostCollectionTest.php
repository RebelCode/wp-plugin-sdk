<?php

namespace RebelCode\WpSdk\Tests\Wp;

use Countable;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\PostCollection;
use Traversable;
use WP_Post;
use function Brain\Monkey\Functions\expect;

class PostCollectionTest extends TestCase
{
    use BrainMonkeyTest;
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpPost();
    }

    protected function mockWpPost(array $data = [])
    {
        return new WP_Post((object) $data);
    }

    public function testItShouldImplementCountable()
    {
        $this->assertInstanceOf(Countable::class, new PostCollection());
    }

    public function testItShouldImplementTraversable()
    {
        $this->assertInstanceOf(Traversable::class, new PostCollection());
    }

    public function testItShouldGetAllPosts()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn(
            $posts = [
                $this->mockWpPost(['ID' => 1]),
                $this->mockWpPost(['ID' => 2]),
            ]
        );

        $this->assertSame($posts, $collection->get());
    }

    public function testItShouldGetAParticularPost()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['p' => 123, 'posts_per_page' => 1]);

        expect('get_posts')->once()->with($expectQuery)->andReturn(
            $posts = [
                $this->mockWpPost(),
                $this->mockWpPost(),
            ]
        );

        $this->assertSame($posts[0], $collection->getById(123));
    }

    public function testItShouldReturnNullIfNoPostIsFound()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['p' => 123, 'posts_per_page' => 1]);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $this->assertNull($collection->getById(123));
    }

    public function testItShouldPaginate()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['page' => 3]);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->page(3)->get();
    }

    public function testItShouldLimit()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['posts_per_page' => 8]);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->limit(8)->get();
    }

    public function testItShouldRemoveLimit()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
                'posts_per_page' => 8,
            ]
        );

        $expectQuery = array_merge($query, ['posts_per_page' => -1]);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->limit(null)->get();
    }

    public function testItShouldOffset()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['offset' => 3]);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->offset(3)->get();
    }

    public function testItShouldSort()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $expectQuery = array_merge($query, ['orderby' => 'title', 'order' => 'DESC']);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->sort('title', 'DESC')->get();
    }

    public function testItShouldAddToQuery()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        $newQuery = [
            'post_title' => 'Lorem ipsum',
            'post_status' => 'draft',
            'author_name' => 'admin',
        ];

        $expectQuery = array_merge($query, $newQuery);

        expect('get_posts')->once()->with($expectQuery)->andReturn([]);

        $collection->with($newQuery)->get();
    }

    public function testItShouldMap()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn([
            $this->mockWpPost(['ID' => 123]),
            $this->mockWpPost(['ID' => 456]),
        ]);

        $result = $collection->map(function ($post) {
            return $post->ID;
        });

        $this->assertEquals([123, 456], $result);
    }

    public function testItShouldFilter()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn([
            $p1 = $this->mockWpPost(['ID' => 123]),
            $p2 = $this->mockWpPost(['ID' => 456]),
            $p3 = $this->mockWpPost(['ID' => 789]),
        ]);

        $posts = $collection->filter(function ($post) {
            return $post->ID > 123;
        });

        $this->assertEquals([$p2, $p3], $posts);
    }

    public function testItShouldReduce()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn([
            $this->mockWpPost(['ID' => 123, 'post_title' => 'foo']),
            $this->mockWpPost(['ID' => 456, 'post_title' => 'bar']),
            $this->mockWpPost(['ID' => 789, 'post_title' => 'baz']),
        ]);

        $value = $collection->reduce('Titles:', function ($value, $post) {
            return $value . ' ' . $post->post_title;
        });

        $this->assertEquals('Titles: foo bar baz', $value);
    }

    public function testItShouldCacheResult()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        // Expect to be called ONLY once
        expect('get_posts')->once()->with($query)->andReturn([
            $this->mockWpPost(['ID' => 1]),
            $this->mockWpPost(['ID' => 2]),
            $this->mockWpPost(['ID' => 3]),
            $this->mockWpPost(['ID' => 4]),
        ]);

        $collection->get();

        $collection->map(function (WP_Post $post) {
            return $post->ID;
        });

        $collection->filter(function ($_) {
            return true;
        });

        $collection->reduce(null, function ($_, $__) {
            return null;
        });
    }

    public function testItShouldClearCacheForNewInstance()
    {
        $query = [
            'post_type' => 'foo',
            'post_status' => 'publish',
            'category' => 'bar',
        ];
        $query2 = array_merge($query, ['post_author' => 1]);

        $posts1 = [
            $this->mockWpPost(['ID' => 1]),
            $this->mockWpPost(['ID' => 2]),
        ];
        $posts2 = [
            $this->mockWpPost(['ID' => 3]),
            $this->mockWpPost(['ID' => 4]),
        ];

        // Expect query to be invoked again for the new instance
        expect('get_posts')->once()->with($query)->andReturn($posts1)->andAlsoExpectIt()
                           ->once()->with($query2)->andReturn($posts2);

        $collection1 = new PostCollection($query);
        $collection1->get();
        $collection1->get();

        $collection2 = $collection1->with(['post_author' => 1]);
        $collection2->get();
        $collection2->get();
    }

    public function testItShouldReturnCount()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn([
            $this->mockWpPost(['ID' => 123]),
            $this->mockWpPost(['ID' => 456]),
            $this->mockWpPost(['ID' => 789]),
        ]);

        $this->assertEquals(3, $collection->count());
    }

    public function testItShouldBeCountable()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn([
            $this->mockWpPost(['ID' => 123]),
            $this->mockWpPost(['ID' => 456]),
            $this->mockWpPost(['ID' => 789]),
        ]);

        $this->assertEquals(3, count($collection));
    }

    public function testItShouldReturnAnIterator()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn(
            $posts = [
                $this->mockWpPost(),
                $this->mockWpPost(),
                $this->mockWpPost(),
            ]
        );

        $iterator = $collection->getIterator();

        $this->assertSame($posts, iterator_to_array($iterator));
    }

    public function testItShouldBeIterable()
    {
        $collection = new PostCollection(
            $query = [
                'post_type' => 'foo',
                'post_status' => 'publish',
                'category' => 'bar',
            ]
        );

        expect('get_posts')->once()->with($query)->andReturn(
            $posts = [
                $this->mockWpPost(),
                $this->mockWpPost(),
                $this->mockWpPost(),
            ]
        );

        foreach ($collection as $i => $post) {
            $this->assertSame($posts[$i], $post);
        }
    }

    public function testItCanCreateAFactory()
    {
        $query = [
            'post_type' => 'foo',
            'post_status' => 'publish',
            'category' => 'bar',
        ];

        // Expect the same query to be passed to WordPress
        expect('get_posts')->once()->with($query)->andReturn([]);

        $factory = PostCollection::factory($query);
        $c = $this->createMock(ContainerInterface::class);

        $collection = $factory($c);
        $collection->get();

        $this->assertInstanceOf(PostCollection::class, $collection);
    }
}
