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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\DateTime;

use Icybee\Binding\PrototypedBindings;

/**
 * Comments model.
 */
class CommentModel extends ActiveRecord\Model
{
	use PrototypedBindings;

	/**
	 * Adds the `status` and `notify` properties if they are not defined, they default to
	 * `pending` and `no`.
	 *
	 * @throws \InvalidArgumentException if the value of the `notify` property is not one of `no`,
	 * `yes`, `author` or `done`.
	 *
	 * @inheritdoc
	 */
	public function save(array $properties, $key = null, array $options = [])
	{
		if (!$key && empty($properties[Comment::CREATED_AT]))
		{
			$properties[Comment::CREATED_AT] = DateTime::now();
		}

		$properties += [

			Comment::STATUS => 'pending',
			Comment::NOTIFY => 'no',
			Comment::UPDATED_AT => DateTime::now()

		];

		if (!in_array($properties[Comment::NOTIFY], [ 'no', 'yes', 'author', 'done' ]))
		{
			throw new \InvalidArgumentException(\ICanBoogie\format
			(
				'Invalid value for property %property: %value', [

					'%property' => Comment::NOTIFY,
					'%value' => $properties[Comment::NOTIFY]

				]
			));
		}

		return parent::save($properties, $key, $options);
	}

	/**
	 * Adds a condition on the `status` field, which should equal {@link Comment::STATUS_APPROVED}.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_approved(Query $query)
	{
		return $query->filter_by_status(Comment::STATUS_APPROVED);
	}

	/**
	 * Adds a condition on the `status` field, which should equal {@link Comment::STATUS_PENDING}.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_pending(Query $query)
	{
		return $query->filter_by_status(Comment::STATUS_PENDING);
	}

	/**
	 * Adds a condition on the `status` field, which should equal {@link Comment::STATUS_SPAM}.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_spam(Query $query)
	{
		return $query->filter_by_status(Comment::STATUS_SPAM);
	}

	/**
	 * Filter the comments according to the site their node is attached to.
	 *
	 * @param Query $query
	 * @param string $site_id Identifier of the site. If `null` `$app->site_id` is used instead.
	 *
	 * @return Query
	 */
	protected function scope_similar_site(Query $query, $site_id = null)
	{
		$app = $this->app;

		if ($site_id === null)
		{
			$site_id = $app->site_id;
		}

		$similar_site = $app->models['nodes']->select('nid, site_id');

		return $query
		->join($similar_site, [ 'as' => 'similar_site', 'on' => 'nid' ])
		->and('site_id = 0 OR site_id = ?', $site_id);
	}

	/**
	 * Finds the nodes the records belong to.
	 *
	 * The `node` property of the records is set to the node they belong to.
	 *
	 * @param array $records
	 *
	 * @return array
	 */
	public function including_node(array $records)
	{
		$keys = [];

		foreach ($records as $record)
		{
			$keys[$record->nid] = $record;
		}

		/* @var $node_model \Icybee\Modules\Nodes\NodeModel */

		$node_model = $this->models['nodes'];
		$nodes = $node_model->find_using_constructor(array_keys($keys));

		foreach ($nodes as $key => $node)
		{
			$keys[$key]->node = $node;
		}

		return $records;
	}
}
