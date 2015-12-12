<?php

namespace Icybee\Modules\Comments;

use Icybee\Modules\Views\ViewOptions as Options;

$assets = [ '../public/page.css' ];

return [

	'comments' => [

		'list' => [

			Options::TITLE => "Comments associated to a node",
			Options::ASSETS => $assets,
			Options::PROVIDER_CLASSNAME => Options::PROVIDER_CLASSNAME_AUTO,
			Options::RENDERS => Options::RENDERS_MANY

		],

		'form' => [

			Options::TITLE => "Comment form",
			Options::ASSETS => $assets,
			Options::RENDERS => Options::RENDERS_OTHER

		]
	]

];
