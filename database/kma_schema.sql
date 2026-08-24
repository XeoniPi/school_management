-- ============================================================
--  KMA — Khalilullah Memorial Academy
--  Database Schema  |  Version 1.0.0
--  Engine: MySQL 8+ / MariaDB 10.4+
--  Charset: utf8mb4_unicode_ci
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+06:00";  -- Bangladesh Standard Time

-- ─────────────────────────────────────────
--  DATABASE
-- ─────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `kma_school`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `kma_school`;

-- ─────────────────────────────────────────
--  TABLE: admin_users
--  Stores admin / staff login credentials
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(60)      NOT NULL UNIQUE,
  `email`        VARCHAR(120)     NOT NULL UNIQUE,
  `password`     VARCHAR(255)     NOT NULL,           -- bcrypt hash
  `full_name`    VARCHAR(120)     NOT NULL,
  `role`         ENUM('super_admin','admin','editor') NOT NULL DEFAULT 'editor',
  `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
  `last_login`   DATETIME         NULL,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Default admin (password: Admin@1234 — CHANGE IN PRODUCTION)
INSERT INTO `admin_users` (`username`,`email`,`password`,`full_name`,`role`) VALUES
('admin','admin@kma.edu.bd','$2y$12$8QVvjAyvf8sMFfazBEJGkukV5y7XeBUiKRhxpJ/u8AXxlGMi9mEre','System Administrator','super_admin');

-- ─────────────────────────────────────────
--  TABLE: notices
--  Notice board — always newest first
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notices` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)   NOT NULL,
  `content`     LONGTEXT       NOT NULL,
  `category`    ENUM('exam','notice','holiday','event','general') NOT NULL DEFAULT 'general',
  `notice_date` DATE           NOT NULL,
  `file_path`   VARCHAR(255)   NULL,                  -- optional PDF attachment
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  `is_pinned`   TINYINT(1)     NOT NULL DEFAULT 0,    -- pinned notices float to top
  `views`       INT UNSIGNED   NOT NULL DEFAULT 0,
  `created_by`  INT UNSIGNED   NOT NULL,
  `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users`(`id`) ON DELETE RESTRICT,
  INDEX `idx_notice_date` (`notice_date` DESC),
  INDEX `idx_is_active`   (`is_active`),
  INDEX `idx_category`    (`category`)
) ENGINE=InnoDB;

-- Sample notices
INSERT INTO `notices` (`title`,`content`,`category`,`notice_date`,`is_active`,`is_pinned`,`created_by`) VALUES
('অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে',
 'সকল শিক্ষার্থীকে জানানো যাচ্ছে যে, ২০২৬ সালের অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে। অফিস থেকে সংগ্রহ করতে বলা হচ্ছে।',
 'exam','2025-05-22',1,1,1),
('২০২৫-২৬ শিক্ষাবর্ষের ভর্তি কার্যক্রম শুরু',
 'আসন সীমিত, দ্রুত ভর্তি নিশ্চিত করুন। বিস্তারিত জানতে অফিসে যোগাযোগ করুন।',
 'notice','2025-05-18',1,0,1),
('ঈদুল আযহা উপলক্ষে ছুটি',
 '১০ জুন থেকে ১৮ জুন পর্যন্ত বিদ্যালয় বন্ধ থাকবে।',
 'holiday','2025-05-15',1,0,1),
('বার্ষিক পুরস্কার বিতরণী অনুষ্ঠান',
 'বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান আগামী ২৮ মে অনুষ্ঠিত হবে।',
 'event','2025-05-10',1,0,1),
('মাসিক মূল্যায়ন পরীক্ষা',
 '২ জুন থেকে মাসিক মূল্যায়ন পরীক্ষা শুরু হবে।',
 'exam','2025-05-05',1,0,1),
('অভিভাবক সমাবেশ',
 '২৫ মে রবিবার সকাল ১০টায় বিদ্যালয় মিলনায়তনে অভিভাবক সমাবেশ অনুষ্ঠিত হবে।',
 'notice','2025-05-01',1,0,1);

