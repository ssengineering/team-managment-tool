<?php


class LockTest extends PHPUnit_Framework_TestCase 
{

	private function callAPI($url, $data=array()) 
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, sprintf('%s?%s', $url, http_build_query($data)));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = json_decode(curl_exec($curl));
		curl_close($curl);
		
		return $result;
	}

	public function testMalformedRequest()
	{
		$expected = (object) array(
			'error' => 'Malformed request',
			'action' => 'none'
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php'));
	}
	
	public function testNoEmployee() 
	{
		$expected = (object) array(
			'error' => 'Malformed request',
			'action' => 'none'
		);
		$data = array(
			'period'   => 'something',
			'lock'     => 0,
			'lockedBy' => 'netId1'
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php', $data));
	}
	
	public function testNoPeriod()
	{
		$expected = (object) array(
			'error' => 'Malformed request',
			'action' => 'none'
		);
		$data = array(
			'employee' => 'netId1',
			'lock'     => 0,
			'lockedBy' => 'netId1'
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php', $data));
	}
	
	public function testNoLock()
	{
		$expected = (object) array(
			'error' => 'Malformed request',
			'action' => 'none'
		);
		$data = array(
			'employee' => 'netId1',
			'period'   => 1,
			'lockedBy' => 'netId1'
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php', $data));
	}
	
	public function testNoLockedBy()
	{
		$expected = (object) array(
			'error' => 'Malformed request',
			'action' => 'none'
		);
		$data = array(
			'employee' => 'netId1',
			'period'   => 1,
			'lock'     => 0
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php', $data));
	}
	
	public function testCheckUnlocked()
	{
		$expected = (object) array(
			'status'   => 'unlocked',
			'lockedBy' => '',
			'ttl'      => '',
			'action'   => 'none'
		);
		$data = array(
			'lockedBy' => 'netId1',
			'employee' => 'netId1',
			'period'   => 2,
			'lock'     => 0
		);
		$this->assertEquals($expected, $this->callAPI('https://tmt/newSchedule/lock.php', $data));
	}
	
	public function testLock()
	{
		$expected = (object) array(
			'status'   => 'locked',
			'lockedBy' => 'netId1',
			'ttl'      => '600',
			'action'   => 'locked'
		);
		$expected_check = (object) array(
			'status'   => 'locked',
			'lockedBy' => 'netId1',
			'ttl'      => '600',
			'action'   => 'none'
		);
		$data = array(
			'lockedBy' => 'netId1',
			'employee' => 'netId1',
			'period'   => 1,
			'lock'     => 1
		);
		$data_check = array(
			'lockedBy' => 'netId2',
			'employee' => 'netId1',
			'period'   => 1,
			'lock'     => 0
		);
		$lock_results = $this->callAPI('https://tmt/newSchedule/lock.php', $data);
		$check_results = $this->callAPI('https://tmt/newSchedule/lock.php', $data_check);
		
		// Check each property separately since ttl could be different (prevents data racing)
		$this->assertEquals($expected->lockedBy, $lock_results->lockedBy);
		$this->assertEquals($expected->status, $lock_results->status);
		$this->assertEquals($expected->action, $lock_results->action);

		$this->assertEquals($expected_check->lockedBy, $check_results->lockedBy);
		$this->assertEquals($expected_check->status, $check_results->status);
		$this->assertEquals($expected_check->action, $check_results->action);
	}
	
	/**
	 * @depends testLock
	 */
	public function testUnlock()
	{
		$expected_unlock = (object) array(
			'status'   => 'unlocked',
			'lockedBy' => '',
			'ttl'      => '',
			'action'   => 'unlocked'
		);
		$expected_check = (object) array(
			'status'   => 'unlocked',
			'lockedBy' => '',
			'ttl'      => '',
			'action'   => 'none'
		);
		$data_unlock = array(
			'lockedBy' => 'netId1',
			'employee' => 'netId1',
			'period'   => 1,
			'lock'     => 0
		);
		$data_check = array(
			'lockedBy' => 'netId2',
			'employee' => 'netId1',
			'period'   => 1,
			'lock'     => 0
		);
		$unlock_results = $this->callAPI('https://tmt/newSchedule/lock.php', $data_unlock);
		$check_results = $this->callAPI('https://tmt/newSchedule/lock.php', $data_check);
		
		// Check each property separately since ttl could be different (prevents data racing)
		$this->assertEquals($expected_unlock->lockedBy, $unlock_results->lockedBy);
		$this->assertEquals($expected_unlock->status, $unlock_results->status);
		$this->assertEquals($expected_unlock->action, $unlock_results->action);

		$this->assertEquals($expected_check->lockedBy, $check_results->lockedBy);
		$this->assertEquals($expected_check->status, $check_results->status);
		$this->assertEquals($expected_check->action, $check_results->action);
	}
}
