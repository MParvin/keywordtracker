-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Mrz 2015 um 15:50
-- Server Version: 5.6.23
-- PHP-Version: 5.3.10-1ubuntu3.16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `c1monitor`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `seotracker_keywords`
--

CREATE TABLE IF NOT EXISTS `seotracker_keywords` (
  `kwID` int(11) NOT NULL AUTO_INCREMENT,
  `kwText` varchar(255) NOT NULL,
  `kwComment` text NOT NULL,
  `pID` int(3) NOT NULL,
  `kwTime` int(5) NOT NULL,
  `kwAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `kwUpdated` datetime NOT NULL,
  PRIMARY KEY (`kwID`),
  UNIQUE KEY `kwID` (`kwID`,`pID`),
  UNIQUE KEY `kwText_2` (`kwText`,`pID`),
  KEY `pID` (`pID`),
  KEY `kwText` (`kwText`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1489 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `seotracker_projects`
--

CREATE TABLE IF NOT EXISTS `seotracker_projects` (
  `pID` int(11) NOT NULL AUTO_INCREMENT,
  `pURL` varchar(255) NOT NULL,
  PRIMARY KEY (`pID`),
  UNIQUE KEY `pURL` (`pURL`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `seotracker_rankings`
--

CREATE TABLE IF NOT EXISTS `seotracker_rankings` (
  `kwpID` int(11) NOT NULL AUTO_INCREMENT,
  `kwID` int(11) NOT NULL,
  `kwPos` int(11) DEFAULT NULL,
  `kwURL` text,
  `kwTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kwpID`),
  KEY `kwID` (`kwID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=94130 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `seotracker_settings`
--

CREATE TABLE IF NOT EXISTS `seotracker_settings` (
  `settingID` int(1) NOT NULL AUTO_INCREMENT,
  `update_time` varchar(55) NOT NULL,
  PRIMARY KEY (`settingID`),
  UNIQUE KEY `settingID` (`settingID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `seotracker_settings`
--

INSERT INTO `seotracker_settings` (`settingID`, `update_time`) VALUES
(1, '0,1,2,3,4,5,6,7,8,9');

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `seotracker_keywords`
--
ALTER TABLE `seotracker_keywords`
  ADD CONSTRAINT `seotracker_keywords_ibfk_1` FOREIGN KEY (`pID`) REFERENCES `seotracker_projects` (`pID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `seotracker_rankings`
--
ALTER TABLE `seotracker_rankings`
  ADD CONSTRAINT `seotracker_rankings_ibfk_1` FOREIGN KEY (`kwID`) REFERENCES `seotracker_keywords` (`kwID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
