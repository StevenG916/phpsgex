ALTER TABLE `%PREFIX%user_message` ADD `time` TIMESTAMP NOT NULL AFTER `mtit`;
UPDATE `%PREFIX%conf` SET `sge_ver`= 137 WHERE `sge_ver`= 136;