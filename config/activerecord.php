<?php

namespace Icybee\Modules\Comments\Facets;

use ICanBoogie\Facets\Criterion\DateCriterion;

return [

	'facets' => [

		'comments' => [

			'nid' => NidCriterion::class,
			'created_at' => DateCriterion::class

		]
	]
];
