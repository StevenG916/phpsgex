ALTER TABLE `%PREFIX%plugins` DROP `type`;
ALTER TABLE `%PREFIX%plugins` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `%PREFIX%conf` ADD `unit_que_parallel` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `unit_que_max`;
UPDATE `%PREFIX%conf` SET `sge_ver`= 140 WHERE `sge_ver`= 139;