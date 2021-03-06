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

use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Icybee\Modules\Forms\AlterFormNotifyParams;
use Icybee\Modules\Forms\NotifyParams;

/**
 * The form used to submit comments.
 *
 * @property \ICanBoogie\Core|\Icybee\Binding\CoreBindings $app
 */
class SubmitForm extends Form implements AlterFormNotifyParams
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('submit.js');
	}

	public function __construct(array $attributes=[])
	{
		$app = $this->app;
		$user = $app->user;
		$is_member = !$user->is_guest;
		$values = [];

		if ($is_member)
		{
			$values[Comment::AUTHOR] = $user->name;
			$values[Comment::AUTHOR_EMAIL] = $user->email;
		}

		parent::__construct(\ICanBoogie\array_merge_recursive($attributes, [

			Form::RENDERER => 'Group',
			Form::VALUES => $values,
			Form::HIDDENS => [

				Operation::DESTINATION => 'comments',
				Operation::NAME => 'save'

			],

			Element::CHILDREN => [

				Comment::AUTHOR => new Text([

					Element::LABEL => 'Name',
					Element::REQUIRED => true

				]),

				Comment::AUTHOR_EMAIL => new Text([

					Element::LABEL => 'E-mail',
					Element::REQUIRED => true,
					Element::VALIDATOR => [ 'Brickrouge\Form::validate_email' ]

				]),

				Comment::AUTHOR_URL => new Text([

					Element::LABEL => 'Website'

				]),

				'link' => new Text([

					Element::LABEL => 'Link'

				]),

				Comment::CONTENTS => new Element('textarea', [

					Element::REQUIRED => true,
					Element::LABEL_MISSING => 'Message',

					'class' => 'span6',
					'rows' => 8

				]),

				Comment::NOTIFY => new Element(Element::TYPE_RADIO_GROUP, [

					Form::LABEL => "Shouhaitez-vous être informé d'une réponse à votre message ?",

					Element::OPTIONS => [

						'yes' => "Bien sûr !",
						'author' => "Seulement si c'est l'auteur du billet qui répond.",
						'no' => "Pas la peine, je viens tous les jours."

					],

					Element::DEFAULT_VALUE => 'no',

					'class' => 'inputs-list'

				])
			],

			Element::IS => 'SubmitComment',

			'action' => '#view-comments-form',
			'class' => 'widget-submit-comment',
			'name' => 'comments/submit'

		]));
	}

	/**
	 * @inheritdoc
	 */
	function alter_form_notify_params(NotifyParams $notify_params)
	{
		$notify_params->bind = $this->app->models['comments'][$notify_params->rc['key']];
	}

	static public function get_defaults()
	{
		/**
		 * @var $app \ICanBoogie\Core|\Icybee\Binding\CoreBindings
		 */
		$app = \ICanBoogie\app();

		if (isset($_GET['type']) && $_GET['type'] == 'notify')
		{
			return [

				'from' => 'no-reply@' . $_SERVER['SERVER_NAME'],
				'subject' => 'Notification de réponse au billet : #{@node.title}',
				'bcc' => $app->user->email,
				'template' => <<<EOT
Bonjour,

Vous recevez cet e-mail parce que vous surveillez le billet "#{@node.title}" sur #{\$app.site.title}.
Ce billet a reçu une réponse depuis votre dernière visite. Vous pouvez utiliser le lien suivant
pour voir les réponses qui ont été faites :

#{@absolute_url}

Aucune autre notification ne vous sera envoyée.

À bientôt sur #{\$app.site.title}.
EOT
			];
		}

		return [

			'notify_subject' => 'Un nouveau commentaire a été posté sur #{$app.site.title}',
			'notify_from' => 'Comments <comments@#{$server.http.host}>',
			'notify_template' => <<<EOT
Bonjour,

Vous recevez ce message parce qu'un nouveau commentaire a été posté sur le site #{\$app.site.title} :

URL : #{@absolute_url}
Auteur : #{@author} <#{@author_email}>

#{@strip_tags()=}

EOT
		];
	}
}
