SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `opens`
-- ----------------------------
DROP TABLE IF EXISTS `opens`;
CREATE TABLE `opens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `campaign_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `recipient_image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ip_address` char(128) NOT NULL,
  `browser_agent` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_image` (`client_image`) USING BTREE,
  KEY `campaign_image` (`campaign_image`),
  KEY `recipient_image` (`recipient_image`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;