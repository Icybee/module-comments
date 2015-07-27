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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

/**
 * Configuration block.
 */
class ConfigBlock extends \Icybee\ConfigBlock
{
	protected function lazy_get_attributes()
	{
		return \ICanBoogie\array_merge_recursive(parent::lazy_get_attributes(), [

			Element::GROUPS => [

				'response' => [

					'title' => "Message de notification à l'auteur lors d'une réponse"

				],

				'spam' => [

					'title' => 'Paramètres anti-spam'

				]
			]
		]);
	}

	protected function lazy_get_children()
	{
		$ns = $this->module->flat_id;

		return array_merge(parent::lazy_get_children(), [

			"local[$ns.form_id]" => new \Icybee\Modules\Forms\PopForm('select', [

				Group::LABEL => 'Formulaire',
				Element::GROUP => 'primary',
				Element::REQUIRED => true,
				Element::DESCRIPTION => "Il s'agit du formulaire à utiliser pour la
				saisie des commentaires."

			]),

			"local[$ns.default_status]" => new Element('select', [

				Group::LABEL => 'Status par défaut',
				Element::DESCRIPTION => "Il s'agit du status par défaut pour les nouveaux commentaires.",
				Element::OPTIONS => [

					'pending' => 'Pending',
					'approved' => 'Approuvé'

				]
			]),

			"local[$ns.delay]" => new Text([

				Group::LABEL => 'Intervale entre deux commentaires',
				Text::ADDON => 'minutes',
				Element::DEFAULT_VALUE => 3,
				Element::GROUP => 'spam',

				'size' => 3,
				'class' => 'measure'

			])
		]);
	}
}
