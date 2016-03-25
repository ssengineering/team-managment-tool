<?php

namespace TMT\accessor;

/**
 * Unit tests for the base Mongodb data accessor class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class MysqlTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$caught = false;
		try {
			$m = new MysqlAccessor();
		} catch(PDOException $e) {
			$caught = true;
		}
		$this->assertFalse($caught);
	}
}
?>
