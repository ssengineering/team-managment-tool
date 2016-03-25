<?php

namespace TMT\api;

/**
 * Unit tests for Quicklinks api
 */
class QuicklinksTest extends \PHPUNit_Framework_TestCase {

	/**
	 * @covers ::get
	 */
	public function testGetSingle() {
		$index = $this->getMockBuilder('\\TMT\\api\\quicklinks\\index')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor"))
			->getMock();
		$accessor = $this->getMockBuilder('\\TMT\\accessor\\Quicklinks')
			->disableOriginalConstructor()
			->setMethods(array("get"))
			->getMock();

		$expected = new \TMT\model\QuickLink((object)array(
			"guid" => "testguid",
			"name" => "testname",
			"netId" => "testnetId",
			"url" => "testurl"
		));
		$accessor->expects($this->once())
			->method("get")
			->with("testguid")
			->willReturn($expected);
		$index->method("getAccessor")
			->willReturn($accessor);
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $expected)));
		$index->get(array(
			"url" => array("api", "quicklinks", "testguid"),
			"request" => array()
		));
	}

	/**
	 * @covers ::get
	 */
	public function testgetByUser() {
		$index = $this->getMockBuilder('\\TMT\\api\\quicklinks\\index')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor"))
			->getMock();
		$accessor = $this->getMockBuilder('\\TMT\\accessor\\Quicklinks')
			->disableOriginalConstructor()
			->setMethods(array("getByUser"))
			->getMock();

		$expected = array(
			new \TMT\model\QuickLink((object)array("guid" => "testguid0", "name" => "testname0", "netId" => "testnetId0", "url" => "testurl0")),
			new \TMT\model\QuickLink((object)array("guid" => "testguid1", "name" => "testname1", "netId" => "testnetId1", "url" => "testurl1"))
		);
		$accessor->expects($this->once())
			->method("getByUser")
			->with("testnetId")
			->willReturn($expected);
		$index->method("getAccessor")
			->willReturn($accessor);
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $expected)));
		$index->get(array(
			"url" => array("api", "quicklinks"),
			"request" => array("netId" => "testnetId")
		));

	}

	/**
	 * @covers ::post
	 */
	public function testPost() {
		$index = $this->getMockBuilder('\\TMT\\api\\quicklinks\\index')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor"))
			->getMock();
		$index->setUserInfo(array("netId" => "testnetId"));
		$accessor = $this->getMockBuilder('\\TMT\\accessor\\Quicklinks')
			->disableOriginalConstructor()
			->setMethods(array("add"))
			->getMock();

		$model = new \TMT\model\QuickLink((object)array(
			'guid' => null, 
			'name' => "testname", 
			'netId' => "testnetId", 
			'url' => "testurl"
		));
		$expected = new \TMT\model\QuickLink((object)array(
			"guid" => "testguid",
			"name" => "testname",
			"netId" => "testnetId",
			"url" => "testurl"
		));
		$accessor->expects($this->once())
			->method("add")
			->with($model)
			->willReturn($expected);
		$index->method("getAccessor")
			->willReturn($accessor);
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $expected)));
		$index->post(array(
			"url" => array("api", "quicklinks"),
			"request" => array('name' => "testname", 'netId' => "testnetId", 'url' => "testurl")
		));
	}

	/**
	 * @covers ::put
	 */
	public function testPut() {
		$index = $this->getMockBuilder('\\TMT\\api\\quicklinks\\index')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor"))
			->getMock();
		$index->setUserInfo(array("netId" => "testnetId"));
		$accessor = $this->getMockBuilder('\\TMT\\accessor\\Quicklinks')
			->disableOriginalConstructor()
			->setMethods(array("update"))
			->getMock();

		$model = new \TMT\model\QuickLink((object)array(
			"name" => "testname",
			"url" => "testurl",
			"netId" => "testnetId",
			"guid" => "testguid"
		));
		$expected = new \TMT\model\QuickLink((object)array(
			"guid" => "testguid",
			"name" => "testname",
			"netId" => "testnetId",
			"url" => "testurl"
		));
		$accessor->expects($this->once())
			->method("update")
			->with($model)
			->willReturn($expected);
		$index->method("getAccessor")
			->willReturn($accessor);
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $expected)));
		$index->put(array(
			"url" => array("api", "quicklinks", "testguid"),
			"request" => array('name' => "testname", 'url' => "testurl", 'netId' => "testnetId")
		));
	}

	/**
	 * @covers ::delete
	 */
	public function testDelete() {
		$index = $this->getMockBuilder('\\TMT\\api\\quicklinks\\index')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor"))
			->getMock();
		$accessor = $this->getMockBuilder('\\TMT\\accessor\\Quicklinks')
			->disableOriginalConstructor()
			->setMethods(array("delete"))
			->getMock();

		$accessor->expects($this->once())
			->method("delete")
			->with("testguid");
		$index->method("getAccessor")
			->willReturn($accessor);
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
		$index->delete(array(
			"url" => array("api", "quicklinks", "testguid"),
			"request" => array()
		));
	}
}
