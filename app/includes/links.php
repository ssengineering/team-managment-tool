<?php

function linkHasChild($index) {
    global $db;
    try {
        $query = $db->prepare("SELECT COUNT(`index`) FROM `link` LEFT JOIN `app` ON `link`.`appId` = `app`.`appId` WHERE parent=:index AND `visible`=1");
        $query->execute(array(':index' => $index));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $rows = $query->fetch(PDO::FETCH_NUM);
    if ($rows[0] == '0') {return false;}
    return true;
}

function linkHasParent($index) {
    global $db;
    try {
        $query = $db->prepare("SELECT parent FROM `link` WHERE `index`=:index AND `visible`=1");
        $query->execute(array(':index' => $index));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $result = $query->fetch(PDO::FETCH_ASSOC);
    if ($result['parent'] == "") {return false;}
    return true;
}

function linkPullChildren($index) {
    global $db;
    try {
        $query = $db->prepare("SELECT * FROM `link` LEFT JOIN `app` ON `link`.`appId` = `app`.`appId` WHERE parent=:index AND `visible`=1 ORDER BY sortOrder ASC");
        $query->execute(array(':index' => $index));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $children = array();
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $children[] = $row;
    }
    return $children;
}

function linkPullChildrenIndex($index) {
    global $db;
    try {
        $query = $db->prepare("SELECT `index` FROM `link` WHERE parent=:index AND `visible`=1 ORDER BY sortOrder ASC");
        $query->execute(array(':index' => $index));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $children = array();
    while ($row = $query->fetch()) {
        $children[] = $row->index;
    }
    return $children;
}

function linkPullTopLinksCurrentArea() {
    global $db, $area;
    try {
        $query = $db->prepare("SELECT * FROM `link` LEFT JOIN `app` ON `link`.`appId`=`app`.`appId` WHERE area=:area AND parent IS NULL AND `visible`=1 ORDER BY sortOrder ASC");
        $query->execute(array(':area' => $area));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $links = array();
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $links[] = $row;
    }
    return $links;
}

function linkPullTopLinksIndexCurrentArea() {
    global $area, $db;
    try {
        $query = $db->prepare("SELECT * FROM `link` LEFT JOIN `app` ON `link`.`appId`=`app`.`appId` WHERE area=:area AND parent IS NULL AND `visible`=1 ORDER BY sortOrder ASC");
        $query->execute(array(':area' => $area));
    } catch (PDOException $e) {
        exit("error in query");
    }

    $links = array();
    while ($row = $query->fetch()) {
        $links[] = $row->index;
    }
    return $links;
}

function linkPullInfo($index) {
    global $db;
    try {
        $query = $db->prepare("SELECT * FROM `link` LEFT JOIN `app` ON `link`.`appId`=`app`.`appId` WHERE `index`=:index AND `visible`=1");
        $query->execute(array(':index' => $index));
    } catch (PDOException $e) {
        exit("error in query");
    }
    return $query->fetch(PDO::FETCH_ASSOC);
}

function isInternal($index) {
    $link = linkPullInfo($index);
    if ($link['internal'] == 1) {
        return true;
    }
    return false;
}

function isNewTab($index) {
    $link = linkPullInfo($index);
    return ($link['newTab'] == '1' ? true : false);
}

function linkReturnFullUrl($index) {
    $link = linkPullInfo($index);
    if ($link['internal'] == 1) {
        if ($link['filePath'] == "") {return "";}
        return "HTTPS://" . $_SERVER['SERVER_NAME'] . "/" . $link['filePath'];
    }
    return $link['filePath'];
}

function linkReturnName($index) {
    $link = linkPullInfo($index);
    return $link['name'];
}

function getAllUserPermissions() {
    global $db, $areaGuid, $netID;

    // Set url for accessing permission microservice
    $url = getEnv('PERMISSIONS_URL');

    // Retrieve permissions
    $result = sendAuthenticatedRequest("GET", $url . "/permission/user/" . $netID . "/" . $areaGuid);

    // Verify that result was returned
    if ($result == null || $result["status"] == "ERROR") {
        return null;
    }

    return $result["data"];
}

function checkLinkPermission(&$perms, $verb, $resource, $admin, $su) {
    // Check if admin permission is required and return true if the user is admin or su
    if ($resource == "admin") {
        if ($admin || $su) {
            return true;
        } else {
            return false;
        }

    }

    // return true if the link doesn't require admin rights but user is admin or su
    if ($admin || $su) {
        return true;
    }

    foreach ($perms as $permission) {
        if ($permission["Resource"] == $resource && $permission["Verb"] == $verb) {
            return true;
        }
    }
    return false;
}

function linkIsVisible($index, &$permissions, $admin, $su) {
    if (linkHasChild($index)) {
        $children = linkPullChildrenIndex($index);
        foreach ($children as $child) {
            if (linkHasChild($child)) {
                $grandchildren = linkPullChildrenIndex($child);
                foreach ($grandchildren as $grandchild) {
                    $link = linkPullInfo($grandchild);
                    if ($link["resource"] == NULL) {
                        return true;
                    }

                    if ($link["resource"] != NULL && checkLinkPermission($permissions, $link["verb"], $link["resource"], $admin, $su)) {
                        return true;
                    }

                }
            } else {
                $link = linkPullInfo($child);
                if ($link["resource"] == NULL) {
                    return true;
                }

                if ($link["resource"] != NULL && checkLinkPermission($permissions, $link["verb"], $link["resource"], $admin, $su)) {
                    return true;
                }

            }
        }
    } else {
        $link = linkPullInfo($index);
        if ($link["resource"] == NULL) {
            return true;
        }

        if ($link["resource"] != NULL && checkLinkPermission($permissions, $link["verb"], $link["resource"], $admin, $su)) {
            return true;
        }

    }
    return false;
}

function pullLinks() {
    global $area;
    $permissions = getAllUserPermissions();
    $admin = isAdmin();
    $su = isSuperuser();

    $content = "<ul>";
    $topLevel = linkPullTopLinksIndexCurrentArea();
    foreach ($topLevel as $curTopLevel) {
        if (!linkIsVisible($curTopLevel, $permissions, $admin, $su)) {
            continue;
        }

        $content .= "<li><a ";
        if (isNewTab($curTopLevel)) {
            $content .= "target='_blank' ";
        }

        $content .= "href=\"" . linkReturnFullUrl($curTopLevel) . "\">" . linkReturnName($curTopLevel) . "</a>";

        if (linkHasChild($curTopLevel)) {
            $content .= "<div class='sub'><div class='links'>";
            $secondLevel = linkPullChildrenIndex($curTopLevel);
            foreach ($secondLevel as $curSecondLevel) {
                if (!linkIsVisible($curSecondLevel, $permissions, $admin, $su)) {
                    continue;
                }

                $content .= "<a ";
                if (isNewTab($curSecondLevel)) {
                    $content .= "target='_blank' ";
                }

                $content .= "href=\"" . linkReturnFullUrl($curSecondLevel) . "\">" . linkReturnName($curSecondLevel) . "</a>";

                if (linkHasChild($curSecondLevel)) {
                    $content .= "<div class='sublinks'>";
                    $thirdLevel = linkPullChildrenIndex($curSecondLevel);
                    foreach ($thirdLevel as $curThirdLevel) {
                        if (!linkIsVisible($curThirdLevel, $permissions, $admin, $su)) {
                            continue;
                        }

                        $content .= "<a ";
                        if (isNewTab($curThirdLevel)) {
                            $content .= "target='_blank' ";
                        }

                        $content .= "href=\"" . linkReturnFullUrl($curThirdLevel) . "\">" . linkReturnName($curThirdLevel) . "</a>";
                    }
                    $content .= "</div>";
                }
            }
            $content .= "</div></div>";
        }
        $content .= "</li>";
    }
    $content .= "</ul>";
    echo $content;
}

function pullSubMenus() {
    global $area, $env, $netID, $db;

    echo "<ul>";

    //quicklinks
    if ($area != null) {
        echo "<li><a href=\"#\">Quick links</a>";
        echo "<div class=\"sub links\">";
		try {
			$stmt = $db->prepare("SELECT * FROM quicklinks WHERE netId=:netId");
			$stmt->execute(array(":netId" => $netID));
		} catch(\PDOException $e) {}
		while($quicklink = $stmt->fetch()) {
        	echo "<a target='_blank' href='".$quicklink->url."'>".$quicklink->name."</a>";
		}
        echo "<a href='/quicklinks/index'><strong>Edit Quick links</strong></a>";
        echo "</div>";
        echo "</li>";
    }

    // area
    $areas = getAreas();
    if (count($areas) > 1) {
        echo "<li><a href=\"#\">Area</a>";
        echo "<div class=\"sub links\">";

        for ($i = 0; $i < count($areas); $i++) {
            echo "<a href=\"\" onclick=\"document.cookie='area=" . $areas[$i] . "; path=/';\">";
            if ($area == $areas[$i]) {
                echo "* ";
            }

            echo getAreaNameById($areas[$i]) . "</a>";
        }

        echo "</div>";
        echo "</li>";
    }

    // environment
    if (isSuperuser()) {
        echo "<li><a href=\"#\">Environment</a>";
        echo "<div class=\"sub links\">";

        echo "<a href=\"\" onclick=\"document.cookie='environment=0; path=/';\">";
        if ($env == 0) {
            echo "* ";
        }

        echo "Development</a>";
        echo "<a href=\"\" onclick=\"document.cookie='environment=1; path=/';\">";
        if ($env == 1) {
            echo "* ";
        }

        echo "Stage</a>";
        echo "<a href=\"\" onclick=\"document.cookie='environment=2; path=/';\">";
        if ($env == 2) {
            echo "* ";
        }

        echo "Production</a>";

        echo "</div>";
        echo "</li>";
        echo "<li>
				<a href=\"#\">Development Tools</a>
				<div class=\"sub links\">
					<a href=\"/resources/index\">Resources</a>
					<a href=\"/notifications/types\">Notification Types</a>
					<a href=\"/tools/addLink\">Add link</a>
					<a href=\"/tools/editLink\">Edit link</a>
					<a href=\"/areaCreator\">Area Creator</a>
					<a href=\"/areaAdmin\">Area Admin</a>
					<a href=\"/areaCreator/apps.php\">App Editor</a>
					<a href=\"/areaCreator/permissions.php\">Old Permission Editor</a>
					<a href=\"/areaCreator/appPermissions.php\">Old App Permission Editor</a>
					<a href=\"/heimdall\">Heimdall</a>
				</div>
			</li>";
    }

    if (count($areas) > 1) {
        // Notifications
        echo "<li><a id='notificationsDropdownHeader' href='#'>Notifications</a>";
        echo "<div id='notificationsDropdown' class='sub links'>";
        echo "</div>";
        echo "</li>";
    }

    echo "</ul>";
}

?>
