<?php

namespace Icybee\Modules\Comments;

use Icybee\Modules\Nodes\Node;

$hooks = Hooks::class . '::';

return [

	Node::class . '::lazy_get_comments' => $hooks . 'get_comments',
	Node::class . '::lazy_get_comments_count' => $hooks . 'get_comments_count',
	Node::class . '::lazy_get_rendered_comments_count' => $hooks . 'get_rendered_comments_count'

];
