<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments\Block\ManageBlock;

use Brickrouge\A;
use Icybee\Block\ManageBlock\Column;
use Icybee\Block\ManageBlock\FilterDecorator;
use Icybee\Modules\Comments\Block\ManageBlock;
use Icybee\Modules\Comments\Comment;
use Icybee\Modules\Comments\CommentModel;

/**
 * Representation of the `nid` column.
 */
class NodeColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, [

			'orderable' => false

		]);
	}

	/**
	 * Loads the nodes associated with the comments.
	 *
	 * @param Comment[] $records
	 *
	 * @inheritdoc
	 */
	public function alter_records(array $records)
	{
		/* @var $comment_model CommentModel */

		$comment_model = $this->manager->model;

		return $comment_model->including_node($records);
	}

	/**
	 * @param Comment $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		$property = $this->id;
		$node = $record->node;

		$rc = '';

		if ($node)
		{
			$title = $node->title;
			$label = \ICanBoogie\escape(\ICanBoogie\shorten($title, 48, .75, $shortened));

			$rc .= new A("", $node->url, [

				'title' => $title,
				'class' => 'icon-external-link'

			]) . ' ';
		}
		else
		{
			$label = '<em class="warn">unknown-node-' . $record->$property . '</em>';
		}

		return $rc . new FilterDecorator($record, $property, $this->is_filtering, $label);
	}
}
