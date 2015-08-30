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

use Brickrouge\DropdownMenu;
use Icybee\Block\ManageBlock\Column;
use Icybee\Modules\Comments\Block\ManageBlock;
use Icybee\Modules\Comments\Comment;

/**
 * Representation of the `status` column.
 */
class StatusColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, $options + [

				'class' => 'pull-right',
				'orderable' => false,
				'filters' => [

					'options' => [

						'=approved' => "Approved",
						'=pending' => "Pending",
						'=spam' => "Spam"

					]
				]
			]);
	}

	/**
	 * @param Comment $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		static $labels = [

			Comment::STATUS_APPROVED => 'Approved',
			Comment::STATUS_PENDING => 'Pending',
			Comment::STATUS_SPAM => 'Spam'

		];

		static $classes = [

			Comment::STATUS_APPROVED => 'btn-success',
			Comment::STATUS_PENDING => 'btn-warning',
			Comment::STATUS_SPAM => 'btn-danger'

		];

		$status = $record->status;
		$status_label = isset($labels[$status]) ? $labels[$status] : "<em>Invalid status code: $status</em>";
		$status_class = isset($classes[$status]) ? $classes[$status] : 'btn-danger';
		$commentid = $record->commentid;

		$menu = new DropdownMenu([

			DropdownMenu::OPTIONS => $labels,

			'value' => $status

		]);

		$classes_json = \Brickrouge\escape(json_encode($classes));

		return <<<EOT
<div class="btn-group" data-property="status" data-key="$commentid" data-classes="$classes_json">
	<span class="btn $status_class dropdown-toggle" data-toggle="dropdown"><span class="text">$status_label</span> <span class="caret"></span></span>
	$menu
</div>
EOT;
	}
}
