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

use ICanBoogie\ActiveRecord;

use Icybee\Modules\Comments\Comment;

/**
 * @property Comment $record
 */
class DeleteBlock extends \Icybee\Block\DeleteBlock
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
