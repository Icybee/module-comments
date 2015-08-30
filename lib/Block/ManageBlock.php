<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments\Block;

use ICanBoogie\ActiveRecord\Query;

use Brickrouge\Document;

use Icybee\Modules\Comments\Comment;
use Icybee\Modules\Nodes\Block\ManageBlock\URLColumn;

class ManageBlock extends \Icybee\Block\ManageBlock
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\Modules\Comments\DIR . 'public/admin.css');
		$document->js->add(\Icybee\Modules\Comments\DIR . 'public/admin.js');
	}

	public function __construct($module, array $attributes=[])
	{
		parent::__construct($module, $attributes + [

			self::T_COLUMNS_ORDER => [

				'comment', 'url', 'status', 'author', 'nid', 'created_at'

			],

			self::T_ORDER_BY => [ 'created_at', 'desc' ]

		]);
	}

	/**
	 * Adds the following columns:
	 *
	 * - `comment`: An instance of {@link ManageBlock\CommentColumn}.
	 * - `url`: An instance of {@link ManageBlock\URLColumn}.
	 * - `status`: An instance of {@link ManageBlock\StatusColumn}.
	 * - `author`: An instance of {@link ManageBlock\AuthorColumn}.
	 * - `nid`: An instance of {@link ManageBlock\NodeColumn}.
	 * - `created`: An instance of {@link \Icybee\Block\ManageBlock\DateTimeColumn}.
	 *
	 * @return array
	 */
	protected function get_available_columns()
	{
		return array_merge(parent::get_available_columns(), [

			'comment'           => __CLASS__ . '\CommentColumn',
			'url'               => URLColumn::class,
			'status'            => __CLASS__ . '\StatusColumn',
			Comment::AUTHOR     => __CLASS__ . '\AuthorColumn',
			Comment::NID        => __CLASS__ . '\NodeColumn',
			Comment::CREATED_AT => 'Icybee\Block\ManageBlock\DateTimeColumn'

		]);
	}

	/**
	 * Update filters with the `status` modifier.
	 */
	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['status']))
		{
			$value = $modifiers['status'];

			if (in_array($value, [ 'approved', 'pending', 'spam' ]))
			{
				$filters['status'] = $value;
			}
			else if (!$value)
			{
				unset($filters['status']);
			}
		}

		return $filters;
	}

	protected function alter_query(Query $query, array $filters)
	{
		return $query->similar_site;
	}
}
