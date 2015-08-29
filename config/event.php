<?php

namespace Icybee\Modules\Comments;

use Icybee;

$hooks = Hooks::class . '::';

return [

	Icybee\Modules\Nodes\Operation\SaveOperation::class . '::process:before' => $hooks . 'before_node_save',
	Icybee\Modules\Nodes\Operation\DeleteOperation::class . '::process' => $hooks . 'on_node_delete',
	Icybee\Modules\Nodes\Node::class . '::collect_dependencies' => $hooks . 'on_node_collect_dependencies',
	Icybee\Modules\Forms\Module::class . '::alter.block.edit' => $hooks . 'alter_block_edit', // FIXME-20120922: this event is no longer fired
//	Icybee\Modules\Views\View::class . '::render' => $hooks . 'on_view_render'

];
