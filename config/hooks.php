<?php

namespace Icybee\Modules\Comments;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'Icybee\Modules\Nodes\SaveOperation::process:before' => $hooks . 'before_node_save',
		'Icybee\Modules\Nodes\DeleteOperation::process' => $hooks . 'on_node_delete',
		'Icybee\Modules\Nodes\Node::collect_dependencies' => $hooks . 'on_node_collect_dependencies',
		'Icybee\Modules\Forms\Module::alter.block.edit' => $hooks . 'alter_block_edit', // FIXME-20120922: this event is no longer fired
//		'Icybee\Modules\Views\View::render' => $hooks . 'on_view_render'

	],

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
