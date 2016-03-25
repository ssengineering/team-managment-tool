<?php

namespace TMT\model;

/**
 * Unit tests for the Mim model class
 */
class RaiseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers Raise::__construct
	 */
	public function testConstruct() 
	{
		$raise = new Raise();
		$expected = array(
			'guid' => null,
			'index' => null,
			'netID' => null,
			'raise' => null,
			'newWage' => null,
			'submitter' => null,
			'date' => null,
			'comments' => null,
			'newWage' => null,
			'isSubmitted' => null
		);
		$this->assertEquals(json_encode($raise), json_encode($expected));
		$raise->netID = 'netId';
		$raise->raise = 10.20;
		$raise->newWage = 10.20;
		$expected['netID'] = 'netId';
		$expected['raise'] = 10.20;
		$expected['newWage'] = 10.20;
		$this->assertEquals(json_encode($raise), json_encode($expected));
		$expected = new Raise($raise);
		$this->assertEquals($raise, $expected);
	}

}
?>
