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
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Nodes\NodeModel as NodeModel;

class Hooks
{
	/*
	 * Events
	 */

	static public function before_node_save(Operation\BeforeProcessEvent $event, \Icybee\Modules\Nodes\Operation\SaveOperation $sender)
	{
		$request = $event->request;

		if (isset($request['metas']['comments/reply']))
		{
			$metas = &$request->params['metas']['comments/reply'];

			$metas += [ 'is_notify' => null ];

			$metas['is_notify'] = filter_var($metas['is_notify'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	/**
	 * Deletes all the comments attached to a node.
	 *
	 * @param Operation\ProcessEvent $event
	 * @param \Icybee\Modules\Nodes\Operation\DeleteOperation $operation
	 */
	static public function on_node_delete(Operation\ProcessEvent $event, \Icybee\Modules\Nodes\Operation\DeleteOperation $operation)
	{
		try
		{
			$model = self::app()->models['comments'];
		}
		catch (\Exception $e)
		{
			return;
		}

		$ids = $model->select('{primary}')->filter_by_nid($operation->key)->all(\PDO::FETCH_COLUMN);

		foreach ($ids as $commentid)
		{
			$model->delete($commentid);
		}
	}

	/**
	 * Adds the comments depending on a node.
	 *
	 * @param \ICanBoogie\ActiveRecord\CollectDependenciesEvent $event
	 * @param \Icybee\Modules\Nodes\Node $target
	 */
	static public function on_node_collect_dependencies(\ICanBoogie\ActiveRecord\CollectDependenciesEvent $event, \Icybee\Modules\Nodes\Node $target)
	{
		$records = self::app()->models['comments']
		->filter_by_nid($target->nid)
		->order('created_at DESC')
		->all;

		foreach ($records as $record)
		{
			$event->add('comments', $record->commentid, \ICanBoogie\shorten($record->contents, 48, 1), true, $record->url);
		}
	}

	static public function alter_block_edit(Event $event)
	{
		if (!isset(self::app()->modules['comments']))
		{
			return;
		}

		$values = null;
		$key = 'comments/reply';
		$metas_prefix = 'metas[' . $key . ']';

		if ($event->entry)
		{
			$entry = $event->entry;

			$values = array
			(
				$metas_prefix => unserialize($entry->metas[$key])
			);
		}

		$ns = \ICanBoogie\escape($metas_prefix);

		$event->tags = \ICanBoogie\array_merge_recursive($event->tags, [

				Form::VALUES => $values ? $values : [ ],

				Element::CHILDREN => [

					$key => new Element\Templated(

						'div', [

							Element::GROUP => 'notify',
							Element::CHILDREN => [

								$metas_prefix . '[is_notify]' => new Element(Element::TYPE_CHECKBOX, [

									Element::LABEL => 'Activer la notification aux réponses',
									Element::DESCRIPTION => "Cette option déclanche l'envoi
									d'un email à l'auteur ayant choisi d'être informé d'une
									réponse à son commentaire."

								]),

								$metas_prefix . '[from]' => new Text([

									Form::LABEL => 'Adresse d\'expédition'

								]),

								$metas_prefix . '[bcc]' => new Text([

									Form::LABEL => 'Copie cachée'

								]),

								$metas_prefix . '[subject]' => new Text([

									Form::LABEL => 'Sujet du message'

								]),

								$metas_prefix . '[template]' => new Element('textarea', [

									Form::LABEL => 'Patron du message',
									Element::DESCRIPTION => "Le sujet du message et le corps du message
									sont formatés par <a href=\"http://github.com/Weirdog/WdPatron\" target=\"_blank\">WdPatron</a>,
									utilisez ses fonctionnalités avancées pour les personnaliser."

								])
							]
						],

						<<<EOT
<div class="panel">
<div class="form-element is_notify">{\${$metas_prefix}[is_notify]}</div>
<table>
<tr><td class="label">{\${$metas_prefix}[from].label:}</td><td>{\${$metas_prefix}[from]}</td>
<td class="label">{\${$metas_prefix}[bcc].label:}</td><td>{\${$metas_prefix}[bcc]}</td></tr>
<tr><td class="label">{\${$metas_prefix}[subject].label:}</td><td colspan="3">{\${$metas_prefix}[subject]}</td></tr>
<tr><td colspan="4">{\${$metas_prefix}[template]}<button type="button" class="reset small warn" value="/api/forms/feedback.comments/defaults?type=notify" data-ns="$ns">Valeurs par défaut</button></td></tr>
</table>
</div>
EOT
					)
				]
			]
		);
	}

	/**
	 * @deprecated
	 */
	static public function on_view_render(Event $event, \Icybee\Modules\Views\View $view)
	{
		if ($view->id != 'articles/view')
		{
			return;
		}

		$editor = self::app()->editors['view'];
		$list = $editor->render('comments/list');
		$submit = $editor->render('comments/submit');

		$event->html .= PHP_EOL . $list . PHP_EOL . $submit;
	}

	/*
	 * Prototype
	 */

	/**
	 * Returns the approved comments associated with a node.
	 *
	 * @param Node $node
	 *
	 * @return Node[]
	 */
	static public function get_comments(Node $node)
	{
		return self::app()
		->models['comments']
		->approved
		->filter_by_nid($node->nid)
		->order('created_at')
		->all;
	}

	/**
	 * Returns the number of approved comments associated with a node.
	 *
	 * @param Node $node
	 *
	 * @return int
	 */
	static public function get_comments_count(Node $node)
	{
		return self::app()
		->models['comments']
		->approved
		->filter_by_nid($node->nid)
		->count;
	}

	/**
	 * Returns the rendered number of comment associated with a node.
	 *
	 * The string is formatted using the `comments.count` locale string.
	 *
	 * @param Node $node
	 *
	 * @return string
	 */
	static public function get_rendered_comments_count(Node $node)
	{
		return self::app()->translate('comments.count', [ ':count' => $node->approved_comments_count ]);
	}

	/*
	 * Markups
	 */

	static public function markup_comments(array $args, \Patron\Engine $patron, $template)
	{
		/* @var $node int */
		/* @var $noauthor bool */
		/* @var $order string */
		/* @var $limit int */
		/* @var $page int */
		/* @var $parseempty bool */

		extract($args);

		#
		# build sql query
		#

		/* @var $model CommentModel */

		$model = self::app()->models['comments'];

		/* @var $arr Query */
		$arr = $model->filter_by_status(Comment::STATUS_APPROVED);

		if ($node)
		{
			$arr->filter_by_nid($node);
		}

		if ($noauthor)
		{
			$arr->where('(SELECT uid FROM {prefix}nodes WHERE nid = comment.nid) != IFNULL(uid, 0)');
		}

		if ($order)
		{
			$arr->order($order);
		}

		if ($limit)
		{
			$arr->limit($limit * $page, $limit);
		}

		$records = $arr->all;

		if (!$records && !$parseempty)
		{
			return null;
		}

		$model->including_node($records);

		return $patron($template, $records);
	}

	static public function markup_form(array $args, \Patron\Engine $patron, $template)
	{
		#
		# Obtain the form to use to add a comment from the 'forms' module.
		#

		$app = self::app();
		$module = $app->modules['comments'];
		$form_id = $app->site->metas['comments.form_id'];

		if (!$form_id)
		{
			throw new \ICanBoogie\Exception\Config($module);
		}

		if (!$app->user->has_permission(\ICanBoogie\Module::PERMISSION_CREATE, 'comments'))
		{
			if (Debug::$mode != Debug::MODE_DEV)
			{
				return null;
			}

			return new \Brickrouge\Alert
			(
				<<<EOT
You don't have permission the create comments,
<a href="{$app->site->path}/admin/users.roles">the <q>Visitor</q> role should be modified.</a>
EOT
, array(), 'error'
			);
		}

		$form = $app->models['forms'][$form_id];

		if (!$form)
		{
			throw new \InvalidArgumentException(\ICanBoogie\format('Uknown form with Id %nid', [

				'nid' => $form_id

			]));
		}

		if (!$form->is_online)
		{
			return null;
		}

		new \BlueTihi\Context\LoadedNodesEvent($patron->context, [ $form ]);

		#
		# Traget Id for the comment
		#

		$page = $app->request->context->page;

		$form->form->hiddens[Comment::NID] = $page->node ? $page->node->nid : $page->nid;
		$form->form->add_class('wd-feedback-comments');

		return $template ? $patron($template, $form) : $form;
	}

	/*
	 * Other
	 */

	static public function dashboard_last()
	{
		$app = self::app();

		if (empty($app->modules['comments']))
		{
			return null;
		}

		$document = $app->document;
		$document->css->add(DIR . 'public/admin.css');

		/* @var $model CommentModel */

		$model = $app->models['comments'];
		$entries = $model
		->where('(SELECT 1 FROM {prefix}nodes WHERE nid = comment.nid AND (siteid = 0 OR siteid = ?)) IS NOT NULL', $app->site_id)
		->order('created_at DESC')->limit(5)->all;

		if (!$entries)
		{
			return '<p class="nothing">' . $app->translate('No record yet') . '</p>';
		}

		$model->including_node($entries);

		$rc = '';
		$context = $app->site->path;

		foreach ($entries as $entry)
		{
			$url = $entry->url;
			$author = \ICanBoogie\escape($entry->author);

			if ($entry->author_url)
			{
				$author = '<a class="author" href="' . \ICanBoogie\escape($entry->author_url) . '">' . $author . '</a>';
			}
			else
			{
				$author = '<strong class="author">' . $author . '</strong>';
			}

			$excerpt = \ICanBoogie\shorten(strip_tags((string) html_entity_decode($entry, ENT_COMPAT, \ICanBoogie\CHARSET)), 140);
			$target_url = $entry->node->url;
			$target_title = \ICanBoogie\escape(\ICanBoogie\shorten($entry->node->title));

			$image = \ICanBoogie\escape($entry->author_icon);

			$entry_class = $entry->status == 'spam' ? 'spam' : '';
			$url_edit = "$context/admin/comments/$entry->commentid/edit";
			$url_delete = "$context/admin/comments/$entry->commentid/delete";

			$date = \ICanBoogie\I18n\format_date($entry->created_at, 'dd MMM');

			$txt_delete = $app->translate('Delete');
			$txt_edit = $app->translate('Edit');
			$txt_display_associated_node = $app->translate('Display associated node');

			$rc .= <<<EOT
<div class="record $entry_class">
	<div class="options">
		<img src="$image&amp;s=48" alt="" />
	</div>

	<div class="contents">
		<div class="head">
		$author
		<span class="date light">$date</span>
		</div>

		<div class="body"><a href="$url">$excerpt</a></div>

		<div class="actions light">
			<a href="$url_edit">$txt_edit</a>, <a href="$url_delete" class="danger">$txt_delete</a> − <a href="$target_url" class="target" title="$txt_display_associated_node">$target_title</a>
		</div>
	</div>
</div>
EOT;
		}

		$count = $model->similar_site->count;
		$txt_all_comments = $app->translate('comments.count', [ ':count' => $count ]);

		$rc .= <<<EOT
<div class="panel-footer"><a href="$context/admin/comments">$txt_all_comments</a></div>
EOT;

		return $rc;
	}

	/*
	 * Support
	 */

	/**
	 * @return \ICanBoogie\Core|\Icybee\Binding\CoreBindings
	 */
	static private function app()
	{
		return \ICanBoogie\app();
	}
}
