<?php

namespace TMT\api\employee;

/**
 * Unit tests for the Mim controller class
 */
class EmployeeTest extends \PHPUnit_Framework_TestCase {

	protected $ctrl;
	protected $areaCtrl;

	protected $employee1 = array(
		'guid'                 => '9b246d87-09d9-45c6-ea58-7c0e0e8cb2fa',
		'netID'                => 'netId',
		'active'               => 1,
		'area'                 => 1,
		'firstName'            => 'First',
		'lastName'             => 'Last',
		'maidenName'           => '',
		'phone'                => '1234567890',
		'email'                => 'email@byu.edu',
		'birthday'             => '1990-01-01',
		'languages'            => 'English, Spanish',
		'hometown'             => 'Salt Lake City',
		'major'                => 'Computer Science',
		'missionOrStudyAbroad' => 'United States',
		'graduationDate'       => '04/15',
		'position'             => 1,
		'shift'                => 'Morning',
		'supervisor'           => 'sup1',
		'hireDate'             => '2015-05-29',
		'certificationLevel'   => 'Level 1',
		'international'        => 0,
		'byuIDnumber'          => '123456789',
		'fullTime'             => 0
	);

	protected $employee2 = array(
		'guid'                 => '04556fcd-dc6d-4644-9dfc-6a4558dd4ace',
		'netID'                => 'employee2',
		'active'               => 0,
		'area'                 => 2,
		'firstName'            => 'Emp',
		'lastName'             => 'Loyee',
		'maidenName'           => 'Maiden',
		'phone'                => '9876543210',
		'email'                => 'emp2@gmail.com',
		'birthday'             => '1990-02-02',
		'languages'            => 'English',
		'hometown'             => '',
		'major'                => '',
		'missionOrStudyAbroad' => '',
		'graduationDate'       => '',
		'position'             => 2,
		'shift'                => '',
		'supervisor'           => '',
		'hireDate'             => '',
		'certificationLevel'   => '',
		'international'        => 0,
		'byuIDnumber'          => '',
		'fullTime'             => 1
	);

	protected $employee3 = array(
		'guid'                 => '9779662a-446e-4e09-c5ce-230f0e757f10',
		'netID'                => 'other',
		'active'               => 1,
		'area'                 => 2,
		'firstName'            => 'Other',
		'lastName'             => 'Person',
		'maidenName'           => '',
		'phone'                => '555-555-5555',
		'email'                => 'other@person',
		'birthday'             => '01/01',
		'languages'            => '',
		'hometown'             => '',
		'major'                => '',
		'missionOrStudyAbroad' => '',
		'graduationDate'       => '',
		'position'             => 1,
		'shift'                => '',
		'supervisor'           => '',
		'hireDate'             => '',
		'certificationLevel'   => '',
		'international'        => 0,
		'byuIDnumber'          => '999999999',
		'fullTime'             => 1
	);

	protected $employee4 = array(
		'guid'                 => '',
		'netID'                => 'inserted',
		'active'               => -1,
		'area'                 => 2,
		'firstName'            => 'fName',
		'lastName'             => 'lName',
		'maidenName'           => 'none',
		'phone'                => '0987654321',
		'email'                => 'test.byu.edu',
		'birthday'             => '1990-02-02',
		'languages'            => 'english',
		'hometown'             => 'orem',
		'major'                => 'it',
		'missionOrStudyAbroad' => 'Mexico',
		'graduationDate'       => '2015-05',
		'position'             => 4,
		'shift'                => 'afternoon',
		'supervisor'           => 'supervisor',
		'hireDate'             => '2015-05-28',
		'certificationLevel'   => 'old',
		'international'        => 1,
		'byuIDnumber'          => '12345',
		'fullTime'             => 0
	);

	protected $TESTING_USER = array(
		'guid'                 => '',
		'netID'                => 'TESTING_USER',
		'active'               => 1,
		'area'                 => 1,
		'firstName'            => 'test',
		'lastName'             => 'user',
		'maidenName'           => '',
		'phone'                => '',
		'email'                => '',
		'birthday'             => '',
		'languages'            => '',
		'hometown'             => '',
		'major'                => '',
		'missionOrStudyAbroad' => '',
		'graduationDate'       => '',
		'position'             => 1,
		'shift'                => '',
		'supervisor'           => '',
		'hireDate'             => '',
		'certificationLevel'   => '',
		'international'        => 0,
		'byuIDnumber'          => '',
		'fullTime'             => 1
	);

