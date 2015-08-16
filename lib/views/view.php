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

use ICanBoogie\I18n;

use BlueTihi\Context;

use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Views\ViewOptions;

class View extends \Icybee\Modules\Views\View
{
	protected function provide($provider, array $conditions)
	{
		$bind = $this->engine->context['this'];

		if ($bind instanceof Node)
		{
			$conditions['nid'] = $bind->nid;
		}

		return parent::provide($provider, $conditions);
	}

	protected function alter_context(Context $context)
	{
		$context = parent::alter_context($context);

		if ($this->renders == ViewOptions::RENDERS_MANY)
		{
			$count = $this->provider->count;

			$context['count'] = $this->app->translate('comments.count', [ ':count' => $count ]);
		}

		return $context;
	}
}
