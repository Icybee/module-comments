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

class Module extends \Icybee\Module
{
	protected function lazy_get_views()
	{
		$assets = array('css' => DIR . 'public/page.css');

		return array
		(
			'list' => array
			(
				View::TITLE => "Comments associated to a node",
				View::ASSETS => $assets,
				View::CLASSNAME => __NAMESPACE__ . '\View',
				View::PROVIDER => __NAMESPACE__ . '\ViewProvider',
				View::RENDERS => View::RENDERS_MANY
			),

			'submit' => array
			(
				View::TITLE => "Comment submit form",
				View::ASSETS => $assets,
				View::RENDERS => View::RENDERS_OTHER
			)
		);
	}

	/*
	static $notifies_response = array
	(
		'subject' => 'Notification de réponse au billet : #{@node.title}',
		'template' => 'Bonjour,

Vous recevez cet email parce que vous surveillez le billet "#{@node.title}" sur <nom_du_site>.
Ce billet a reçu une réponse depuis votre dernière visite. Vous pouvez utiliser le lien suivant
pour voir les réponses qui ont été faites :

#{@absolute_url}

Aucune autre notification ne vous sera envoyée.

À bientôt sur <url_du_site>',
		'from' => 'VotreSite <no-reply@votre_site.com>'
	);
	*/
}