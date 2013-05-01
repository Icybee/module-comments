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

class CommentTest extends \PHPUnit_Framework_TestCase
{
	public function test_set_node()
	{
		$r = new Comment;
		$r->node = true;
	}
}