-- phpMyAdmin SQL Dump
-- version 3.5.0-rc1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 19, 2012 at 07:03 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fsc`
--

-- --------------------------------------------------------

--
-- Table structure for table `investigation`
--

CREATE TABLE IF NOT EXISTS `investigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startDate` date DEFAULT NULL,
  `schoolName` varchar(255) DEFAULT NULL,
  `centre` varchar(255) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

CREATE TABLE IF NOT EXISTS `measurements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteInvestigationId` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `investigationSeriesIndex` int(11) DEFAULT NULL,
  `value` float DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `investigationSeriesIndex` (`investigationSeriesIndex`),
  KEY `type` (`type`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sitealias`
--

CREATE TABLE IF NOT EXISTS `sitealias` (
  `site_id` int(11) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `centre` varchar(255) DEFAULT NULL,
  KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `siteinvestigations`
--

CREATE TABLE IF NOT EXISTS `siteinvestigations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteId` int(11) DEFAULT NULL,
  `investigationId` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  KEY `siteId` (`siteId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `centre` varchar(255) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `lat` (`lat`),
  KEY `lon` (`lon`),
  KEY `centre` (`centre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_formats`
--

CREATE TABLE IF NOT EXISTS `file_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(100) CHARACTER SET utf8 NOT NULL,
  `fields` varchar(1000) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
