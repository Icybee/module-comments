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

use ICanBoogie\Errors;

use Icybee\Binding\PrototypedBindings;
use Icybee\Modules\Comments\Comment;
use Icybee\Modules\Comments\Module;
use Icybee\Modules\Nodes\Node;

/**
 * Saves a comment.
 *
 * @property \ICanBoogie\Core|\Icybee\Binding\CoreBindings|\ICanBoogie\Binding\Mailer\CoreBindings $app
 * @property Comment $record
 */
class SaveOperation extends \ICanBoogie\Module\Operation\SaveOperation
{
	use PrototypedBindings;

	protected function lazy_get_properties()
	{
		$properties = parent::lazy_get_properties();
		$user = $this->app->user;

		if ($this->key)
		{
			unset($properties[Comment::NID]);

			if (!$user->has_permission(Module::PERMISSION_ADMINISTER))
			{
				unset($properties[Comment::AUTHOR_IP]);
			}
		}
		else
		{
			$properties[Comment::AUTHOR_IP] = $this->request->ip;

			if (!$user->is_guest)
			{
				$properties[Comment::UID] = $user->uid;
			}
		}

		if (!$user->has_permission(Module::PERMISSION_MANAGE, $this->module))
		{
			$properties['status'] = null;
		}

		if (!$this->key && empty($properties['status']))
		{
			/* @var $node Node */
			$node = $this->app->models['nodes'][$properties[Comment::NID]];
			$properties['status'] = $node->site->metas->get($this->module->flat_id . '.default_status', 'pending');
		}

		return $properties;
	}

	protected function validate(Errors $errors)
	{
		$request = $this->request;

		if ($request['link'])
		{
			throw new \Exception('It looks like you are a vile spam bot.');
		}

		$nid = $request[Comment::NID];

		if ($nid && !$this->app->models['nodes']->exists($nid))
		{
			$errors->add(Comment::NID, "Invalid node identifier: %nid", [ 'nid' => $nid ]);

			return false;
		}

		#
		# the article id is required when creating a message
		#

		if (!$this->key)
		{
			if (!$nid)
			{
				$errors->add(Comment::NID, "The node id is required to create a comment.");

				return false;
			}

			#
			# validate IP
			#

			if ($this->module->model->where('author_ip = ? AND status = "spam"', $request->ip)->rc)
			{
				$errors->add(null, "A previous message from your IP was marked as spam.");
			}
		}

		$author_url = $request[Comment::AUTHOR_URL];

		if ($author_url && !filter_var($author_url, FILTER_VALIDATE_URL))
		{
			$errors->add(null, "Invalid URL: %url", [ 'url' => $author_url ]);
		}

		if (!$this->app->user_id)
		{
			#
			# delay between last post
			#

			$interval = $this->app->site->metas[$this->module->flat_id . '.delay'] ?: 5;

			$last = $this->module->model
			->select('created_at')
			->where
			(
				'(author = ? OR author_email = ? OR author_ip = ?) AND created_at + INTERVAL ? MINUTE > UTC_TIMESTAMP()',
				$request['author'], $request['author_email'], $request->ip, $interval
			)
			->order('created_at DESC')
			->rc;

			if ($last)
			{
				$errors->add(null, "Les commentaires ne peuvent être faits à moins de $interval minutes d'intervale.");
			}
		}

		return !count($errors);
	}

	protected function process()
	{
		$rc = parent::process();

		if (!$this->key)
		{
			$this->notify($rc['key']);

			if ($this->properties['status'] == 'approved')
			{
				$this->response->location = $this->record->url;
			}
		}

		return $rc;
	}

	/**
	 * Notify users that a reply to their comment has been posted.
	 *
	 * @param int $comment_id
	 */
	protected function notify($comment_id)
	{
		$form_id = $this->app->site->metas['comments.form_id'];

		if (!$form_id)
		{
			return;
		}

		try
		{
			/* @var $form \Icybee\Modules\Forms\Form */

			$form = $this->app->models['forms'][$form_id];
		}
		catch (\Exception $e) { return; }

		$options = unserialize($form->metas['comments/reply']);

		if (!$options)
		{
			return;
		}

		$model = $this->module->model;
		$comment = $this->record;

		/* @var $records Comment[] */

		#
		# search previous message for notify
		#

		$records = $model->where
		(
			'nid = ? AND `{primary}` < ? AND (`notify` = "yes" OR `notify` = "author") AND author_email != ?',

			$comment->nid, $comment_id, $comment->author_email
		)
		->all;

		if (!$records)
		{
			return;
		}

		#
		# prepare subject and message
		#

		$patron = \Patron\get_patron();
		$subject = $patron($options['subject'], $comment);
		$message = $patron($options['template'], $comment);

		$from = $options['from'];
		$bcc = $options['bcc'];

		foreach ($records as $entry)
		{
			#
			# notify only if the author of the node post a comment
			#

			if ($entry->notify == 'author' && $comment->uid != $comment->node->uid)
			{
				continue;
			}

			\ICanBoogie\log
			(
				'Send notify to %author (email: %email, message n°%commentid, mode: %notify)', [

					'%author' => $entry->author,
					'%email' => $entry->author_email,
					'%commentid' => $entry->commentid,
					'%notify' => $entry->notify

				]
			);

			$rc = $this->app->mail([

				'to' => $entry->author_email,
				'from' => $from,
				'bcc' => $bcc,
				'body' => $message,
				'subject' => $subject,
				'type' => 'plain'

			]);

			if (!$rc)
			{
				\ICanBoogie\log_error('Unable to send notify to %author', [ '%author' => $entry->author ]);

				continue;
			}

			$entry->notify = 'done';
			$entry->save();
		}
	}
}
