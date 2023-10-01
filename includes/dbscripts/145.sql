CREATE TABLE `%PREFIX%mapcityimage` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `points` int(5) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `%PREFIX%city` ADD `points` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `loyality`;

UPDATE `%PREFIX%conf` SET `sge_ver`= 146 WHERE `sge_ver`= 145;