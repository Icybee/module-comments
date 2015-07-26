<?php

namespace Icybee\Modules\Comments;

use Icybee\Routing\RoutesMaker as Make;

return Make::admin('comments', AdminController::class);

/*[

	'!admin:manage' => [

		'pattern' => '!auto',
		'controller' => true

	],

	'!admin:new' => [

		'pattern' => '!auto',
		'controller' => true

	],

	'!admin:config' => [

		'pattern' => '!auto',
		'controller' => true

	],

	'!admin:edit' => [

		'pattern' => '!auto',
		'controller' => true

	]
];
*/