-- ─────────────────────────────────────────
--  TABLE: classes
--  Pre-primary through Grade 5
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `classes` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `class_key`   VARCHAR(10)    NOT NULL UNIQUE,       -- pk, c1, c2 ... c5
  `class_name`  VARCHAR(80)    NOT NULL,              -- বাংলা
  `class_name_en` VARCHAR(80)  NOT NULL,              -- English
  `age_range`   VARCHAR(20)    NOT NULL,
  `description` TEXT           NULL,
  `total_seats` SMALLINT       NOT NULL DEFAULT 60,
  `sort_order`  TINYINT        NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `classes` VALUES
(1,'pk','প্রাক-প্রাথমিক','Pre-Primary','৪–৫ বছর','খেলার ছলে শেখার মাধ্যমে মৌলিক দক্ষতা অর্জন।',60,0,1),
(2,'c1','প্রথম শ্রেণি','Class One','৫–৬ বছর','জাতীয় পাঠ্যক্রম অনুসরণ করে মূল বিষয়ে ভিত্তি গড়ে তোলা।',60,1,1),
(3,'c2','দ্বিতীয় শ্রেণি','Class Two','৬–৭ বছর','ভাষাগত দক্ষতা ও বিজ্ঞান সচেতনতা বিকাশ।',50,2,1),
(4,'c3','তৃতীয় শ্রেণি','Class Three','৭–৮ বছর','পূর্ণাঙ্গ বিষয় পাঠদান ও মানসিক বিকাশ।',50,3,1),
(5,'c4','চতুর্থ শ্রেণি','Class Four','৮–৯ বছর','পঞ্চম শ্রেণির প্রস্তুতিপর্ব।',40,4,1),
(6,'c5','পঞ্চম শ্রেণি','Class Five','৯–১০ বছর','PECE প্রস্তুতি শ্রেণি।',40,5,1);

-- ─────────────────────────────────────────
--  TABLE: subjects
--  Subject master list
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `subjects` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(10)    NOT NULL UNIQUE,       -- bn, en, math …
  `name_bn`     VARCHAR(80)    NOT NULL,
  `name_en`     VARCHAR(80)    NOT NULL,
  `type`        ENUM('core','religion','extra','optional') NOT NULL DEFAULT 'core',
  `color_class` VARCHAR(40)    NOT NULL DEFAULT 's-bn', -- CSS pill class
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `subjects` VALUES
(1,'bn','বাংলা','Bangla','core','s-bn',1),
(2,'en','ইংরেজি','English','core','s-en',1),
(3,'math','গণিত','Math','core','s-math',1),
(4,'sci','বিজ্ঞান','Science','core','s-sci',1),
(5,'soc','সমাজ','Social Studies','core','s-soc',1),
(6,'rel','ধর্ম ও নৈতিক শিক্ষা','Religion & Moral','religion','s-rel',1),
(7,'ict','তথ্যপ্রযুক্তি','ICT','extra','s-ict',1),
(8,'art','চারুকলা','Art & Craft','extra','s-art',1),
(9,'pe','শারীরিক শিক্ষা','Physical Education','extra','s-pe',1);

