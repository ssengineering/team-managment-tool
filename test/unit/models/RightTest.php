<?php

namespace TMT\model;

/**
 * Unit tests for the Right model class
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RightTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testEmptyConstruct() {
		$right = new Right();
		$this->assertEquals(null, $right->guid);
		$this->assertEquals(null, $right->ID);
		$this->assertEquals(null, $right->rightName);
		$this->assertEquals(null, $right->description);
		$this->assertEquals(null, $right->rightType);
		$this->assertEquals(null, $right->rightLevel);
		$this->assertEquals(null, $right->area);
	}

	/**
	 * @covers ::__construct
	 */
	public function testPartialConstruct() {
		$partial = array(
			"rightName"    => "test"
		);
		$right = new Right($partial);
		$this->assertEquals(null,   $right->guid);
		$this->assertEquals(null,   $right->ID);
		$this->assertEquals("test", $right->rightName);
		$this->assertEquals(null,   $right->description);
		$this->assertEquals(null,   $right->rightType);
		$this->assertEquals(null,   $right->rightLevel);
		$this->assertEquals(null,   $right->area);
	}

	/**
	 * @covers ::__construct
	 */
	public function testFullConstruct() {
		$partial = array(
			"ID"    => 1,
			"rightName"    => "test",
			"description"    => "description",
			"rightType"    => "BASIC",
			"rightLevel"    => 1,
			"area"    => 2,
			"guid" => "11111111-1111-1111-1111-111111111111"
		);
		$right = new Right($partial);
		$this->assertEquals("11111111-1111-1111-1111-111111111111", $right->guid);
		$this->assertEquals(1,  $right->ID);
		$this->assertEquals("test",  $right->rightName);
		$this->assertEquals("description",  $right->description);
		$this->assertEquals("BASIC",  $right->rightType);
		$this->assertEquals(1,  $right->rightLevel);
		$this->assertEquals(2,  $right->area);
	}
}
?>
