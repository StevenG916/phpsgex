CREATE TABLE `%PREFIX%ally_user_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `allyId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `chargeId` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `%PREFIX%ally_charge` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_ally` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `desc` text NOT NULL,
  `img` varchar(320) NOT NULL,
  `admin` int(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE `%PREFIX%conf` SET `sge_ver`= 141 WHERE `sge_ver`= 140;