<?php

namespace TMT\model;

/**
 * Unit tests for the RightStatusStatus model class
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RightStatusTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testEmptyConstruct() {
		$rightStatus = new RightStatus();
		$this->assertEquals(null, $rightStatus->guid);
		$this->assertEquals(null, $rightStatus->ID);
		$this->assertEquals(null, $rightStatus->rightID);      
		$this->assertEquals(null, $rightStatus->netID);
		$this->assertEquals(null, $rightStatus->rightStatus);
		$this->assertEquals(null, $rightStatus->requestedBy);
		$this->assertEquals(null, $rightStatus->requestedDate);
		$this->assertEquals(null, $rightStatus->updatedBy);
		$this->assertEquals(null, $rightStatus->updatedDate);
		$this->assertEquals(null, $rightStatus->removedBy);
		$this->assertEquals(null, $rightStatus->removedDate);
	}

	/**
	 * @covers ::__construct
	 */
	public function testPartialConstruct() {
		$partial = array(
			"rightID"    => 1
		);
		$rightStatus = new RightStatus($partial);
		$this->assertEquals(null, $rightStatus->guid);
		$this->assertEquals(null, $rightStatus->ID);
		$this->assertEquals(1,    $rightStatus->rightID);
		$this->assertEquals(null, $rightStatus->netID);
		$this->assertEquals(null, $rightStatus->rightStatus);
		$this->assertEquals(null, $rightStatus->requestedBy);
		$this->assertEquals(null, $rightStatus->requestedDate);
		$this->assertEquals(null, $rightStatus->updatedBy);
		$this->assertEquals(null, $rightStatus->updatedDate);
		$this->assertEquals(null, $rightStatus->removedBy);
		$this->assertEquals(null, $rightStatus->removedDate);
	}

	/**
	 * @covers ::__construct
	 */
	public function testFullConstruct() {
		$partial = array(
			"ID" => 1,
			"rightID" => 2,
			"netID" => 'netId',
			"rightStatus" => 3,
			"requestedBy" => "test",
			"requestedDate" => "2015-01-01",
			"updatedBy" => "test",
			"updatedDate" => "2015-01-02",
			"removedBy" => "test",
			"removedDate" => "2015-01-03",
			"guid" => "11111111-1111-1111-1111-111111111111"
		);
		$rightStatus = new RightStatus($partial);
		$this->assertEquals("11111111-1111-1111-1111-111111111111", $rightStatus->guid);
		$this->assertEquals(1,  $rightStatus->ID);
		$this->assertEquals(2,  $rightStatus->rightID);
		$this->assertEquals('netId',  $rightStatus->netID);
		$this->assertEquals(3,  $rightStatus->rightStatus);
		$this->assertEquals("test",  $rightStatus->requestedBy);
		$this->assertEquals("2015-01-01",  $rightStatus->requestedDate);
		$this->assertEquals("test",  $rightStatus->updatedBy);
		$this->assertEquals("2015-01-02",  $rightStatus->updatedDate);
		$this->assertEquals("test",  $rightStatus->removedBy);
		$this->assertEquals("2015-01-03",  $rightStatus->removedDate);
	}
}
?>
