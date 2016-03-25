<?php

namespace TMT\controller;

/**
 * Unit tests for the EmailHandler controller class
 */
class RightHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @before
	 */
	public function testRevokeAll()
	{
		$expected = array("sent" => array(), "failed" => array(), "manual" => array());
		$handler = new RightHandler();
		$result = $handler->revokeAll('netId', 'employee2');
		$this->assertEquals($expected, $result);
	}

}
?>
