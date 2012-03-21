CREATE TABLE IF NOT EXISTS `lylina_passwordreset` (
  `user_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `valid_until` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`),
  KEY `valid_until` (`valid_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
