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

	'prototypes' => [

		'Icybee\Modules\Nodes\Node::lazy_get_comments' => $hooks . 'get_comments',
		'Icybee\Modules\Nodes\Node::lazy_get_comments_count' => $hooks . 'get_comments_count',
		'Icybee\Modules\Nodes\Node::lazy_get_rendered_comments_count' => $hooks . 'get_rendered_comments_count',
		'Icybee\Modules\Nodes\Model::including_comments_count' => $hooks . 'including_comments_count'

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
