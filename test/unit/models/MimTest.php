<?php

namespace TMT\model;

/**
 * Unit tests for the Mim model class
 */
class MimTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers Mim::__construct
	 * @covers Mim::jsonSerialize
	 */
	public function testConstruct() 
	{
		$mim = new Mim();
		$expected = '{"guid":null,"netID":null,"firstName":null,"lastName":null}';
		$this->assertEquals(json_encode($mim), $expected);
		$mim->netID = "testID";	
		$mim->firstName = "testFirst";	
		$mim->lastName = "testLast";	
		$mim->guid = '11111111-1111-1111-1111-111111111111';
		$expected = '{"guid":"11111111-1111-1111-1111-111111111111","netID":"testID","firstName":"testFirst","lastName":"testLast"}';
		$this->assertEquals(json_encode($mim), $expected);
		$mim_copy = new Mim($mim);
		$this->assertEquals($mim_copy, $mim);
	}

}
?>
