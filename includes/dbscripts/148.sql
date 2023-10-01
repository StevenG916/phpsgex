ALTER TABLE `%PREFIX%conf` ADD `serverEnd` DATETIME NULL DEFAULT NULL AFTER `researchfast_molt`;

UPDATE `%PREFIX%conf` SET `sge_ver`= 149 WHERE `sge_ver`= 148;