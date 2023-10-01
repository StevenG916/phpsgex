ALTER TABLE `%PREFIX%t_builds` ADD `imgtags` TEXT NOT NULL AFTER `maxlev`;
UPDATE `%PREFIX%conf` SET `sge_ver` = '133' WHERE `sge_ver` = '132';