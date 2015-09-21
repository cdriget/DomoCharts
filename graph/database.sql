--------------------------------------------------------------------------------
---- File    : database.sql                                                 ----
---- Author  : Christophe DRIGET                                            ----
---- Version : 5.0                                                          ----
---- History : September 2015 : Initial release                             ----
---- Note    : MySQL Database queries                                       ----
--------------------------------------------------------------------------------

-- --------------------------------------------------------
-- Structure des tables
-- --------------------------------------------------------

--
-- Structure de la table `domotique_battery_day`
--

CREATE TABLE IF NOT EXISTS `domotique_battery_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `value` tinyint(3) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_co2`
--

CREATE TABLE IF NOT EXISTS `domotique_co2` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_co2_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` smallint(5) unsigned DEFAULT NULL,
  `avg_value` smallint(5) unsigned DEFAULT NULL,
  `max_value` smallint(5) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_device`
--

CREATE TABLE IF NOT EXISTS `domotique_device` (
  `id` mediumint(9) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `room_id` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_device_type`
--

CREATE TABLE IF NOT EXISTS `domotique_device_type` (
  `device_id` mediumint(9) NOT NULL,
  `type_id` smallint(5) unsigned NOT NULL,
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `ordre` mediumint(8) unsigned DEFAULT NULL,
  `color` char(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_energy_day`
--

CREATE TABLE IF NOT EXISTS `domotique_energy_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `base_value` decimal(7,3) DEFAULT NULL,
  `hc_value` decimal(7,3) DEFAULT NULL,
  `hp_value` decimal(7,3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_humidity`
--

CREATE TABLE IF NOT EXISTS `domotique_humidity` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_humidity_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` tinyint(3) unsigned DEFAULT NULL,
  `avg_value` tinyint(3) unsigned DEFAULT NULL,
  `max_value` tinyint(3) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_humidity_month` (
  `id` int(10) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` tinyint(3) unsigned DEFAULT NULL,
  `min_day_value` tinyint(3) unsigned DEFAULT NULL,
  `avg_value` tinyint(3) unsigned DEFAULT NULL,
  `max_day_value` tinyint(3) unsigned DEFAULT NULL,
  `max_value` tinyint(3) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_light`
--

CREATE TABLE IF NOT EXISTS `domotique_light` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_light_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` smallint(5) unsigned DEFAULT NULL,
  `avg_value` smallint(5) unsigned DEFAULT NULL,
  `max_value` smallint(5) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_noise`
--

CREATE TABLE IF NOT EXISTS `domotique_noise` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_noise_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` tinyint(3) unsigned DEFAULT NULL,
  `avg_value` tinyint(3) unsigned DEFAULT NULL,
  `max_value` tinyint(3) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_power`
--

CREATE TABLE IF NOT EXISTS `domotique_power` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` decimal(7,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_power_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` decimal(7,2) DEFAULT NULL,
  `avg_value` decimal(7,2) DEFAULT NULL,
  `max_value` decimal(7,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_pressure`
--

CREATE TABLE IF NOT EXISTS `domotique_pressure` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` decimal(5,1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_pressure_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` decimal(5,1) DEFAULT NULL,
  `avg_value` decimal(5,1) DEFAULT NULL,
  `max_value` decimal(5,1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_rain`
--

CREATE TABLE IF NOT EXISTS `domotique_rain` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_room`
--

CREATE TABLE IF NOT EXISTS `domotique_room` (
  `room_id` smallint(6) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `section_id` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_temperature`
--

CREATE TABLE IF NOT EXISTS `domotique_temperature` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` decimal(5,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_temperature_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` decimal(5,2) DEFAULT NULL,
  `avg_value` decimal(5,2) DEFAULT NULL,
  `max_value` decimal(5,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_temperature_month` (
  `id` int(10) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` decimal(5,2) DEFAULT NULL,
  `min_day_value` decimal(5,2) DEFAULT NULL,
  `avg_value` decimal(5,2) DEFAULT NULL,
  `max_day_value` decimal(5,2) DEFAULT NULL,
  `max_value` decimal(5,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure de la table `domotique_type`
--

CREATE TABLE IF NOT EXISTS `domotique_type` (
  `id` smallint(5) unsigned NOT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_water`
--

CREATE TABLE IF NOT EXISTS `domotique_water` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_water_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `sum_value` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_water_month` (
  `id` int(10) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_day_value` SMALLINT unsigned NULL DEFAULT NULL,
  `sum_value` smallint(5) unsigned NULL DEFAULT NULL,
  `max_day_value` SMALLINT unsigned NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Structure des tables `domotique_wind`
--

CREATE TABLE IF NOT EXISTS `domotique_wind` (
  `id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` smallint(6) NOT NULL,
  `value` decimal(5,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domotique_wind_day` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `device_id` smallint(6) NOT NULL,
  `min_value` decimal(5,2) DEFAULT NULL,
  `avg_value` decimal(5,2) DEFAULT NULL,
  `max_value` decimal(5,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Index des tables
-- --------------------------------------------------------

ALTER TABLE `domotique_battery_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_co2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_co2_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`room_id`);

ALTER TABLE `domotique_device_type`
  ADD PRIMARY KEY (`device_id`,`type_id`);

ALTER TABLE `domotique_energy_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_humidity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_humidity_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_humidity_month`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`device_id`,`year`,`month`);

ALTER TABLE `domotique_light`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_light_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_noise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_noise_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_power`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_power_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_pressure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_pressure_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_rain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_room`
  ADD PRIMARY KEY (`room_id`);

ALTER TABLE `domotique_temperature`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_temperature_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

ALTER TABLE `domotique_temperature_month`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`device_id`,`year`,`month`);

ALTER TABLE `domotique_type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `domotique_water`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_water_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`);

ALTER TABLE `domotique_water_month`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`device_id`,`year`,`month`);

ALTER TABLE `domotique_wind`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device` (`device_id`,`time`);

ALTER TABLE `domotique_wind_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_date` (`date`,`device_id`),
  ADD KEY `device_id` (`device_id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT des tables
-- --------------------------------------------------------

--
-- AUTO_INCREMENT pour la table `domotique_battery_day`
--
ALTER TABLE `domotique_battery_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_co2`
--
ALTER TABLE `domotique_co2`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_co2_day`
--
ALTER TABLE `domotique_co2_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_energy_day`
--
ALTER TABLE `domotique_energy_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour les tables `domotique_humidity`
--
ALTER TABLE `domotique_humidity`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `domotique_humidity_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `domotique_humidity_month`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `domotique_light`
--
ALTER TABLE `domotique_light`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_light_day`
--
ALTER TABLE `domotique_light_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_noise`
--
ALTER TABLE `domotique_noise`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_noise_day`
--
ALTER TABLE `domotique_noise_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_power`
--
ALTER TABLE `domotique_power`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_power_day`
--
ALTER TABLE `domotique_power_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_pressure`
--
ALTER TABLE `domotique_pressure`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_pressure_day`
--
ALTER TABLE `domotique_pressure_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_rain`
--
ALTER TABLE `domotique_rain`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour les tables `domotique_temperature`
--
ALTER TABLE `domotique_temperature`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `domotique_temperature_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `domotique_temperature_month`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_type`
--
ALTER TABLE `domotique_type`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_water`
--
ALTER TABLE `domotique_water`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_water_day`
--
ALTER TABLE `domotique_water_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `domotique_water_month`
--
ALTER TABLE `domotique_water_month`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour les tables `domotique_wind`
--

ALTER TABLE `domotique_wind`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `domotique_wind_day`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Changement de schema
-- --------------------------------------------------------

DROP TABLE IF EXISTS `domotique_battery`;

ALTER TABLE `domotique_battery_day` CHANGE COLUMN `avg_value` `value` tinyint(3) unsigned DEFAULT NULL;
ALTER TABLE `domotique_battery_day` DROP COLUMN `min_value`;
ALTER TABLE `domotique_battery_day` DROP COLUMN `max_value`;