-- ─────────────────────────────────────────
--  TABLE: class_subjects
--  Many-to-many: which subjects each class has
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `class_subjects` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `class_id`    INT UNSIGNED   NOT NULL,
  `subject_id`  INT UNSIGNED   NOT NULL,
  `teacher_name` VARCHAR(120)  NULL,
  `sort_order`  TINYINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_subj` (`class_id`,`subject_id`),
  FOREIGN KEY (`class_id`)   REFERENCES `classes`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Class–subject assignments
INSERT INTO `class_subjects`(`class_id`,`subject_id`,`teacher_name`,`sort_order`) VALUES
-- pk
(1,1,'মোছা. শাহিদা পারভীন',0),(1,2,'মোছা. সুমাইয়া বেগম',1),
(1,3,'মো. কামরুল ইসলাম',2),(1,6,'মোছা. নাসরিন আক্তার',3),
-- c1
(2,1,'মোছা. শাহিদা পারভীন',0),(2,2,'মো. জাহিদ হাসান',1),
(2,3,'মো. কামরুল ইসলাম',2),(2,6,'মোছা. নাসরিন আক্তার',3),
-- c2
(3,1,'মোছা. শাহিদা পারভীন',0),(3,2,'মো. জাহিদ হাসান',1),
(3,3,'মো. কামরুল ইসলাম',2),(3,5,'মোছা. নাসরিন আক্তার',3),
(3,6,'মোছা. নাসরিন আক্তার',4),
-- c3
(4,1,'মোছা. শাহিদা পারভীন',0),(4,2,'মো. জাহিদ হাসান',1),
(4,3,'মো. আবদুর রহিম',2),(4,5,'মোছা. নাসরিন আক্তার',3),
(4,6,'মোছা. নাসরিন আক্তার',4),(4,7,'মো. জাহিদ হাসান',5),
-- c4
(5,1,'মোছা. শাহিদা পারভীন',0),(5,2,'মো. জাহিদ হাসান',1),
(5,3,'মো. আবদুর রহিম',2),(5,4,'মো. কামরুল ইসলাম',3),
(5,5,'মোছা. নাসরিন আক্তার',4),(5,6,'মোছা. নাসরিন আক্তার',5),
(5,7,'মো. জাহিদ হাসান',6),
-- c5
(6,1,'মোছা. শাহিদা পারভীন',0),(6,2,'মো. জাহিদ হাসান',1),
(6,3,'মো. আবদুর রহিম',2),(6,4,'মো. কামরুল ইসলাম',3),
(6,5,'মোছা. নাসরিন আক্তার',4),(6,6,'মোছা. নাসরিন আক্তার',5),
(6,7,'মো. জাহিদ হাসান',6),(6,9,'মো. রফিকুল ইসলাম',7);

-- ─────────────────────────────────────────
--  TABLE: class_routines
--  Weekly timetable per class
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `class_routines` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `class_id`    INT UNSIGNED   NOT NULL,
  `day_of_week` TINYINT        NOT NULL,  -- 0=Sat … 5=Thu (Bangladesh 6-day week)
  `period_no`   TINYINT        NOT NULL,  -- 1–7  (0 = assembly break handled via type)
  `period_type` ENUM('assembly','subject','break') NOT NULL DEFAULT 'subject',
  `subject_id`  INT UNSIGNED   NULL,
  `time_start`  TIME           NOT NULL,
  `time_end`    TIME           NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_routine` (`class_id`,`day_of_week`,`period_no`),
  FOREIGN KEY (`class_id`)   REFERENCES `classes`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: syllabus_chapters
--  Chapter list per class per subject
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `syllabus_chapters` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `class_id`    INT UNSIGNED   NOT NULL,
  `subject_id`  INT UNSIGNED   NOT NULL,
  `chapter_no`  TINYINT        NOT NULL,
  `title`       VARCHAR(255)   NOT NULL,
  `topics`      TEXT           NULL,
  `is_exam`     TINYINT(1)     NOT NULL DEFAULT 0,  -- পরীক্ষায় আসবে badge
  `is_important` TINYINT(1)    NOT NULL DEFAULT 0,  -- গুরুত্বপূর্ণ badge
  `book_title`  VARCHAR(200)   NULL,
  `book_author` VARCHAR(200)   NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`class_id`)   REFERENCES `classes`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  INDEX `idx_class_subj` (`class_id`,`subject_id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: holidays
--  Annual holiday calendar
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `holidays` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)   NOT NULL,
  `description` TEXT           NULL,
  `type`        ENUM('govt','school','exam','event') NOT NULL DEFAULT 'govt',
  `start_date`  DATE           NOT NULL,
  `end_date`    DATE           NOT NULL,
  `duration`    VARCHAR(30)    NULL,       -- e.g. "১ দিন", "৯ দিন"
  `year`        SMALLINT       NOT NULL,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_start_date` (`start_date`),
  INDEX `idx_year`       (`year`)
) ENGINE=InnoDB;

