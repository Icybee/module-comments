<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments\Operation;

use ICanBoogie\ErrorCollection;
use ICanBoogie\Operation;

/**
 * Gives the user a visual feedback of the message he's typing.
 */
class PreviewOperation extends Operation
{
	protected function validate(ErrorCollection $errors)
	{
		if (!$this->request['contents'])
		{
			$errors->add('contents');
		}

		return $errors;
	}

	protected function process()
	{
		$contents = $this->request['contents'];
		$contents = \Textmark_Parser::parse($contents);

		return \Icybee\Kses::sanitizeComment($contents);
	}
}
