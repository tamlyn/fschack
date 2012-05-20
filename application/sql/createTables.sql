-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 20, 2012 at 10:04 AM
-- Server version: 5.0.95
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `outlandish_fsc`
--

-- --------------------------------------------------------

--
-- Table structure for table `file_formats`
--

CREATE TABLE IF NOT EXISTS `file_formats` (
  `id` int(11) NOT NULL auto_increment,
  `hash` varchar(100) character set utf8 NOT NULL,
  `fields` varchar(1000) character set utf8 NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `investigations`
--

CREATE TABLE IF NOT EXISTS `investigations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `startDate` date DEFAULT NULL,
  `schoolName` varchar(255) DEFAULT NULL,
  `centre` varchar(255) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

CREATE TABLE IF NOT EXISTS `measurements` (
  `id` int(11) NOT NULL auto_increment,
  `siteInvestigationId` int(11) default NULL,
  `type` varchar(255) default NULL,
  `investigationSeriesIndex` int(11) default NULL,
  `value` float default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `investigationSeriesIndex` (`investigationSeriesIndex`),
  KEY `type` (`type`),
  KEY `value` (`value`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=271 ;

-- --------------------------------------------------------

--
-- Table structure for table `sitealias`
--

CREATE TABLE IF NOT EXISTS `sitealias` (
  `site_id` int(11) default NULL,
  `alias` varchar(255) default NULL,
  `centre` varchar(255) default NULL,
  KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `siteinvestigations`
--

CREATE TABLE IF NOT EXISTS `siteinvestigations` (
  `id` int(11) NOT NULL auto_increment,
  `siteId` int(11) default NULL,
  `investigationId` int(11) default NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `siteOrder` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `siteId` (`siteId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL auto_increment,
  `lat` float default NULL,
  `lon` float default NULL,
  `title` varchar(255) default NULL,
  `centre` varchar(255) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `lat` (`lat`),
  KEY `lon` (`lon`),
  KEY `centre` (`centre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
