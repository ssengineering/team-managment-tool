<?php
/**
 * agent_logins.php
 * Contains a class with methods for accessing the agent_logins table in the database
 * @package oasis
 */

require('../includes/dbconnect.php');
require_once('../includes/guid.php');

class agent_logins
{
  function add_parent($text)
  {
	global $db;
	try {
		$insertQuery = $db->prepare("INSERT INTO agentLogins (value, guid) VALUES (:text, :guid)");
		$insertQuery->execute(array(':text' => $text, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
  }

  function delete_item($id)
  {
	global $db;
	try {
		$idQuery = $db->prepare("SELECT itemId FROM agentLogins WHERE parent = :id");
		$idQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
    while($row = $idQuery->fetch(PDO::FETCH_ASSOC)) {
		agent_logins::delete_item($row['itemId']);
	}
	try {
		$deleteQuery = $db->prepare("DELETE FROM agentLogins WHERE itemId = :id");
		$deleteQuery->execute(array(':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
  }

  function add_child($parent, $label, $text)
  {
	global $db;
	try {
		$insertQuery = $db->prepare("INSERT INTO agentLogins (parent, label, value, guid) VALUES (:parent, :label, :text, :guid)");
		$insertQuery->execute(array(':parent' => $parent, ':label' => $label, ':text' => $text, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
  }

  function edit_item($id, $label, $text)
  {
	global $db;
	try {
		$updateQuery = $db->prepare("UPDATE agentLogins SET label = :label, value = :text WHERE itemId = :id");
		$updateQuery->execute(array(':label' => $label, ':text' => $text, ':id' => $id));
	} catch(PDOException $e) {
		exit("error in query");
	}
  }

function get_list($parent = 0)
{
	global $db;
	try {
		$loginsQuery = $db->prepare("SELECT * FROM agentLogins WHERE parent = :parent ORDER BY itemId");
		$loginsQuery->execute(array(':parent' => $parent));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$table = $loginsQuery->fetchAll(PDO::FETCH_ASSOC);
   
	foreach($table as $key => $row)
	{
		$table[$key]['children'] = agent_logins::get_list($row['itemId']); 
	} 
	return  $table;
}
}
?>
