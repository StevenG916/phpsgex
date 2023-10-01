ALTER TABLE `%PREFIX%city_research_que` ADD `level` INT(3) UNSIGNED NOT NULL AFTER `res_id`;
ALTER TABLE `%PREFIX%conf` DROP `popaddpl`;
UPDATE `%PREFIX%conf` SET `sge_ver`= 136 WHERE `sge_ver`= 135;