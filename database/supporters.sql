-- Gumbalkán Supporters Community Wall
-- UTF8MB4 charset throughout

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection = utf8mb4;

-- ─── supporters ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_supporters` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nickname`         VARCHAR(50)     NOT NULL,
  `email`            VARCHAR(255)    NOT NULL,
  `whatsapp_number`  VARCHAR(30)     DEFAULT NULL,
  `whatsapp_group`   VARCHAR(100)    DEFAULT NULL,
  `wants_community`  TINYINT(1)      NOT NULL DEFAULT 0,
  `ip_address`       VARCHAR(45)     NOT NULL DEFAULT '',
  `is_founding`      TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nickname` (`nickname`),
  UNIQUE KEY `uq_email`    (`email`),
  KEY `idx_created_at`     (`created_at`),
  KEY `idx_ip_address`     (`ip_address`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── admin_users ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── rate_limits ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_rate_limits` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45)  NOT NULL,
  `action`     VARCHAR(50)  NOT NULL DEFAULT 'register',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_action`  (`ip_address`, `action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── reels (Instagram videa spravovaná z adminu) ───────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_reels` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `url`        VARCHAR(255) NOT NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── sponsors (firemní partneři spravovaní z adminu) ────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_sponsors` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `logo_path`  VARCHAR(255) NOT NULL DEFAULT '',
  `url`        VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── photos (foto galerie spravovaná z adminu) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `GUM_photos` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_path`  VARCHAR(255) NOT NULL,
  `caption`    VARCHAR(160) NOT NULL DEFAULT '',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
