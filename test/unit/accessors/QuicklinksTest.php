<?php

namespace TMT\accessor;

/**
 * Unit tests for Quicklinks accessor
 */
class QuicklinksTest extends \PHPUNit_Framework_TestCase {

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM quicklinks WHERE guid=:guid");
		$mockdb->expectExecute(array(
			':guid' => "testguid"
		));
		$mockdb->setReturnData(array(
			(object)array('guid' => "testData0", 'name' => "testData1", 'netId' => "testData2", 'url' => "testData3")
		));
		$expected = new \TMT\model\QuickLink((object)array('guid' => "testData0", 'name' => "testData1", 'netId' => "testData2", 'url' => "testData3"));
		$accessor = new Quicklinks($mockdb);
		$actual = $accessor->get("testguid");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getByUser
	 */
	public function testgetByUser() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM quicklinks WHERE netId=:netId");
		$mockdb->expectExecute(array(
			':netId' => "testnetId"
		));
		$mockdb->setReturnData(array(
			(object)array('guid' => "testData00", 'name' => "testData01", 'netId' => "testData02", 'url' => "testData03"),
			(object)array('guid' => "testData10", 'name' => "testData11", 'netId' => "testData12", 'url' => "testData13")
		));
		$expected = array(
			new \TMT\model\QuickLink((object)array('guid' => "testData00", 'name' => "testData01", 'netId' => "testData02", 'url' => "testData03")),
			new \TMT\model\QuickLink((object)array('guid' => "testData10", 'name' => "testData11", 'netId' => "testData12", 'url' => "testData13"))
		);
		$accessor = new Quicklinks($mockdb);
		$actual = $accessor->getByUser("testnetId");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::add
	 */
	public function testadd() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO quicklinks (guid,name,netId,url) VALUES (:guid,:name,:netId,:url)");
		$mockdb->expectExecute(array(
			':guid' => "guid1",
			':name' => "testname",
			':netId' => "testnetId",
			':url' => "testurl"
		));

		$mockdb->expectPrepare("SELECT * FROM quicklinks WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "guid1"));
		$mockdb->setReturnData(array(
			(object)array('guid' => "guid1", 'name' => "testname", 'netId' => "testnetId", 'url' => "testurl")
		));
		$expected = new \TMT\model\QuickLink((object)array('guid' => "guid1", 'name' => "testname", 'netId' => "testnetId", 'url' => "testurl"));

		$accessor = new Quicklinks($mockdb);
		$accessor->setGuidCreator(new MockGuidCreator("guid1"));
		$actual = $accessor->add(new \TMT\model\QuickLink((object)array("guid" => "testguid", "name" => "testname", "netId" => "testnetId", "url" => "testurl")));
		$mockdb->verify();
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdate() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("UPDATE quicklinks SET name=:name, url=:url WHERE guid=:guid");
		$mockdb->expectExecute(array(
			':name' => "testname",
			':url' => "testurl",
			':guid' => "testguid"
		));
		$mockdb->expectPrepare("SELECT * FROM quicklinks WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "testguid"));
		$mockdb->setReturnData(array(
			(object)array('guid' => "testguid", 'name' => "testname", 'netId' => "testnetId", 'url' => "testurl")
		));
		$expected = new \TMT\model\QuickLink((object)array('guid' => "testguid", 'name' => "testname", 'netId' => "testnetId", 'url' => "testurl"));
		$accessor = new Quicklinks($mockdb);
		$actual = $accessor->update(new \TMT\model\QuickLink((object)array('guid' => "testguid", 'name' => "testname", 'netId' => "testnetId", 'url' => "testurl")));
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::delete
	 */
	public function testDelete() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM quicklinks WHERE guid=:guid");
		$mockdb->expectExecute(array(
			':guid' => 'testguid'
		));
		$accessor = new Quicklinks($mockdb);
		$accessor->delete('testguid');
		$mockdb->verify();
	}
}
