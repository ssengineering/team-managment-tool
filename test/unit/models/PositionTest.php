<?php

namespace TMT\model;

class PositionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers ::__construct
     */
    public function testEmptyConstruct() {
        $position = new Position();
		$this->assertEquals($position->guid, null);
        $this->assertEquals($position->positionId, null);
        $this->assertEquals($position->positionName, null);
        $this->assertEquals($position->positionDescription, null);
        $this->assertEquals($position->employeeArea, null);
        $this->assertEquals($position->deleted, 0);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct() {
        $position = (object) array(
			'guid'                => '11111111-1111-1111-1111-111111111111',
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 1,
            'deleted'             => 0
        );

        $position2 = new \TMT\model\Position($position);

		$this->assertEquals($position2->guid, '11111111-1111-1111-1111-111111111111');
        $this->assertEquals($position2->positionId, 1);
        $this->assertEquals($position2->positionName, 'MY POSITION');
        $this->assertEquals($position2->positionDescription, 'THIS IS A COOL JOB');
        $this->assertEquals($position2->employeeArea, 1);
        $this->assertEquals($position2->deleted, 0);
    }
}
