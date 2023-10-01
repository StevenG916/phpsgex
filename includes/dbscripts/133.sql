ALTER TABLE `%PREFIX%t_builds` ADD `opthtml` TEXT NOT NULL AFTER `imgtags`;
UPDATE `%PREFIX%conf` SET `sge_ver` = '134' WHERE `sge_ver` = '133';