-- ============================================================
--  KMA — Migration: admission source tracking
--  Run this once on your LIVE database (kma_school) via
--  phpMyAdmin → kma_school → SQL tab → paste & Go
--
--  Why: website submissions should sit as a "request" (pending)
--  until a staff member Accepts/Denies it, while an admission a
--  staff member enters directly from the dashboard should be
--  confirmed immediately. This column tells them apart.
-- ============================================================

ALTER TABLE `admissions`
  ADD COLUMN `source` ENUM('website','staff') NOT NULL DEFAULT 'website' AFTER `status`;
