<?php

namespace TMT\accessor;

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
class EmployeeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$employeeAccessor = new \TMT\accessor\Employee();
		$employee = $employeeAccessor->get('netId');

		$this->assertEquals('netId', $employee->netID);
		$this->assertEquals(1, $employee->active);
		$this->assertEquals(1, $employee->area);
		$this->assertEquals('First', $employee->firstName);
		$this->assertEquals('Last', $employee->lastName);
		$this->assertEquals('', $employee->maidenName);
		$this->assertEquals('1234567890', $employee->phone);
		$this->assertEquals('email@byu.edu', $employee->email);
		$this->assertEquals('1990-01-01', $employee->birthday);
		$this->assertEquals('English, Spanish', $employee->languages);
		$this->assertEquals('Salt Lake City', $employee->hometown);
		$this->assertEquals('Computer Science', $employee->major);
		$this->assertEquals('United States', $employee->missionOrStudyAbroad);
		$this->assertEquals('04/15', $employee->graduationDate);
		$this->assertEquals(1, $employee->position);
		$this->assertEquals('Morning', $employee->shift);
		$this->assertEquals('sup1', $employee->supervisor);
		$this->assertEquals('2015-05-29', $employee->hireDate);
		$this->assertEquals('Level 1', $employee->certificationLevel);
		$this->assertEquals(0, $employee->international);
		$this->assertEquals('123456789', $employee->byuIDnumber);
		$this->assertEquals(0, $employee->fullTime);
		$this->assertEquals("9b246d87-09d9-45c6-ea58-7c0e0e8cb2fa", $employee->guid);
	}

	/**
	 * @covers ::save
	 */
	public function testInsert() {
		$employee = (object) array(
			'netID'                => 'inserted',
			'active'               => -1,
			'area'                 => 2,
			'firstName'            => 'first',
			'lastName'             => 'last',
			'maidenName'           => 'maiden',
			'phone'                => '1234567890',
			'email'                => 'email@byu.edu',
			'birthday'             => '1990-01-01',
			'languages'            => 'english, spanish',
			'hometown'             => 'provo',
			'major'                => 'cs',
			'missionOrStudyAbroad' => 'USA',
			'graduationDate'       => '2015-04',
			'position'             => 3,
			'shift'                => 'morning',
			'supervisor'           => 'sup',
			'hireDate'             => '2015-05-29',
			'certificationLevel'   => 'new hire',
			'international'        => 0,
			'byuIDnumber'          => '987654321',
			'fullTime'             => 1,
			'guid'                 => null
		);
		$employeeObj = new \TMT\model\Employee($employee);
		$accessor = new \TMT\accessor\Employee();
		$accessor->save($employeeObj);
		$employee2 = $accessor->get('inserted');

		$this->assertEquals('inserted', $employee2->netID);
		$this->assertEquals(-1, $employee2->active);
		$this->assertEquals(2, $employee2->area);
		$this->assertEquals('first', $employee2->firstName);
		$this->assertEquals('last', $employee2->lastName);
		$this->assertEquals('maiden', $employee2->maidenName);
		$this->assertEquals('1234567890', $employee2->phone);
		$this->assertEquals('email@byu.edu', $employee2->email);
		$this->assertEquals('1990-01-01', $employee2->birthday);
		$this->assertEquals('english, spanish', $employee2->languages);
		$this->assertEquals('provo', $employee2->hometown);
		$this->assertEquals('cs', $employee2->major);
		$this->assertEquals('USA', $employee2->missionOrStudyAbroad);
		$this->assertEquals('2015-04', $employee2->graduationDate);
		$this->assertEquals(3, $employee2->position);
		$this->assertEquals('morning', $employee2->shift);
		$this->assertEquals('sup', $employee2->supervisor);
		$this->assertEquals('2015-05-29', $employee2->hireDate);
		$this->assertEquals('new hire', $employee2->certificationLevel);
		$this->assertEquals(0, $employee2->international);
		$this->assertEquals('987654321', $employee2->byuIDnumber);
		$this->assertEquals(1, $employee2->fullTime);
	}

	/**
	 * @covers ::save
	 * @covers ::get
	 */
	public function testUpdate() {
		$accessor = new \TMT\accessor\Employee();
		$employee = $accessor->get("inserted");
		$employee->active = 1;
		$employee->area = 3;
		$employee->firstName = 'fName';
		$employee->lastName = 'lName';
		$employee->maidenName = 'none';
		$employee->phone = '0987654321';
		$employee->email = 'test.byu.edu';
		$employee->birthday = '1990-02-02';
		$employee->languages = 'english';
		$employee->hometown = 'orem';
		$employee->major = 'it';
		$employee->missionOrStudyAbroad = 'Mexico';
		$employee->graduationDate = '2015-05';
		$employee->position = 4;
		$employee->shift = 'afternoon';
		$employee->supervisor = 'supervisor';
		$employee->hireDate = '2015-05-28';
		$employee->certificationLevel = 'old';
		$employee->international = 1;
		$employee->byuIDnumber = '12345';
		$employee->fullTime = 0;
		$accessor->save($employee);
		$employee2 = $accessor->get('inserted');
		$this->assertEquals(1, $employee2->active);
		$this->assertEquals(3, $employee2->area);
		$this->assertEquals('fName', $employee2->firstName);
		$this->assertEquals('lName', $employee2->lastName);
		$this->assertEquals('none', $employee2->maidenName);
		$this->assertEquals('0987654321', $employee2->phone);
		$this->assertEquals('test.byu.edu', $employee2->email);
		$this->assertEquals('1990-02-02', $employee2->birthday);
		$this->assertEquals('english', $employee2->languages);
		$this->assertEquals('orem', $employee2->hometown);
		$this->assertEquals('it', $employee2->major);
		$this->assertEquals('Mexico', $employee2->missionOrStudyAbroad);
		$this->assertEquals('2015-05', $employee2->graduationDate);
		$this->assertEquals(4, $employee2->position);
		$this->assertEquals('afternoon', $employee2->shift);
		$this->assertEquals('supervisor', $employee2->supervisor);
		$this->assertEquals('2015-05-28', $employee2->hireDate);
		$this->assertEquals('old', $employee2->certificationLevel);
		$this->assertEquals(1, $employee2->international);
		$this->assertEquals('12345', $employee2->byuIDnumber);
		$this->assertEquals(0, $employee2->fullTime);
	}

	/**
	 * @covers ::getByArea
	 */
	public function testGetByArea() {
		$accessor = new \TMT\accessor\Employee();
		$employee = $accessor->get('inserted');
		$employee->area = 2;
		$accessor->save($employee);
		$employees = $accessor->getByArea(2);
		$this->assertEquals(3, count($employees));
		$employees = $accessor->getByArea(4);
		$this->assertEquals(0, count($employees));
		$employee = $accessor->get('inserted');
		$employee->active = 0;
		$accessor->save($employee);
		$employees = $accessor->getByArea(2, true, 0);
		$this->assertEquals(2, count($employees));
		$employee = $accessor->get('inserted');
		$employee->active = -1;
		$accessor->save($employee);
		$employees = $accessor->getByArea(2, true, -1);
		$this->assertEquals(1, count($employees));
		$this->assertEquals('inserted', $employees[0]->netID);
		$employees = $accessor->getByArea(array(1,2));
		$this->assertEquals(4, count($employees));
		$employees = $accessor->getByArea(array(1,2), true, 1);
		$this->assertEquals(2, count($employees));
	}

	/**
	 * @covers ::search
	 */
	public function testSearch() {
		$accessor = new \TMT\accessor\Employee();
		$employees = $accessor->search();
		$this->assertEquals(0, count($employees));
		$employees = $accessor->search(array());
		$this->assertEquals(0, count($employees));
		$employees = $accessor->search(array('something' => "L"));
		$this->assertEquals(0, count($employees));
		$employees = $accessor->search(array('lastName' => "l"));
		$this->assertEquals(3, count($employees));
		$employees = $accessor->search(array('firstName' => "f"));
		$this->assertEquals(2, count($employees));
		$employees = $accessor->search(array('firstName' => "f", 'lastName' => 'o'));
		$this->assertEquals(4, count($employees));
		$employees = $accessor->search(array('firstName' => "f", 'lastName' => 'o', 'area' => 1));
		$this->assertEquals(1, count($employees));
		$this->assertEquals("netId", $employees[0]->netID);
		$employees = $accessor->search(array('firstName' => "f", 'lastName' => 'o', 'active' => 1));
		$this->assertEquals(2, count($employees));
		$employees = $accessor->search(array('netId' => "e"));
		$this->assertEquals(4, count($employees));
		$employees = $accessor->search(array('netId' => "e", 'area' => 4));
		$this->assertEquals(0, count($employees));
		$employees = $accessor->search(array('netId' => "e", 'fullTime' => 1));
		$this->assertEquals(2, count($employees));
		$employees = $accessor->search(array('active' => 1, 'area' => 1, 'fullTime' => 0));
		$this->assertEquals(1, count($employees));
		$this->assertEquals($employees[0]->netID, "netId");
		$employees = $accessor->search(array('active' => 1, 'area' => 2, 'fullTime' => 1));
		$this->assertEquals(1, count($employees));
		$this->assertEquals("other", $employees[0]->netID);
		$employees = $accessor->search(array('firstName' => 'e', 'lastName' => 'e', 'netId' => 'e', 'active' => 0, 'area' => 2, 'fullTime' => 1));
		$this->assertEquals(1, count($employees));
		$this->assertEquals("employee2", $employees[0]->netID);
		$employees = $accessor->search(array('firstName' => 'e', 'lastName' => 'e', 'netId' => 'e'));
		$this->assertEquals(4, count($employees));
		$employees = $accessor->search(array('firstName' => 'emp', 'lastName' => 'emp', 'netId' => 'emp'));
		$this->assertEquals(1, count($employees));
		$this->assertEquals("employee2", $employees[0]->netID);
	}

	/**
	 * @covers ::getByArea
	 */
	public function testGetByAreaNotDefault() {
		$accessor = new \TMT\accessor\Employee();
		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$stmt = $pdo->prepare("INSERT INTO employeeAreaPermissions (netID, area, guid) VALUES (:netId1, :area1, :guid), (:netId2, :area2, :guid2)");
		$stmt->execute(array(':netId1' => 'employee2', ':area1' => 1, ':guid' => $accessor->newGuid(), ':netId2' => 'netId', ':area2' => 2, ':guid2' => $accessor->newGuid()));

		$accessor = new \TMT\accessor\Employee();
		$employees = $accessor->getByArea(1, false);
		$this->assertEquals(2, count($employees));
		$employees = $accessor->getByArea(2, false);
		$this->assertEquals(4, count($employees));
		$employees = $accessor->getByArea(2, false, 1);
		$this->assertEquals(2, count($employees));
		$employees = $accessor->getByArea(array(1), false, 0);
		$this->assertEquals(1, count($employees));

		$stmt2 = $pdo->prepare("DELETE FROM employeeAreaPermissions");
		$stmt2->execute();
	}
}
?>
