SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tmt_permissions`
--
DROP DATABASE IF EXISTS `tmt_permissions`;
CREATE DATABASE IF NOT EXISTS `tmt_permissions` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `tmt_permissions`;


-- --------------------------------------------------------

--
-- Table structure for table `policy`
--

DROP TABLE IF EXISTS `policy`;
CREATE TABLE IF NOT EXISTS `policy` (
  `actor` char(36) NOT NULL,
  `verb` varchar(50) NOT NULL,
  `resource` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `groupMembers`
--

DROP TABLE IF EXISTS `groupMembers`;
CREATE TABLE IF NOT EXISTS `groupMembers` (
  `netId` varchar(255) NOT NULL,
  `groupGuid` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `guid` char(36) NOT NULL,
  `area` char(36) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `superuser`;
CREATE TABLE IF NOT EXISTS `superuser` (
  `netId` char(36) PRIMARY KEY,
  `active` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `netId` varchar(255),
  `area` char(36),
  PRIMARY KEY (`netId`, `area`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `guid` char(36) PRIMARY KEY DEFAULT '00000000-0000-0000-0000-000000000000',
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actor` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `policy`
--
ALTER TABLE `policy`
  ADD PRIMARY KEY (`actor`,`verb`,`resource`);

--
-- Indexes for table `groupMembers`
--
ALTER TABLE `groupMembers`
  ADD PRIMARY KEY (`netId`,`groupGuid`),
  ADD KEY `groupGuid` (`groupGuid`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`guid`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groupMembers`
--
ALTER TABLE `groupMembers`
  ADD CONSTRAINT `groupMembers_ibfk_1` FOREIGN KEY (`groupGuid`) REFERENCES `groups` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;


--
-- Database: `sites_ops_resources`
--
DROP DATABASE IF EXISTS `tmt_resources`;
CREATE DATABASE IF NOT EXISTS `tmt_resources` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `tmt_resources`;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
CREATE TABLE IF NOT EXISTS `resources` (
  `guid` char(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `apiEndpoint` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resourceTypes`
--

DROP TABLE IF EXISTS `resourceTypes`;
CREATE TABLE IF NOT EXISTS `resourceTypes` (
  `guid` char(36) NOT NULL,
  `resourceGUID` char(36) NOT NULL,
  `type` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resourceVerbs`
--

DROP TABLE IF EXISTS `resourceVerbs`;
CREATE TABLE IF NOT EXISTS `resourceVerbs` (
  `guid` char(36) NOT NULL,
  `resourceGUID` char(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `resourceTypes`
--
ALTER TABLE `resourceTypes`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `objectGUID` (`resourceGUID`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `resourceVerbs`
--
ALTER TABLE `resourceVerbs`
  ADD PRIMARY KEY (`guid`),
  ADD KEY `resourceGUID` (`resourceGUID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `resourceTypes`
--
ALTER TABLE `resourceTypes`
  ADD CONSTRAINT `resourceTypes_ibfk_1` FOREIGN KEY (`type`) REFERENCES `resources` (`guid`);

--
-- Constraints for table `resourceVerbs`
--
ALTER TABLE `resourceVerbs`
  ADD CONSTRAINT `resourceVerbs_ibfk_1` FOREIGN KEY (`resourceGUID`) REFERENCES `resources` (`guid`);


SET FOREIGN_KEY_CHECKS = 1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
