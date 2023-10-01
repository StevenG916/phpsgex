DROP TABLE IF EXISTS %PREFIX%mapcityimage;

CREATE TABLE `%PREFIX%mapcityimage` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `points` int(5) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

UPDATE `%PREFIX%conf` SET `sge_ver`= 148 WHERE `sge_ver`= 147;