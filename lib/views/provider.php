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

class ViewProvider extends \ICanBoogie\ActiveRecord\Fetcher
{
	public function alter_query(Query $query)
	{
		return parent::alter_query($query)->and('status != "spam" AND status != "pending"');
	}
}
