# ************************************************************
# Sequel Pro SQL dump
# Version 5446
#
# https://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.5.5-10.4.19-MariaDB)
# Database: aspra_v1_2019
# Generation Time: 2021-09-06 03:56:17 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table client_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `client_tokens`;

CREATE TABLE `client_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(191) NOT NULL DEFAULT ' ',
  `somewords` varchar(255) NOT NULL DEFAULT '',
  `revoked` tinyint(3) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `client_tokens` WRITE;
/*!40000 ALTER TABLE `client_tokens` DISABLE KEYS */;

INSERT INTO `client_tokens` (`id`, `group`, `somewords`, `revoked`, `created_at`, `updated_at`)
VALUES
	(1,'admin','z2ykDPFSGz9maqtKKNxLRg/C3UfQG6n6bXwRzWRCack=',0,'2021-08-22 16:22:39','2021-08-22 16:22:42'),
	(2,'users','nIzZXanroFR8KoKjTC1eneN/WHoED29Y7BpmFfD+5mk=',0,'2021-08-22 16:22:55','2021-08-22 16:22:55');

/*!40000 ALTER TABLE `client_tokens` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_tokens`;

CREATE TABLE `user_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned NOT NULL,
  `client_id` int(11) unsigned NOT NULL,
  `revoked` tinyint(3) NOT NULL DEFAULT 0,
  `expire_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user_id_users` (`user_id`),
  KEY `fk_client_id` (`client_id`),
  CONSTRAINT `fk_client_id` FOREIGN KEY (`client_id`) REFERENCES `client_tokens` (`id`),
  CONSTRAINT `fk_user_id_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;

INSERT INTO `user_tokens` (`id`, `token`, `user_id`, `client_id`, `revoked`, `expire_at`, `created_at`, `updated_at`)
VALUES
	(1,'5230453354336c566546525562576c34516e42434d6d4a4561446830616b3943546e63776453746d576b56336356645a5a556c3664334a6d4d6d7042556e4a6e57554e4b65454e77535441764e6d316f626e59774d47684954473949534770434e47645265565272566b30784c30464e4f57633950513d3d',3,2,36,'2021-09-22 22:15:40','2021-08-22 19:58:24','2021-08-23 22:15:40');

/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(191) NOT NULL DEFAULT '',
  `lastname` varchar(191) NOT NULL DEFAULT '',
  `email` varchar(191) NOT NULL DEFAULT '',
  `username` varchar(191) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `verified_at` datetime DEFAULT NULL,
  `remember` varchar(255) NOT NULL DEFAULT ' ',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `USERNAME` (`username`),
  UNIQUE KEY `EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `username`, `password`, `verified_at`, `remember`, `created_at`, `updated_at`)
VALUES
	(3,'johny','walker','admin@localhost.net','admin','$2y$10$OOOH7QBaPmv8LCvrSRMBkuGEVakXgTfhNNPLXsykT6ps5.Oa6VzOi','2021-08-22 22:10:09','Iyg2V2G4vqZFIl1n1CIKYxogQuNAUBf0OCIObs2RlmE+5dn+/4XMjDyt7WmPAkmY$MGJjODJjMWVlMWM1NmRkYzQ1YjRmMmE4Yjc5ZmZjN2Y=','2021-08-21 00:26:41','2021-08-23 22:15:40');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
