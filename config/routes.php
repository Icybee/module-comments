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

use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

use Icybee\Modules\Comments\Operation\StatusOperation;
use Icybee\Routing\RouteMaker as Make;

return [

	'api:comments:status' => [

		'pattern' => '/api/comments/<commentid:\d+>/status',
		'controller' => StatusOperation::class,
		'via' => Request::METHOD_PUT,
		'param_translation_list' => [

			'commentid' => Operation::KEY

		]

	]

] + Make::admin('comments', Routing\CommentsAdminController::class, [

	'id_name' => 'commentid'

]);
