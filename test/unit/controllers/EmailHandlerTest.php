<?php

namespace TMT\controller;

/**
 * Unit tests for the EmailHandler controller class
 */
class EmailHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 * @covers ::processEmail
	 */
	public function testConstruct() {
		$email = new \TMT\model\Email((object) array(
			"recipients" => "test@byu.edu",
			"subject"    => "Testing",
			"message"    => "This is a test message",
			"cc"         => "test2@byu.edu",
			"bcc"        => "test3@byu.edu"
		));
		$handler = new \TMT\controller\EmailHandler($email);
		$result = $handler->getEmail();

		$this->assertEquals($email, $result);
	}

	/**
	 * @covers ::__construct
	 * @covers ::processEmail
	 */
	public function testConstructWithArrays() {
		$email = new \TMT\model\Email((object) array(
			"recipients" => array("test1@byu.edu", "test2@byu.edu", "test3@byu.edu"),
			"subject"    => "Testing",
			"message"    => "This is a test message",
			"cc"         => array("test4@byu.edu", "test5@byu.edu"),
			"bcc"        => array("test6@byu.edu", "test7@byu.edu")
		));
		$handler = new \TMT\controller\EmailHandler($email);
		$result = $handler->getEmail();

		$this->assertEquals("test1@byu.edu, test2@byu.edu, test3@byu.edu", $result->recipients);
		$this->assertEquals("test4@byu.edu,test5@byu.edu", $result->cc);
		$this->assertEquals("test6@byu.edu,test7@byu.edu", $result->bcc);
	}

	/**
	 * @covers ::__construct
	 * @covers ::processEmail
	 * @expectedException \TMT\exception\EmailException
	 */
	public function testBadRecipients() {
		$email = new \TMT\model\Email((object) array(
			"recipients" => null,
			"subject"    => "Testing",
			"message"    => "This is a test message",
			"cc"         => array("test4@byu.edu", "test5@byu.edu"),
			"bcc"        => array("test6@byu.edu", "test7@byu.edu")
		));
		$handler = new \TMT\controller\EmailHandler($email);
	}

	/**
	 * @covers ::__construct
	 * @covers ::processEmail
	 * @expectedException \TMT\exception\EmailException
	 */
	public function testBadCC() {
		$email = new \TMT\model\Email((object) array(
			"recipients" => array("test1@byu.edu", "test2@byu.edu", "test3@byu.edu"),
			"subject"    => "Testing",
			"message"    => "This is a test message",
			"cc"         => 1,
			"bcc"        => array("test6@byu.edu", "test7@byu.edu")
		));
		$handler = new \TMT\controller\EmailHandler($email);
	}
}
?>
