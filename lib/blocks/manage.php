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

class ManageBlock extends \Icybee\ManageBlock
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(DIR . 'public/admin.css');
		$document->js->add(DIR . 'public/admin.js');
	}

	public function __construct($module, array $attributes=[])
	{
		parent::__construct($module, $attributes + [

			self::T_COLUMNS_ORDER => [

				'comment', 'url', 'status', 'author', 'nid', 'created_at'

			],

			self::T_ORDER_BY => [ 'created_at', 'desc' ]

		]);
	}

	/**
	 * Adds the following columns:
	 *
	 * - `comment`: An instance of {@link ManageBlock\CommentColumn}.
	 * - `url`: An instance of {@link ManageBlock\URLColumn}.
	 * - `status`: An instance of {@link ManageBlock\StatusColumn}.
	 * - `author`: An instance of {@link ManageBlock\AuthorColumn}.
	 * - `nid`: An instance of {@link ManageBlock\NodeColumn}.
	 * - `created`: An instance of {@link \Icybee\ManageBlock\DateTimeColumn}.
	 *
	 * @return array
	 */
	protected function get_available_columns()
	{
		return array_merge(parent::get_available_columns(), [

			'comment'           => __CLASS__ . '\CommentColumn',
			'url'               => 'Icybee\Modules\Nodes\ManageBlock\URLColumn',
			'status'            => __CLASS__ . '\StatusColumn',
			Comment::AUTHOR     => __CLASS__ . '\AuthorColumn',
			Comment::NID        => __CLASS__ . '\NodeColumn',
			Comment::CREATED_AT => 'Icybee\ManageBlock\DateTimeColumn'

		]);
	}

	/**
	 * Update filters with the `status` modifier.
	 */
	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['status']))
		{
			$value = $modifiers['status'];

			if (in_array($value, [ 'approved', 'pending', 'spam' ]))
			{
				$filters['status'] = $value;
			}
			else if (!$value)
			{
				unset($filters['status']);
			}
		}

		return $filters;
	}

	protected function alter_query(Query $query, array $filters)
	{
		return $query->similar_site;
	}
}

/*
 * Columns
 */

namespace Icybee\Modules\Comments\ManageBlock;

use ICanBoogie\ActiveRecord\Query;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\DropdownMenu;

use Icybee\ManageBlock;
use Icybee\ManageBlock\Column;
use Icybee\ManageBlock\EditDecorator;
use Icybee\ManageBlock\FilterDecorator;
use Icybee\Modules\Comments\Comment;
use Icybee\Modules\Comments\CommentModel;

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
	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			$query->filter_by_author_email($filter_value);
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

/**
 * Representation of the `nid` column.
 */
class NodeColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, [

			'orderable' => false

		]);
	}

	/**
	 * Loads the nodes associated with the comments.
	 *
	 * @param Comment[] $records
	 *
	 * @inheritdoc
	 */
	public function alter_records(array $records)
	{
		/* @var $comment_model CommentModel */

		$comment_model = $this->manager->model;

		return $comment_model->including_node($records);
	}

	/**
	 * @param Comment $record
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		$property = $this->id;
		$node = $record->node;

		$rc = '';

		if ($node)
		{
			$title = $node->title;
			$label = \ICanBoogie\escape(\ICanBoogie\shorten($title, 48, .75, $shortened));

			$rc .= new A("", $node->url, [

				'title' => $title,
				'class' => 'icon-external-link'

			]) . ' ';
		}
		else
		{
			$label = '<em class="warn">unknown-node-' . $record->$property . '</em>';
		}

		return $rc . new FilterDecorator($record, $property, $this->is_filtering, $label);
	}
}
