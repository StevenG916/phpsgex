ALTER TABLE `%PREFIX%city_build_que` ADD `level` INT(3) NOT NULL AFTER `build`;
ALTER TABLE `%PREFIX%city_build_que` ADD UNIQUE( `city`, `build`, `level`);
UPDATE `%PREFIX%conf` SET `sge_ver` = '132' WHERE `sge_ver` = '131';