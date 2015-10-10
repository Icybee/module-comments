<?php

namespace Icybee\Modules\Comments\Facets;

use ICanBoogie\Facets\DateTimeCriterion;

return [

	'facets' => [

		'comments' => [

			'nid' => NidCriterion::class,
			'created_at' => DateTimeCriterion::class

		]
	]
];
