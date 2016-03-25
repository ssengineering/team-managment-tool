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
class EmployeeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testEmptyConstruct() {
		$employee = new Employee();
		$this->assertEquals($employee->guid, null);
		$this->assertEquals($employee->netID, null);
		$this->assertEquals($employee->active, null);
		$this->assertEquals($employee->area, null);
		$this->assertEquals($employee->firstName, null);
		$this->assertEquals($employee->lastName, null);
		$this->assertEquals($employee->maidenName, null);
		$this->assertEquals($employee->phone, null);
		$this->assertEquals($employee->email, null);
		$this->assertEquals($employee->birthday, null);
		$this->assertEquals($employee->languages, null);
		$this->assertEquals($employee->hometown, null);
		$this->assertEquals($employee->major, null);
		$this->assertEquals($employee->missionOrStudyAbroad, null);
		$this->assertEquals($employee->graduationDate, null);
		$this->assertEquals($employee->position, null);
		$this->assertEquals($employee->shift, null);
		$this->assertEquals($employee->supervisor, null);
		$this->assertEquals($employee->hireDate, null);
		$this->assertEquals($employee->certificationLevel, null);
		$this->assertEquals($employee->international, null);
		$this->assertEquals($employee->byuIDnumber, null);
		$this->assertEquals($employee->fullTime, null);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$employee = (object) array(
			'guid'                 => '11111111-1111-1111-1111-111111111111',
			'netID'                => 'netId',
			'active'               => -1,
			'area'                 => 2,
			'firstName'            => 'first',
			'lastName'             => 'last',
			'maidenName'           => 'maiden',
			'phone'                => '1234567890',
			'email'                => 'email.byu.edu',
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
			'fullTime'             => 1
		);
		$this->assertEquals($employee->guid, '11111111-1111-1111-1111-111111111111');
		$this->assertEquals($employee->netID, 'netId');
		$this->assertEquals($employee->active, -1);
		$this->assertEquals($employee->area, 2);
		$this->assertEquals($employee->firstName, 'first');
		$this->assertEquals($employee->lastName, 'last');
		$this->assertEquals($employee->maidenName, 'maiden');
		$this->assertEquals($employee->phone, '1234567890');
		$this->assertEquals($employee->email, 'email.byu.edu');
		$this->assertEquals($employee->birthday, '1990-01-01');
		$this->assertEquals($employee->languages, 'english, spanish');
		$this->assertEquals($employee->hometown, 'provo');
		$this->assertEquals($employee->major, 'cs');
		$this->assertEquals($employee->missionOrStudyAbroad, 'USA');
		$this->assertEquals($employee->graduationDate, '2015-04');
		$this->assertEquals($employee->position, 3);
		$this->assertEquals($employee->shift, 'morning');
		$this->assertEquals($employee->supervisor, 'sup');
		$this->assertEquals($employee->hireDate, '2015-05-29');
		$this->assertEquals($employee->certificationLevel, 'new hire');
		$this->assertEquals($employee->international, 0);
		$this->assertEquals($employee->byuIDnumber, '987654321');
		$this->assertEquals($employee->fullTime, 1);
	}
}
?>
