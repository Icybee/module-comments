<?php

namespace Icybee\Modules\Comments;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'feedback',
	Module::T_DESCRIPTION => 'Implements comments for nodes',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::ACTIVERECORD_CLASS => __NAMESPACE__ . '\Comment',
			Model::BELONGS_TO => array('nodes', 'users'),
			Model::CLASSNAME => __NAMESPACE__ . '\Model',
			Model::SCHEMA => array
			(
				'fields' => array
				(
					'commentid' => 'serial',
					'nid' => 'foreign',
					'parentid' => 'foreign',
					'uid' => 'foreign',
					'author' => array('varchar', 32),
					'author_email' => array('varchar', 64),
					'author_url' => 'varchar',
					'author_ip' => array('varchar', 45),
					'contents' => 'text',
					'status' => array('enum', array('pending', 'approved', 'spam'), 'indexed' => true),
					'notify' => array('enum', array('no', 'yes', 'author', 'done'), 'indexed' => true),
					'created' => array('timestamp', 'default' => 'CURRENT_TIMESTAMP'),
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRES => array
	(
		'nodes' => '1.0'
	),

	Module::T_TITLE => 'Comments',
	Module::T_VERSION => '1.0'
);

/*
 * About ENUM performance: http://www.mysqlperformanceblog.com/2008/01/24/enum-fields-vs-varchar-vs-int-joined-table-what-is-faster/
 */