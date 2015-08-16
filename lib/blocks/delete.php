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

/**
 * @property Comment $record
 */
class DeleteBlock extends \Icybee\DeleteBlock
{
	protected function get_record_name()
	{
		return \ICanBoogie\shorten($this->record->contents, 32, 1);
	}

	/**
	 * @inheritdoc
	 *
	 * @param ActiveRecord|Comment $record
	 *
	 * @return string
	 */
	protected function render_preview(ActiveRecord $record)
	{
		return \ICanBoogie\escape($record->contents);
	}
}
