--
-- Table structure for table `opens`
--

CREATE TABLE IF NOT EXISTS `opens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `campaign_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `recipient_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ip_address` char(128) NOT NULL,
  `browser_agent` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_image` (`client_image`,`campaign_image`,`recipient_image`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
