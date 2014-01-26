# The "Comments" module (comments)

Allows users to attach comments to nodes.

The modules provides a comment submission form with a real time comment preview and all the
screens required to manage the comments and configure the module.





### Requirements

This module is designed to work with the CMS [Icybee](http://icybee.org/).





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require": {
		"icybee/module-comments": "2.x"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-comments), its repository can
be cloned with the following command line:

	$ git clone git://github.com/module-comments.git comments





## Documentation

The documentation for the package and its dependencies can be generated with the `make doc`
command. The documentation is generated in the `docs` directory using [ApiGen](http://apigen.org/).
The package directory can later by cleaned with the `make clean` command.
	




## License

This module is licensed under the New BSD License - See the LICENSE file for details.





## Prototype methods





### get_comments

The `get_comments` prototype method returns the approved comments associated with a node.

```php
<?php

$core->models['articles']->one->comments;
```





### get_comments_count

The `get_comments_count` prototype method returns the number of approved comments associated with
a node.

```php
<?php

$core->models['articles']->one->comments_count;
```