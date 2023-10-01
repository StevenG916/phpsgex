ALTER TABLE `%PREFIX%units` ADD `startTime` INT(35) NULL DEFAULT '0' AFTER `action`;
ALTER TABLE `%PREFIX%units` MODIFY `startTime` INT(35) NULL DEFAULT '0' AFTER `where`;

UPDATE `%PREFIX%conf` SET `sge_ver`= 147 WHERE `sge_ver`= 146;