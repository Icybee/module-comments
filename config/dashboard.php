<?php

namespace Icybee\Modules\Comments;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'feedback-comments-last' => [

		'title' => "Last comments",
		'callback' => $hooks . 'dashboard_last',
		'column' => 1

	]
];
