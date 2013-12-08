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

use ICanBoogie\DateTime;

/**
 * A comment.
 *
 * @property DateTime $created_at The date and time at which the node was created.
 * @property DateTime $updated_at The date and time at which the node was updated.
 * @property-read string $absolute_url URL of the comment.
 * @property-read string $author_icon URL of the author's Gravatar.
 * @property-read string $css_class A suitable string for the HTML `class` attribute.
 * @property-read array $css_class_names CSS class names.
 * @property-read string $excerpt HTML excerpt of the comment, made of the first 55 words.
 * @property-read bool $is_author `true` if the author of the comment is the author of the attached node.
 * @property-read \Icybee\Modules\Nodes\Node $node The node the comment is attached to.
 * @property-read string $url URL of the comment relative to the website.
 */
class Comment extends \ICanBoogie\ActiveRecord implements \Brickrouge\CSSClassNames
{
	const COMMENTID = 'commentid';
	const NID = 'nid';
	const PARENTID = 'parentid';
	const UID = 'uid';
	const AUTHOR = 'author';
	const AUTHOR_EMAIL = 'author_email';
	const AUTHOR_URL = 'author_url';
	const AUTHOR_IP = 'author_ip';
	const CONTENTS = 'contents';
	const STATUS = 'status';
	const STATUS_APPROVED = 'approved';
	const STATUS_PENDING = 'pending';
	const STATUS_SPAM = 'spam';
	const NOTIFY = 'notify';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';

	/**
	 * Comment identifier.
	 *
	 * @var int
	 */
	public $commentid;

	/**
	 * Node identifier.
	 *
	 * @var int
	 */
	public $nid;

	/**
	 * Parent comment identifier.
	 *
	 * @var int
	 */
	public $parentid;

	/**
	 * User identifier.
	 *
	 * The user identifier is zero (0) if the user is a guest.
	 *
	 * @var int
	 */
	public $uid;

	/**
	 * Author name.
	 *
	 * @var string
	 */
	public $author;

	/**
	 * Author email.
	 *
	 * @var string
	 */
	public $author_email;

	/**
	 * Author's website URL.
	 *
	 * @var string
	 */
	public $author_url;

	/**
	 * Author IP.
	 *
	 * @var string
	 */
	public $author_ip;

	/**
	 * Body of the comment.
	 *
	 * @var string
	 */
	public $contents;

	/**
	 * Status. One of `pending`, `approved` and `spam`.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Notify status. One of `no`, `yes`, `author` and `done`.
	 *
	 * @var string
	 */
	public $notify;

	/**
	 * The date and time the comment was created.
	 *
	 * @var \ICanBoogie\DateTime
	 */
	private $created_at;

	/**
	 * Returns the date and time the comment was created.
	 *
	 * @return \ICanBoogie\DateTime
	 */
	protected function get_created_at()
	{
		$datetime = $this->created_at;

		if ($datetime instanceof DateTime)
		{
			return $datetime;
		}

		return $this->created_at = ($datetime === null) ? DateTime::none() : new DateTime($datetime, 'utc');
	}

	/**
	 * Sets the date and time the comment was created.
	 *
	 * @param \DateTime|string $datetime
	 */
	protected function set_created_at($datetime)
	{
		$this->created_at = $datetime;
	}

	/**
	 * The date and time the comment was updated.
	 *
	 * @var \ICanBoogie\DateTime
	 */
	private $updated_at;

	/**
	 * Returns the date and time the comment was updated.
	 *
	 * @return \ICanBoogie\DateTime
	 */
	protected function get_updated_at()
	{
		$datetime = $this->updated_at;

		if ($datetime instanceof DateTime)
		{
			return $datetime;
		}

		return $this->updated_at = ($datetime === null) ? DateTime::none() : new DateTime($datetime, 'utc');
	}

	/**
	 * Sets the date and time the comment was updated.
	 *
	 * @param \DateTime|string $datetime
	 */
	protected function set_updated_at($datetime)
	{
		$this->updated_at = $datetime;
	}

	/**
	 * Defaults model to "comments".
	 */
	public function __construct($model='comments')
	{
		parent::__construct($model);
	}

	/**
	 * Returns the URL of the comment.
	 *
	 * The URL of the comment is created from the URL of the node and to identifier of the comment
	 * using the following pattern: `{node.url}#comment{commentid}`.
	 *
	 * @return string
	 */
	protected function get_url()
	{
		$node = $this->node;

		return ($node ? $this->node->url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	/**
	 * Returns the absolute URL of the comment.
	 *
	 * @return string
	 */
	protected function get_absolute_url()
	{
		$node = $this->node;

		return ($node ? $this->node->absolute_url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	/**
	 * Returns the URL of the author's Gravatar.
	 *
	 * @return string
	 */
	protected function get_author_icon()
	{
		$hash = md5(strtolower(trim($this->author_email)));

		return 'http://www.gravatar.com/avatar/' . $hash . '.jpg?' . http_build_query
		(
			array
			(
				'd' => 'identicon'
			)
		);
	}

	/**
	 * Returns an HTML excerpt of the comment.
	 *
	 * @param int $limit The maximum number of words to use to create the excerpt. Defaults to 55.
	 *
	 * @return string
	 */
	public function excerpt($limit=55)
	{
		return \ICanBoogie\excerpt((string) $this, $limit);
	}

	/**
	 * Returns an HTML excerpt of the comment.
	 *
	 * @return string
	 */
	protected function lazy_get_excerpt()
	{
		return $this->excerpt();
	}

	/**
	 * Whether the author of the node is the author of the comment.
	 *
	 * @return boolean `true` if the author is the same, `false` otherwise.
	 */
	protected function get_is_author()
	{
		return $this->node->uid == $this->uid;
	}

	/**
	 * Returns the CSS class names of the comment.
	 *
	 * @return array[string]mixed
	 */
	protected function lazy_get_css_class_names()
	{
		return array
		(
			'type' => 'comment',
			'id' => 'comment-' . $this->commentid,
			'author-reply' => $this->is_author
		);
	}

	/**
	 * Return the CSS class of the comment.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		return \Brickrouge\render_css_class($this->css_class_names, $modifiers);
	}

	/**
	 * Returns the CSS class of the comment.
	 *
	 * @return string
	 */
	protected function lazy_get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Renders the comment into a HTML string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$str = \Textmark_Parser::parse($this->contents);

		return \Icybee\Kses::sanitizeComment($str);
	}
}