-- ============================================================
--  KMA — Migration: login brute-force protection
--  Run this once on your LIVE database (kma_school) via
--  phpMyAdmin → kma_school → SQL tab → paste & Go
-- ============================================================

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45)  NOT NULL,
  `username`   VARCHAR(60)  NULL,
  `attempted_at` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
