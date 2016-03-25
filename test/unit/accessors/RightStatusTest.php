<?php

namespace TMT\accessor;

/**
 * Unit tests for the RightStatus accessor class
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RightStatusTest extends \PHPUnit_Framework_TestCase {
	
	public static function setUpBeforeClass()
	{
		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$stmt = $pdo->prepare("INSERT INTO `employeeRightsStatus` VALUES
				(1, 'netId', 1, 1, 'test', '2015-01-01', 'test', '2015-01-01', NULL, NULL, '22222222-2222-2222-2222-222222222222'),
				(2, 'netId', 2, 2, 'test', '2015-01-01', 'test', '2015-01-01', NULL, NULL, '33333333-3333-3333-3333-333333333333'),
				(3, 'netId', 3, 2, 'test', '2015-01-01', 'test', '2015-01-01', NULL, NULL, '44444444-4444-4444-4444-444444444444');
		");
		$stmt->execute();

	}

	/**
	 * @covers ::getAll
	 */
	public function testGetAll()
	{
		$rightStatusAcc = new RightStatus();
		$rights = $rightStatusAcc->getAll('netId');
		$this->assertEquals(count($rights), 3);
		$rights = $rightStatusAcc->getAll('netId', 1);
		$this->assertEquals(count($rights), 2);
		$rights = $rightStatusAcc->getAll('netId', 2);
		$this->assertEquals(count($rights), 1);
	}

	/**
	 * @covers ::revokeAll
	 * @depends testGetAll
	 */
	public function testRevokeAll()
	{
		$rightStatusAcc = new RightStatus();
		$rights = $rightStatusAcc->revokeAll('netId', 'employee2');
		$this->assertEquals(count($rights), 3);
		foreach($rights as $right) 
		{
			$this->assertEquals($right->rightStatus, 3);
			$this->assertEquals($right->removedBy, 'employee2');
			$this->assertEquals($right->removedDate, date('Y-m-d'));
		}
		$rights = $rightStatusAcc->getAll('netId');
		$this->assertEquals(count($rights), 3);
		foreach($rights as $right) 
		{
			$this->assertEquals($right->rightStatus, 3);
			$this->assertEquals($right->removedBy, 'employee2');
			$this->assertEquals($right->removedDate, date('Y-m-d'));
		}
	}
}
?>
