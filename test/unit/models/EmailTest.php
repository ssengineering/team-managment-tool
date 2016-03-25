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
class EmailTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testEmptyConstruct() {
		$email = new Email();
		$this->assertEquals(null,  $email->subject);
		$this->assertEquals(null,  $email->message);
		$this->assertEquals(null,  $email->recipients);
		$this->assertEquals(null,  $email->cc);
		$this->assertEquals(null,  $email->bcc);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$email = (object) array(
			"subject"    => "test",
			"message"    => "This is just a test",
			"recipients" => "someone@byu.edu",
			"cc"         => "other_person@byu.edu",
			"bcc"        => "guy@byu.edu"
		);
		$email = new Email($email);
		$this->assertEquals("test",                 $email->subject);
		$this->assertEquals("This is just a test",  $email->message);
		$this->assertEquals("someone@byu.edu",      $email->recipients);
		$this->assertEquals("other_person@byu.edu", $email->cc);
		$this->assertEquals("guy@byu.edu",          $email->bcc);
	}
}
?>
