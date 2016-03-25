<?php

namespace TMT\model;

class Link extends Model {

	/**
	 * @var string The name of the link (text displayed)
	 */
	public $name;

	/**
	 * @var string The resource guid that the user must be able to
	 *   access to see this link
	 */
	public $resource;

	/**
	 * @var string The action the user must be able to perform on the
	 *   resource to able to see this link.
	 */
	public $verb;

	/**
	 * @var bool Whether or not this link should be opened in a new tab
	 */
	public $newTab;

	/**
	 * @var string The url the link goes to
	 */
	public $url;

	/**
	 * @var array(\TMT\model\Link) an array of child links
	 */
	public $children;

	/**
	 * Constructor to build a link
	 *
	 * @param $link stdObject with name, permission, newTab, url, and children defined
	 */
	public function __construct($link = null) {
		if($link == null)
			return;

		$this->name       = isset($link->name)       ?       $link->name     : null;
		$this->resource   = isset($link->resource)   ?       $link->resource : null;
		$this->verb       = isset($link->verb)       ?       $link->verb     : null;
		$this->newTab     = isset($link->newTab)     ? (bool)$link->newTab   : false;
		$this->url        = isset($link->url)        ?       $link->url      : null;
		$this->children   = isset($link->children)   ?       $link->children : null;
	}
}
