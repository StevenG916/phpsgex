ALTER TABLE `%PREFIX%t_unt` CHANGE `dif` `def` INT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `%PREFIX%t_unt` ADD `def_archer` INT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `build`, ADD `def_horse` INT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `def_archer`;

UPDATE `%PREFIX%conf` SET `sge_ver`= 142 WHERE `sge_ver`= 141;