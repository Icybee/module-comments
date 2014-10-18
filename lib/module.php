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

use Icybee\Modules\Views\ViewOptions;

class Module extends \Icybee\Module
{
	protected function lazy_get_views()
	{
		$assets = array('css' => DIR . 'public/page.css');

		return array
		(
			'list' => array
			(
				ViewOptions::TITLE => "Comments associated to a node",
				ViewOptions::ASSETS => $assets,
				ViewOptions::PROVIDER_CLASSNAME => ViewOptions::PROVIDER_CLASSNAME_AUTO,
				ViewOptions::RENDERS => ViewOptions::RENDERS_MANY
			),

			'submit' => array
			(
				ViewOptions::TITLE => "Comment submit form",
				ViewOptions::ASSETS => $assets,
				ViewOptions::RENDERS => ViewOptions::RENDERS_OTHER
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