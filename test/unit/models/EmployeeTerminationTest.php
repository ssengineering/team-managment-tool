<?php

namespace TMT\model;

class EmployeeTerminationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers ::__construct
     */
    public function testEmptyConstruct() {
        $employee = new EmployeeTermination();
		$this->assertEquals($employee->guid, null);
        $this->assertEquals($employee->reasons, null);
        $this->assertEquals($employee->attendance, null);
        $this->assertEquals($employee->attitude, null);
        $this->assertEquals($employee->performance, null);
        $this->assertEquals($employee->netID, null);
        $this->assertEquals($employee->submitter, null);
        $this->assertEquals($employee->area, null);
        $this->assertEquals($employee->rehirable, null);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct() {
        $position = (object) array(
			'guid'               => '11111111-1111-1111-1111-111111111111',
            'reasons'            => 'TOO COOL',
            'attendance'         => 'EVERY HOUR OF EVERY DAY',
            'attitude'           => 'GODLIKE',
            'performance'        => 'THE BEST',
            'netID'              => 'employee2',
            'submitter'          => 'netId',
            'area'               => 2,
            'rehirable'          => 'true'
        );

        $position2 = new \TMT\model\EmployeeTermination($position);

		$this->assertEquals($position2->guid, '11111111-1111-1111-1111-111111111111');
        $this->assertEquals($position2->reasons, 'TOO COOL');
        $this->assertEquals($position2->attendance, 'EVERY HOUR OF EVERY DAY');
        $this->assertEquals($position2->attitude, 'GODLIKE');
        $this->assertEquals($position2->performance, 'THE BEST');
        $this->assertEquals($position2->netID, 'employee2');
        $this->assertEquals($position2->submitter, 'netId');
        $this->assertEquals($position2->area, 2);
        $this->assertEquals($position2->rehirable, 'true');
    }
}
