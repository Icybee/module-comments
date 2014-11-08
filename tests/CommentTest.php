<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

use Icybee\Modules\Nodes\Node;

class CommentTest extends \PHPUnit_Framework_TestCase
{
	static private $instance;

	static public function setupBeforeClass()
	{
		self::$instance = new Comment;
	}

	/**
	 * @dataProvider provide_test_write_readonly_properties
	 * @expectedException ICanBoogie\PropertyNotWritable
	 * @param string $property Property name.
	 */
	public function test_write_readonly_properties($property)
	{
		self::$instance->$property = null;
	}

	public function provide_test_write_readonly_properties()
	{
		$properties = 'absolute_url author_icon is_author url';

		return array_map(function($v) { return (array) $v; }, explode(' ', $properties));
	}

	public function test_created_at()
	{
		$comment = new Comment;
		$this->assertInstanceOf('ICanBoogie\DateTime', $comment->created_at);
		$this->assertTrue($comment->created_at->is_empty);

		$comment->created_at = 'now';
		$this->assertInstanceOf('ICanBoogie\DateTime', $comment->created_at);

		$this->assertArrayHasKey('created_at', $comment->__sleep());
		$this->assertArrayHasKey('created_at', $comment->to_array());
	}

	public function test_updated_at()
	{
		$comment = new Comment;
		$this->assertInstanceOf('ICanBoogie\DateTime', $comment->updated_at);
		$this->assertTrue($comment->updated_at->is_empty);

		$comment->updated_at = 'now';
		$this->assertInstanceOf('ICanBoogie\DateTime', $comment->updated_at);

		$this->assertArrayHasKey('updated_at', $comment->__sleep());
		$this->assertArrayHasKey('updated_at', $comment->to_array());
	}

	/**
	 * @dataProvider provide_test_property
	 */
	public function test_css_propery($property, $expected, $properties)
	{
		$this->assertEquals($expected, Comment::from($properties)->$property);
	}

	public function provide_test_property()
	{
		return [

			[
				'css_class',
				'comment comment-123 author-reply', [

					'commentid' => 123,
					'uid' => 1,
					'node' => Node::from([ 'uid' => 1 ])
				]
			],

			[
				'css_class',
				'comment comment-456', [

					'commentid' => 456,
					'uid' => 123,
					'node' => Node::from([ 'uid' => 1 ])
				]
			]

		];
	}
}
