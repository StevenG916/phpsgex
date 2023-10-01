ALTER TABLE `%PREFIX%conf` CHANGE `max_unit_que` `unit_que_max` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0', CHANGE `max_research_que` `research_que_max` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0', CHANGE `max_build_que` `build_que_max` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `%PREFIX%conf` ADD `build_que_parallel` SMALLINT(2) NOT NULL DEFAULT 0 AFTER `build_que_max`;
ALTER TABLE `%PREFIX%conf` CHANGE `sge_ver` `sge_ver` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 135;
UPDATE `%PREFIX%conf` SET `sge_ver`= 135 WHERE `sge_ver`= 134;