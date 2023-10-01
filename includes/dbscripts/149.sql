ALTER TABLE `%PREFIX%mapcityimage` ADD `abbandoned` BOOLEAN NOT NULL DEFAULT FALSE AFTER `image`;
ALTER TABLE `%PREFIX%mapcityimage` ADD `bonus` BOOLEAN NOT NULL DEFAULT FALSE AFTER `abbandoned`;

UPDATE `%PREFIX%conf` SET `sge_ver`= 150 WHERE `sge_ver`= 149;