INSERT INTO `holidays`(`title`,`type`,`start_date`,`end_date`,`duration`,`year`) VALUES
('ইংরেজি নববর্ষ','govt','2026-01-01','2026-01-01','১ দিন',2026),
('শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস','govt','2026-02-21','2026-02-21','১ দিন',2026),
('ঐতিহাসিক ৭ই মার্চ দিবস','govt','2026-03-07','2026-03-07','১ দিন',2026),
('বঙ্গবন্ধুর জন্মবার্ষিকী ও জাতীয় শিশু দিবস','govt','2026-03-17','2026-03-17','১ দিন',2026),
('মহান স্বাধীনতা ও জাতীয় দিবস','govt','2026-03-26','2026-03-26','১ দিন',2026),
('পহেলা বৈশাখ – বাংলা নববর্ষ','govt','2026-04-14','2026-04-14','১ দিন',2026),
('মে দিবস – আন্তর্জাতিক শ্রম দিবস','govt','2026-05-01','2026-05-01','১ দিন',2026),
('বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান','event','2026-05-28','2026-05-28','১ দিন',2026),
('ঈদুল আযহার বিদ্যালয় ছুটি','school','2026-06-10','2026-06-18','৯ দিন',2026),
('জাতীয় শোক দিবস','govt','2026-08-15','2026-08-15','১ দিন',2026),
('বিদ্যালয় বার্ষিক ক্রীড়া প্রতিযোগিতা','event','2026-09-05','2026-09-05','১ দিন',2026),
('জেলহত্যা দিবস','govt','2026-11-03','2026-11-03','১ দিন',2026),
('মহান বিজয় দিবস','govt','2026-12-16','2026-12-16','১ দিন',2026),
('শীতকালীন ছুটি','school','2026-12-22','2026-12-31','১০ দিন',2026);

-- ─────────────────────────────────────────
--  TABLE: exam_schedules
--  Exam timetable
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `exam_schedules` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `exam_type`   ENUM('first_term','mid_term','annual','monthly') NOT NULL,
  `exam_date`   DATE           NOT NULL,
  `day_name`    VARCHAR(30)    NULL,
  `subject_id`  INT UNSIGNED   NULL,
  `subject_label` VARCHAR(120) NULL,       -- override / extra text
  `time_start`  TIME           NULL,
  `time_end`    TIME           NULL,
  `full_marks`  SMALLINT       NULL,
  `mark_type`   ENUM('full','half','oral','practical') NOT NULL DEFAULT 'full',
  `year`        SMALLINT       NOT NULL DEFAULT 2026,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  `sort_order`  TINYINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: downloads
