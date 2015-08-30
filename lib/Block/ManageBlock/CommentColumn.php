<?php

namespace Icybee\Modules\Comments\Block\ManageBlock;

use Icybee\Block\ManageBlock\Column;
use Icybee\Block\ManageBlock\EditDecorator;
use Icybee\Modules\Comments\Block\ManageBlock;

/**
 * Representation of the `comment` column.
 */
class CommentColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, [

			'orderable' => false

		]);
	}

	public function render_cell($record)
	{
		return new EditDecorator(\ICanBoogie\shorten(strip_tags($record), 48, 1), $record);
	}
}
