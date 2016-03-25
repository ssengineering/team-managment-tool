<?php

namespace TMT\api\position;

/**
 * Unit tests for the Position controller class
 */
class PositionTest extends \PHPUnit_Framework_TestCase {

	protected $positionCtrl;
	protected $positionAreaCtrl;
	protected $emptyParams;

	/**
	 * @before
	 */
	public function setUpAccessor()
	{
		$this->positionCtrl = $this->getMock('\TMT\api\position\index', array('checkPermission'));
		$this->positionAreaCtrl = $this->getMock('\TMT\api\position\area', array('checkPermission'));
		$this->positionCtrl->method('checkPermission')->willReturn(true);
		$this->positionAreaCtrl->method('checkPermission')->willReturn(true);
		$this->emptyParams = array("url" => array("api", "position"), "request" => array());
	}

	/**
	 * @covers index::get
	 * @covers index::post
	 * @covers index::put
	 * @covers index::delete
	 */
	public function testValidCRUD() 
	{
		// Expectations
		// POST
		$output = array("status" => "OK", "data" => array());
		$output['data'] = array("positionId" => 3, "positionName" => "TEST", 
			"positionDescription" => "TEST", "employeeArea" => 1, "deleted" => 0);
		$outputStr = "";
		$outputStr.= json_encode($output);
		// PUT
		$output['data']['positionDescription'] = "TEST_UPDATED";
		$outputStr.= json_encode($output);
		// GET
		$tmp = $output['data'];
		$output['data'] = array($tmp);
		$outputStr.= json_encode($output);
		// PUT
		$output['data'] = $tmp;
		$output['data']['employeeArea'] = 2;
		$outputStr.= json_encode($output);
		// DELETE
		$output['data'] = array("positionId" => null, "positionName" => null, 
			"positionDescription" => null, "employeeArea" => null, "deleted" => null);
		$outputStr.= json_encode($output);
		$output['data'] = [];
		// GET
		$outputStr.= json_encode($output);
		// GET
		$outputStr.= json_encode($output);
		$this->expectOutputString($outputStr);

		// Calls
		ob_start();
		$this->positionCtrl->post(array("request" => array(
			"positionName" => "TEST",
			"positionDescription" => "TEST"
		)));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		unset($result["data"]["guid"]);
		echo json_encode($result);

		ob_start();
		$this->positionCtrl->put(array("url" => array("api", "position", "3"), 
			"request" => array(
			"positionDescription" => "TEST_UPDATED"
		)));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		unset($result["data"]["guid"]);
		echo json_encode($result);

		ob_start();
		$this->positionCtrl->get($this->emptyParams);
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		unset($result["data"][0]["guid"]);
		echo json_encode($result);

		ob_start();
		$this->positionCtrl->put(array("url" => array("api", "position", "3"), 
			"request" => array(
			"employeeArea" => 2
		)));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		unset($result["data"]["guid"]);
		echo json_encode($result);

		ob_start();
		$this->positionCtrl->delete(array("url" => array("api", "position", "1")));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		unset($result["data"]["guid"]);
		echo json_encode($result);

		ob_start();
		$this->positionAreaCtrl->get(array("url" => array("api", "position", "area", "3")));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		echo json_encode($result);

		ob_start();
		$this->positionAreaCtrl->get(array("url" => array("api", "position", "area", "4")));
		$output = ob_get_contents();
		ob_end_clean();
		$result = json_decode($output, true);
		echo json_encode($result);
	}

	/**
	 * @covers index::post
	 * @covers index::put
	 * @covers index::delete
	 */
	public function testInvalidCRUD() 
	{
		// Expectations
		$errors = json_encode(array("status"=>"ERROR",
			"message"=>"Must specify positionName and positionDescription"));
		$errors.= json_encode(array("status"=>"ERROR",
			"message"=>"No positionId provided"));
		$errors.= json_encode(array("status"=>"ERROR",
			"message"=>"No positionId provided"));
		$this->expectOutputString($errors);

		$this->positionCtrl->post($this->emptyParams);
		$this->positionCtrl->put(array("url" => array("api", "position"), 
			"request" => array(
			"employeeArea" => 2
		)));
		$this->positionCtrl->delete(array("url" => array("api", "position"))); 
	}

}
?>
