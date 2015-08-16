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

use Icybee\Routing\RouteMaker as Make;

return Make::admin('comments', Routing\CommentsAdminController::class, [

	'id_name' => 'commentid'

]);
