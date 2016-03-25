<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationType accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class NotificationPreferenceTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::getRecipients
	 */
	public function testGetRecipients() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT notificationPreferences.*, employee.email FROM notificationPreferences
			JOIN employee ON notificationPreferences.netId=employee.netID WHERE type=:type AND notificationPreferences.area=:area");
		$mockdb->expectExecute(array(':type' => "type1", ':area' => "areaguid"));
		$mockdb->setReturnData(array(
			(object)array("netId" => "netId1", "type" => "type1", "method" => "email", "area" => "areaguid", "email" => "someone@byu.edu"),
			(object)array("netId" => "netId2", "type" => "type1", "method" => "onsite", "area" => "areaguid", "email" => "someoneelse@byu.edu"),
		));
		$expected = array(
			new \TMT\model\NotificationPreference(array("netId" => "netId1", "type" => "type1", "method" => "email", "area" => "areaguid", "email" => "someone@byu.edu")),
			new \TMT\model\NotificationPreference(array("netId" => "netId2", "type" => "type1", "method" => "onsite", "area" => "areaguid", "email" => "someoneelse@byu.edu")),
		);
		$accessor = new NotificationPreferences($mockdb);
		$actual = $accessor->getRecipients("type1", "areaguid");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getUserPreferences
	 */
	public function testGetUserPreferences() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationPreferences WHERE netId=:netId AND area=:area");
		$mockdb->expectExecute(array(':netId' => "netId", ':area' => "areaguid"));
		$mockdb->setReturnData(array(
			(object)array("netId" => "netId", "type" => "type1", "method" => "email", "area" => "areaguid"),
			(object)array("netId" => "netId", "type" => "type2", "method" => "onsite", "area" => "areaguid"),
		));
		$expected = array(
			new \TMT\model\NotificationPreference(array("netId" => "netId", "type" => "type1", "method" => "email", "area" => "areaguid")),
			new \TMT\model\NotificationPreference(array("netId" => "netId", "type" => "type2", "method" => "onsite", "area" => "areaguid")),
		);
		$accessor = new NotificationPreferences($mockdb);
		$actual = $accessor->getUserPreferences("netId", "areaguid");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getByName
	 */
	public function testGetUserPreferencesNoArea() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationPreferences WHERE netId=:netId");
		$mockdb->expectExecute(array(':netId' => "netId"));
		$mockdb->setReturnData(array(
			(object)array("netId" => "netId", "type" => "type1", "method" => "email", "area" => "area1"),
			(object)array("netId" => "netId", "type" => "type2", "method" => "onsite", "area" => "area2"),
		));
		$expected = array(
			new \TMT\model\NotificationPreference(array("netId" => "netId", "type" => "type1", "method" => "email", "area" => "area1")),
			new \TMT\model\NotificationPreference(array("netId" => "netId", "type" => "type2", "method" => "onsite", "area" => "area2")),
		);
		$accessor = new NotificationPreferences($mockdb);
		$actual = $accessor->getUserPreferences("netId");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::add
	 */
	public function testAdd() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO notificationPreferences (netId, type, method, area) VALUES(:netId, :type, :method, :area)");
		$mockdb->expectExecute(array(":netId" => "netId", ":type" => "type1", ":method" => "email", ":area" => "areaguid"));
		$accessor = new NotificationPreferences($mockdb);
		$accessor->add(new \TMT\model\NotificationPreference(array("netId" => "netId", "type" => "type1", "method" => "email", "area" => "areaguid")));
		$mockdb->verify();
	}

	/**
	 *	@covers ::delete
	 */
	public function testDelete() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM notificationPreferences WHERE netId=:netId AND type=:type AND area=:area");
		$mockdb->expectExecute(array(":netId" => "netId", ":type" => "nottype", ":area" => "areaguid"));
		$accessor = new NotificationPreferences($mockdb);
		$accessor->delete("netId", "nottype", "areaguid");
		$mockdb->verify();
	}

	/**
	 *	@covers ::deleteAll
	 */
	public function testDeleteAll() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM notificationPreferences WHERE netId=:netId");
		$mockdb->expectExecute(array(":netId" => "netId"));
		$accessor = new NotificationPreferences($mockdb);
		$accessor->deleteAll("netId");
		$mockdb->verify();
	}
}
?>
