<?php

namespace TMT\accessor;

/**
 * Unit tests for the Right accessor class
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RightTest extends \PHPUnit_Framework_TestCase {
	
	public function testEmptyGet()
	{
		$rightAcc = new Right();
		$right = $rightAcc->get(-1);
		$this->assertEquals(null, $right->ID);
		$this->assertEquals(null, $right->rightName);
		$this->assertEquals(null, $right->description);
		$this->assertEquals(null, $right->rightType);
		$this->assertEquals(null, $right->rightLevel);
		$this->assertEquals(null, $right->area);
		$this->assertEquals(null, $right->guid);
	}

	public function testCrud()
	{
		$rightAcc = new Right();
		$right = new \TMT\model\Right();
		$right->ID = 1;
		$right->rightName = "Test";
		$right->description = "Description";
		$right->rightType = "BASIC";
		$right->rightLevel = 1;
		$right->area = 1;
		$right->guid = null;
		$inserted = $rightAcc->insert($right);
		$inserted->guid = null;
		$this->assertEquals($right, $inserted);	
		$right->rightName = "Updated";
		$updated = $rightAcc->update($right);
		$updated->guid = null;
		$this->assertEquals($right, $updated);	
		$deleted = $rightAcc->delete($right);
		$this->assertEquals(null,  $deleted->ID);
		$this->assertEquals(null,  $deleted->rightName);
		$this->assertEquals(null,  $deleted->description);
		$this->assertEquals(null,  $deleted->rightType);
		$this->assertEquals(null,  $deleted->rightLevel);
		$this->assertEquals(null,  $deleted->area);
		$this->assertEquals(null,  $deleted->guid);
	}

}
?>
