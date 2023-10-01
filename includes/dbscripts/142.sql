ALTER TABLE `%PREFIX%ally_charge` MODIFY `desc` text NOT NULL;
ALTER TABLE `%PREFIX%ally_charge` MODIFY `id_ally` int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE `%PREFIX%ally_charge` MODIFY `img` varchar(320) NOT NULL;
ALTER TABLE `%PREFIX%ally_charge` MODIFY `name` varchar(60) NOT NULL;
CREATE TABLE IF NOT EXISTS `%PREFIX%ally_invites` (
  `id` int(11) UNSIGNED NOT NULL,
  `allyId` int(11) UNSIGNED NOT NULL,
  `to_userId` int(11) UNSIGNED NOT NULL,
  `from_userId` int(11) UNSIGNED NOT NULL,
  `charge_id` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `%PREFIX%ally_user_role` MODIFY `allyId` int(11) UNSIGNED NOT NULL;
ALTER TABLE `%PREFIX%ally_user_role` MODIFY `chargeId` int(11) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `%PREFIX%news` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Nessun Titolo',
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(320) COLLATE utf8_unicode_ci NOT NULL,
  `graphic` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `%PREFIX%t_unt` MODIFY `def_archer` int(5) NOT NULL;
ALTER TABLE `%PREFIX%t_unt` MODIFY `def_horse` int(5) NOT NULL;
ALTER TABLE `%PREFIX%t_unt` MODIFY `def` int(5) UNSIGNED NOT NULL DEFAULT '5';
ALTER TABLE `%PREFIX%users` ADD `points_attack` int(11) NOT NULL;
ALTER TABLE `%PREFIX%users` ADD `points_defence` int(11) NOT NULL;
ALTER TABLE `%PREFIX%users` ADD `points_supporter` int(11) NOT NULL;


UPDATE `%PREFIX%conf` SET `sge_ver`= 143 WHERE `sge_ver`= 142;