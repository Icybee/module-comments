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

use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Nodes\Node;

class Hooks
{
	/*
	 * Events
	 */

	static public function before_node_save(Operation\BeforeProcessEvent $event, \Icybee\Modules\Nodes\SaveOperation $sender)
	{
		$request = $event->request;

		if (isset($request['metas']['comments/reply']))
		{
			$metas = &$request->params['metas']['comments/reply'];

			$metas += array
			(
				'is_notify' => null
			);

			$metas['is_notify'] = filter_var($metas['is_notify'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	/**
	 * Deletes all the comments attached to a node.
	 *
	 * @param Operation\ProcessEvent $event
	 * @param Icybee\Modules\Nodes\DeleteOperation $sender
	 */
	static public function on_node_delete(Operation\ProcessEvent $event, \Icybee\Modules\Nodes\DeleteOperation $operation)
	{
		global $core;

		try
		{
			$model = $core->models['comments'];
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
		global $core;

		$records = $core->models['comments']->filter_by_nid($target->nid)->order('created_at DESC')->all;

		foreach ($records as $record)
		{
			$event->add('comments', $record->commentid, \ICanBoogie\shorten($record->contents, 48, 1), true, $record->url);
		}
	}

	static public function alter_block_edit(Event $event)
	{
		global $core;

		if (!isset($core->modules['comments']))
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

		$event->tags = \ICanBoogie\array_merge_recursive
		(
			$event->tags, array
			(
				Form::VALUES => $values ? $values : array(),

				Element::CHILDREN => array
				(
					$key => new Element\Templated
					(
						'div', array
						(
							Element::GROUP => 'notify',
							Element::CHILDREN => array
							(
								$metas_prefix . '[is_notify]' => new Element
								(
									Element::TYPE_CHECKBOX, array
									(
										Element::LABEL => 'Activer la notification aux réponses',
										Element::DESCRIPTION => "Cette option déclanche l'envoi
										d'un email à l'auteur ayant choisi d'être informé d'une
										réponse à son commentaire."
									)
								),

								$metas_prefix . '[from]' => new Text
								(
									array
									(
										Form::LABEL => 'Adresse d\'expédition'
									)
								),

								$metas_prefix . '[bcc]' => new Text
								(
									array
									(
										Form::LABEL => 'Copie cachée'
									)
								),

								$metas_prefix . '[subject]' => new Text
								(
									array
									(
										Form::LABEL => 'Sujet du message'
									)
								),

								$metas_prefix . '[template]' => new Element
								(
									'textarea', array
									(
										Form::LABEL => 'Patron du message',
										Element::DESCRIPTION => "Le sujet du message et le corps du message
										sont formatés par <a href=\"http://github.com/Weirdog/WdPatron\" target=\"_blank\">WdPatron</a>,
										utilisez ses fonctionnalités avancées pour les personnaliser."
									)
								)
							)
						),

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
				)
			)
		);
	}

	static public function on_view_render(Event $event, \Icybee\Modules\Views\View $view)
	{
		global $core;

		if ($event->id != 'articles/view')
		{
			return;
		}

		$editor = $core->editors['view'];
		$list = $editor->render('comments/list');
		$submit = $editor->render('comments/submit');

		$event->rc .= PHP_EOL . $list . PHP_EOL . $submit;
	}

	/*
	 * Prototype
	 */

	/**
	 * Returns the approved comments associated with a node.
	 *
	 * @param Node $ar
	 *
	 * @return array[]Node
	 */
	static public function get_comments(Node $ar)
	{
		global $core;

		return $core->models['comments']->where('nid = ? AND status = "approved"', $ar->nid)->order('created_at')->all;
	}

	/**
	 * Returns the number of approved comments associated with a node.
	 *
	 * @param Node $ar
	 *
	 * @return int
	 */
	static public function get_comments_count(Node $ar)
	{
		global $core;

		return $core->models['comments']->where('nid = ? AND status = "approved"', $ar->nid)->count;
	}

	/**
	 * Returns the rendered number of comment associated with a node.
	 *
	 * The string is formated using the `comments.count` locale string.
	 *
	 * @param Node $ar
	 *
	 * @return string
	 */
	static public function get_rendered_comments_count(Node $ar)
	{
		return I18n\t('comments.count', array(':count' => $ar->comments_count));
	}

	static public function including_comments_count(\Icybee\Modules\Nodes\Model $target, array $records)
	{
		global $core;

		$keys = array();

		foreach ($records as $record)
		{
			$keys[$record->nid] = $record;
		}

		$counts = $core->models['comments']->approved->filter_by_nid(array_keys($keys))->count('nid');
		$counts = $counts + array_combine(array_keys($keys), array_fill(0, count($keys), 0));

		foreach ($counts as $nid => $count)
		{
			$keys[$nid]->comments_count = $count;
		}

		return $records;
	}

	/*
	 * Markups
	 */

	static public function markup_comments(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		extract($args);

		#
		# build sql query
		#

		$model = $core->models['comments'];
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
			return;
		}

		$model->including_node($records);

		return $patron($template, $records);
	}

	static public function markup_form(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		#
		# Obtain the form to use to add a comment from the 'forms' module.
		#

		$module = $core->modules['comments'];
		$form_id = $core->site->metas['comments.form_id'];

		if (!$form_id)
		{
			throw new Exception\Config($module);
		}

		if (!$core->user->has_permission(\ICanBoogie\Module::PERMISSION_CREATE, 'comments'))
		{
			if (Debug::$mode != Debug::MODE_DEV)
			{
				return;
			}

			return new \Brickrouge\Alert
			(
				<<<EOT
You don't have permission the create comments,
<a href="{$core->site->path}/admin/users.roles">the <q>Visitor</q> role should be modified.</a>
EOT
, array(), 'error'
			);
		}

		$form = $core->models['forms'][$form_id];

		if (!$form)
		{
			throw new Exception
			(
				'Uknown form with Id %nid', array
				(
					'%nid' => $form_id
				)
			);
		}

		new \BlueTihi\Context\LoadedNodesEvent($patron->context, array($form));

		#
		# Traget Id for the comment
		#

		$page = $core->request->context->page;

		$form->form->hiddens[Comment::NID] = $page->node ? $page->node->nid : $page->nid;
		$form->form->add_class('wd-feedback-comments');

		return $template ? $patron($template, $form) : $form;
	}

	/*
	 * Other
	 */

	static public function dashboard_last()
	{
		global $core;

		if (empty($core->modules['comments']))
		{
			return;
		}

		$document = $core->document;
		$document->css->add('../public/admin.css');

		$model = $core->models['comments'];
		$entries = $model
		->where('(SELECT 1 FROM {prefix}nodes WHERE nid = comment.nid AND (siteid = 0 OR siteid = ?)) IS NOT NULL', $core->site_id)
		->order('created_at DESC')->limit(5)->all;

		if (!$entries)
		{
			return '<p class="nothing">' . I18n\t('No record yet') . '</p>';
		}

		$model->including_node($entries);

		$rc = '';
		$context = $core->site->path;

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

			$txt_delete = I18n\t('Delete');
			$txt_edit = I18n\t('Edit');
			$txt_display_associated_node = I18n\t('Display associated node');

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

		$count = $model->joins(':nodes')->where('siteid = 0 OR siteid = ?', $core->site_id)->count;
		$txt_all_comments = I18n\t('comments.count', array(':count' => $count));

		$rc .= <<<EOT
<div class="panel-footer"><a href="$context/admin/comments">$txt_all_comments</a></div>
EOT;

		return $rc;
	}
}