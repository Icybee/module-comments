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

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Facets\Criterion;

class ApprovedCommentsCountCriterion extends Criterion
{
	public function alter_query(Query $query)
	{
		$join = $query->model->models['comments']
			->select('nid, COUNT(commentid) AS approved_comments_count')
			->group('nid')
			->where('status = "approved"');

		return $query->join($join, [ 'mode' => 'LEFT', 'on' => 'nid' ]);
	}
}
