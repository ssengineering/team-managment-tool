<?php
require(dirname(__DIR__).'/includes/includeMeBlank.php');

// Temporary fix for running the TMT in a docker container
$redis_host = 'localhost';
$redis_port = 6379;
if (getenv('ENVIRONMENT') != 'MANHATTAN'){
	$redis_host = getenv('REDIS_HOST');

	// if REDIS_PORT set, then set to REDIS_PORT, else set to 6397
	$redis_port = getenv('REDIS_HOST_PORT') ? getenv('REDIS_HOST_PORT') : 6379;
}


// If the request does not contain all the needed pieces.
if (!isset($_GET['employee']) || !isset($_GET['period']) || !isset($_GET['lock']) || !isset($_GET['lockedBy']) ) {
	$return = array();
	$return['error']  = 'Malformed request';
	$return['action'] = 'none';

	echo json_encode($return);
	exit();
}

$employee = trim($_GET['employee']);

$period = trim($_GET['period']);

$lock = (int) trim($_GET['lock']);

$lockedBy = trim($_GET['lockedBy']);

$redis = new Redis();

// Try to connect to the redis host, if unreachable, error out and exit
try {
	$redis->connect($redis_host, $redis_port);
} catch (RedisException $e) {
	$return = array();
	$return['error']  = 'Could not connect to the redis host';
	$return['action'] = 'none';
	echo json_encode($return);
	exit();
}

// Set the name of the key for the lock
$keyName = "schedule:$employee:$period";

// Set the ttl on the lock
$lockDuration = 600;

// Check to see if a lock is in place
// NOTE: $curLockedBy = false if the key does not exist
$curLockedBy = $redis->hGet($keyName, 'lockedBy');

// Construct the return object
$return = array(
	'status'   => '',
	'action'   => '',
	'lockedBy' => '',
	'ttl'      => ''
);

// If a lock was requested
if ($lock) {

	// If lock is already held, or not held
	if ($curLockedBy == $lockedBy || !$curLockedBy) {

		// Set the lock and set the expire timout
		$redis->hSet($keyName, 'lockedBy', $lockedBy);
		$redis->expire($keyName, $lockDuration);

		$return['status'] = 'locked';
		// If the lock is renewed, then set to renewed, otherwise set to locked
		$return['action'] = ($curLockedBy == $lockedBy) ? 'renewed' : 'locked';
		$return['lockedBy'] = $lockedBy;
		$return['ttl'] = 600;

	} else { // If the lock is held by another user

		$return['status']   = 'locked';
		$return['action']   = 'none';
		$return['lockedBy'] = $curLockedBy;
		$return['ttl']      = $redis->ttl($keyName);

	}

} else { // If an unlock was requested

	// If the lock is held by the user requesting the unlock
	if ($curLockedBy == $lockedBy) {
		$redis->del($keyName);

		$return['status'] = 'unlocked';
		$return['action'] = 'unlocked';

	} else if (!$curLockedBy) { // If there is no lock

		$return['status'] = 'unlocked';
		$return['action'] = 'none';

	} else { // If the lock is held by someone else

		$return['status']   = 'locked';
		$return['action']   = 'none';
		$return['lockedBy'] = $curLockedBy;
		$return['ttl']      = $redis->ttl($keyName);

	}
}

// Return the response
echo json_encode($return);




?>
