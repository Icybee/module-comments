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

use Brickrouge\A;
use Brickrouge\Element;
use ICanBoogie\ActiveRecord\Query;
use Icybee\Block\ManageBlock\Column;
use Icybee\Block\ManageBlock\FilterDecorator;
use Icybee\Modules\Comments\Block\ManageBlock;
use Icybee\Modules\Comments\Comment;

/**
 * Representation of the `author` column.
 */
class AuthorColumn extends Column
{
	/**
	 * Filters the records according to the `email` column.
	 *
	 * @inheritdoc
	 */
	public function alter_query_with_value(Query $query, $value)
	{
		if ($value)
		{
			$query->filter_by_author_email($value);
		}

		return $query;
	}

	/**
	 * Orders the records according to the `author` column.
	 *
	 * @inheritdoc
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $query->order('`author` ' . ($order_direction < 0 ? 'DESC' : 'ASC'));
	}

	private $discreet_value;

	/**
	 * @param Comment $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		if ($this->discreet_value == $record->author_email)
		{
			return ManageBlock::DISCREET_PLACEHOLDER;
		}

		$this->discreet_value = $record->author_email;

		$rc = '';

		if ($record->author_email)
		{
			$rc .= new Element('img', [

				'src' => $record->author_icon . '&s=32',
				'alt' => $record->author,
				'width' => 32,
				'height' => 32

			]);
		}

		$rc .= '<div class="details">';

		$rc .= new FilterDecorator($record, $this->id, $this->is_filtering, $record->author, $record->author_email);

		$email = $record->author_email;

		if ($email)
		{
			$rc .= '<br /><span class="small">&lt;';
			$rc .= new A($email, 'mailto:' . $email);
			$rc .= '&gt;</span>';
		}

		$url = $record->author_url;

		if ($url)
		{
			$rc .= '<br /><span class="small">';
			$rc .= new A($url, $url, [ 'target' => '_blank' ]);
			$rc .= '</span>';
		}

		$rc .= '</div>';

		return $rc;
	}
}
