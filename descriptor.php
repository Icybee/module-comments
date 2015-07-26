<?php

namespace Icybee\Modules\Comments;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::CATEGORY => 'feedback',
	Descriptor::DESCRIPTION => 'Implements comments for nodes',
	Descriptor::MODELS => [

		'primary' => [

			Model::BELONGS_TO => [ 'nodes', 'users' ],
			Model::SCHEMA => [

				'commentid' => 'serial',
				'nid' => 'foreign',
				'parentid' => 'foreign',
				'uid' => 'foreign',
				'author' => [ 'varchar', 32 ],
				'author_email' => [ 'varchar', 64 ],
				'author_url' => 'varchar',
				'author_ip' => [ 'varchar', 45 ],
				'contents' => 'text',
				'status' => [ 'enum', [ 'pending', 'approved', 'spam' ], 'indexed' => true ],
				'notify' => [ 'enum', [ 'no', 'yes', 'author', 'done' ], 'indexed' => true ],
				'created_at' => [ 'timestamp', 'default' => 'CURRENT_TIMESTAMP' ],
				'updated_at' => 'timestamp'

			]
		]
	],

	Descriptor::NS => __NAMESPACE__,
	Descriptor::REQUIRES => [ 'nodes' ],
	Descriptor::TITLE => "Comments"

];

/*
 * About ENUM performance: http://www.mysqlperformanceblog.com/2008/01/24/enum-fields-vs-varchar-vs-int-joined-table-what-is-faster/
 */
