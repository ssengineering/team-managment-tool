<?php

namespace TMT\model;

/**
 * Unit tests for the employee accessor class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class LinkTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testEmptyConstruct() {
		$link = new Link();
		$this->assertEquals(null,  $link->name);
		$this->assertEquals(null,  $link->resource);
		$this->assertEquals(null,  $link->verb);
		$this->assertEquals(false, $link->newTab);
		$this->assertEquals(null,  $link->url);
		$this->assertEquals(null,  $link->children);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$child = (object) array(
			"name"       => "child",
			"resource"   => null,
			"verb"       => null,
			"newTab"     => 0,
			"url"        => null,
			"children"   => array()
		);
		$link = (object) array(
			"name"       => "linkName",
			"resource"   => "1",
			"verb"       => "read",
			"newTab"     => 1,
			"url"        => "somewhere",
			"children"   => array(new Link($child))
		);
		$link = new Link($link);
		$this->assertEquals("linkName", $link->name);
		$this->assertEquals("1", $link->resource);
		$this->assertEquals("read", $link->verb);
		$this->assertEquals(true, $link->newTab);
		$this->assertEquals("somewhere", $link->url);
		$this->assertEquals(1, count($link->children));

		$this->assertEquals("child", $link->children[0]->name);
		$this->assertEquals(null, $link->children[0]->resource);
		$this->assertEquals(null, $link->children[0]->verb);
		$this->assertEquals(false, $link->children[0]->newTab);
		$this->assertEquals(null, $link->children[0]->url);
		$this->assertEquals(0, count($link->children[0]->children));
	}
}
?>
