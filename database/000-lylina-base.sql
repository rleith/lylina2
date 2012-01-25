-- phpMyAdmin SQL Dump
-- version 3.4.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 24, 2012 at 08:43 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `lms_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `adodb_logsql`
--

CREATE TABLE IF NOT EXISTS `adodb_logsql` (
  `created` datetime NOT NULL,
  `sql0` varchar(250) NOT NULL,
  `sql1` text NOT NULL,
  `params` text NOT NULL,
  `tracer` text NOT NULL,
  `timer` decimal(16,6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_feeds`
--

CREATE TABLE IF NOT EXISTS `lylina_feeds` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `fallback_url` varchar(255) NOT NULL,
  `favicon_url` varchar(255) NOT NULL,
  `name` varchar(255) default NULL,
  `lastmod` varchar(255) default NULL,
  `etag` varchar(255) default NULL,
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=424 ;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_items`
--

CREATE TABLE IF NOT EXISTS `lylina_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `feed_id` smallint(5) unsigned NOT NULL default '0',
  `post_id` varchar(512) NOT NULL default '0',
  `length` mediumint(8) unsigned NOT NULL,
  `url` varchar(255) NOT NULL default '',
  `title` varchar(512) NOT NULL default '''no title''',
  `body` mediumtext,
  `dt` datetime NOT NULL default '0000-00-00 00:00:00',
  `viewed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_item` (`feed_id`,`post_id`),
  KEY `dt` (`dt`),
  KEY `feed_id` (`feed_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=631190 ;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_preferences`
--

CREATE TABLE IF NOT EXISTS `lylina_preferences` (
  `name` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_userfeeds`
--

CREATE TABLE IF NOT EXISTS `lylina_userfeeds` (
  `feed_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `feed_name` varchar(255) default NULL,
  PRIMARY KEY  (`feed_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_users`
--

CREATE TABLE IF NOT EXISTS `lylina_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(255) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `magic` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `lylina_vieweditems`
--

CREATE TABLE IF NOT EXISTS `lylina_vieweditems` (
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `viewed` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

