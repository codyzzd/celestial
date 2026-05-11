-- Celestial database structure export
-- Generated at: 2026-05-11 16:34:27 UTC
-- Source database: `u821249804_caravana`
-- Data rows are intentionally not included.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `stakes`;
CREATE TABLE `stakes` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cod` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cod` (`cod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` varchar(36) NOT NULL,
  `id_stake` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `obs` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `seat_map` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_stake` (`id_stake`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`id_stake`) REFERENCES `stakes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `caravans`;
CREATE TABLE `caravans` (
  `id` varchar(36) NOT NULL,
  `destination` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `return_date` date NOT NULL,
  `return_time` time NOT NULL,
  `obs` text DEFAULT NULL,
  `id_stake` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `total_seats` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_stake` (`id_stake`),
  CONSTRAINT `caravans_ibfk_1` FOREIGN KEY (`id_stake`) REFERENCES `stakes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `caravan_vehicles`;
CREATE TABLE `caravan_vehicles` (
  `id` varchar(36) NOT NULL,
  `id_caravan` varchar(36) NOT NULL,
  `id_vehicle` varchar(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_vehicle` (`id_vehicle`),
  KEY `caravan_vehicles_ibfk_2` (`id_caravan`),
  CONSTRAINT `caravan_vehicles_ibfk_1` FOREIGN KEY (`id_vehicle`) REFERENCES `vehicles` (`id`),
  CONSTRAINT `caravan_vehicles_ibfk_2` FOREIGN KEY (`id_caravan`) REFERENCES `caravans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `destinations`;
CREATE TABLE `destinations` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sexs`;
CREATE TABLE `sexs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `slug` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `wards`;
CREATE TABLE `wards` (
  `id` varchar(36) NOT NULL,
  `id_stake` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cod` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cod` (`cod`),
  KEY `idx_id_stake` (`id_stake`) USING BTREE,
  CONSTRAINT `wards_ibfk_1` FOREIGN KEY (`id_stake`) REFERENCES `stakes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `relationship`;
CREATE TABLE `relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `id_stake` varchar(36) DEFAULT NULL,
  `id_ward` varchar(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `role` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remember_token` varchar(32) DEFAULT NULL,
  `reset_token` varchar(32) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_remember_token` (`remember_token`),
  KEY `idx_id_stake` (`id_stake`),
  KEY `idx_role` (`role`),
  KEY `idx_id_ward` (`id_ward`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_stake`) REFERENCES `stakes` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`id_ward`) REFERENCES `wards` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `passengers`;
CREATE TABLE `passengers` (
  `id` varchar(36) NOT NULL,
  `id_ward` varchar(36) NOT NULL,
  `id_church` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `nasc_date` date NOT NULL,
  `sex` int(11) NOT NULL,
  `id_document` int(11) NOT NULL,
  `document` varchar(255) NOT NULL,
  `obs` mediumtext DEFAULT NULL,
  `id_relationship` int(11) NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `barcode` varchar(20) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_document` (`id_document`),
  KEY `idx_sex` (`sex`),
  KEY `idx_user` (`created_by`),
  KEY `idx_ward` (`id_ward`),
  KEY `idx_relationship` (`id_relationship`) USING BTREE,
  CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`id_document`) REFERENCES `documents` (`id`),
  CONSTRAINT `passengers_ibfk_2` FOREIGN KEY (`sex`) REFERENCES `sexs` (`id`),
  CONSTRAINT `passengers_ibfk_4` FOREIGN KEY (`id_ward`) REFERENCES `wards` (`id`),
  CONSTRAINT `passengers_ibfk_5` FOREIGN KEY (`id_relationship`) REFERENCES `relationship` (`id`),
  CONSTRAINT `passengers_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `role_alt`;
CREATE TABLE `role_alt` (
  `id` varchar(36) NOT NULL,
  `role` int(11) NOT NULL,
  `stake_id` varchar(36) NOT NULL,
  `ward_id` varchar(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expire_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `seats`;
CREATE TABLE `seats` (
  `id` varchar(36) NOT NULL,
  `id_caravan_vehicle` varchar(36) DEFAULT NULL,
  `id_caravan` varchar(36) NOT NULL,
  `id_passenger` varchar(36) NOT NULL,
  `seat` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_payed` tinyint(1) DEFAULT 0,
  `created_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `no_seat` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_caravan` (`id_caravan`),
  KEY `id_caravan_vehicle` (`id_caravan_vehicle`),
  CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`id_caravan_vehicle`) REFERENCES `caravan_vehicles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
