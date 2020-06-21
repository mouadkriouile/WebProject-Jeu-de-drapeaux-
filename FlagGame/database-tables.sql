-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.11-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table flag_game.continents
DROP TABLE IF EXISTS `continents`;
CREATE TABLE IF NOT EXISTS `continents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `continent` varchar(32) DEFAULT NULL COMMENT 'Continent name',
  `name` varchar(32) DEFAULT NULL COMMENT 'Continent name in french',
  `center` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Bounds [north, west, south, east] ',
  `visitor_allowed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`continent`),
  KEY `keys` (`continent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table flag_game.countries
DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cca3` varchar(3) DEFAULT NULL COMMENT 'Country Code Alpha-3',
  `continent` varchar(32) DEFAULT NULL COMMENT 'Continent',
  `name` varchar(32) DEFAULT NULL,
  `geojson` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`cca3`),
  KEY `keys` (`cca3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table flag_game.params
DROP TABLE IF EXISTS `params`;
CREATE TABLE IF NOT EXISTS `params` (
  `name` tinytext DEFAULT NULL,
  `value` text DEFAULT NULL,
  KEY `keys` (`name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table flag_game.quizzes
DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `continent` varchar(64) NOT NULL DEFAULT '0',
  `countries` varchar(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `keys` (`continent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table flag_game.stats
DROP TABLE IF EXISTS `stats`;
CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `quiz_result` text DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `time_multiplayer` int(11) DEFAULT NULL,
  `score` decimal(10,2) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keys` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table flag_game.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '0',
  `password` varchar(128) NOT NULL DEFAULT '0',
  `registration_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`email`),
  KEY `keys` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;