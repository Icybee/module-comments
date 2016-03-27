<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use Icybee\Modules\Comments\Comment;

/**
 * @property Comment $record
 */
class StatusOperation extends Operation
{
	protected function get_controls()
	{
		return [

			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER

		] + parent::get_controls();
	}

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		$status = $this->request['status'];

		if ($status !== null && !in_array($status, [ Comment::STATUS_APPROVED, Comment::STATUS_PENDING, Comment::STATUS_SPAM ]))
		{
			$errors->add('status', "Invalid status value: %status", [ 'status' => $status ]);
		}

		return $errors;
	}

	protected function process()
	{
		$record = $this->record;
		$status = $this->request['status'];

		if ($status)
		{
			$record->status = $status;
		}

		$record->save();

		return true;
	}
}
