<?php

namespace TMT\accessor;

/**
 * Unit tests for the EmployeeRaiseLog data accessor class
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class EmployeeRaiseLogTest extends \PHPUnit_Framework_TestCase {

	protected $raiseAcc;

	/**
	 * @before
	 */
	public function setUpAccessor()
	{
		$this->raiseAcc = new EmployeeRaiseLog();
	}

	/**
	 * @covers EmployeeRaiseLog::get
	 * @covers EmployeeRaiseLog::insert
	 */
	public function testInsert()
	{
		$raise = new \TMT\model\Raise(array(
			'index' => null,
			'netID' => 'netId',
            'raise' => 10.00,
            'newWage' => 10.00,
            'submitter' => 'employee2',
            'date' => '2015-01-01',
            'comments' => 'Starting Wage',
            'isSubmitted' => 1,
			'guid' => null
		));
		$result = $this->raiseAcc->insert($raise);
		$raise->index = 1;
		$result->guid = null;
		$this->assertEquals($raise, $result);
	}

	/**
	 * @covers EmployeeRaiseLog::get
	 * @covers EmployeeRaiseLog::getCurrent
	 * @covers EmployeeRaiseLog::getAll
	 * @depends testInsert
	 */
	public function testGet() 
	{
		$expected = new \TMT\model\Raise(array(
			'index' => '1',
			'netID' => 'netId',
            'raise' => '10.00',
            'newWage' => '10.00',
            'submitter' => 'employee2',
            'date' => '2015-01-01',
            'comments' => 'Starting Wage',
            'isSubmitted' => '1',
			'guid' => null
		));
		$result1 = $this->raiseAcc->get(1);
		$result1->guid = null;
		$this->assertEquals($expected, $result1);
		$result2 = $this->raiseAcc->getCurrent('netId');
		$result2->guid = null;
		$this->assertEquals($expected, $result2);
		$result3 = $this->raiseAcc->getAll('netId');
		$result3[0]->guid = null;
		$this->assertEquals(array($expected), $result3);
	}

}
?>