--  PDF downloads (routine, syllabus, etc.)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `downloads` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255)   NOT NULL,
  `description` VARCHAR(255)   NULL,
  `category`    ENUM('routine','syllabus','exam_schedule','holiday','form','other') NOT NULL DEFAULT 'other',
  `class_id`    INT UNSIGNED   NULL,
  `file_name`   VARCHAR(255)   NOT NULL,
  `file_path`   VARCHAR(255)   NOT NULL,
  `file_size`   VARCHAR(20)    NULL,       -- "120 KB"
  `file_type`   VARCHAR(10)    NOT NULL DEFAULT 'pdf',
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  `uploaded_by` INT UNSIGNED   NOT NULL,
  `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`class_id`)    REFERENCES `classes`(`id`)      ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users`(`id`)  ON DELETE RESTRICT,
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: admissions
--  Online admission application form
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admissions` (
  `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `app_no`            VARCHAR(20)    NOT NULL UNIQUE,   -- e.g. KMA-2026-0001
  -- Student info
  `student_name_bn`   VARCHAR(120)   NOT NULL,
  `student_name_en`   VARCHAR(120)   NOT NULL,
  `dob`               DATE           NOT NULL,
  `gender`            ENUM('male','female','other') NOT NULL,
  `religion`          ENUM('islam','hinduism','christianity','buddhism','other') NOT NULL DEFAULT 'islam',
  `blood_group`       VARCHAR(5)     NULL,
  `apply_class_id`    INT UNSIGNED   NOT NULL,
  `prev_school`       VARCHAR(200)   NULL,
  `birth_cert_no`     VARCHAR(20)    NOT NULL,
  -- Guardian info
  `father_name`       VARCHAR(120)   NOT NULL,
  `mother_name`       VARCHAR(120)   NOT NULL,
  `father_occupation` VARCHAR(80)    NULL,
  `mother_occupation` VARCHAR(80)    NULL,
  `guardian_phone`    VARCHAR(15)    NOT NULL,
  `guardian_email`    VARCHAR(120)   NULL,
  `father_nid`        VARCHAR(17)    NOT NULL,
  `annual_income`     ENUM('low','medium','high','higher') NULL,
  -- Address
  `address`           TEXT           NOT NULL,
  `district`          VARCHAR(60)    NOT NULL,
  `upazila`           VARCHAR(60)    NULL,
  `post_code`         VARCHAR(10)    NULL,
  -- Extra
  `scholarship_apply` TINYINT(1)     NOT NULL DEFAULT 0,
  `hear_about`        VARCHAR(30)    NULL,
  `remarks`           TEXT           NULL,
  -- Files
  `photo_path`        VARCHAR(255)   NULL,
  `birth_cert_path`   VARCHAR(255)   NULL,
  -- Status
  `status`            ENUM('pending','shortlisted','admitted','rejected','waitlisted') NOT NULL DEFAULT 'pending',
  `reviewed_by`       INT UNSIGNED   NULL,
  `reviewed_at`       DATETIME       NULL,
  `review_note`       TEXT           NULL,
  -- Meta
  `ip_address`        VARCHAR(45)    NULL,
  `created_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`apply_class_id`) REFERENCES `classes`(`id`)      ON DELETE RESTRICT,
  FOREIGN KEY (`reviewed_by`)    REFERENCES `admin_users`(`id`)   ON DELETE SET NULL,
  INDEX `idx_status`     (`status`),
  INDEX `idx_class`      (`apply_class_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: gallery
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200)   NOT NULL,
  `image_path`  VARCHAR(255)   NOT NULL,
  `category`    VARCHAR(60)    NULL,
  `sort_order`  SMALLINT       NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  `uploaded_by` INT UNSIGNED   NOT NULL,
  `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
--  TABLE: site_settings
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_settings` (
  `key_name`    VARCHAR(80)    NOT NULL,
  `value`       TEXT           NULL,
  `description` VARCHAR(200)   NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB;

INSERT INTO `site_settings` VALUES
('school_name_bn','খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি','বিদ্যালয়ের বাংলা নাম'),
('school_name_en','Khalilullah Memorial Academy (KMA)','School name in English'),
('school_phone','+880 1866-751015','Phone number'),
('school_email','info@kma.edu.bd','Email address'),
('school_address','মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী, বাংলাদেশ।','Address'),
('school_facebook','https://www.facebook.com/KhalilullahMemorialAcademy','Facebook URL'),
('school_youtube','https://www.youtube.com/@KhalilullahMemorialAcademy','YouTube URL'),
('school_whatsapp','+8801866751015','WhatsApp number'),
('office_hours','শনিবার – বৃহস্পতিবার: সকাল ৮:০০ – দুপুর ১:৩০','Office hours'),
('admission_open','1','1=open, 0=closed'),
('current_year','2026','Academic year');

-- ─────────────────────────────────────────
--  TABLE: contact_messages
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `sender_name`     VARCHAR(120)   NOT NULL,
  `sender_phone`    VARCHAR(15)    NOT NULL,
  `sender_email`    VARCHAR(120)   NULL,
  `relation`        VARCHAR(30)    NULL,
  `subject`         VARCHAR(80)    NOT NULL,
  `message`         TEXT           NOT NULL,
  `contact_method`  ENUM('phone','email','whatsapp') NOT NULL DEFAULT 'phone',
  `is_read`         TINYINT(1)     NOT NULL DEFAULT 0,
  `ip_address`      VARCHAR(45)    NULL,
  `created_at`      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- END OF SCHEMA
-- ============================================================