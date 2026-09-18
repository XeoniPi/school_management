-- ============================================================
--  KMA — Migration: Accounts module + Role-Based Access Control
--  Run this once on your LIVE database (kma_school) via
--  phpMyAdmin → kma_school → SQL tab → paste & Go
-- ============================================================

-- ── 1. Extend admin_users.role to add the two new roles ──
ALTER TABLE `admin_users`
  MODIFY `role` ENUM('super_admin','admin','editor','accounts','moderator') NOT NULL DEFAULT 'editor';

-- ── 2. Transactions ledger (income + expense, one unified table) ──
CREATE TABLE IF NOT EXISTS `transactions` (
  `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `invoice_no`       VARCHAR(30)    NULL UNIQUE,
  `type`             ENUM('income','expense') NOT NULL,
  `category`         VARCHAR(40)    NOT NULL,
  `amount`           DECIMAL(12,2)  NOT NULL,
  `transaction_date` DATE           NOT NULL,
  `payment_method`   VARCHAR(30)    NULL,
  `remark`           VARCHAR(255)   NULL,
  `party_type`       ENUM('student','faculty','other') NOT NULL DEFAULT 'other',
  `student_id`       INT UNSIGNED   NULL,
  `faculty_id`       INT UNSIGNED   NULL,
  `party_name`       VARCHAR(150)   NULL,
  `created_by`       INT UNSIGNED   NOT NULL,
  `created_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_category` (`category`),
  INDEX `idx_date` (`transaction_date`),
  INDEX `idx_student` (`student_id`),
  INDEX `idx_faculty` (`faculty_id`),
  CONSTRAINT `transactions_ibfk_student` FOREIGN KEY (`student_id`) REFERENCES `admissions`(`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`)    ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_admin`   FOREIGN KEY (`created_by`) REFERENCES `admin_users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Per-user, per-module granular permissions ──
CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `admin_id`   INT UNSIGNED  NOT NULL,
  `module_key` VARCHAR(40)   NOT NULL,
  `can_read`   TINYINT(1)    NOT NULL DEFAULT 0,
  `can_insert` TINYINT(1)    NOT NULL DEFAULT 0,
  `can_edit`   TINYINT(1)    NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1)    NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_module` (`admin_id`,`module_key`),
  CONSTRAINT `admin_permissions_ibfk_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
