CREATE TABLE `items` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `name_de` varchar(128) DEFAULT NULL,
  `icon` varchar(128) NOT NULL,
  `i_level` smallint(5) unsigned NOT NULL,
  `quality` tinyint(3) unsigned NOT NULL,
  `inv_id` tinyint(3) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `sub_type` tinyint(3) unsigned NOT NULL,
  `col_a` tinyint(3) unsigned NOT NULL,
  `gem_1` tinyint(3) unsigned NOT NULL,
  `gem_2` tinyint(4) NOT NULL,
  `gem_3` tinyint(4) NOT NULL,
  `display_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `locale_skip` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `loadouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(32) NOT NULL,
  `label` varchar(64) NOT NULL,
  `set_name` varchar(64) NOT NULL,
  `items` varchar(200) NOT NULL,
  `gender` varchar(16) NOT NULL,
  `race` varchar(16) NOT NULL,
  `created` datetime NOT NULL,
  `views` bigint(20) unsigned NOT NULL,
  `rating_sum` bigint(20) unsigned NOT NULL,
  `rating_votes` bigint(20) unsigned NOT NULL,
  `rating_score` decimal(2,1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`ref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL,
  `body` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

