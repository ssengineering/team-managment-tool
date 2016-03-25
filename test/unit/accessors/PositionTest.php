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

class PositionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @covers ::get
     */
    public function testGet() {
        $positionData = (object) array(
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 1,
            'deleted'             => 0
        );

        $positionAccessor = new Position();

        $positionAccessor->save($positionData);
        $position = $positionAccessor->get(1);

        $this->assertEquals(1, $position->positionId);
        $this->assertEquals('MY POSITION', $position->positionName);
        $this->assertEquals('THIS IS A COOL JOB', $position->positionDescription);
        $this->assertEquals(1, $position->employeeArea);
        $this->assertEquals(0, $position->deleted);
    }

    /**
     * @covers ::save
     */
    public function testInsert() {
        $position = (object) array(
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 1,
            'deleted'             => 0,
			'guid'                => null
        );

        $positionObj = new \TMT\model\Position($position);
        $accessor = new \TMT\accessor\Position();
        $accessor->save($positionObj);
        $position2 = $accessor->get(1);

        $this->assertEquals(1, $position2->positionId);
        $this->assertEquals('MY POSITION', $position2->positionName);
        $this->assertEquals('THIS IS A COOL JOB', $position2->positionDescription);
        $this->assertEquals(1, $position2->employeeArea);
        $this->assertEquals(0, $position2->deleted);
    }

    /**
     * @covers ::save
     * @covers ::get
     */
    public function testUpdate() {
        $position = (object) array(
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 2,
            'deleted'             => 0,
			'guid'                => null,
        );

        $accessor = new Position();
        $accessor->save($position);

        $getter = $accessor->get(1);

        $this->assertEquals(1, $getter->positionId);
        $this->assertEquals(2, $getter->employeeArea);

        $position2 = (object) array(
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 1,
            'deleted'             => 0
        );

        $accessor->save($position2);

        $getter = $accessor->get(1);

        $this->assertEquals(1, $getter->positionId);
        $this->assertEquals(1, $getter->employeeArea);
    }

    /**
     * @covers ::getByArea
     */
    public function testGetByArea() {
        $position = (object) array(
            'positionId'          => 1,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 2,
            'deleted'             => 0,
			'guid'                => null
        );

        $position2 = (object) array(
            'positionId'          => 2,
            'positionName'        => 'MY POSITION',
            'positionDescription' => 'THIS IS A COOL JOB',
            'employeeArea'        => 2,
            'deleted'             => 0,
			'guid'                => null
        );

        $accessor = new Position();
        $model = new Position();
        $model->save($position);
        $model2 = new Position();
        $model2->save($position2);

        $position = $accessor->getByArea(2);
        $this->assertEquals(2, count($position));

        // Clean up
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $db   = getenv('DB_NAME');
        $connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
        $options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
        $pdo = new \PDO($connectStr, $user, $pass, $options);
        $stmt = $pdo->prepare("DELETE FROM positions");
        $stmt->execute();
    }
}
