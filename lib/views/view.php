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

use Icybee\Modules\Views\ViewOptions;

class View extends \Icybee\Modules\Views\View
{
	protected function provide($provider, array $conditions)
	{
		$bind = $this->engine->context['this'];

		if ($bind instanceof \Icybee\Modules\Nodes\Node)
		{
			$conditions['nid'] = $bind->nid;
		}

		return parent::provide($provider, $conditions);
	}

	protected function alter_context(\BlueTihi\Context $context)
	{
		$context = parent::alter_context($context);

		if ($this->renders == ViewOptions::RENDERS_MANY)
		{
			$count = $this->provider->count;

			$context['count'] = I18n\t('comments.count', [ ':count' => $count ]);
		}

		return $context;
	}
}
