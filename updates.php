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

/**
 * - Renames the `created` columns as `created_as`.
 * - Creates the `updated_at` column.
 *
 * @module comments
 */
class Update20131208 extends \ICanBoogie\Updater\Update
{
	public function update_column_created_at()
	{
		$this->module->model
		->assert_has_column('created')
		->rename_column('created', 'created_at');
	}

	public function update_column_updated_at()
	{
		$this->module->model
		->assert_not_has_column('updated_at')
		->create_column('updated_at');
	}
}