SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sites_ops_test`
--
CREATE DATABASE IF NOT EXISTS `sites_ops_test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `sites_ops_test`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `createDashTaskList`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `createDashTaskList`(IN $date DATE, IN $day VARCHAR(5), IN $start TIME, IN $end TIME, IN $area INT)
BEGIN
DROP TEMPORARY TABLE IF EXISTS todaysTasks;
DROP TEMPORARY TABLE IF EXISTS completedToday;

CREATE TEMPORARY TABLE completedToday AS
	SELECT routineTaskLog.* FROM routineTaskLog WHERE routineTaskLog.dateDue = $date; 

SET @p_query = CONCAT("CREATE TEMPORARY TABLE todaysTasks AS
	SELECT routineTasks.*, taskId,completed,completedBy,timeCompleted,dateCompleted,comments,muted,mutedBy,timeMuted,dateMuted,mutedComments 
FROM routineTasks
LEFT JOIN completedToday ON routineTasks.ID = completedToday.taskID
WHERE ((routineTasks.day ='", $date,"' OR routineTasks.",$day," = '1') OR completedToday.dateDue = '",$date,"') AND routineTasks.enabled = '1' AND routineTasks.area = '",$area,"'" );
PREPARE r_query FROM @p_query;
EXECUTE r_query;
SELECT * FROM todaysTasks WHERE timeDue BETWEEN $start AND $end ORDER BY timeDue ASC;

END$$

DROP PROCEDURE IF EXISTS `createEmployee`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `createEmployee`(IN $netID VARCHAR(255), IN $active INT, IN $area INT, IN $firstName VARCHAR(255), IN $lastName VARCHAR(255), IN $phone VARCHAR(255), IN $email VARCHAR(255), IN $chqID VARCHAR(255), IN $birthday VARCHAR(255), IN $languages VARCHAR(255), IN $hometown VARCHAR(255), IN $major VARCHAR(255), IN $mission VARCHAR(255), IN $gradDate VARCHAR(255), IN $position VARCHAR(255), IN $shift VARCHAR(255), IN $supervisor VARCHAR(255), IN $hireDate VARCHAR(255))
BEGIN

    INSERT INTO employee VALUES ($netID, $active, $area, $firstName, $lastName, $phone, $email, $chqID, $birthday, $languages, $hometown, $major, $mission, $gradDate, $position, $shift, $supervisor, $hireDate);
    INSERT INTO permissions (netID) VALUES ($netID);
    
END$$

DROP PROCEDURE IF EXISTS `createTaskList`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `createTaskList`(IN `$date` DATE, IN `$day` VARCHAR(5), IN `$area` INT)
BEGIN
DROP TEMPORARY TABLE IF EXISTS todaysTasks; 
DROP TEMPORARY TABLE IF EXISTS completedToday; 

CREATE TEMPORARY TABLE completedToday AS 
   SELECT routineTaskLog.* FROM routineTaskLog WHERE routineTaskLog.dateDue = $date AND routineTaskLog.area = $area; 

SET @dayOfMonth = DAYOFMONTH($date);


IF DAYOFMONTH($date)=DAYOFMONTH(LAST_DAY($date)) THEN 
SET @p_query = CONCAT("CREATE TEMPORARY TABLE todaysTasks AS
   SELECT routineTasks.*, taskId,completed,completedBy,timeCompleted,dateCompleted,comments,muted,mutedBy,timeMuted,dateMuted,mutedComments
FROM routineTasks
LEFT JOIN completedToday ON routineTasks.ID = completedToday.taskID
WHERE (routineTasks.day ='", $date,"' OR routineTasks.",$day," = '1' OR completedToday.dateDue = '",$date,"' OR `routineTasks`.`dayOfMonth` >= ",@dayOfMonth,") AND routineTasks.enabled = '1' AND routineTasks.area = '",$area,"'" ); 
ELSE
SET @p_query = CONCAT("CREATE TEMPORARY TABLE todaysTasks AS
   SELECT routineTasks.*, taskId,completed,completedBy,timeCompleted,dateCompleted,comments,muted,mutedBy,timeMuted,dateMuted,mutedComments
FROM routineTasks
LEFT JOIN completedToday ON routineTasks.ID = completedToday.taskID
WHERE (routineTasks.day ='", $date,"' OR routineTasks.",$day," = '1' OR completedToday.dateDue = '",$date,"' OR `routineTasks`.`dayOfMonth` = ",@dayOfMonth,") AND routineTasks.enabled = '1' AND routineTasks.area = '",$area,"'" ); 
END IF;

PREPARE r_query FROM @p_query; 
EXECUTE r_query;
SELECT * FROM todaysTasks ORDER BY timeDue ASC; 
DROP TEMPORARY TABLE IF EXISTS todaysTasks; 
DROP TEMPORARY TABLE IF EXISTS completedToday; 
END$$

DROP PROCEDURE IF EXISTS `crmSearch`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `crmSearch`(IN `lastName` VARCHAR(40), IN `firstName` VARCHAR(40), IN `departmentName` VARCHAR(75))
BEGIN
IF lastName != '' & firstName != '' THEN
    SELECT * FROM `crmClient` WHERE fName LIKE CONCAT('%',firstName, '%') AND lName LIKE CONCAT('%',lastName, '%');
ELSEIF lastName != '' THEN 
    SELECT * FROM `crmClient` WHERE lName LIKE CONCAT('%',lastName, '%') OR netID LIKE CONCAT('%',lastName, '%');
ELSEIF firstName != '' THEN
    SELECT * FROM `crmClient` WHERE fName LIKE CONCAT('%',firstName, '%');
ELSEIF departmentName != '' THEN
    SELECT * FROM `crmClient` WHERE departName LIKE CONCAT('%',departmentName, '%');
END IF;
END$$

DROP PROCEDURE IF EXISTS `getLinksByArea`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `getLinksByArea`(IN $area INT)
BEGIN
DROP TEMPORARY TABLE IF EXISTS myLinks;

CREATE TEMPORARY TABLE myLinks AS
	SELECT links.*
	FROM links
	WHERE links.area = $area;
SELECT * FROM myLinks;
END$$

DROP PROCEDURE IF EXISTS `getPermissions`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `getPermissions`(IN $netID VARCHAR(255))
BEGIN
	SELECT employee.area,employee.position,employeePermissions.*
	FROM employeePermissions
	LEFT JOIN employee ON employeePermissions.netID = employee.netID
	WHERE employeePermissions.netID = $netID;
END$$

DROP PROCEDURE IF EXISTS `getScheduleList`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `getScheduleList`(IN area_in INT, IN user_id varchar(255))
BEGIN
	SELECT employee.netID, employee.firstName, employee.lastName, employee.area FROM employee 
	WHERE employee.netID IN 
		(SELECT DISTINCT employee.netID FROM employee 
		 LEFT JOIN employeeAreaPermissions ON employeeAreaPermissions.netID=employee.netID 
		 WHERE (employeeAreaPermissions.area=area_in OR employee.area=area_in) AND employee.active=1)
	AND employee.netID NOT IN 
		(SELECT DISTINCT employeeTags.netID FROM tags 
		 INNER JOIN employeeTags ON tags.id=employeeTags.tag 
		 WHERE tags.short='not-schedulable' AND area=area_in AND employeeTags.netID!=user_id) 	 
	ORDER BY CASE WHEN employee.area=area_in THEN 0 ELSE 1 END, employee.lastName;
END$$

DROP PROCEDURE IF EXISTS `getTurnOverNotes`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `getTurnOverNotes`(IN `vSubmittedBy` VARCHAR(255), IN `vOwnedBy` VARCHAR(255), IN `vStartDate` DATETIME, IN `vEndDate` DATETIME, IN `vCleared` TINYINT(1) UNSIGNED, IN `vNoteText` TEXT, IN `vClosingComment` TEXT)
    COMMENT 'Returns turnOverNotes that meet the specifications.'
BEGIN

IF vStartDate = '0000-00-00 00:00:00' THEN
	SET vStartDate = NULL;
END IF;
IF vEndDate = '0000-00-00 00:00:00' THEN
	SET vEndDate = NULL;
END IF;
IF vCleared = 2 THEN
	SET vCleared = NULL;
END IF;

SELECT `supNotes`.*, sBy.`lastName` AS 'sLastName', sBy.`firstName` AS 'sFirstName', cBy.`lastName` AS 'cLastName', cBy.`firstName` AS 'cFirstName'
FROM `supNotes` 
LEFT JOIN `employee` AS sBy ON `submittedBy` = sBy.`netID` 
LEFT JOIN `employee` AS cBy ON `clearedBy` = cBy.`netID` 
WHERE 

COALESCE(`submittedBy`, '') LIKE CONCAT('%', vSubmittedBy, '%') AND 
COALESCE(`clearedBy`, '') LIKE CONCAT('%', vOwnedBy, '%') AND 
(
    `timeSubmitted` BETWEEN COALESCE(vStartDate, `timeSubmitted`) AND COALESCE(vEndDate, `timeSubmitted`) 
    OR 
	`timeCleared` BETWEEN COALESCE(vStartDate, `timeCleared`) AND COALESCE(vEndDate, `timeCleared`)
) AND 
`cleared` = COALESCE(vCleared, `cleared`) AND 
`note` LIKE CONCAT('%', vNoteText, '%')  AND 
`closingComment` LIKE CONCAT('%', vClosingComment, '%') 
ORDER BY `timeSubmitted` DESC, `timeCleared` DESC;

END$$

DROP PROCEDURE IF EXISTS `newCreateTaskList`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `newCreateTaskList`(IN $date DATE, IN $day VARCHAR(5))
BEGIN
DROP TEMPORARY TABLE IF EXISTS todaysTasks;
DROP TEMPORARY TABLE IF EXISTS completedToday;

CREATE TEMPORARY TABLE completedToday AS
	SELECT routineTaskLog.* FROM routineTaskLog WHERE routineTaskLog.dateDue = $date; 

SET @p_query = CONCAT("CREATE TEMPORARY TABLE todaysTasks AS
	SELECT routineTasks.*, taskId,completed,completedBy,timeCompleted,dateCompleted,comments,muted,mutedBy,timeMuted,dateMuted,mutedComments 
FROM routineTasks
LEFT JOIN completedToday ON routineTasks.ID = completedToday.taskID
WHERE ((routineTasks.day ='", $date,"' OR routineTasks.",$day," = '1') OR completedToday.dateDue = '",$date,"') AND routineTasks.enabled = '1'" );
PREPARE r_query FROM @p_query;
EXECUTE r_query;
SELECT * FROM todaysTasks ORDER BY timeDue ASC;
DROP TEMPORARY TABLE IF EXISTS todaysTasks;
DROP TEMPORARY TABLE IF EXISTS completedToday;
END$$

DROP PROCEDURE IF EXISTS `permissions`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `permissions`(IN netIDVar VARCHAR(255), IN permissionVar VARCHAR(255))
BEGIN
    DECLARE q VARCHAR(255);
    SET @p_query = CONCAT("SELECT ",permissionVar," FROM employeePermissions WHERE netID='",netIDVar,"'");
    PREPARE r_query FROM @p_query;
    EXECUTE r_query;
END$$

DROP PROCEDURE IF EXISTS `searchTags`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `searchTags`(IN `area_in` INT, IN `tag_name` VARCHAR(255))
BEGIN
  DECLARE tag_id INT;
    SET tag_id = (SELECT id FROM tags WHERE tags.short = tag_name);

  SELECT netID FROM employeeTags WHERE area=area_in AND tag=tag_id;
END$$

DROP PROCEDURE IF EXISTS `tagEmployee`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `tagEmployee`(IN `netID_in` VARCHAR(255), IN `area_in` INT, IN `tag_name` VARCHAR(255), IN `long_name` TEXT)
BEGIN
	DECLARE tag_id INT;
    SET tag_id = (SELECT id FROM tags WHERE tags.short = tag_name);
    IF(tag_id IS NULL) THEN
    	INSERT INTO tags (short, description) VALUES (tag_name, long_name);
    	SET tag_id = LAST_INSERT_ID();
    END IF;
	INSERT INTO employeeTags(netID, tag, area) VALUES (netID_in,tag_id,area_in);
END$$

DROP PROCEDURE IF EXISTS `untagEmployee`$$
CREATE DEFINER=`devteam`@`%` PROCEDURE `untagEmployee`(IN `netID_in` VARCHAR(255), IN `area_in` INT, IN `tag_name` VARCHAR(255))
BEGIN
	DECLARE tag_id INT;
   SET tag_id = (SELECT id FROM tags WHERE tags.short = tag_name);
	SELECT netID_in, tag_id, area_in;
	DELETE FROM employeeTags 
   WHERE netID=netID_in 
    	AND tag=tag_id 
      AND area=area_in;
	
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activitiesBoard`
--

DROP TABLE IF EXISTS `activitiesBoard`;
CREATE TABLE IF NOT EXISTS `activitiesBoard` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `categoryId` text NOT NULL,
  `allDay` tinyint(1) NOT NULL,
  `imageUrl` text NOT NULL,
  `moreInfoUrl` text NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `originalTitle` text NOT NULL,
  `originalDate` date NOT NULL,
  `originalStartTime` time NOT NULL,
  `originalEndTime` time NOT NULL,
  `originalDescription` text NOT NULL,
  `originalCategoryId` text NOT NULL,
  `originalLatitude` float NOT NULL,
  `originalLongitude` float NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `activitiesBoard`
--

TRUNCATE TABLE `activitiesBoard`;
-- --------------------------------------------------------

--
-- Table structure for table `agentLogins`
--

DROP TABLE IF EXISTS `agentLogins`;
CREATE TABLE IF NOT EXISTS `agentLogins` (
  `itemId` smallint(5) unsigned NOT NULL,
  `parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `label` varchar(127) DEFAULT NULL,
  `value` varchar(127) NOT NULL DEFAULT '',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `agentLogins`
--

TRUNCATE TABLE `agentLogins`;
-- --------------------------------------------------------

--
-- Table structure for table `app`
--

DROP TABLE IF EXISTS `app`;
CREATE TABLE IF NOT EXISTS `app` (
  `appId` int(11) NOT NULL COMMENT 'Primary Key',
  `appName` varchar(64) DEFAULT NULL COMMENT 'App Name',
  `description` text COMMENT 'Description of what the app is used for',
  `filePath` text NOT NULL COMMENT 'Path to the app from web-root',
  `internal` int(1) NOT NULL DEFAULT '1' COMMENT 'Flags whether this is an internal app or an external one.',
  `resource` char(36),
  `verb` varchar(50),
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='A list of all apps on the site. An app is a page that would have a link to it.';

--
-- Truncate table before insert `app`
--

TRUNCATE TABLE `app`;
--
-- Dumping data for table `app`
--

INSERT INTO `app` (`appId`, `appName`, `description`, `filePath`, `internal`, `guid`) VALUES
(1, 'app1', '', 'test', 1, '0ea9008a-44ad-4df0-a76a-f6224e6c9db9'),
(2, 'app2', '', 'url/path', 0, 'd470ae61-1514-484b-f676-95f874ee6221');

-- --------------------------------------------------------

--
-- Table structure for table `appPermission`
--

DROP TABLE IF EXISTS `appPermission`;
CREATE TABLE IF NOT EXISTS `appPermission` (
  `appPermissionId` int(11) NOT NULL,
  `appId` int(11) NOT NULL COMMENT 'FK to `app`',
  `permissionId` int(11) NOT NULL COMMENT 'FK to `permission`',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tracks which apps make use of which permissions.';

--
-- Truncate table before insert `appPermission`
--

TRUNCATE TABLE `appPermission`;
-- --------------------------------------------------------

--
-- Table structure for table `assessmentsEmployeeGroupList`
--

DROP TABLE IF EXISTS `assessmentsEmployeeGroupList`;
CREATE TABLE IF NOT EXISTS `assessmentsEmployeeGroupList` (
  `ID` int(10) unsigned NOT NULL COMMENT 'ID for table entries.',
  `employee` varchar(255) NOT NULL COMMENT 'Name of employee.',
  `group` int(10) unsigned NOT NULL COMMENT 'ID of group.',
  `startDate` date NOT NULL COMMENT 'Date employee was added to group.',
  `endDate` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date employee was removed from group.',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag for if entry is deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Keeps track of who is a part of what group.';

--
-- Truncate table before insert `assessmentsEmployeeGroupList`
--

TRUNCATE TABLE `assessmentsEmployeeGroupList`;
-- --------------------------------------------------------

--
-- Table structure for table `assessmentsGroup`
--

DROP TABLE IF EXISTS `assessmentsGroup`;
CREATE TABLE IF NOT EXISTS `assessmentsGroup` (
  `ID` int(10) unsigned NOT NULL COMMENT 'ID of group.',
  `name` varchar(255) NOT NULL COMMENT 'Name of group.',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Defines if the group has been deleted.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `assessmentsGroup`
--

TRUNCATE TABLE `assessmentsGroup`;
-- --------------------------------------------------------

--
-- Table structure for table `assessmentsGroupRequiredTests`
--

DROP TABLE IF EXISTS `assessmentsGroupRequiredTests`;
CREATE TABLE IF NOT EXISTS `assessmentsGroupRequiredTests` (
  `ID` int(10) unsigned NOT NULL COMMENT 'ID for index.',
  `group` int(11) NOT NULL COMMENT 'ID of group.',
  `test` int(10) unsigned NOT NULL COMMENT 'ID of required test.',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag if the group was deleted.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Shows what tests each group needs';

--
-- Truncate table before insert `assessmentsGroupRequiredTests`
--

TRUNCATE TABLE `assessmentsGroupRequiredTests`;
-- --------------------------------------------------------

--
-- Table structure for table `assessmentsResults`
--

DROP TABLE IF EXISTS `assessmentsResults`;
CREATE TABLE IF NOT EXISTS `assessmentsResults` (
  `ID` int(10) unsigned NOT NULL COMMENT 'ID for test results.',
  `employee` varchar(255) NOT NULL COMMENT 'NetID of test taker.',
  `grader` varchar(255) NOT NULL COMMENT 'NetID of person who submitted the test results.',
  `test` int(10) unsigned NOT NULL COMMENT 'ID of test taken.',
  `date` date NOT NULL COMMENT 'Date the test was taken.',
  `passed` tinyint(1) NOT NULL COMMENT 'Flag for if the employee passed the test.',
  `score` int(10) unsigned NOT NULL COMMENT 'Score recieved on test.',
  `attempt` tinyint(3) unsigned NOT NULL COMMENT 'Attempt number for this test.',
  `notes` text NOT NULL COMMENT 'Notes for this attempt.',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag for if this entry is deleted.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Records results for a test taken by an employee.';

--
-- Truncate table before insert `assessmentsResults`
--

TRUNCATE TABLE `assessmentsResults`;
-- --------------------------------------------------------

--
-- Table structure for table `assessmentsTest`
--

DROP TABLE IF EXISTS `assessmentsTest`;
CREATE TABLE IF NOT EXISTS `assessmentsTest` (
  `ID` int(10) unsigned NOT NULL COMMENT 'Index for table.',
  `name` varchar(255) NOT NULL COMMENT 'Name of test.',
  `timePeriod` int(10) unsigned NOT NULL COMMENT 'How many days from the start date the employee has to pass the test.',
  `points` int(10) unsigned NOT NULL COMMENT 'The total number of points for the test.',
  `passingPercentage` int(10) unsigned NOT NULL COMMENT 'The percentage, out of 100, needed to pass the test.',
  `creationDate` date NOT NULL,
  `quizFlag` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Field to mark if an entry has been deleted.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table contains all of the details for tests, certifications, quizes, etc. ';

--
-- Truncate table before insert `assessmentsTest`
--

TRUNCATE TABLE `assessmentsTest`;
-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `contactPriority` tinyint(2) NOT NULL,
  `managerFlag` tinyint(1) NOT NULL,
  `guid` char(36) CHARACTER SET latin1 NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='for dynamic contact page with search';

--
-- Truncate table before insert `contacts`
--

TRUNCATE TABLE `contacts`;
-- --------------------------------------------------------

--
-- Table structure for table `contactsHierarchy`
--

DROP TABLE IF EXISTS `contactsHierarchy`;
CREATE TABLE IF NOT EXISTS `contactsHierarchy` (
  `id` int(11) NOT NULL,
  `groupName` varchar(127) NOT NULL DEFAULT '',
  `higherUp` varchar(127) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '0',
  `guid` char(36) CHARACTER SET latin1 NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `contactsHierarchy`
--

TRUNCATE TABLE `contactsHierarchy`;
-- --------------------------------------------------------

--
-- Table structure for table `crmClient`
--

DROP TABLE IF EXISTS `crmClient`;
CREATE TABLE IF NOT EXISTS `crmClient` (
  `ID` bigint(20) unsigned NOT NULL COMMENT 'Client ID',
  `fName` varchar(40) NOT NULL COMMENT 'First Name',
  `lName` varchar(40) NOT NULL COMMENT 'Last Name',
  `netId` varchar(60) NOT NULL COMMENT 'NetID/LDSAccount of the client',
  `workphone` varchar(17) NOT NULL COMMENT 'Work Phone',
  `mobilephone` varchar(17) NOT NULL COMMENT 'Mobile Phone',
  `email` varchar(70) NOT NULL COMMENT 'Email',
  `serviceStartDate` date NOT NULL COMMENT 'Date we started to provide services for this client',
  `departName` varchar(75) NOT NULL COMMENT 'Name of Department',
  `role` varchar(36) NOT NULL COMMENT 'Client''s Role',
  `skills` varchar(255) NOT NULL COMMENT 'User-defined Tech Skills',
  `satisfaction` varchar(2) NOT NULL DEFAULT '0' COMMENT 'how content is our client with our service',
  `ola` varchar(255) NOT NULL COMMENT 'Refrence# for ourSLA with this Client',
  `contact` enum('Work Phone','Mobile Phone','Email') NOT NULL DEFAULT 'Work Phone' COMMENT 'Preferred Method of Contact for the Client',
  `friendliness` varchar(20) NOT NULL DEFAULT '0,0' COMMENT 'Cumulative rating of how friendly this client is according to the ratings given him/her by our agents',
  `knowHow` varchar(20) NOT NULL DEFAULT '0,0' COMMENT 'Our Internal Rating of the Client''s Tech. Knowledge',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `crmClient`
--

TRUNCATE TABLE `crmClient`;
-- --------------------------------------------------------

--
-- Table structure for table `crmDepartment`
--

DROP TABLE IF EXISTS `crmDepartment`;
CREATE TABLE IF NOT EXISTS `crmDepartment` (
  `departmentID` int(10) unsigned NOT NULL COMMENT 'ID to Identify the Department',
  `departName` varchar(75) NOT NULL COMMENT 'Department Name',
  `who` enum('BYU','CHQ') NOT NULL DEFAULT 'BYU' COMMENT 'If this is a BYU Dept. or a CHQ Dept.',
  `serviceStart` date NOT NULL COMMENT 'When we began to provide service for this department',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `crmDepartment`
--

TRUNCATE TABLE `crmDepartment`;
-- --------------------------------------------------------

--
-- Table structure for table `crmLog`
--

DROP TABLE IF EXISTS `crmLog`;
CREATE TABLE IF NOT EXISTS `crmLog` (
  `logID` bigint(20) unsigned NOT NULL COMMENT 'ID # for a new interaction with one of our clients',
  `ID` bigint(20) unsigned NOT NULL COMMENT 'Client ID',
  `madeBy` varchar(36) NOT NULL COMMENT 'This is the netID of the employee that made the comment in the log',
  `type` enum('Other','Complaint','Service Request') NOT NULL DEFAULT 'Other' COMMENT 'What kind of interaction with the customer is occuring',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time when interaction occured',
  `info` text NOT NULL COMMENT 'Description of our interaction with the client',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `crmLog`
--

TRUNCATE TABLE `crmLog`;
-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE IF NOT EXISTS `employee` (
  `netID` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0' COMMENT '1=Active, 0=Inactive, -1=Terminated',
  `area` int(11) NOT NULL COMMENT 'Default Area (see table areas for area codes)',
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `maidenName` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(42) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` varchar(50) NOT NULL,
  `languages` text NOT NULL,
  `hometown` varchar(255) NOT NULL DEFAULT '',
  `major` varchar(100) NOT NULL DEFAULT '',
  `missionOrStudyAbroad` varchar(100) NOT NULL DEFAULT '',
  `graduationDate` varchar(50) NOT NULL,
  `position` int(10) NOT NULL,
  `shift` varchar(100) NOT NULL DEFAULT '',
  `supervisor` varchar(100) NOT NULL,
  `hireDate` varchar(50) NOT NULL,
  `certificationLevel` varchar(50) NOT NULL,
  `international` tinyint(1) NOT NULL DEFAULT '0',
  `byuIDnumber` varchar(20) NOT NULL,
  `fullTime` tinyint(1) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employee`
--

TRUNCATE TABLE `employee`;
--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`netID`, `active`, `area`, `firstName`, `lastName`, `maidenName`, `phone`, `email`, `birthday`, `languages`, `hometown`, `major`, `missionOrStudyAbroad`, `graduationDate`, `position`, `shift`, `supervisor`, `hireDate`, `certificationLevel`, `international`, `byuIDnumber`, `fullTime`, `guid`) VALUES
('employee2', 0, 2, 'Emp', 'Loyee', 'Maiden', '9876543210', 'emp2@gmail.com', '1990-02-02', 'English', '', '', '', '', 2, '', '', '', '', 0, '', 1, '04556fcd-dc6d-4644-9dfc-6a4558dd4ace'),
('netId', 1, 1, 'First', 'Last', '', '1234567890', 'email@byu.edu', '1990-01-01', 'English, Spanish', 'Salt Lake City', 'Computer Science', 'United States', '04/15', 1, 'Morning', 'sup1', '2015-05-29', 'Level 1', 0, '123456789', 0, '9b246d87-09d9-45c6-ea58-7c0e0e8cb2fa'),
('other', 1, 2, 'Other', 'Person', '', '555-555-5555', 'other@person', '01/01', '', '', '', '', '', 1, '', '', '', '', 0, '999999999', 1, '9779662a-446e-4e09-c5ce-230f0e757f10');

-- --------------------------------------------------------

--
-- Table structure for table `employeeAreaPermissions`
--

DROP TABLE IF EXISTS `employeeAreaPermissions`;
CREATE TABLE IF NOT EXISTS `employeeAreaPermissions` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeAreaPermissions`
--

TRUNCATE TABLE `employeeAreaPermissions`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeAreas`
--

DROP TABLE IF EXISTS `employeeAreas`;
CREATE TABLE IF NOT EXISTS `employeeAreas` (
  `ID` int(11) NOT NULL,
  `area` varchar(255) NOT NULL,
  `longName` varchar(255) NOT NULL,
  `startDay` int(11) NOT NULL DEFAULT '0',
  `endDay` int(11) NOT NULL DEFAULT '6',
  `startTime` decimal(3,1) NOT NULL DEFAULT '0.0',
  `endTime` decimal(3,1) NOT NULL DEFAULT '23.0',
  `hourSize` decimal(2,1) NOT NULL DEFAULT '1.0',
  `homePage` varchar(255) NOT NULL DEFAULT 'whiteboard',
  `postSchedulesByDefault` tinyint(1) NOT NULL DEFAULT '1',
  `canEmployeesEditWeeklySchedule` tinyint(1) NOT NULL DEFAULT '1',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeAreas`
--

TRUNCATE TABLE `employeeAreas`;
--
-- Dumping data for table `employeeAreas`
--

INSERT INTO `employeeAreas` (`ID`, `area`, `longName`, `startDay`, `endDay`, `startTime`, `endTime`, `hourSize`, `homePage`, `postSchedulesByDefault`, `canEmployeesEditWeeklySchedule`, `guid`) VALUES
(1, 'TEST1', 'Test Area 1', 0, 6, 0.0, 23.0, 1.0, 'whiteboard', 1, 1, 'a6cec04d-8629-44a8-b8c0-1a61c40c64fb'),
(2, 'TEST2', 'Test Area 2', 2, 6, 0.0, 23.0, 1.0, 'whiteboard', 1, 1, '12ba0b6c-57ef-4d98-fc10-c367ad10b8c7');

-- --------------------------------------------------------

--
-- Table structure for table `employeePermissions`
--

DROP TABLE IF EXISTS `employeePermissions`;
CREATE TABLE IF NOT EXISTS `employeePermissions` (
  `index` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `permission` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeePermissions`
--

TRUNCATE TABLE `employeePermissions`;
-- --------------------------------------------------------

--
-- Table structure for table `employeePositionHistory`
--

DROP TABLE IF EXISTS `employeePositionHistory`;
CREATE TABLE IF NOT EXISTS `employeePositionHistory` (
  `id` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `positionName` varchar(255) NOT NULL,
  `startDate` varchar(255) NOT NULL,
  `endDate` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeePositionHistory`
--

TRUNCATE TABLE `employeePositionHistory`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRaiseLog`
--

DROP TABLE IF EXISTS `employeeRaiseLog`;
CREATE TABLE IF NOT EXISTS `employeeRaiseLog` (
  `index` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `raise` decimal(10,2) NOT NULL,
  `newWage` decimal(10,2) NOT NULL,
  `submitter` varchar(255) NOT NULL COMMENT 'Submitter''s Net ID',
  `date` varchar(255) NOT NULL,
  `comments` longtext NOT NULL,
  `isSubmitted` int(11) NOT NULL DEFAULT '0',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeRaiseLog`
--

TRUNCATE TABLE `employeeRaiseLog`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRaiseReasons`
--

DROP TABLE IF EXISTS `employeeRaiseReasons`;
CREATE TABLE IF NOT EXISTS `employeeRaiseReasons` (
  `index` int(11) NOT NULL,
  `reason` text NOT NULL,
  `area` int(11) NOT NULL,
  `raise` decimal(4,2) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeRaiseReasons`
--

TRUNCATE TABLE `employeeRaiseReasons`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRights`
--

DROP TABLE IF EXISTS `employeeRights`;
CREATE TABLE IF NOT EXISTS `employeeRights` (
  `ID` int(11) NOT NULL,
  `rightName` varchar(255) NOT NULL,
  `description` varchar(511) NOT NULL,
  `rightType` varchar(255) NOT NULL,
  `rightLevel` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) CHARACTER SET latin1 NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `employeeRights`
--

TRUNCATE TABLE `employeeRights`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRightsEmails`
--

DROP TABLE IF EXISTS `employeeRightsEmails`;
CREATE TABLE IF NOT EXISTS `employeeRightsEmails` (
  `ID` int(11) NOT NULL,
  `rightID` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `add_title` mediumtext NOT NULL,
  `add_body` longtext NOT NULL,
  `del_title` mediumtext NOT NULL,
  `del_body` longtext NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeRightsEmails`
--

TRUNCATE TABLE `employeeRightsEmails`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRightsLevels`
--

DROP TABLE IF EXISTS `employeeRightsLevels`;
CREATE TABLE IF NOT EXISTS `employeeRightsLevels` (
  `name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeRightsLevels`
--

TRUNCATE TABLE `employeeRightsLevels`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeRightsStatus`
--

DROP TABLE IF EXISTS `employeeRightsStatus`;
CREATE TABLE IF NOT EXISTS `employeeRightsStatus` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `rightID` int(11) NOT NULL,
  `rightStatus` int(11) NOT NULL,
  `requestedBy` varchar(255) DEFAULT NULL,
  `requestedDate` date DEFAULT NULL,
  `updatedBy` varchar(255) DEFAULT NULL,
  `updatedDate` date DEFAULT NULL,
  `removedBy` varchar(255) DEFAULT NULL,
  `removedDate` date DEFAULT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeRightsStatus`
--

TRUNCATE TABLE `employeeRightsStatus`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeTags`
--

DROP TABLE IF EXISTS `employeeTags`;
CREATE TABLE IF NOT EXISTS `employeeTags` (
  `netID` varchar(255) NOT NULL,
  `tag` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeTags`
--

TRUNCATE TABLE `employeeTags`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeTerminationDetails`
--

DROP TABLE IF EXISTS `employeeTerminationDetails`;
CREATE TABLE IF NOT EXISTS `employeeTerminationDetails` (
  `terminationId` int(10) NOT NULL,
  `terminationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reasons` longtext NOT NULL,
  `attendance` varchar(255) NOT NULL,
  `attitude` varchar(255) NOT NULL,
  `performance` varchar(255) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `submitter` varchar(255) DEFAULT NULL,
  `area` int(11) NOT NULL,
  `rehirable` varchar(50) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeTerminationDetails`
--

TRUNCATE TABLE `employeeTerminationDetails`;
-- --------------------------------------------------------

--
-- Table structure for table `employeeWages`
--

DROP TABLE IF EXISTS `employeeWages`;
CREATE TABLE IF NOT EXISTS `employeeWages` (
  `netID` varchar(255) NOT NULL,
  `wage` decimal(10,2) NOT NULL COMMENT 'Current Wage',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `employeeWages`
--

TRUNCATE TABLE `employeeWages`;
-- --------------------------------------------------------

--
-- Table structure for table `empPreferences`
--

DROP TABLE IF EXISTS `empPreferences`;
CREATE TABLE IF NOT EXISTS `empPreferences` (
  `preferenceId` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `preferencesObject` text NOT NULL COMMENT 'JSON object, basic structure is {''all'': {}, 1: {}, 2: {}, 3: {}} where the int is an area id.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `empPreferences`
--

TRUNCATE TABLE `empPreferences`;
-- --------------------------------------------------------

--
-- Table structure for table `executiveNotification`
--

DROP TABLE IF EXISTS `executiveNotification`;
CREATE TABLE IF NOT EXISTS `executiveNotification` (
  `ID` bigint(8) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `ticketNum` varchar(50) NOT NULL,
  `incidentCoord` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endDate` date NOT NULL,
  `endTime` time NOT NULL,
  `status` tinyint(1) NOT NULL,
  `priority` int(8) NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `executiveNotification`
--

TRUNCATE TABLE `executiveNotification`;
-- --------------------------------------------------------

--
-- Table structure for table `executiveNotificationSMS`
--

DROP TABLE IF EXISTS `executiveNotificationSMS`;
CREATE TABLE IF NOT EXISTS `executiveNotificationSMS` (
  `smsId` int(11) NOT NULL COMMENT 'The id of the SMS sent',
  `ticket` varchar(40) NOT NULL COMMENT 'The ticket number of the associated P1',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tracks texts for P1 incidents';

--
-- Truncate table before insert `executiveNotificationSMS`
--

TRUNCATE TABLE `executiveNotificationSMS`;
-- --------------------------------------------------------

--
-- Table structure for table `executiveNotificationUpdate`
--

DROP TABLE IF EXISTS `executiveNotificationUpdate`;
CREATE TABLE IF NOT EXISTS `executiveNotificationUpdate` (
  `updateID` bigint(10) NOT NULL,
  `execNoteID` bigint(8) NOT NULL,
  `updateText` longtext NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `executiveNotificationUpdate`
--

TRUNCATE TABLE `executiveNotificationUpdate`;
-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `comments` longtext NOT NULL,
  `date` date NOT NULL,
  `select` int(1) NOT NULL COMMENT '1="selected" 0 = "unselected"',
  `status` varchar(25) NOT NULL COMMENT '0=unread, 1=read, 2=Accepted, 3=Declined, 4=Assigned',
  `assignment` varchar(255) NOT NULL,
  `type` varchar(25) NOT NULL,
  `area` varchar(255) DEFAULT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `feedback`
--

TRUNCATE TABLE `feedback`;
-- --------------------------------------------------------

--
-- Table structure for table `genericPage`
--

DROP TABLE IF EXISTS `genericPage`;
CREATE TABLE IF NOT EXISTS `genericPage` (
  `contentId` int(10) unsigned NOT NULL COMMENT 'Primary Key for genericPage content',
  `table` varchar(255) NOT NULL COMMENT 'Name of the page being referenced.',
  `content` longtext NOT NULL COMMENT 'HTML content of the page "table"',
  `dateSubmitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the content was submitted',
  `submittedBy` varchar(255) NOT NULL COMMENT 'NetId of the person who submitted the content.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `genericPage`
--

TRUNCATE TABLE `genericPage`;
-- --------------------------------------------------------

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `submitDate` date NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `images`
--

TRUNCATE TABLE `images`;
-- --------------------------------------------------------

--
-- Table structure for table `incidentManagement`
--

DROP TABLE IF EXISTS `incidentManagement`;
CREATE TABLE IF NOT EXISTS `incidentManagement` (
  `incidentId` text NOT NULL,
  `description` text NOT NULL,
  `engineersDo` text NOT NULL,
  `opsDo` text NOT NULL,
  `rootCause` text NOT NULL,
  `resolution` text NOT NULL,
  `prevention` text NOT NULL,
  `improvement` text NOT NULL,
  `emailFlag` int(11) NOT NULL,
  `dateCreated` date NOT NULL,
  `startTime` text NOT NULL,
  `endTime` text NOT NULL,
  `incidentSysId` text NOT NULL,
  `impact` text NOT NULL,
  `exceed4` text NOT NULL,
  `associatedPT` text NOT NULL,
  `rfcCause` text NOT NULL,
  `thirdPartyCause` text NOT NULL,
  `incidentCoordinator` text NOT NULL,
  `escalatedTo` text NOT NULL,
  `responsibleEngTeam` text NOT NULL,
  `thirdPartyResolve` text NOT NULL,
  `kbUsed` text NOT NULL,
  `kbHelpful` text NOT NULL,
  `engEmail` int(11) NOT NULL,
  `oitEmail` int(11) NOT NULL,
  `managersEmail` int(11) NOT NULL,
  `discoveredBy` text NOT NULL,
  `userMessage` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `incidentManagement`
--

TRUNCATE TABLE `incidentManagement`;
-- --------------------------------------------------------

--
-- Table structure for table `iptv`
--

DROP TABLE IF EXISTS `iptv`;
CREATE TABLE IF NOT EXISTS `iptv` (
  `id` int(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `guid` char(36) CHARACTER SET latin1 NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `iptv`
--

TRUNCATE TABLE `iptv`;
-- --------------------------------------------------------

--
-- Table structure for table `kronosEdit`
--

DROP TABLE IF EXISTS `kronosEdit`;
CREATE TABLE IF NOT EXISTS `kronosEdit` (
  `index` int(11) NOT NULL,
  `addRemove` text NOT NULL,
  `inOut` text NOT NULL,
  `time` text NOT NULL,
  `date` text NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `kronosEdit`
--

TRUNCATE TABLE `kronosEdit`;
-- --------------------------------------------------------

--
-- Table structure for table `link`
--

DROP TABLE IF EXISTS `link`;
CREATE TABLE IF NOT EXISTS `link` (
  `index` int(11) NOT NULL,
  `name` varchar(64) NOT NULL COMMENT 'Text to appear for the link',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Should the relation between an area and an app be shown as a link on the site?',
  `permission` int(11) DEFAULT NULL COMMENT 'What permission does the link require to be viewed--if any',
  `newTab` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Should this open in a new tab?',
  `sortOrder` int(11) NOT NULL COMMENT 'What order should this be displayed in compared to its siblings',
  `parent` int(11) DEFAULT NULL COMMENT 'What Link is this under?',
  `area` int(11) NOT NULL COMMENT 'Which area is this link for?',
  `appId` int(11) DEFAULT NULL COMMENT 'Which App does this link to?',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='New Links Table';

--
-- Truncate table before insert `link`
--

TRUNCATE TABLE `link`;
--
-- Dumping data for table `link`
--

INSERT INTO `link` (`index`, `name`, `visible`, `permission`, `newTab`, `sortOrder`, `parent`, `area`, `appId`, `guid`) VALUES
(1, 'link1', 1, NULL, 1, 0, NULL, 1, NULL, '6a49fba1-ae89-44c0-bd35-09e16990a0e4'),
(2, 'link2', 1, NULL, 0, 1, NULL, 1, NULL, '12bea5f7-afaf-4a96-9260-5c175bf6d039'),
(3, 'child1', 1, NULL, 0, 0, 2, 1, 1, '8a6860a8-cb3b-4189-d37f-eef2e5ed6cb1'),
(4, 'child2', 1, NULL, 0, 1, 2, 1, NULL, '358ae1bc-3665-445f-ada2-f61c0b212ddc'),
(5, 'child3', 1, NULL, 0, 2, 2, 1, NULL, 'db4fe7e5-964b-4dfd-877c-376290a023a1'),
(6, 'grandchild1', 1, NULL, 0, 0, 4, 1, 2, 'ca433edc-98ce-415b-abc9-ae14a0cde6d7'),
(7, 'grandchild2', 1, NULL, 0, 1, 4, 1, NULL, '62edc825-c5bd-4262-b1a8-bbc1329e26d6');

-- --------------------------------------------------------

--
-- Table structure for table `majorIncidentManagers`
--

DROP TABLE IF EXISTS `majorIncidentManagers`;
CREATE TABLE IF NOT EXISTS `majorIncidentManagers` (
  `netID` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `majorIncidentManagers`
--

TRUNCATE TABLE `majorIncidentManagers`;
--
-- Dumping data for table `majorIncidentManagers`
--

INSERT INTO `majorIncidentManagers` (`netID`, `guid`) VALUES
('employee2', '9d610176-a949-4a6b-e312-094078b57097'),
('netId', 'a24e84cb-b320-42b7-bfc2-350e770d0697');

-- --------------------------------------------------------

--
-- Table structure for table `managerReportCategory`
--

DROP TABLE IF EXISTS `managerReportCategory`;
CREATE TABLE IF NOT EXISTS `managerReportCategory` (
  `id` int(10) unsigned NOT NULL,
  `category` varchar(255) NOT NULL COMMENT 'name of the category',
  `area` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Categories used in the Weekly Report app';

--
-- Truncate table before insert `managerReportCategory`
--

TRUNCATE TABLE `managerReportCategory`;
-- --------------------------------------------------------

--
-- Table structure for table `managerReports`
--

DROP TABLE IF EXISTS `managerReports`;
CREATE TABLE IF NOT EXISTS `managerReports` (
  `ID` int(11) NOT NULL,
  `netID` varchar(25) NOT NULL,
  `submitDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editDate` datetime DEFAULT NULL,
  `comments` text NOT NULL,
  `category` int(10) unsigned NOT NULL,
  `submitted` int(11) NOT NULL DEFAULT '0' COMMENT '0 pending review; -1 not sent to director; 1 sent to director',
  `area` int(11) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - not deleted, 1 - deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `managerReports`
--

TRUNCATE TABLE `managerReports`;
-- --------------------------------------------------------

--
-- Table structure for table `notificationGroup`
--

DROP TABLE IF EXISTS `notificationGroup`;
CREATE TABLE IF NOT EXISTS `notificationGroup` (
  `groupId` int(10) unsigned NOT NULL,
  `groupName` varchar(200) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `notificationGroup`
--

TRUNCATE TABLE `notificationGroup`;
-- --------------------------------------------------------

--
-- Table structure for table `notificationGroupMember`
--

DROP TABLE IF EXISTS `notificationGroupMember`;
CREATE TABLE IF NOT EXISTS `notificationGroupMember` (
  `groupMemberId` int(10) unsigned NOT NULL,
  `groupName` varchar(200) NOT NULL,
  `memberId` int(10) unsigned NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `notificationGroupMember`
--

TRUNCATE TABLE `notificationGroupMember`;
-- --------------------------------------------------------

--
-- Table structure for table `notificationMember`
--

DROP TABLE IF EXISTS `notificationMember`;
CREATE TABLE IF NOT EXISTS `notificationMember` (
  `memberId` int(10) unsigned NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(120) DEFAULT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `notificationMember`
--

TRUNCATE TABLE `notificationMember`;
-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `permissionId` int(11) NOT NULL,
  `shortName` varchar(255) NOT NULL,
  `longName` longtext NOT NULL,
  `description` longtext NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `permission`
--

TRUNCATE TABLE `permission`;
-- --------------------------------------------------------

--
-- Table structure for table `permissionArea`
--

DROP TABLE IF EXISTS `permissionArea`;
CREATE TABLE IF NOT EXISTS `permissionArea` (
  `index` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `permissionId` int(11) DEFAULT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `permissionArea`
--

TRUNCATE TABLE `permissionArea`;
-- --------------------------------------------------------

--
-- Table structure for table `permissionsGroupMembers`
--

DROP TABLE IF EXISTS `permissionsGroupMembers`;
CREATE TABLE IF NOT EXISTS `permissionsGroupMembers` (
  `ID` int(11) NOT NULL,
  `permID` int(11) NOT NULL,
  `groupID` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `permissionsGroupMembers`
--

TRUNCATE TABLE `permissionsGroupMembers`;
-- --------------------------------------------------------

--
-- Table structure for table `permissionsGroups`
--

DROP TABLE IF EXISTS `permissionsGroups`;
CREATE TABLE IF NOT EXISTS `permissionsGroups` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `permissionsGroups`
--

TRUNCATE TABLE `permissionsGroups`;
-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
CREATE TABLE IF NOT EXISTS `positions` (
  `positionId` int(11) NOT NULL,
  `positionName` varchar(150) NOT NULL,
  `positionDescription` varchar(255) NOT NULL,
  `employeeArea` int(11) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `positions`
--

TRUNCATE TABLE `positions`;
-- --------------------------------------------------------

--
-- Table structure for table `reportAbsence`
--

DROP TABLE IF EXISTS `reportAbsence`;
CREATE TABLE IF NOT EXISTS `reportAbsence` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `shiftStart` time NOT NULL,
  `shiftEnd` time NOT NULL,
  `reason` longtext NOT NULL,
  `noCall` text NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitter` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportAbsence`
--

TRUNCATE TABLE `reportAbsence`;
-- --------------------------------------------------------

--
-- Table structure for table `reportCommendable`
--

DROP TABLE IF EXISTS `reportCommendable`;
CREATE TABLE IF NOT EXISTS `reportCommendable` (
  `ID` int(11) NOT NULL,
  `employee` text NOT NULL,
  `date` date NOT NULL,
  `reason` longtext NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `public` int(11) NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportCommendable`
--

TRUNCATE TABLE `reportCommendable`;
-- --------------------------------------------------------

--
-- Table structure for table `reportComments`
--

DROP TABLE IF EXISTS `reportComments`;
CREATE TABLE IF NOT EXISTS `reportComments` (
  `id` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` longtext NOT NULL,
  `meetingRequest` int(11) NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportComments`
--

TRUNCATE TABLE `reportComments`;
-- --------------------------------------------------------

--
-- Table structure for table `reportInfoChangeRequest`
--

DROP TABLE IF EXISTS `reportInfoChangeRequest`;
CREATE TABLE IF NOT EXISTS `reportInfoChangeRequest` (
  `id` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` longtext NOT NULL COMMENT 'this is the information that gets submitted for info change request and unusual call',
  `type` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportInfoChangeRequest`
--

TRUNCATE TABLE `reportInfoChangeRequest`;
-- --------------------------------------------------------

--
-- Table structure for table `reportingDashboard`
--

DROP TABLE IF EXISTS `reportingDashboard`;
CREATE TABLE IF NOT EXISTS `reportingDashboard` (
  `index` int(11) NOT NULL,
  `reportName` text NOT NULL,
  `description` longtext NOT NULL,
  `fileName` text NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportingDashboard`
--

TRUNCATE TABLE `reportingDashboard`;
-- --------------------------------------------------------

--
-- Table structure for table `reportPerformanceReviewed`
--

DROP TABLE IF EXISTS `reportPerformanceReviewed`;
CREATE TABLE IF NOT EXISTS `reportPerformanceReviewed` (
  `id` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `month` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportPerformanceReviewed`
--

TRUNCATE TABLE `reportPerformanceReviewed`;
-- --------------------------------------------------------

--
-- Table structure for table `reportPolicyReminder`
--

DROP TABLE IF EXISTS `reportPolicyReminder`;
CREATE TABLE IF NOT EXISTS `reportPolicyReminder` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `reason` longtext NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitter` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportPolicyReminder`
--

TRUNCATE TABLE `reportPolicyReminder`;
-- --------------------------------------------------------

--
-- Table structure for table `reportSecurityViolation`
--

DROP TABLE IF EXISTS `reportSecurityViolation`;
CREATE TABLE IF NOT EXISTS `reportSecurityViolation` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `violation` text NOT NULL,
  `reason` longtext NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitter` varchar(255) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportSecurityViolation`
--

TRUNCATE TABLE `reportSecurityViolation`;
-- --------------------------------------------------------

--
-- Table structure for table `reportTardy`
--

DROP TABLE IF EXISTS `reportTardy`;
CREATE TABLE IF NOT EXISTS `reportTardy` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `reason` longtext NOT NULL,
  `time` time NOT NULL,
  `noCall` text NOT NULL,
  `break` varchar(5) NOT NULL DEFAULT 'No',
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitter` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `reportTardy`
--

TRUNCATE TABLE `reportTardy`;
-- --------------------------------------------------------

--
-- Table structure for table `routineTaskLog`
--

DROP TABLE IF EXISTS `routineTaskLog`;
CREATE TABLE IF NOT EXISTS `routineTaskLog` (
  `ID` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `taskId` int(11) NOT NULL,
  `timeDue` time NOT NULL,
  `dateDue` date NOT NULL,
  `area` int(11) NOT NULL,
  `completed` int(11) DEFAULT NULL,
  `completedBy` varchar(100) DEFAULT NULL,
  `timeCompleted` time DEFAULT NULL,
  `dateCompleted` date DEFAULT NULL,
  `comments` text,
  `muted` int(11) DEFAULT NULL,
  `mutedBy` varchar(100) DEFAULT NULL,
  `timeMuted` time DEFAULT NULL,
  `dateMuted` date DEFAULT NULL,
  `mutedComments` varchar(255) DEFAULT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `routineTaskLog`
--

TRUNCATE TABLE `routineTaskLog`;
-- --------------------------------------------------------

--
-- Table structure for table `routineTasks`
--

DROP TABLE IF EXISTS `routineTasks`;
CREATE TABLE IF NOT EXISTS `routineTasks` (
  `ID` int(11) NOT NULL,
  `title` text NOT NULL,
  `descr` longtext NOT NULL,
  `creator` varchar(100) NOT NULL,
  `createDate` date NOT NULL,
  `editor` varchar(100) DEFAULT NULL,
  `editDate` date DEFAULT NULL,
  `timeDue` time NOT NULL,
  `day` date DEFAULT NULL,
  `mon` int(11) NOT NULL DEFAULT '0',
  `tue` int(11) NOT NULL DEFAULT '0',
  `wed` int(11) NOT NULL DEFAULT '0',
  `thu` int(11) NOT NULL DEFAULT '0',
  `fri` int(11) NOT NULL DEFAULT '0',
  `sat` int(11) NOT NULL DEFAULT '0',
  `sun` int(11) NOT NULL DEFAULT '0',
  `dayOfMonth` int(11) NOT NULL COMMENT 'Used for monthly tasks',
  `enabled` int(11) DEFAULT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `routineTasks`
--

TRUNCATE TABLE `routineTasks`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleDefault`
--

DROP TABLE IF EXISTS `scheduleDefault`;
CREATE TABLE IF NOT EXISTS `scheduleDefault` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `hourType` int(11) NOT NULL,
  `period` int(11) NOT NULL,
  `startTime` time NOT NULL,
  `startDate` int(11) NOT NULL,
  `endTime` time NOT NULL,
  `endDate` int(11) NOT NULL,
  `hourTotal` double NOT NULL COMMENT 'This is the total number of hours for the shift',
  `area` int(11) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0' COMMENT 'To flag when a shift has been deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleDefault`
--

TRUNCATE TABLE `scheduleDefault`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleHourRequests`
--

DROP TABLE IF EXISTS `scheduleHourRequests`;
CREATE TABLE IF NOT EXISTS `scheduleHourRequests` (
  `ID` int(11) NOT NULL,
  `postDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `netId` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `area` int(11) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=Not Deleted 1=Deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleHourRequests`
--

TRUNCATE TABLE `scheduleHourRequests`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleHourTypes`
--

DROP TABLE IF EXISTS `scheduleHourTypes`;
CREATE TABLE IF NOT EXISTS `scheduleHourTypes` (
  `ID` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(64) NOT NULL,
  `longName` longtext NOT NULL,
  `permission` varchar(255) NOT NULL,
  `tradable` tinyint(1) NOT NULL DEFAULT '0',
  `defaultView` tinyint(1) NOT NULL DEFAULT '0',
  `selfSchedulable` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'This denotes if an employee without scheduling permissions can schedule this type of hour',
  `nonwork` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Flags whether or not this shift type should count towards the weekly hour total.',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Flag for deletion. 0=active, 1=removed.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleHourTypes`
--

TRUNCATE TABLE `scheduleHourTypes`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleNotes`
--

DROP TABLE IF EXISTS `scheduleNotes`;
CREATE TABLE IF NOT EXISTS `scheduleNotes` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `semester` int(11) NOT NULL,
  `requestedHours` int(11) NOT NULL,
  `registeredHours` int(11) NOT NULL,
  `notes` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleNotes`
--

TRUNCATE TABLE `scheduleNotes`;
-- --------------------------------------------------------

--
-- Table structure for table `schedulePosting`
--

DROP TABLE IF EXISTS `schedulePosting`;
CREATE TABLE IF NOT EXISTS `schedulePosting` (
  `ID` int(11) NOT NULL,
  `weekStart` date NOT NULL,
  `area` int(11) NOT NULL,
  `post` tinyint(1) NOT NULL DEFAULT '1',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `schedulePosting`
--

TRUNCATE TABLE `schedulePosting`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleSemesters`
--

DROP TABLE IF EXISTS `scheduleSemesters`;
CREATE TABLE IF NOT EXISTS `scheduleSemesters` (
  `ID` int(11) NOT NULL,
  `semester` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `area` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the default schedule for employees without scheduling rights ought to be locked. 0 = not locked; 1 = locked.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleSemesters`
--

TRUNCATE TABLE `scheduleSemesters`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleTradeBids`
--

DROP TABLE IF EXISTS `scheduleTradeBids`;
CREATE TABLE IF NOT EXISTS `scheduleTradeBids` (
  `ID` int(11) NOT NULL,
  `tradeID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `hour` time NOT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `extra` int(11) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0' COMMENT 'To flag when a shift has been deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleTradeBids`
--

TRUNCATE TABLE `scheduleTradeBids`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleTrades`
--

DROP TABLE IF EXISTS `scheduleTrades`;
CREATE TABLE IF NOT EXISTS `scheduleTrades` (
  `ID` int(11) NOT NULL,
  `postedBy` varchar(255) NOT NULL,
  `postedDate` date NOT NULL,
  `approvedBy` varchar(255) DEFAULT NULL,
  `approvedOn` date DEFAULT NULL,
  `shiftId` int(11) NOT NULL,
  `startDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endDate` date NOT NULL,
  `endTime` time NOT NULL,
  `hourType` int(11) NOT NULL,
  `bids` int(11) NOT NULL DEFAULT '0' COMMENT '0: no bids, 1: bids, 2: approved',
  `notes` mediumtext,
  `area` int(11) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0' COMMENT 'To flag when a shift has been deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleTrades`
--

TRUNCATE TABLE `scheduleTrades`;
-- --------------------------------------------------------

--
-- Table structure for table `scheduleWeekly`
--

DROP TABLE IF EXISTS `scheduleWeekly`;
CREATE TABLE IF NOT EXISTS `scheduleWeekly` (
  `ID` int(11) NOT NULL,
  `employee` varchar(255) NOT NULL,
  `startTime` time NOT NULL,
  `startDate` date NOT NULL,
  `endTime` time NOT NULL,
  `endDate` date NOT NULL,
  `hourType` int(11) NOT NULL,
  `hourTotal` double NOT NULL COMMENT 'This is the total number of hours for the shift',
  `defaultID` int(11) DEFAULT NULL,
  `trade` varchar(255) DEFAULT NULL,
  `posted` int(11) DEFAULT NULL,
  `area` int(11) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0' COMMENT 'To flag when a shift has been deleted',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `scheduleWeekly`
--

TRUNCATE TABLE `scheduleWeekly`;
-- --------------------------------------------------------

--
-- Table structure for table `serverRoomCheckin`
--

DROP TABLE IF EXISTS `serverRoomCheckin`;
CREATE TABLE IF NOT EXISTS `serverRoomCheckin` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `timeIn` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inBy` varchar(255) NOT NULL,
  `timeOut` datetime DEFAULT NULL,
  `outBy` varchar(255) NOT NULL,
  `cardNumber` varchar(20) NOT NULL,
  `purpose` longtext NOT NULL,
  `inNotes` longtext NOT NULL,
  `outNotes` longtext,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `serverRoomCheckin`
--

TRUNCATE TABLE `serverRoomCheckin`;
-- --------------------------------------------------------

--
-- Table structure for table `silentMonitor`
--

DROP TABLE IF EXISTS `silentMonitor`;
CREATE TABLE IF NOT EXISTS `silentMonitor` (
  `index` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `submitDate` date NOT NULL,
  `callDate` date NOT NULL,
  `completed` int(11) NOT NULL DEFAULT '0',
  `overallComment` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This keeps track of whether or not the item has been "deleted" from the website and if it whould show up.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `silentMonitor`
--

TRUNCATE TABLE `silentMonitor`;
-- --------------------------------------------------------

--
-- Table structure for table `silentMonitorCallCriteria`
--

DROP TABLE IF EXISTS `silentMonitorCallCriteria`;
CREATE TABLE IF NOT EXISTS `silentMonitorCallCriteria` (
  `smid` int(11) NOT NULL,
  `callNum` int(11) NOT NULL,
  `criteriaIndex` int(11) NOT NULL,
  `rating` varchar(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `silentMonitorCallCriteria`
--

TRUNCATE TABLE `silentMonitorCallCriteria`;
-- --------------------------------------------------------

--
-- Table structure for table `silentMonitorCalls`
--

DROP TABLE IF EXISTS `silentMonitorCalls`;
CREATE TABLE IF NOT EXISTS `silentMonitorCalls` (
  `smid` int(11) NOT NULL,
  `date` date NOT NULL,
  `callNum` int(11) NOT NULL,
  `comments` longtext NOT NULL,
  `rating` int(11) NOT NULL,
  `criteriaAvg` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Keeps track of whether or not the item has been "deleted" and if it should show up on the web site.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `silentMonitorCalls`
--

TRUNCATE TABLE `silentMonitorCalls`;
-- --------------------------------------------------------

--
-- Table structure for table `silentMonitorCriteriaInfo`
--

DROP TABLE IF EXISTS `silentMonitorCriteriaInfo`;
CREATE TABLE IF NOT EXISTS `silentMonitorCriteriaInfo` (
  `index` int(11) NOT NULL,
  `title` text NOT NULL,
  `contents` text NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `silentMonitorCriteriaInfo`
--

TRUNCATE TABLE `silentMonitorCriteriaInfo`;
-- --------------------------------------------------------

--
-- Table structure for table `supervisorReportDraft`
--

DROP TABLE IF EXISTS `supervisorReportDraft`;
CREATE TABLE IF NOT EXISTS `supervisorReportDraft` (
  `ID` int(11) NOT NULL,
  `date` date NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `area` int(11) NOT NULL,
  `outages` longtext NOT NULL,
  `problems` longtext NOT NULL,
  `misc` longtext NOT NULL,
  `supTasks` longtext NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supervisorReportDraft`
--

TRUNCATE TABLE `supervisorReportDraft`;
-- --------------------------------------------------------

--
-- Table structure for table `supervisorReportDraftTask`
--

DROP TABLE IF EXISTS `supervisorReportDraftTask`;
CREATE TABLE IF NOT EXISTS `supervisorReportDraftTask` (
  `ID` int(11) NOT NULL COMMENT 'Index for table.',
  `draftID` int(11) NOT NULL COMMENT 'ID of associated draft.',
  `name` varchar(255) NOT NULL COMMENT 'ID of HTML check box element.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table of tasks that were saved for a Supervisor Report draft.';

--
-- Truncate table before insert `supervisorReportDraftTask`
--

TRUNCATE TABLE `supervisorReportDraftTask`;
-- --------------------------------------------------------

--
-- Table structure for table `supervisorReportSD`
--

DROP TABLE IF EXISTS `supervisorReportSD`;
CREATE TABLE IF NOT EXISTS `supervisorReportSD` (
  `ID` int(11) NOT NULL,
  `date` date NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `area` int(11) NOT NULL,
  `outages` longtext NOT NULL,
  `problems` longtext NOT NULL,
  `misc` longtext NOT NULL,
  `supTasks` longtext NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supervisorReportSD`
--

TRUNCATE TABLE `supervisorReportSD`;
-- --------------------------------------------------------

--
-- Table structure for table `supervisorReportSDTasks`
--

DROP TABLE IF EXISTS `supervisorReportSDTasks`;
CREATE TABLE IF NOT EXISTS `supervisorReportSDTasks` (
  `ID` int(11) NOT NULL,
  `text` text NOT NULL,
  `checklist` tinyint(4) NOT NULL,
  `area` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supervisorReportSDTasks`
--

TRUNCATE TABLE `supervisorReportSDTasks`;
-- --------------------------------------------------------

--
-- Table structure for table `supervisorReportSecurityDesk`
--

DROP TABLE IF EXISTS `supervisorReportSecurityDesk`;
CREATE TABLE IF NOT EXISTS `supervisorReportSecurityDesk` (
  `ID` int(11) NOT NULL,
  `date` date NOT NULL,
  `submitter` varchar(255) NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `area` int(11) NOT NULL,
  `securityProblems` longtext NOT NULL,
  `shiftProblems` longtext NOT NULL,
  `misc` longtext NOT NULL,
  `emailSent` tinyint(1) NOT NULL DEFAULT '0',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supervisorReportSecurityDesk`
--

TRUNCATE TABLE `supervisorReportSecurityDesk`;
-- --------------------------------------------------------

--
-- Table structure for table `supNotes`
--

DROP TABLE IF EXISTS `supNotes`;
CREATE TABLE IF NOT EXISTS `supNotes` (
  `noteId` int(10) unsigned NOT NULL,
  `note` longtext NOT NULL,
  `cleared` tinyint(4) NOT NULL,
  `submittedBy` varchar(120) NOT NULL,
  `timeSubmitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clearedBy` varchar(120) DEFAULT NULL,
  `timeCleared` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `closingComment` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supNotes`
--

TRUNCATE TABLE `supNotes`;
-- --------------------------------------------------------

--
-- Table structure for table `supReport`
--

DROP TABLE IF EXISTS `supReport`;
CREATE TABLE IF NOT EXISTS `supReport` (
  `entryId` int(10) unsigned NOT NULL,
  `entry` longtext NOT NULL,
  `submittedBy` varchar(125) NOT NULL,
  `timeSubmitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `supReport`
--

TRUNCATE TABLE `supReport`;
-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `typeId` int(11) NOT NULL,
  `typeName` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `mustApprove` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Determines if this tag requires approval before being viewed by the general populace.',
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `tag`
--

TRUNCATE TABLE `tag`;
-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL,
  `short` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='An easy way to create app-specific tags for employees';

--
-- Truncate table before insert `tags`
--

TRUNCATE TABLE `tags`;
-- --------------------------------------------------------

--
-- Table structure for table `teaming`
--

DROP TABLE IF EXISTS `teaming`;
CREATE TABLE IF NOT EXISTS `teaming` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `supervisorID` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `teamed` tinyint(1) NOT NULL,
  `timely` tinyint(1) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `teaming`
--

TRUNCATE TABLE `teaming`;
-- --------------------------------------------------------

--
-- Table structure for table `teamingLog`
--

DROP TABLE IF EXISTS `teamingLog`;
CREATE TABLE IF NOT EXISTS `teamingLog` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `supervisorID` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `teamed` int(11) NOT NULL,
  `timely` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `teamingLog`
--

TRUNCATE TABLE `teamingLog`;
-- --------------------------------------------------------

--
-- Table structure for table `teamMembers`
--

DROP TABLE IF EXISTS `teamMembers`;
CREATE TABLE IF NOT EXISTS `teamMembers` (
  `ID` int(11) NOT NULL,
  `netID` varchar(255) NOT NULL,
  `teamID` int(11) NOT NULL,
  `isSupervisor` tinyint(1) NOT NULL COMMENT 'whether the employee has supervisor rights to the team (not necessarily the team manager)',
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `teamMembers`
--

TRUNCATE TABLE `teamMembers`;
-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `lead` varchar(255) NOT NULL,
  `email` text NOT NULL,
  `isShift` tinyint(1) NOT NULL DEFAULT '0',
  `area` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `teams`
--

TRUNCATE TABLE `teams`;
-- --------------------------------------------------------

--
-- Table structure for table `ticketReview`
--

DROP TABLE IF EXISTS `ticketReview`;
CREATE TABLE IF NOT EXISTS `ticketReview` (
  `entryNum` int(11) unsigned NOT NULL,
  `ticketNum` varchar(11) NOT NULL,
  `agentID` varchar(255) NOT NULL,
  `submitterID` varchar(255) NOT NULL,
  `ticketDate` date NOT NULL,
  `reviewDate` date NOT NULL,
  `viewDate` date NOT NULL,
  `agentViewed` int(1) NOT NULL,
  `requestor` varchar(3) NOT NULL COMMENT 'Client for ticket',
  `contactInfo` varchar(3) NOT NULL COMMENT 'Client''s information',
  `ssc` varchar(3) NOT NULL COMMENT 'Service and Symptom Category',
  `ticketSource` varchar(3) NOT NULL COMMENT 'Phone/Email',
  `priority` varchar(3) NOT NULL COMMENT 'Priority',
  `kbOrSource` varchar(3) NOT NULL COMMENT 'KB/Source',
  `workOrderNumber` varchar(3) NOT NULL COMMENT 'Work Order Number',
  `templates` varchar(3) NOT NULL COMMENT 'Used Templates?',
  `troubleshooting` varchar(3) NOT NULL COMMENT 'Troubleshooting/Needed Info',
  `closureCodes` varchar(3) NOT NULL COMMENT 'Closure Codes',
  `professionalism` varchar(3) NOT NULL,
  `comments` longtext NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time stamp of when ticket was entered only.  Not when ticket gets updated.',
  `sentEmail` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether email was sent to employee letting him know that a ticket review was submitted.',
  `area` tinyint(2) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `ticketReview`
--

TRUNCATE TABLE `ticketReview`;
-- --------------------------------------------------------

--
-- Table structure for table `timeTrack`
--

DROP TABLE IF EXISTS `timeTrack`;
CREATE TABLE IF NOT EXISTS `timeTrack` (
  `id` int(11) unsigned NOT NULL,
  `inOut` text NOT NULL,
  `startTime` date NOT NULL,
  `time` time NOT NULL,
  `project` text NOT NULL,
  `modify` text NOT NULL,
  `employee` text NOT NULL,
  `entryForTeam` text NOT NULL,
  `teamId` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `timeTrack`
--

TRUNCATE TABLE `timeTrack`;
-- --------------------------------------------------------

--
-- Table structure for table `unscheduledRFC`
--

DROP TABLE IF EXISTS `unscheduledRFC`;
CREATE TABLE IF NOT EXISTS `unscheduledRFC` (
  `ID` int(11) NOT NULL,
  `ticketNumber` varchar(25) NOT NULL,
  `engineerName` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `startTime` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endTime` varchar(255) NOT NULL,
  `endDate` date NOT NULL,
  `impact` text NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `unscheduledRFC`
--

TRUNCATE TABLE `unscheduledRFC`;
-- --------------------------------------------------------

--
-- Table structure for table `wallOfFame`
--

DROP TABLE IF EXISTS `wallOfFame`;
CREATE TABLE IF NOT EXISTS `wallOfFame` (
  `index` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `netID` text NOT NULL COMMENT 'Net ID of the employee',
  `submitter` text NOT NULL COMMENT 'Net ID of the submitter',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` longtext NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `wallOfFame`
--

TRUNCATE TABLE `wallOfFame`;
-- --------------------------------------------------------

--
-- Table structure for table `whiteboard`
--

DROP TABLE IF EXISTS `whiteboard`;
CREATE TABLE IF NOT EXISTS `whiteboard` (
  `messageId` int(11) NOT NULL,
  `ownerId` varchar(255) NOT NULL COMMENT 'NetID of the person who made the post.',
  `type` int(11) NOT NULL,
  `title` longtext NOT NULL COMMENT 'The Subject heading of the whiteboard message.',
  `message` longtext NOT NULL COMMENT 'The content of the whiteboard message post.',
  `postDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time and date the message was posted/will be posted.',
  `expireDate` date NOT NULL COMMENT 'The date after which this message should no longer appear on the whiteboard.',
  `mandatory` int(11) NOT NULL COMMENT 'This is a flag for whether or not this post is mandatory for all employees to read.',
  `kb` varchar(255) NOT NULL COMMENT 'The kb number of the associated knowledge base article.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='The new inter-area whiteboard.';

--
-- Truncate table before insert `whiteboard`
--

TRUNCATE TABLE `whiteboard`;
-- --------------------------------------------------------

--
-- Table structure for table `whiteboardAreas`
--

DROP TABLE IF EXISTS `whiteboardAreas`;
CREATE TABLE IF NOT EXISTS `whiteboardAreas` (
  `id` int(11) NOT NULL,
  `whiteboardId` int(11) NOT NULL COMMENT 'The id of the associated whiteboard message.',
  `areaId` int(11) NOT NULL COMMENT 'The id of the employee area that should be able to view this message.',
  `approved` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Have I been approved?',
  `approvedBy` varchar(255) DEFAULT NULL COMMENT 'Who approved me',
  `approvedOn` datetime DEFAULT NULL COMMENT 'When was I born into approval',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Am I allowed to exist?',
  `deletedBy` varchar(255) DEFAULT NULL COMMENT 'Who murdered me?',
  `deletedOn` datetime DEFAULT NULL COMMENT 'The time and date I was officially declared dead.',
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table tracks which areas should be able to view which whiteboard posts.';

--
-- Truncate table before insert `whiteboardAreas`
--

TRUNCATE TABLE `whiteboardAreas`;
-- --------------------------------------------------------

--
-- Table structure for table `whiteboardMandatoryLog`
--

DROP TABLE IF EXISTS `whiteboardMandatoryLog`;
CREATE TABLE IF NOT EXISTS `whiteboardMandatoryLog` (
  `netID` varchar(255) NOT NULL,
  `msgID` int(11) NOT NULL,
  `guid` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Truncate table before insert `whiteboardMandatoryLog`
--

TRUNCATE TABLE `whiteboardMandatoryLog`;
--
-- Indexes for dumped tables
--

--
-- Indexes for table `activitiesBoard`
--
ALTER TABLE `activitiesBoard`
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `agentLogins`
--
ALTER TABLE `agentLogins`
  ADD PRIMARY KEY (`itemId`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`appId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `internal` (`internal`);

--
-- Indexes for table `appPermission`
--
ALTER TABLE `appPermission`
  ADD PRIMARY KEY (`appPermissionId`),
  ADD UNIQUE KEY `appId_2` (`appId`,`permissionId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `appId` (`appId`,`permissionId`),
  ADD KEY `permissionId` (`permissionId`),
  ADD KEY `appId_3` (`appId`),
  ADD KEY `permissionId_2` (`permissionId`);

--
-- Indexes for table `assessmentsEmployeeGroupList`
--
ALTER TABLE `assessmentsEmployeeGroupList`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `assessmentsGroup`
--
ALTER TABLE `assessmentsGroup`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `assessmentsGroupRequiredTests`
--
ALTER TABLE `assessmentsGroupRequiredTests`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `assessmentsResults`
--
ALTER TABLE `assessmentsResults`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `assessmentsTest`
--
ALTER TABLE `assessmentsTest`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `contactsHierarchy`
--
ALTER TABLE `contactsHierarchy`
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `crmClient`
--
ALTER TABLE `crmClient`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netId` (`netId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `departName` (`departName`);

--
-- Indexes for table `crmDepartment`
--
ALTER TABLE `crmDepartment`
  ADD PRIMARY KEY (`departmentID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `departName` (`departName`);

--
-- Indexes for table `crmLog`
--
ALTER TABLE `crmLog`
  ADD PRIMARY KEY (`logID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `ID` (`ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`netID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `positionForeignKey` (`position`);

--
-- Indexes for table `employeeAreaPermissions`
--
ALTER TABLE `employeeAreaPermissions`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `employeeAreas`
--
ALTER TABLE `employeeAreas`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeePermissions`
--
ALTER TABLE `employeePermissions`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `netID_2` (`netID`,`permission`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `permission` (`permission`);

--
-- Indexes for table `employeePositionHistory`
--
ALTER TABLE `employeePositionHistory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `employeeRaiseLog`
--
ALTER TABLE `employeeRaiseLog`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`);

--
-- Indexes for table `employeeRaiseReasons`
--
ALTER TABLE `employeeRaiseReasons`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeeRights`
--
ALTER TABLE `employeeRights`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID` (`ID`,`rightName`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeeRightsEmails`
--
ALTER TABLE `employeeRightsEmails`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `rightID` (`rightID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeeRightsLevels`
--
ALTER TABLE `employeeRightsLevels`
  ADD UNIQUE KEY `level` (`level`,`area`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeeRightsStatus`
--
ALTER TABLE `employeeRightsStatus`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netID` (`netID`,`rightID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `employeeTags`
--
ALTER TABLE `employeeTags`
  ADD UNIQUE KEY `no_dups` (`netID`,`tag`,`area`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `employeeTerminationDetails`
--
ALTER TABLE `employeeTerminationDetails`
  ADD PRIMARY KEY (`terminationId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `area` (`area`),
  ADD KEY `submitter` (`submitter`);

--
-- Indexes for table `employeeWages`
--
ALTER TABLE `employeeWages`
  ADD PRIMARY KEY (`netID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `empPreferences`
--
ALTER TABLE `empPreferences`
  ADD PRIMARY KEY (`preferenceId`),
  ADD UNIQUE KEY `employee` (`employee`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `executiveNotification`
--
ALTER TABLE `executiveNotification`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `incidentCoord` (`incidentCoord`),
  ADD KEY `submitter` (`submitter`);

--
-- Indexes for table `executiveNotificationSMS`
--
ALTER TABLE `executiveNotificationSMS`
  ADD PRIMARY KEY (`smsId`),
  ADD UNIQUE KEY `ticket` (`ticket`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `executiveNotificationUpdate`
--
ALTER TABLE `executiveNotificationUpdate`
  ADD PRIMARY KEY (`updateID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `submitter` (`submitter`),
  ADD KEY `execNoteID` (`execNoteID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `genericPage`
--
ALTER TABLE `genericPage`
  ADD PRIMARY KEY (`contentId`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netID` (`netID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `incidentManagement`
--
ALTER TABLE `incidentManagement`
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `iptv`
--
ALTER TABLE `iptv`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `kronosEdit`
--
ALTER TABLE `kronosEdit`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `submitter` (`submitter`);

--
-- Indexes for table `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `visible` (`visible`),
  ADD KEY `permission` (`permission`),
  ADD KEY `sortOrder` (`sortOrder`),
  ADD KEY `parent` (`parent`),
  ADD KEY `area` (`area`),
  ADD KEY `appId` (`appId`);

--
-- Indexes for table `majorIncidentManagers`
--
ALTER TABLE `majorIncidentManagers`
  ADD PRIMARY KEY (`netID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `managerReportCategory`
--
ALTER TABLE `managerReportCategory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `managerReports`
--
ALTER TABLE `managerReports`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `category` (`category`),
  ADD KEY `deleted` (`deleted`);

--
-- Indexes for table `notificationGroup`
--
ALTER TABLE `notificationGroup`
  ADD PRIMARY KEY (`groupId`),
  ADD UNIQUE KEY `groupName` (`groupName`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `notificationGroupMember`
--
ALTER TABLE `notificationGroupMember`
  ADD PRIMARY KEY (`groupMemberId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `groupName` (`groupName`,`memberId`),
  ADD KEY `memberId` (`memberId`);

--
-- Indexes for table `notificationMember`
--
ALTER TABLE `notificationMember`
  ADD PRIMARY KEY (`memberId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD UNIQUE KEY `email` (`email`,`phone`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`permissionId`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `permissionArea`
--
ALTER TABLE `permissionArea`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `permissionId` (`permissionId`);

--
-- Indexes for table `permissionsGroupMembers`
--
ALTER TABLE `permissionsGroupMembers`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `permID_2` (`permID`,`groupID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `permID` (`permID`),
  ADD KEY `groupID` (`groupID`);

--
-- Indexes for table `permissionsGroups`
--
ALTER TABLE `permissionsGroups`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`positionId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `employeeArea` (`employeeArea`);

--
-- Indexes for table `reportAbsence`
--
ALTER TABLE `reportAbsence`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `employee` (`employee`);

--
-- Indexes for table `reportCommendable`
--
ALTER TABLE `reportCommendable`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `reportComments`
--
ALTER TABLE `reportComments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`);

--
-- Indexes for table `reportInfoChangeRequest`
--
ALTER TABLE `reportInfoChangeRequest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`);

--
-- Indexes for table `reportingDashboard`
--
ALTER TABLE `reportingDashboard`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `reportPerformanceReviewed`
--
ALTER TABLE `reportPerformanceReviewed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`);

--
-- Indexes for table `reportPolicyReminder`
--
ALTER TABLE `reportPolicyReminder`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `employee` (`employee`);

--
-- Indexes for table `reportSecurityViolation`
--
ALTER TABLE `reportSecurityViolation`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `employee` (`employee`),
  ADD KEY `submitter` (`submitter`);

--
-- Indexes for table `reportTardy`
--
ALTER TABLE `reportTardy`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `employee` (`employee`);

--
-- Indexes for table `routineTaskLog`
--
ALTER TABLE `routineTaskLog`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `taskId` (`taskId`,`timeDue`,`dateDue`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `routineTasks`
--
ALTER TABLE `routineTasks`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `scheduleDefault`
--
ALTER TABLE `scheduleDefault`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `employee` (`employee`),
  ADD KEY `hourType` (`hourType`),
  ADD KEY `period` (`period`);

--
-- Indexes for table `scheduleHourRequests`
--
ALTER TABLE `scheduleHourRequests`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netId` (`netId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `scheduleHourTypes`
--
ALTER TABLE `scheduleHourTypes`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`),
  ADD KEY `value` (`value`);

--
-- Indexes for table `scheduleNotes`
--
ALTER TABLE `scheduleNotes`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netID` (`netID`,`semester`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID_2` (`netID`),
  ADD KEY `semester` (`semester`);

--
-- Indexes for table `schedulePosting`
--
ALTER TABLE `schedulePosting`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `weekStart` (`weekStart`,`area`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `scheduleSemesters`
--
ALTER TABLE `scheduleSemesters`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `startDate` (`startDate`,`area`),
  ADD UNIQUE KEY `semester` (`semester`,`area`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `scheduleTradeBids`
--
ALTER TABLE `scheduleTradeBids`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `tradeID` (`tradeID`,`employee`,`hour`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `scheduleTrades`
--
ALTER TABLE `scheduleTrades`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `postedBy` (`postedBy`),
  ADD KEY `shiftId` (`shiftId`),
  ADD KEY `approvedBy` (`approvedBy`),
  ADD KEY `area` (`area`),
  ADD KEY `startDate` (`startDate`),
  ADD KEY `endDate` (`endDate`),
  ADD KEY `bids` (`bids`),
  ADD KEY `deleted` (`deleted`);

--
-- Indexes for table `scheduleWeekly`
--
ALTER TABLE `scheduleWeekly`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `Employee` (`employee`),
  ADD KEY `area` (`area`),
  ADD KEY `defaultID` (`defaultID`),
  ADD KEY `hourType` (`hourType`),
  ADD KEY `startDate` (`startDate`),
  ADD KEY `endDate` (`endDate`),
  ADD KEY `posted` (`posted`),
  ADD KEY `deleted` (`deleted`);

--
-- Indexes for table `serverRoomCheckin`
--
ALTER TABLE `serverRoomCheckin`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `silentMonitor`
--
ALTER TABLE `silentMonitor`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `submitDate` (`submitDate`);

--
-- Indexes for table `silentMonitorCallCriteria`
--
ALTER TABLE `silentMonitorCallCriteria`
  ADD PRIMARY KEY (`smid`,`callNum`,`criteriaIndex`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `callNum` (`callNum`);

--
-- Indexes for table `silentMonitorCalls`
--
ALTER TABLE `silentMonitorCalls`
  ADD UNIQUE KEY `smid_2` (`smid`,`callNum`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `callNum` (`callNum`),
  ADD KEY `smid` (`smid`);

--
-- Indexes for table `silentMonitorCriteriaInfo`
--
ALTER TABLE `silentMonitorCriteriaInfo`
  ADD UNIQUE KEY `index` (`index`,`area`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `supervisorReportDraft`
--
ALTER TABLE `supervisorReportDraft`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `supervisorReportDraftTask`
--
ALTER TABLE `supervisorReportDraftTask`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `supervisorReportSD`
--
ALTER TABLE `supervisorReportSD`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `supervisorReportSDTasks`
--
ALTER TABLE `supervisorReportSDTasks`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `supervisorReportSecurityDesk`
--
ALTER TABLE `supervisorReportSecurityDesk`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `supNotes`
--
ALTER TABLE `supNotes`
  ADD PRIMARY KEY (`noteId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `submittedBy` (`submittedBy`),
  ADD KEY `clearedBy` (`clearedBy`);

--
-- Indexes for table `supReport`
--
ALTER TABLE `supReport`
  ADD PRIMARY KEY (`entryId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `submittedBy` (`submittedBy`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`typeId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short` (`short`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `teaming`
--
ALTER TABLE `teaming`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netID` (`netID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `supervisorID` (`supervisorID`);

--
-- Indexes for table `teamingLog`
--
ALTER TABLE `teamingLog`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID` (`netID`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `teamMembers`
--
ALTER TABLE `teamMembers`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `netID_2` (`netID`,`teamID`,`area`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `teamID` (`teamID`),
  ADD KEY `area` (`area`),
  ADD KEY `netID` (`netID`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `manager` (`lead`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `ticketReview`
--
ALTER TABLE `ticketReview`
  ADD PRIMARY KEY (`entryNum`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `agentID` (`agentID`),
  ADD KEY `submitterID` (`submitterID`);

--
-- Indexes for table `timeTrack`
--
ALTER TABLE `timeTrack`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `unscheduledRFC`
--
ALTER TABLE `unscheduledRFC`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `wallOfFame`
--
ALTER TABLE `wallOfFame`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Indexes for table `whiteboard`
--
ALTER TABLE `whiteboard`
  ADD PRIMARY KEY (`messageId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `whiteboardAreas`
--
ALTER TABLE `whiteboardAreas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whiteboardId` (`whiteboardId`,`areaId`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `areaId` (`areaId`),
  ADD KEY `approved` (`approved`,`approvedBy`,`approvedOn`,`deleted`,`deletedBy`,`deletedOn`);

--
-- Indexes for table `whiteboardMandatoryLog`
--
ALTER TABLE `whiteboardMandatoryLog`
  ADD UNIQUE KEY `netID` (`netID`,`msgID`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `netID_2` (`netID`),
  ADD KEY `msgID` (`msgID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activitiesBoard`
--
ALTER TABLE `activitiesBoard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `agentLogins`
--
ALTER TABLE `agentLogins`
  MODIFY `itemId` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `app`
--
ALTER TABLE `app`
  MODIFY `appId` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
--
-- AUTO_INCREMENT for table `appPermission`
--
ALTER TABLE `appPermission`
  MODIFY `appPermissionId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `assessmentsEmployeeGroupList`
--
ALTER TABLE `assessmentsEmployeeGroupList`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for table entries.';
--
-- AUTO_INCREMENT for table `assessmentsGroup`
--
ALTER TABLE `assessmentsGroup`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID of group.';
--
-- AUTO_INCREMENT for table `assessmentsGroupRequiredTests`
--
ALTER TABLE `assessmentsGroupRequiredTests`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for index.';
--
-- AUTO_INCREMENT for table `assessmentsResults`
--
ALTER TABLE `assessmentsResults`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for test results.';
--
-- AUTO_INCREMENT for table `assessmentsTest`
--
ALTER TABLE `assessmentsTest`
  MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Index for table.';
--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `contactsHierarchy`
--
ALTER TABLE `contactsHierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `crmClient`
--
ALTER TABLE `crmClient`
  MODIFY `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Client ID';
--
-- AUTO_INCREMENT for table `crmDepartment`
--
ALTER TABLE `crmDepartment`
  MODIFY `departmentID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID to Identify the Department';
--
-- AUTO_INCREMENT for table `crmLog`
--
ALTER TABLE `crmLog`
  MODIFY `logID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID # for a new interaction with one of our clients';
--
-- AUTO_INCREMENT for table `employeeAreaPermissions`
--
ALTER TABLE `employeeAreaPermissions`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeAreas`
--
ALTER TABLE `employeeAreas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeePermissions`
--
ALTER TABLE `employeePermissions`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeePositionHistory`
--
ALTER TABLE `employeePositionHistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeRaiseLog`
--
ALTER TABLE `employeeRaiseLog`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeRaiseReasons`
--
ALTER TABLE `employeeRaiseReasons`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeRights`
--
ALTER TABLE `employeeRights`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeRightsEmails`
--
ALTER TABLE `employeeRightsEmails`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeRightsStatus`
--
ALTER TABLE `employeeRightsStatus`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employeeTerminationDetails`
--
ALTER TABLE `employeeTerminationDetails`
  MODIFY `terminationId` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `empPreferences`
--
ALTER TABLE `empPreferences`
  MODIFY `preferenceId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `executiveNotification`
--
ALTER TABLE `executiveNotification`
  MODIFY `ID` bigint(8) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `executiveNotificationSMS`
--
ALTER TABLE `executiveNotificationSMS`
  MODIFY `smsId` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The id of the SMS sent';
--
-- AUTO_INCREMENT for table `executiveNotificationUpdate`
--
ALTER TABLE `executiveNotificationUpdate`
  MODIFY `updateID` bigint(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `genericPage`
--
ALTER TABLE `genericPage`
  MODIFY `contentId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key for genericPage content';
--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `iptv`
--
ALTER TABLE `iptv`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `kronosEdit`
--
ALTER TABLE `kronosEdit`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `link`
--
ALTER TABLE `link`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `managerReportCategory`
--
ALTER TABLE `managerReportCategory`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `managerReports`
--
ALTER TABLE `managerReports`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `notificationGroup`
--
ALTER TABLE `notificationGroup`
  MODIFY `groupId` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `notificationGroupMember`
--
ALTER TABLE `notificationGroupMember`
  MODIFY `groupMemberId` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `notificationMember`
--
ALTER TABLE `notificationMember`
  MODIFY `memberId` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `permissionId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissionArea`
--
ALTER TABLE `permissionArea`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissionsGroupMembers`
--
ALTER TABLE `permissionsGroupMembers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissionsGroups`
--
ALTER TABLE `permissionsGroups`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `positionId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportAbsence`
--
ALTER TABLE `reportAbsence`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportCommendable`
--
ALTER TABLE `reportCommendable`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportComments`
--
ALTER TABLE `reportComments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportInfoChangeRequest`
--
ALTER TABLE `reportInfoChangeRequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportingDashboard`
--
ALTER TABLE `reportingDashboard`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportPerformanceReviewed`
--
ALTER TABLE `reportPerformanceReviewed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportPolicyReminder`
--
ALTER TABLE `reportPolicyReminder`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportSecurityViolation`
--
ALTER TABLE `reportSecurityViolation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `reportTardy`
--
ALTER TABLE `reportTardy`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `routineTaskLog`
--
ALTER TABLE `routineTaskLog`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `routineTasks`
--
ALTER TABLE `routineTasks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleDefault`
--
ALTER TABLE `scheduleDefault`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleHourRequests`
--
ALTER TABLE `scheduleHourRequests`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleHourTypes`
--
ALTER TABLE `scheduleHourTypes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleNotes`
--
ALTER TABLE `scheduleNotes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `schedulePosting`
--
ALTER TABLE `schedulePosting`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleSemesters`
--
ALTER TABLE `scheduleSemesters`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleTradeBids`
--
ALTER TABLE `scheduleTradeBids`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleTrades`
--
ALTER TABLE `scheduleTrades`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `scheduleWeekly`
--
ALTER TABLE `scheduleWeekly`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `serverRoomCheckin`
--
ALTER TABLE `serverRoomCheckin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `silentMonitor`
--
ALTER TABLE `silentMonitor`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supervisorReportDraft`
--
ALTER TABLE `supervisorReportDraft`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supervisorReportDraftTask`
--
ALTER TABLE `supervisorReportDraftTask`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Index for table.';
--
-- AUTO_INCREMENT for table `supervisorReportSD`
--
ALTER TABLE `supervisorReportSD`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supervisorReportSDTasks`
--
ALTER TABLE `supervisorReportSDTasks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supervisorReportSecurityDesk`
--
ALTER TABLE `supervisorReportSecurityDesk`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supNotes`
--
ALTER TABLE `supNotes`
  MODIFY `noteId` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `supReport`
--
ALTER TABLE `supReport`
  MODIFY `entryId` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `typeId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `teaming`
--
ALTER TABLE `teaming`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `teamingLog`
--
ALTER TABLE `teamingLog`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `teamMembers`
--
ALTER TABLE `teamMembers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ticketReview`
--
ALTER TABLE `ticketReview`
  MODIFY `entryNum` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `timeTrack`
--
ALTER TABLE `timeTrack`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `unscheduledRFC`
--
ALTER TABLE `unscheduledRFC`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wallOfFame`
--
ALTER TABLE `wallOfFame`
  MODIFY `index` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `whiteboard`
--
ALTER TABLE `whiteboard`
  MODIFY `messageId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `whiteboardAreas`
--
ALTER TABLE `whiteboardAreas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `appPermission`
--
ALTER TABLE `appPermission`
  ADD CONSTRAINT `appPermission_ibfk_1` FOREIGN KEY (`permissionId`) REFERENCES `permission` (`permissionId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appPermission_ibfk_2` FOREIGN KEY (`appId`) REFERENCES `app` (`appId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crmClient`
--
ALTER TABLE `crmClient`
  ADD CONSTRAINT `crmClient_ibfk_1` FOREIGN KEY (`departName`) REFERENCES `crmDepartment` (`departName`) ON UPDATE CASCADE;

--
-- Constraints for table `crmLog`
--
ALTER TABLE `crmLog`
  ADD CONSTRAINT `crmLog_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `crmClient` (`ID`) ON UPDATE CASCADE;

--
-- Constraints for table `employeeAreaPermissions`
--
ALTER TABLE `employeeAreaPermissions`
  ADD CONSTRAINT `employeeAreaPermissions_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employeeAreaPermissions_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employeePermissions`
--
ALTER TABLE `employeePermissions`
  ADD CONSTRAINT `employeePermissions_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employeePermissions_ibfk_3` FOREIGN KEY (`permission`) REFERENCES `permissionArea` (`index`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employeePositionHistory`
--
ALTER TABLE `employeePositionHistory`
  ADD CONSTRAINT `employeePositionHistory_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employeePositionHistory_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employeeRaiseLog`
--
ALTER TABLE `employeeRaiseLog`
  ADD CONSTRAINT `employeeRaiseLog_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employeeTags`
--
ALTER TABLE `employeeTags`
  ADD CONSTRAINT `area_link` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`),
  ADD CONSTRAINT `netID_link` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`);

--
-- Constraints for table `employeeTerminationDetails`
--
ALTER TABLE `employeeTerminationDetails`
  ADD CONSTRAINT `employeeTerminationDetails_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employeeTerminationDetails_ibfk_2` FOREIGN KEY (`submitter`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `employeeWages`
--
ALTER TABLE `employeeWages`
  ADD CONSTRAINT `employeeWages_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `empPreferences`
--
ALTER TABLE `empPreferences`
  ADD CONSTRAINT `empPreferences_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `executiveNotification`
--
ALTER TABLE `executiveNotification`
  ADD CONSTRAINT `executiveNotification_ibfk_1` FOREIGN KEY (`incidentCoord`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `executiveNotification_ibfk_2` FOREIGN KEY (`submitter`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `executiveNotificationUpdate`
--
ALTER TABLE `executiveNotificationUpdate`
  ADD CONSTRAINT `executiveNotificationUpdate_ibfk_1` FOREIGN KEY (`execNoteID`) REFERENCES `executiveNotification` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `executiveNotificationUpdate_ibfk_2` FOREIGN KEY (`submitter`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `images_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kronosEdit`
--
ALTER TABLE `kronosEdit`
  ADD CONSTRAINT `kronosEdit_ibfk_1` FOREIGN KEY (`submitter`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `link_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_ibfk_3` FOREIGN KEY (`appId`) REFERENCES `app` (`appId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_ibfk_5` FOREIGN KEY (`permission`) REFERENCES `permissionArea` (`index`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `link_ibfk_6` FOREIGN KEY (`parent`) REFERENCES `link` (`index`);

--
-- Constraints for table `majorIncidentManagers`
--
ALTER TABLE `majorIncidentManagers`
  ADD CONSTRAINT `netID` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `managerReports`
--
ALTER TABLE `managerReports`
  ADD CONSTRAINT `managerReports_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `managerReports_ibfk_2` FOREIGN KEY (`category`) REFERENCES `managerReportCategory` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `notificationGroupMember`
--
ALTER TABLE `notificationGroupMember`
  ADD CONSTRAINT `notificationGroupMember_ibfk_1` FOREIGN KEY (`groupName`) REFERENCES `notificationGroup` (`groupName`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notificationGroupMember_ibfk_2` FOREIGN KEY (`memberId`) REFERENCES `notificationMember` (`memberId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permissionArea`
--
ALTER TABLE `permissionArea`
  ADD CONSTRAINT `permissionArea_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permissionArea_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `permission` (`permissionId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permissionsGroupMembers`
--
ALTER TABLE `permissionsGroupMembers`
  ADD CONSTRAINT `permissionsGroupMembers_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `permissionsGroups` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permissionsGroupMembers_ibfk_3` FOREIGN KEY (`permID`) REFERENCES `permissionArea` (`index`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`employeeArea`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportAbsence`
--
ALTER TABLE `reportAbsence`
  ADD CONSTRAINT `reportAbsence_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reportAbsence_ibfk_2` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportCommendable`
--
ALTER TABLE `reportCommendable`
  ADD CONSTRAINT `reportCommendable_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportComments`
--
ALTER TABLE `reportComments`
  ADD CONSTRAINT `reportComments_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportPerformanceReviewed`
--
ALTER TABLE `reportPerformanceReviewed`
  ADD CONSTRAINT `reportPerformanceReviewed_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportPolicyReminder`
--
ALTER TABLE `reportPolicyReminder`
  ADD CONSTRAINT `reportPolicyReminder_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reportPolicyReminder_ibfk_2` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportSecurityViolation`
--
ALTER TABLE `reportSecurityViolation`
  ADD CONSTRAINT `reportSecurityViolation_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reportSecurityViolation_ibfk_2` FOREIGN KEY (`submitter`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reportTardy`
--
ALTER TABLE `reportTardy`
  ADD CONSTRAINT `reportTardy_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reportTardy_ibfk_2` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `routineTaskLog`
--
ALTER TABLE `routineTaskLog`
  ADD CONSTRAINT `routineTaskLog_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `routineTasks`
--
ALTER TABLE `routineTasks`
  ADD CONSTRAINT `routineTasks_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleDefault`
--
ALTER TABLE `scheduleDefault`
  ADD CONSTRAINT `scheduleDefault_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleDefault_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleDefault_ibfk_5` FOREIGN KEY (`hourType`) REFERENCES `scheduleHourTypes` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleDefault_ibfk_6` FOREIGN KEY (`period`) REFERENCES `scheduleSemesters` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `scheduleHourRequests`
--
ALTER TABLE `scheduleHourRequests`
  ADD CONSTRAINT `scheduleHourRequests_ibfk_1` FOREIGN KEY (`netId`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scheduleHourRequests_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleHourTypes`
--
ALTER TABLE `scheduleHourTypes`
  ADD CONSTRAINT `scheduleHourTypes_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleNotes`
--
ALTER TABLE `scheduleNotes`
  ADD CONSTRAINT `scheduleNotes_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scheduleNotes_ibfk_2` FOREIGN KEY (`semester`) REFERENCES `scheduleSemesters` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleSemesters`
--
ALTER TABLE `scheduleSemesters`
  ADD CONSTRAINT `scheduleSemesters_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scheduleTradeBids`
--
ALTER TABLE `scheduleTradeBids`
  ADD CONSTRAINT `scheduleTradeBids_ibfk_1` FOREIGN KEY (`tradeID`) REFERENCES `scheduleTrades` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scheduleTrades`
--
ALTER TABLE `scheduleTrades`
  ADD CONSTRAINT `scheduleTrades_ibfk_1` FOREIGN KEY (`postedBy`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleTrades_ibfk_5` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `scheduleWeekly`
--
ALTER TABLE `scheduleWeekly`
  ADD CONSTRAINT `scheduleWeekly_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employee` (`netID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleWeekly_ibfk_3` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `scheduleWeekly_ibfk_4` FOREIGN KEY (`hourType`) REFERENCES `scheduleHourTypes` (`ID`),
  ADD CONSTRAINT `scheduleWeekly_ibfk_5` FOREIGN KEY (`defaultID`) REFERENCES `scheduleDefault` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `silentMonitorCallCriteria`
--
ALTER TABLE `silentMonitorCallCriteria`
  ADD CONSTRAINT `silentMonitorCallCriteria_ibfk_1` FOREIGN KEY (`smid`) REFERENCES `silentMonitorCalls` (`smid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `silentMonitorCallCriteria_ibfk_2` FOREIGN KEY (`callNum`) REFERENCES `silentMonitorCalls` (`callNum`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `silentMonitorCalls`
--
ALTER TABLE `silentMonitorCalls`
  ADD CONSTRAINT `silentMonitorCalls_ibfk_1` FOREIGN KEY (`smid`) REFERENCES `silentMonitor` (`index`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `silentMonitorCriteriaInfo`
--
ALTER TABLE `silentMonitorCriteriaInfo`
  ADD CONSTRAINT `silentMonitorCriteriaInfo_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supervisorReportSDTasks`
--
ALTER TABLE `supervisorReportSDTasks`
  ADD CONSTRAINT `supervisorReportSDTasks_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supNotes`
--
ALTER TABLE `supNotes`
  ADD CONSTRAINT `supNotes_ibfk_1` FOREIGN KEY (`submittedBy`) REFERENCES `employee` (`netID`),
  ADD CONSTRAINT `supNotes_ibfk_2` FOREIGN KEY (`clearedBy`) REFERENCES `employee` (`netID`);

--
-- Constraints for table `supReport`
--
ALTER TABLE `supReport`
  ADD CONSTRAINT `supReport_ibfk_1` FOREIGN KEY (`submittedBy`) REFERENCES `employee` (`netID`) ON UPDATE CASCADE;

--
-- Constraints for table `tag`
--
ALTER TABLE `tag`
  ADD CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teaming`
--
ALTER TABLE `teaming`
  ADD CONSTRAINT `teaming_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teaming_ibfk_2` FOREIGN KEY (`supervisorID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teamingLog`
--
ALTER TABLE `teamingLog`
  ADD CONSTRAINT `teamingLog_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teamingLog_ibfk_3` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teamMembers`
--
ALTER TABLE `teamMembers`
  ADD CONSTRAINT `teamMembers_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teamMembers_ibfk_2` FOREIGN KEY (`teamID`) REFERENCES `teams` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teamMembers_ibfk_3` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`area`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `whiteboard`
--
ALTER TABLE `whiteboard`
  ADD CONSTRAINT `whiteboard_ibfk_1` FOREIGN KEY (`type`) REFERENCES `tag` (`typeId`);

--
-- Constraints for table `whiteboardAreas`
--
ALTER TABLE `whiteboardAreas`
  ADD CONSTRAINT `whiteboardAreas_ibfk_1` FOREIGN KEY (`whiteboardId`) REFERENCES `whiteboard` (`messageId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `whiteboardAreas_ibfk_2` FOREIGN KEY (`areaId`) REFERENCES `employeeAreas` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `whiteboardMandatoryLog`
--
ALTER TABLE `whiteboardMandatoryLog`
  ADD CONSTRAINT `whiteboardMandatoryLog_ibfk_1` FOREIGN KEY (`netID`) REFERENCES `employee` (`netID`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
