<?php

namespace TMT\accessor;

class Links extends MysqlAccessor {

	/**
	 * Used to pull the entire link structure for an area
	 *
	 * @param $area int The area's id
	 *
	 * @return array(\TMT\model\Link)
	 */
	public function getTree($area) {
		$tree = array();

		// Get parent (top level) links
		$stmt = $this->pdo->prepare("SELECT link.index, link.name, app.resource, app.verb, link.newTab, app.filePath, app.internal
			FROM `link` LEFT JOIN `app` ON `link`.`appId`=`app`.`appId`
			WHERE area=:area AND parent IS NULL AND `visible`=1 ORDER BY sortOrder ASC");
		$stmt->execute(array(':area' => $area));

		while($parent = $stmt->fetch()) {
			// Abstract filePath and internal attributes into url
			if($parent->filePath == null) {
				unset($parent->filePath);
				unset($parent->internal);
				$parent->url = null;
			} else {
				$url = $parent->filePath;
				if($parent->internal)
					$url = "/".$url;
				$parent->url = $url;
				unset($parent->filePath);
				unset($parent->internal);
			}
			$parent->children = array(); // Add children attribute

			// Pull all children
			$stmt2 = $this->pdo->prepare("SELECT link.index, link.name, app.resource, app.verb, link.newTab, app.filePath, app.internal
				FROM `link` LEFT JOIN `app` ON `link`.`appId` = `app`.`appId`
				WHERE parent=:index AND `visible`=1 ORDER BY sortOrder ASC");
			$stmt2->execute(array(':index' => $parent->index));

			while($child = $stmt2->fetch()) {
				// Abstract filePath and internal attributes into url
				if($child->filePath == null) {
					unset($child->filePath);
					unset($child->internal);
					$child->url = null;
				} else {
					$url = $child->filePath;
					if($child->internal)
						$url = "/".$url;
					$child->url = $url;
					unset($child->filePath);
					unset($child->internal);
				}
				$child->children = array(); // Add children attribute

				// Pull all children
				$stmt3 = $this->pdo->prepare("SELECT link.index, link.name, app.resource, app.verb, link.newTab, app.filePath, app.internal
					FROM `link` LEFT JOIN `app` ON `link`.`appId` = `app`.`appId`
					WHERE parent=:index AND `visible`=1 ORDER BY sortOrder ASC");
				$stmt3->execute(array(':index' => $child->index));

				while($grandchild = $stmt3->fetch()) {
					// Abstract filePath and internal attributes into url
					if($grandchild->filePath == null) {
						unset($grandchild->filePath);
						unset($grandchild->internal);
						$grandchild->url = null;
					} else {
						$url = $grandchild->filePath;
						if($grandchild->internal)
							$url = "/".$url;
						$grandchild->url = $url;
						unset($grandchild->filePath);
						unset($grandchild->internal);
					}
					unset($grandchild->index); // get rid of index since we don't need it
					$grandchild->children = array();
					$child->children[] = new \TMT\model\Link($grandchild);
				}
				unset($child->index); // get rid of index since we don't need it
				$parent->children[] = new \TMT\model\Link($child);
			}
			unset($parent->index); // get rid of index since we don't need it
			$tree[] = new \TMT\model\Link($parent);
		}

		return $tree;
	}
}
?>
