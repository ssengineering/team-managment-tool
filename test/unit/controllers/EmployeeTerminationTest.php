<?php

namespace TMT\api\position;

/**
 * Unit tests for the employee termination class
 */

class EmployeeTerminationTest extends \PHPUnit_Framework_TestCase {

    /**
     *  @covers ::get
     */
    public function testGet() {
        $data = (object)array("reasons"=>"Bad", "attendance"=>"Good", "attitude"=>"Excellent", "performance"=>"Bad", "netID"=>"netId", "submitter"=>"subNetId", "area"=>1, "rehirable"=>"false");
		$mock = $this->getMockBuilder('\TMT\api\employee\terminate')
			->disableOriginalConstructor()
			->setMethods(array("getAccessor", "requireAuthentication", "forcePermission"))
			->getMock();
		$mock->expects($this->once())
			->method("requireAuthentication")
			->willReturn(true);
		$mock->expects($this->once())
			->method("forcePermission")
			->with("read", "59b0f789-6bb6-414d-a860-ca61fdcf372f")
			->willReturn(true);

		$termAcc = $this->getMockBuilder('\TMT\accessor\EmployeeTermination')
			->disableOriginalConstructor()
			->setMethods(array("get"))
			->getMock();
		$termAcc->expects($this->once())
			->method("get")
			->with("testNetId")
			->willReturn(new \TMT\model\EmployeeTermination($data));

		$mock->expects($this->once())
			->method("getAccessor")
			->with("EmployeeTermination")
			->willReturn($termAcc);

		$this->expectOutputString(json_encode(array("status"=>"OK", "data"=>new \TMT\model\EmployeeTermination($data))));
		$mock->get(array("url" => array("api", "employee", "terminate", "testNetId"), "request" => array()));
    }

    /**
     * @covers ::post
     */
    public function testPost() {
		$data = array("reasons"=>"Bad", "attendance"=>"Good", "attitude"=>"Excellent", "performance"=>"Bad", "netID"=>"netId", "submitter"=>"subNetId", "area"=>1, "rehirable"=>"false");

		// Mock the termination api endpoint
		$mock = $this->getMockBuilder('\TMT\api\employee\terminate')
			->disableOriginalConstructor()
			->setMethods(array("getController", "getAccessor", "requireAuthentication", "forcePermission", "sendAuthenticatedRequest"))
			->getMock();
		$mock->expects($this->once())
			->method("requireAuthentication")
			->willReturn(true);
		$mock->expects($this->once())
			->method("forcePermission")
			->with("update", "1450ff35-82a7-45ed-adcf-ffa254ebafa2")
			->willReturn(true);
		$mock->expects($this->once())
			->method("sendAuthenticatedRequest")
			->with("DELETE", "http://".getenv("PERMISSIONS_URL")."/groupMembers/netId")
			->willReturn(array("status" => "OK", "data" => "success"));
		$mock->setUserInfo(array(
			"netId" => "subNetId",
			"area" => 1
		));

		// Mock Employee accessor
		$empAcc = $this->getMockBuilder('\TMT\accessor\Employee')
			->disableOriginalConstructor()
			->setMethods(array("get"))
			->getMock();
		$empAcc->expects($this->once())
			->method("get")
			->with("netId")
			->willReturn(new \TMT\model\Employee((object)array(
				"netID" => "netId",
				"area" => 1
			)));

		// Mock NotificationPreferences accessor
		$npAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
			->disableOriginalConstructor()
			->setMethods(array("deleteAll"))
			->getMock();
		$npAcc->expects($this->once())
			->method("deleteAll")
			->with("netId")
			->willReturn(true);

		// Mock EmployeeTermination accessor
		$etAcc = $this->getMockBuilder('\TMT\accessor\EmployeeTermination')
			->disableOriginalConstructor()
			->setMethods(array("save"))
			->getMock();
		$etAcc->expects($this->once())
			->method("save")
			->with(new \TMT\model\EmployeeTermination((object)$data))
			->willReturn(true);

		// Mock the RightHandler object
		$rightHandler = $this->getMockBuilder('\TMT\controller\RightHandler')
			->disableOriginalConstructor()
			->setMethods(array("revokeAll"))
			->getMock();
		$rightHandler->expects($this->once())
			->method("revokeAll")
			->with("netId", "subNetId")
			->willReturn(array("sent" => array(), "failed" => array(), "manual" => array()));
		$mock->expects($this->once())
			->method("getController")
			->with("RightHandler")
			->willReturn($rightHandler);

		// Mock getting accessors
		$mock->expects($this->exactly(3))
			->method("getAccessor")
			->withConsecutive(array("Employee"), array("NotificationPreferences"), array("EmployeeTermination"))
			->will($this->onConsecutiveCalls($empAcc, $npAcc, $etAcc));

		// Run test
		$this->expectOutputString(json_encode(array("status"=>"OK", "data"=>array(
			"permissions" => true,
			"notifications" => true,
			"rights" => array("sent" => array(), "failed" => array(), "manual" => array()),
			"terminated" => true
		))));
		$mock->post(array("url" => array("api", "employee", "terminate"), "request" => $data));
    }
}
