<?php

namespace Icybee\Modules\Comments;

$hooks = Hooks::class . '::';

return [

	'patron.markups' => [

		'comments' => [ $hooks . 'markup_comments', [

			'node' => null,
			'order' => 'created_at asc',
			'limit' => 0,
			'page' => 0,
			'noauthor' => false,
			'parseempty' => false

		] ],

		'comments:form' => [ $hooks . 'markup_form', [

			'select' => [ 'expression' => true, 'default' => 'this', 'required' => true ]

		] ]
	]
];
