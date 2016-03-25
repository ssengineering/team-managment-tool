<?php

namespace TMT\accessor;

class Quicklinks extends MysqlAccessor {

	/**
	 * Retrieve a single object
	 *
	 * @param $guid
	 *
	 * @return \TMT\model\QuickLink
	 */
	public function get($guid) {
		$stmt = $this->pdo->prepare("SELECT * FROM quicklinks WHERE guid=:guid");
		$stmt->execute(array(
			':guid' => $guid
		));

		if($obj = $stmt->fetch(\PDO::FETCH_OBJ))
			return new \TMT\model\QuickLink($obj);

		return new \TMT\model\QuickLink();
	}

	/**
	 * Gets all instances that match these parameters: netId
	 *
	 * @param $netId
	 *
	 * @return array(\TMT\model\QuickLink)
	 */
	public function getByUser($netId) {
		$stmt = $this->pdo->prepare("SELECT * FROM quicklinks WHERE netId=:netId");
		$stmt->execute(array(
			':netId' => $netId
		));
		$objs = array();
		while($obj = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$objs[] = new \TMT\model\QuickLink($obj);
		}
		return $objs;
	}

	/**
	 * Insert a new instance into the database
	 *
	 * @param \TMT\model\QuickLink
	 *
	 * @return array(\TMT\model\QuickLink)
	 */
	public function add($obj) {
		$newGuid = $this->newGuid();
		$stmt = $this->pdo->prepare("INSERT INTO quicklinks (guid,name,netId,url) VALUES (:guid,:name,:netId,:url)");
		$stmt->execute(array(
			':guid' => $newGuid,
			':name' => $obj->name,
			':netId' => $obj->netId,
			':url' => $obj->url
		));
		return $this->get($newGuid);
	}

	/**
	 * Updates the given object
	 *
	 * @param \TMT\model\QuickLink
	 *
	 * @return array(\TMT\model\QuickLink)
	 */
	public function update($obj) {
		$stmt = $this->pdo->prepare("UPDATE quicklinks SET name=:name, url=:url WHERE guid=:guid");
		$stmt->execute(array(
			':name' => $obj->name,
			':url' => $obj->url,
			':guid' => $obj->guid
		));
		return $this->get($obj->guid);
	}

	/**
	 * Delete a row
	 *
	 * @param $guid
	 */
	public function delete($guid) {
		$stmt = $this->pdo->prepare("DELETE FROM quicklinks WHERE guid=:guid");
		$stmt->execute(array(
			':guid' => $guid
		));
	}
}