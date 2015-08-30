<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments\Block;

use Brickrouge\Element;
use Brickrouge\Group;
use Brickrouge\Text;

use Icybee\Modules\Comments\Comment;

/**
 * A block to edit comments.
 */
class EditBlock extends \Icybee\Block\EditBlock
{
	protected function lazy_get_children()
	{
		$values = $this->values;

		return [

			Comment::AUTHOR => new Text([

				Group::LABEL => 'Author',
				Element::REQUIRED => true

			]),

			Comment::AUTHOR_EMAIL => new Text([

				Group::LABEL => 'E-mail',
				Element::REQUIRED => true

			]),

			Comment::AUTHOR_URL => new Text([

				Group::LABEL => 'URL'

			]),

			Comment::AUTHOR_IP => new Text([

				Group::LABEL => 'Adresse IP',

				'disabled' => true

			]),

			Comment::CONTENTS => new Element('textarea', [

				Group::LABEL => 'Message',
				Element::REQUIRED => true,

				'rows' => 10

			]),

			Comment::NOTIFY => new Element(Element::TYPE_RADIO_GROUP, [

				Group::LABEL => 'Notification',
				Element::DEFAULT_VALUE => 'no',
				Element::REQUIRED => true,
				Element::OPTIONS => [

					'yes' => 'Bien sûr !',
					'author' => "Seulement si c'est l'auteur du billet qui répond",
					'no' => 'Pas la peine, je viens tous les jours',
					'done' => 'Notification envoyée'

				],

				Element::DESCRIPTION => (($values[Comment::NOTIFY] == 'done') ? "Un message de notification a été envoyé." : null),

				'class' => 'inputs-list'

			]),

			Comment::STATUS => new Element('select', [

				Group::LABEL => 'Status',
				Element::REQUIRED => true,
				Element::OPTIONS => [

					null => '',
					'pending' => 'Pending',
					'approved' => 'Aprouvé',
					'spam' => 'Spam'

				]
			])
		];
	}
}
