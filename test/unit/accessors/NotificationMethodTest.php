<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationMethod accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class NotificationMethodTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::getAll
	 */
	public function testGet() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT name FROM notificationMethods");
		$mockdb->expectExecute();
		$mockdb->setReturnData(array(
			(object)array("name" => "email"),
			(object)array("name" => "onsite")
		));
		$expected = array("email", "onsite");
		$accessor = new NotificationMethod($mockdb);
		$actual = $accessor->getAll();
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::add
	 */
	public function testAdd() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO notificationMethods (name) VALUES(:name)");
		$mockdb->expectExecute(array(":name" => "text"));
		$accessor = new NotificationMethod($mockdb);
		$actual = $accessor->add("text");
		$mockdb->verify();
	}

	/**
	 *	@covers ::delete
	 */
	public function testDelete() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM notificationMethods WHERE name=:name");
		$mockdb->expectExecute(array(":name" => "email"));
		$accessor = new NotificationMethod($mockdb);
		$actual = $accessor->delete("email");
		$mockdb->verify();
	}
}
?>
