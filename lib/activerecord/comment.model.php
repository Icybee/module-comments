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

/**
 * Comments model.
 */
class Model extends ActiveRecord\Model
{
	/**
	 * Adds the `status` and `notify` properties if they are not defined, they default to
	 * `pending` and `no`.
	 *
	 * @throws \InvalidArgumentException if the value of the `notify` property is not one of `no`,
	 * `yes`, `author` or `done`.
	 */
	public function save(array $properties, $key=null, array $options=array())
	{
		$properties += array
		(
			Comment::CREATED => DateTime::now(),
			Comment::STATUS => 'pending',
			Comment::NOTIFY => 'no'
		);

		if (!in_array($properties[Comment::NOTIFY], array('no', 'yes', 'author', 'done')))
		{
			throw new \InvalidArgumentException(\ICanBoogie\format
			(
				'Invalid value for property %property: %value', array
				(
					'%property' => Comment::NOTIFY,
					'%value' => $properties[Comment::NOTIFY]
				)
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
	protected function scope_spam(Query $query, $approved=true)
	{
		return $query->filter_by_status(Comment::STATUS_SPAM);
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
		$keys = array();

		foreach ($records as $record)
		{
			$keys[$record->nid] = $record;
		}

		$nodes = ActiveRecord\get_model('nodes')->find_using_constructor(array_keys($keys));

		foreach ($nodes as $key => $node)
		{
			$keys[$key]->node = $node;
		}

		return $records;
	}
}