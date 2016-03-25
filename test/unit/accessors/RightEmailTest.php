<?php

namespace TMT\accessor;

/**
 * Unit tests for the RightEmail accessor class
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RightEmailTest extends \PHPUnit_Framework_TestCase {
	
	public static function setUpBeforeClass()
	{
		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$stmt = $pdo->prepare("INSERT INTO `employeeRightsEmails` VALUES
			(1, 1, 'test@test.com', 'test@test.com', 'Test Add Title', 'Test Add Body', 
			 'Test Del Title', 'Test Del Body', '77777777-7777-7777-7777-777777777777')
		");
		$stmt->execute();
	}

	/**
	 * @covers ::getAll
	 */
	public function testGet()
	{
		$rightEmailAcc = new RightEmail();
		$email = $rightEmailAcc->getByRight(1, true);
		$expected_add = new \TMT\model\Email(array(
			"recipients" => "test@test.com",
			"cc" => "test@test.com",
			"bcc" => null,
			"subject" => "Test Add Title",
			"message" => "Test Add Body"
		));
		$expected_delete = new \TMT\model\Email(array(
			"recipients" => "test@test.com",
			"cc" => "test@test.com",
			"bcc" => null,
			"subject" => "Test Del Title",
			"message" => "Test Del Body"
		));
		$email = $rightEmailAcc->getByRight(1, false);
		$this->assertEquals($expected_delete, $email); 
	}

}
?>