	protected $success = array(
		"status" => "OK",
		"data"   => "Success"
	);

	public function setUp() {
		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);

		$stmt = $pdo->prepare("SELECT guid, netID FROM employee WHERE netID='inserted' OR netID='TESTING_USER'");
		$stmt->execute();
		while($employee = $stmt->fetch()) {
			if($employee->netID == "inserted") {
				$this->employee4["guid"] = $employee->guid;
			} else {
				$this->TESTING_USER["guid"] = $employee->guid;
			}
		}
	}

	/**
	 * Set up 
	 */
	public function testInsert() {
		$this->ctrl = new \TMT\api\employee\index();

		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);

		// Use for set up and to test
		ob_start();
		$post_array = array("url" => array("api", "employee", "TESTING_USER"), "request" => $this->TESTING_USER);
		$post_array['request']['wage'] = 0.00;
		$this->ctrl->post($post_array);
		ob_end_clean();

		// set up necessary permissions
		$stmt = $pdo->prepare("INSERT INTO permission (shortName, longName, description) VALUES ('editEmployeeInfo', 'Edit employee information', 'ability to edit employees')");
		$stmt->execute();
		$stmt = $pdo->prepare("INSERT INTO permissionArea (area, permissionId, guid) VALUE (1, 1, '12345678-1234-1324-1234-123456781234'), (2, 1, '12345678-1234-1324-1234-123456781235')");
		$stmt->execute();
		$stmt = $pdo->prepare("INSERT INTO employeeAreaPermissions (netID, area) VALUES ('TESTING_USER', 2)");
		$stmt->execute();
		$stmt = $pdo->prepare("INSERT INTO employeePermissions (netID, permission, guid) VALUES ('TESTING_USER', 2, '32345678-1234-1324-1234-123456781234'), ('TESTING_USER', 1, '22345678-1234-1324-1234-123456781234')");
		$stmt->execute();
	}

	/**
	 * @covers index::get
	 * @depends testInsert
	 */
	public function testGet() {
		$this->ctrl = new \TMT\api\employee\index();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $this->employee1)));
		$this->ctrl->get(array("url" => array("api", "employee", "netId")));
	}

	/**
	 * @covers index::get
	 * @depends testInsert
	 */
	public function testGetSearch() {
		$this->ctrl = new \TMT\api\employee\index();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $this->employee1)));
		$this->ctrl->get(array("url" => array("api", "employee", "netId"), "request" => array("area" => 1, "active" => 1)));
	}

	/**
	 * @covers index::put
	 * @depends testGet
	 */
	public function testPut() {
		$this->ctrl = $this->getMock('\TMT\api\employee\index', array('can', 'isSuperuser'));
		$this->ctrl->method("can")->willReturn(true);
		$this->ctrl->method("isSuperuser")->willReturn(true);
		$this->employee1['phone'] = "1";
		$tmp = new \TMT\model\Employee((object) $this->employee1);
		$tmp->phone = "1234567890";
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $this->employee1)).
			json_encode(array("status" => "OK", "data" => $tmp)));
		$this->ctrl->put(array("url" => array("api", "employee", "netId"), "request" => array("netID" => "netId", "phone" => "1")));
		$this->employee1['phone'] = "1234567890";
		$this->ctrl->put(array("url" => array("api", "employee", "netId"), "request" => $this->employee1));
	}

	/**
	 * @covers area::get
	 * @depends testPut
	 */
	public function testArea() {
		$this->areaCtrl = new \TMT\api\employee\area();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => array($this->employee1, $this->TESTING_USER))));
		$this->areaCtrl->get(array("url" => array("api", "employee", "area", "1"), "request" => array()));
	}
	/**
	 * @covers area::get
	 * @depends testArea
	 */
	public function testAreaMultipleAreas() {
		$this->areaCtrl = new \TMT\api\employee\area();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => array($this->employee2, $this->employee4, $this->employee1, $this->employee3, $this->TESTING_USER))));
		$this->areaCtrl->get(array("url" => array("api", "employee", "area"), "request" => array("areas" => array("1","2"))));
	}
	/**
	 * @covers area::get
	 * @depends testAreaMultipleAreas
	 */
	public function testAreaTwoAreaFormats() {
		$this->areaCtrl = new \TMT\api\employee\area();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => array($this->employee2, $this->employee4, $this->employee1, $this->employee3, $this->TESTING_USER))));
		$this->areaCtrl->get(array("url" => array("api", "employee", "area", "2"), "request" => array("areas" => array("1"))));
	}
	/**
	 * @covers area::get
	 * @depends testAreaTwoAreaFormats
	 */
	public function testAreaActive() {
		$this->areaCtrl = new \TMT\api\employee\area();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => array($this->employee2))));
		$this->areaCtrl->get(array("url" => array("api", "employee", "area"), "request" => array("active" => "0", "areas" => array("2"))));
	}
	/**
	 * @covers area::get
	 * @depends testAreaActive
	 */
	public function testAreaDefaultArea() {
		$this->areaCtrl = new \TMT\api\employee\area();
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => array($this->employee3, $this->TESTING_USER))));
		$this->areaCtrl->get(array("url" => array("api", "employee", "area", "2"), "request" => array("active" => "1", "defaultOnly" => "false")));

		// Clean up
		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$stmt = $pdo->prepare("DELETE FROM employee WHERE netID='TESTING_USER'");
		$stmt->execute();
		$stmt = $pdo->prepare("DELETE FROM permission");
		$stmt->execute();
		$stmt = $pdo->prepare("DELETE FROM permissionArea");
		$stmt->execute();
		$stmt = $pdo->prepare("DELETE FROM employeeAreaPermissions");
		$stmt->execute();
		$stmt = $pdo->prepare("DELETE FROM employeePermissions");
		$stmt->execute();
	}

	/**
	 * @covers index::post
	 */
	public function testPost() {
		$this->ctrl = $this->getMock('\TMT\api\employee\index', array('can'));
		$this->ctrl->method("can")->willReturn(true);
		$minimum = array(
			'netID'                => 'notRealUser',
			'firstName'            => 'Not',
			'lastName'             => 'Real',
			'phone'                => '555-555-5555',
			'email'                => 'not_real@byu.edu',
			'position'             => 1,
			'byuIDnumber'          => '000000000',
			'wage'         		   => '10.20',
		);
		$full = array(
			'guid'                 => null,
			'netID'                => 'notRealUser',
			'active'               => 1,
			'area'                 => 1,
			'firstName'            => 'Not',
			'lastName'             => 'Real',
			'maidenName'           => '',
			'phone'                => '555-555-5555',
			'email'                => 'not_real@byu.edu',
			'birthday'             => '',
			'languages'            => '',
			'hometown'             => '',
			'major'                => '',
			'missionOrStudyAbroad' => '',
			'graduationDate'       => '',
			'position'             => 1,
			'shift'                => '',
			'supervisor'           => '',
			'hireDate'             => '',
			'certificationLevel'   => '',
			'international'        => 0,
			'byuIDnumber'          => '000000000',
			'fullTime'             => 0
		);
		$full2 = array(
			'guid'                 => null,
			'netID'                => 'notRealUser2',
			'active'               => 1,
			'area'                 => 1,
			'firstName'            => 'Not',
			'lastName'             => 'Real',
			'maidenName'           => 'test',
			'phone'                => '555-555-5555',
			'email'                => 'not_real@byu.edu',
			'birthday'             => 'test',
			'languages'            => 'test',
			'hometown'             => 'test',
			'major'                => 'test',
			'missionOrStudyAbroad' => 'test',
			'graduationDate'       => 'test',
			'position'             => 1,
			'shift'                => 'test',
			'supervisor'           => 'test',
			'hireDate'             => 'test',
			'certificationLevel'   => 'test',
			'international'        => 1,
			'byuIDnumber'          => '100000000',
			'fullTime'             => 0
		);
		$this->expectOutputString(json_encode(array("status"=>"OK","data"=>$full)).
			json_encode(array("status"=>"OK","data"=>$full2)).json_encode(
				array("status"=>"ERROR","message"=>"Must include valid starting wage")));
		$post_array = array("url"=>array("api","employee"), "request"=>$minimum);
		$this->ctrl->post($post_array);
		$post_array = array("url"=>array("api","employee"), "request"=>$full2);
		$post_array['request']['wage'] = 10.50;
		$this->ctrl->post($post_array);
		$post_array = array("url"=>array("api","employee"), "request"=>$full2);
		$this->ctrl->post($post_array);
	}
}
?>
