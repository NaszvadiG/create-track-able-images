SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `recipients`
-- ----------------------------
DROP TABLE IF EXISTS `recipients`;
CREATE TABLE `recipients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(5) unsigned NOT NULL,
  `recipient_email` char(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `recipient_email_hash` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `image` char(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;