<?php

namespace TMT\accessor;

/**
 * Unit tests for the employee termination accessor class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

class EmployeeTerminationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers ::save
     * @covers ::get
     */
    public function testGet() {
        $positionData = (object) array(
            'reasons'     => 'TOO COOL',
            'attendance'  => 'EVERY HOUR OF EVERY DAY',
            'attitude'    => 'GODLIKE',
            'performance' => 'THE BEST',
            'netID'       => 'netId',
            'submitter'   => 'employee2',
            'area'        => 2,
            'rehirable'   => 'true',
			'guid'        => null
        );

        $positionAccessor = new EmployeeTermination();

        $positionAccessor->save($positionData);
        $position = $positionAccessor->get('netId');

        $this->assertEquals($position->reasons, 'TOO COOL');
        $this->assertEquals($position->attendance, 'EVERY HOUR OF EVERY DAY');
        $this->assertEquals($position->attitude, 'GODLIKE');
        $this->assertEquals($position->performance, 'THE BEST');
        $this->assertEquals($position->netID, 'netId');
        $this->assertEquals($position->submitter, 'employee2');
        $this->assertEquals($position->area, 2);
        $this->assertEquals($position->rehirable, 'true');
    }
}
