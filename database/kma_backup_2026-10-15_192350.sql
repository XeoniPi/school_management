-- ============================================================
-- KMA Database Backup
-- Database: kma_school
-- Generated: 2026-10-15 19:23:50
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table: `admin_permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_permissions`;
CREATE TABLE `admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `module_key` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_read` tinyint(1) NOT NULL DEFAULT '0',
  `can_insert` tinyint(1) NOT NULL DEFAULT '0',
  `can_edit` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_module` (`admin_id`,`module_key`),
  CONSTRAINT `admin_permissions_ibfk_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_permissions` (`id`,`admin_id`,`module_key`,`can_read`,`can_insert`,`can_edit`,`can_delete`) VALUES
('1','6','notices','1','1','1','0'),
('2','6','admissions','1','1','0','0'),
('3','6','classes','1','1','1','0'),
('4','6','faculty','1','0','0','0'),
('5','6','holidays','1','1','1','0'),
('6','6','exam_schedule','1','1','1','0'),
('7','6','downloads','1','1','1','0'),
('8','6','gallery','1','1','1','0'),
('9','6','accounts','0','0','0','0'),
('10','6','settings','0','0','0','0'),
('11','6','users','0','0','0','0');

-- --------------------------------------------------------
-- Table: `admin_users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','editor','accounts','moderator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_users` (`id`,`username`,`email`,`password`,`full_name`,`role`,`is_active`,`last_login`,`created_at`,`updated_at`) VALUES
('5','admin','mdroky2016s.s.c@gmail.com','$2y$10$q5XQIYp.imnZj8bCKMQvT.PjpYAEra.SE85Yjsl8clIk8Vhw3DAzC','ABDUR RAHMAN ROKY','super_admin','1','2026-10-15 21:51:20','2026-08-19 15:02:04','2026-10-15 21:51:20'),
('6','moderator','m@gmail.com','$2y$10$oRpcubRRW3X9d12kBxchueJQstXCwbLjA8ksSAsYxgFF35QvVSQPW','abd','moderator','1','2026-10-15 18:08:11','2026-09-02 00:37:24','2026-10-15 18:08:11');

-- --------------------------------------------------------
-- Table: `admissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admissions`;
CREATE TABLE `admissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name_bn` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name_en` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `religion` enum('islam','hinduism','christianity','buddhism','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'islam',
  `blood_group` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apply_class_id` int(10) unsigned NOT NULL,
  `prev_school` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_cert_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `father_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mother_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `father_occupation` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_occupation` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guardian_email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_nid` varchar(17) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annual_income` enum('low','medium','high','higher') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upazila` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scholarship_apply` tinyint(1) NOT NULL DEFAULT '0',
  `hear_about` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_cert_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','enrolled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `source` enum('website','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'website',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_no` (`app_no`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_status` (`status`),
  KEY `idx_class` (`apply_class_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`apply_class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admissions` (`id`,`app_no`,`student_name_bn`,`student_name_en`,`dob`,`gender`,`religion`,`blood_group`,`apply_class_id`,`prev_school`,`birth_cert_no`,`father_name`,`mother_name`,`father_occupation`,`mother_occupation`,`guardian_phone`,`guardian_email`,`father_nid`,`annual_income`,`address`,`district`,`upazila`,`post_code`,`scholarship_apply`,`hear_about`,`remarks`,`photo_path`,`birth_cert_path`,`status`,`source`,`reviewed_by`,`reviewed_at`,`review_note`,`ip_address`,`created_at`,`updated_at`) VALUES
('1','KMA-2026-0001','md arif hossain','Arif','2020-11-19','male','islam','B+','3','14no primary school','20005167852121789','kasem','halima','business','homemaker','01445783690','kasem@gmail.com','1254863892','medium','খলিল মেম্বার বাড়ির রোড়, জালাল সারেং জামে মসজিদ, চরজুবলী, মধ্যম বাগ্যা, সুবর্ণচর, নোয়াখালী।','নোয়াখালী','সুবর্ণচর','3812','1','social','document ki ki lagbe?','photo_1788289465_005bde83.jpeg','cert_1788289465_df74d1ef.jpeg','enrolled','website','5','2026-09-02 01:06:04','admission done','::1','2026-09-02 01:04:25','2026-09-02 01:06:04'),
('2','KMA-2026-0002','abdullah','mia','2017-02-09','male','islam','O+','6','ak primary school','12453698745213658','soikat','rokiya','Probashi','homemaker','01536546981','soikat@gmail.com','253644789952','higher','middle bagga, ward-04, Pankhar bazer, charjabbar','noakhali','subarnachar','3812','1','banner','test adminssion','photo_1789756930_b3280419.png','cert_1789756930_68302202.jpeg','rejected','website','5','2026-09-19 00:53:33','in this time class five donot start it.','::1','2026-09-19 00:42:10','2026-09-19 00:53:33');

-- --------------------------------------------------------
-- Table: `class_routines`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `class_routines`;
CREATE TABLE `class_routines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `period_no` tinyint(4) NOT NULL,
  `period_type` enum('assembly','subject','break') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'subject',
  `subject_id` int(10) unsigned DEFAULT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_routine` (`class_id`,`day_of_week`,`period_no`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_routines_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_routines_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `class_subjects`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `class_subjects`;
CREATE TABLE `class_subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `teacher_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_subj` (`class_id`,`subject_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `class_subjects` (`id`,`class_id`,`subject_id`,`teacher_name`,`sort_order`) VALUES
('1','1','1','মোছা. শাহিদা পারভীন','0'),
('2','1','2','মোছা. সুমাইয়া বেগম','1'),
('3','1','3','মো. কামরুল ইসলাম','2'),
('4','1','6','মোছা. নাসরিন আক্তার','3'),
('5','2','1','মোছা. শাহিদা পারভীন','0'),
('6','2','2','মো. জাহিদ হাসান','1'),
('7','2','3','মো. কামরুল ইসলাম','2'),
('8','2','6','মোছা. নাসরিন আক্তার','3'),
('9','3','1','মোছা. শাহিদা পারভীন','0'),
('10','3','2','মো. জাহিদ হাসান','1'),
('11','3','3','মো. কামরুল ইসলাম','2'),
('12','3','5','মোছা. নাসরিন আক্তার','3'),
('13','3','6','মোছা. নাসরিন আক্তার','4'),
('14','4','1','মোছা. শাহিদা পারভীন','0'),
('15','4','2','মো. জাহিদ হাসান','1'),
('16','4','3','মো. আবদুর রহিম','2'),
('17','4','5','মোছা. নাসরিন আক্তার','3'),
('18','4','6','মোছা. নাসরিন আক্তার','4'),
('19','4','7','মো. জাহিদ হাসান','5'),
('20','5','1','মোছা. শাহিদা পারভীন','0'),
('21','5','2','মো. জাহিদ হাসান','1'),
('22','5','3','মো. আবদুর রহিম','2'),
('23','5','4','মো. কামরুল ইসলাম','3'),
('24','5','5','মোছা. নাসরিন আক্তার','4'),
('25','5','6','মোছা. নাসরিন আক্তার','5'),
('26','5','7','মো. জাহিদ হাসান','6'),
('27','6','1','মোছা. শাহিদা পারভীন','0'),
('28','6','2','মো. জাহিদ হাসান','1'),
('29','6','3','মো. আবদুর রহিম','2'),
('30','6','4','মো. কামরুল ইসলাম','3'),
('31','6','5','মোছা. নাসরিন আক্তার','4'),
('32','6','6','মোছা. নাসরিন আক্তার','5'),
('33','6','7','মো. জাহিদ হাসান','6'),
('34','6','9','মো. রফিকুল ইসলাম','7');

-- --------------------------------------------------------
-- Table: `classes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_key` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name_en` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_range` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `total_seats` smallint(6) NOT NULL DEFAULT '60',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_key` (`class_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `classes` (`id`,`class_key`,`class_name`,`class_name_en`,`age_range`,`description`,`total_seats`,`sort_order`,`is_active`) VALUES
('1','pk','প্রাক-প্রাথমিক','Pre-Primary','৪–৫ বছর','খেলার ছলে শেখার মাধ্যমে মৌলিক দক্ষতা অর্জন।','60','0','1'),
('2','c1','প্রথম শ্রেণি','Class One','৫–৬ বছর','জাতীয় পাঠ্যক্রম অনুসরণ করে মূল বিষয়ে ভিত্তি গড়ে তোলা।','60','1','1'),
('3','c2','দ্বিতীয় শ্রেণি','Class Two','৬–৭ বছর','ভাষাগত দক্ষতা ও বিজ্ঞান সচেতনতা বিকাশ।','50','2','1'),
('4','c3','তৃতীয় শ্রেণি','Class Three','৭–৮ বছর','পূর্ণাঙ্গ বিষয় পাঠদান ও মানসিক বিকাশ।','50','3','1'),
('5','c4','চতুর্থ শ্রেণি','Class Four','৮–৯ বছর','পঞ্চম শ্রেণির প্রস্তুতিপর্ব।','40','4','1'),
('6','c5','পঞ্চম শ্রেণি','Class Five','৯–১০ বছর','PECE প্রস্তুতি শ্রেণি।','40','5','1');

-- --------------------------------------------------------
-- Table: `contact_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relation` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_method` enum('phone','email','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'phone',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `downloads`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('routine','syllabus','exam_schedule','holiday','form','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `class_id` int(10) unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf',
  `download_count` int(10) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_category` (`category`),
  CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `exam_schedules`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `exam_schedules`;
CREATE TABLE `exam_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_type` enum('first_term','mid_term','annual','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `day_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `subject_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `full_marks` smallint(6) DEFAULT NULL,
  `mark_type` enum('full','half','oral','practical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full',
  `class_id` int(10) unsigned NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint(6) NOT NULL DEFAULT '2026',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_exam_type` (`exam_type`),
  KEY `idx_class_id` (`class_id`),
  KEY `idx_subject_id` (`subject_id`),
  CONSTRAINT `exam_schedules_ibfk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedules_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_schedules` (`id`,`exam_type`,`exam_date`,`day_name`,`subject_id`,`subject_label`,`time_start`,`time_end`,`full_marks`,`mark_type`,`class_id`,`notes`,`year`,`is_active`,`sort_order`) VALUES
('1','first_term','2026-03-10',NULL,'1',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','1'),
('2','first_term','2026-03-11',NULL,'2',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','2'),
('3','first_term','2026-03-12',NULL,'3',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','3'),
('4','first_term','2026-03-10',NULL,'1',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','1'),
('5','first_term','2026-03-11',NULL,'2',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','2'),
('6','first_term','2026-03-12',NULL,'3',NULL,'10:00:00','12:00:00','100','full','2',NULL,'2026','1','3');

-- --------------------------------------------------------
-- Table: `faculty`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `faculty`;
CREATE TABLE `faculty` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_bn` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('administration','teacher','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teacher',
  `education` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portfolio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `faculty_ibfk_admin` (`uploaded_by`),
  CONSTRAINT `faculty_ibfk_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faculty` (`id`,`name_bn`,`name_en`,`designation`,`category`,`education`,`experience`,`photo_path`,`email`,`phone`,`portfolio_url`,`bio`,`sort_order`,`is_active`,`uploaded_by`,`created_at`,`updated_at`) VALUES
('1','abdur rahman roky','abdur rahman roky','Owner','administration','BSc in CSE','4','faculty_1792063856_0525e9.png','arr@gmail.com','0175463871','https://xeonipi.github.io/CodeWithRoky/','শিক্ষার আলো ছড়িয়ে দেওয়ার মহৎ লক্ষ্যকে সামনে রেখে খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি প্রতিষ্ঠার সিদ্ধান্ত নেওয়া হয় ২৯ অক্টোবর ২০২৫ সালে। ০৩ নভেম্বর ২০২৫ তারিখে প্রস্তাবটি সামাজিক পর্যায়ে উপস্থাপন করা হয়। ০৯ নভেম্বর ২০২৫ তারিখে চূড়ান্ত সিদ্ধান্ত গৃহীত হয় এবং ২০২৬ সাল থেকে একাডেমিক কার্যক্রম শুরু হয়।\r\n\r\nপ্রতিষ্ঠাতা অ্যাডভোকেট মো. হারুন উর রশীদ হারুন এবং আব্দুর রহমান রকির অক্লান্ত প্রচেষ্টায় আজ এই বিদ্যালয় থেকে মেধাবী শিক্ষার্থীরা উজ্জ্বল ভবিষ্যতের পথে এগিয়ে যাচ্ছে।','1','1','5','2026-09-02 00:40:40','2026-10-15 17:30:56'),
('2','mohin uddin','Md. Mohin Uddin','Head Master','teacher','Alim Pass','6','faculty_1788288892_554cc7.jpg','mu@gmail.com','01554639871','','There is now a time-based compatibility policy for AGP and Android Studio. Each Android Studio version will support AGP versions released within the previous 3 years. AGP versions older than 3 years will no longer be supported in newer Android Studio versions. If your project\'s AGP version is incompatible, Android Studio will require you to update AGP.','1','1','5','2026-09-02 00:54:52','2026-09-02 00:54:52'),
('3','abdur rahman roky','abdur rahman roky','Head Master','staff','BSc in CSE','4','faculty_1792066190_5d0da4.png','arr@gmail.com','0175463871','https://xeonipi.github.io/CodeWithRoky/','text','0','1','6','2026-10-15 18:09:50','2026-10-15 18:09:50');

-- --------------------------------------------------------
-- Table: `gallery`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery` (`id`,`title`,`caption`,`image_path`,`category`,`sort_order`,`is_active`,`uploaded_by`,`created_at`) VALUES
('4','আপনার আবেদন আমরা পেয়েছি। ৪৮ ঘণ্টার মধ্যে আপনার মোবাইলে কল করা হবে।','আপনার আবেদন আমরা পেয়েছি। ৪৮ ঘণ্টার মধ্যে আপনার মোবাইলে কল করা হবে।','gallery_1789757294_66b76d.jpg','sports','1','1','5','2026-09-19 00:48:14');

-- --------------------------------------------------------
-- Table: `holidays`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `holidays`;
CREATE TABLE `holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('govt','school','exam','event') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'govt',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `duration` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint(6) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `holidays` (`id`,`title`,`description`,`type`,`start_date`,`end_date`,`duration`,`year`,`is_active`) VALUES
('1','ইংরেজি নববর্ষ',NULL,'govt','2026-01-01','2026-01-01','১ দিন','2026','1'),
('2','শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস',NULL,'govt','2026-02-21','2026-02-21','১ দিন','2026','1'),
('3','ঐতিহাসিক ৭ই মার্চ দিবস',NULL,'govt','2026-03-07','2026-03-07','১ দিন','2026','1'),
('4','বঙ্গবন্ধুর জন্মবার্ষিকী ও জাতীয় শিশু দিবস',NULL,'govt','2026-03-17','2026-03-17','১ দিন','2026','1'),
('5','মহান স্বাধীনতা ও জাতীয় দিবস',NULL,'govt','2026-03-26','2026-03-26','১ দিন','2026','1'),
('6','পহেলা বৈশাখ – বাংলা নববর্ষ',NULL,'govt','2026-04-14','2026-04-14','১ দিন','2026','1'),
('7','মে দিবস – আন্তর্জাতিক শ্রম দিবস',NULL,'govt','2026-05-01','2026-05-01','১ দিন','2026','1'),
('8','বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান',NULL,'event','2026-05-28','2026-05-28','১ দিন','2026','1'),
('9','ঈদুল আযহার বিদ্যালয় ছুটি',NULL,'school','2026-06-10','2026-06-18','৯ দিন','2026','1'),
('10','জাতীয় শোক দিবস',NULL,'govt','2026-08-15','2026-08-15','১ দিন','2026','1'),
('11','বিদ্যালয় বার্ষিক ক্রীড়া প্রতিযোগিতা',NULL,'event','2026-09-05','2026-09-05','১ দিন','2026','1'),
('12','জেলহত্যা দিবস',NULL,'govt','2026-11-03','2026-11-03','১ দিন','2026','1'),
('13','মহান বিজয় দিবস',NULL,'govt','2026-12-16','2026-12-16','১ দিন','2026','1'),
('14','শীতকালীন ছুটি',NULL,'school','2026-12-22','2026-12-31','১০ দিন','2026','1');

-- --------------------------------------------------------
-- Table: `login_attempts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `notices`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('exam','notice','holiday','event','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `notice_date` date NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_notice_date` (`notice_date`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_category` (`category`),
  CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notices` (`id`,`title`,`content`,`category`,`notice_date`,`file_path`,`is_active`,`is_pinned`,`views`,`created_by`,`created_at`,`updated_at`) VALUES
('1','অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে','সকল শিক্ষার্থীকে জানানো যাচ্ছে যে, ২০২৬ সালের অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে। অফিস থেকে সংগ্রহ করতে বলা হচ্ছে।','exam','2026-09-24',NULL,'1','0','4','1','2026-06-15 02:56:02','2026-09-01 20:05:55'),
('2','২০২৫-২৬ শিক্ষাবর্ষের ভর্তি কার্যক্রম শুরু','আসন সীমিত, দ্রুত ভর্তি নিশ্চিত করুন। বিস্তারিত জানতে অফিসে যোগাযোগ করুন।','notice','2025-05-18',NULL,'1','1','0','1','2026-06-15 02:56:02','2026-09-01 20:06:34'),
('3','ঈদুল আযহা উপলক্ষে ছুটি','১০ জুন থেকে ১৮ জুন পর্যন্ত বিদ্যালয় বন্ধ থাকবে।','holiday','2025-05-15',NULL,'1','0','0','1','2026-06-15 02:56:02','2026-06-15 02:56:02'),
('4','বার্ষিক পুরস্কার বিতরণী অনুষ্ঠান','বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান আগামী ২৮ মে অনুষ্ঠিত হবে।','event','2025-05-10',NULL,'1','0','0','1','2026-06-15 02:56:02','2026-06-15 02:56:02'),
('5','মাসিক মূল্যায়ন পরীক্ষা','২ জুন থেকে মাসিক মূল্যায়ন পরীক্ষা শুরু হবে।','exam','2025-05-05',NULL,'1','0','0','1','2026-06-15 02:56:02','2026-06-15 02:56:02'),
('6','অভিভাবক সমাবেশ','২৫ মে রবিবার সকাল ১০টায় বিদ্যালয় মিলনায়তনে অভিভাবক সমাবেশ অনুষ্ঠিত হবে।','notice','2025-05-01',NULL,'1','0','0','1','2026-06-15 02:56:02','2026-06-15 02:56:02');

-- --------------------------------------------------------
-- Table: `site_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `key_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`key_name`,`value`,`description`) VALUES
('admission_open','1','1=open, 0=closed'),
('current_year','2026','Academic year'),
('footer_text','',NULL),
('logo_path','assets/images/logo.jpg',NULL),
('meta_description','',NULL),
('office_hours','শনিবার – বৃহস্পতিবার: সকাল ৮:০০ – দুপুর ১:৩০','Office hours'),
('school_address','খলিল মেম্বার বাড়ির রোড, পাংখার বাজার, চরজুবলী, সুবর্ণচর, নোয়াখালী, বাংলাদেশ।','Address'),
('school_email','info@kma.edu.bd','Email address'),
('school_email2','admission@gmail.com',NULL),
('school_facebook','https://www.facebook.com/KhalilullahMemorialAcademy','Facebook URL'),
('school_map_url','',NULL),
('school_name_bn','খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি','বিদ্যালয়ের বাংলা নাম'),
('school_name_en','Khalilullah Memorial Academy-(KMA)','School name in English'),
('school_phone','+880 1581-903072','Phone number'),
('school_phone2','',NULL),
('school_tagline','Modern Education, Timeless Values',NULL),
('school_whatsapp','+8801866751015','WhatsApp number'),
('school_youtube','https://www.youtube.com/@KhalilullahMemorialAcademy','YouTube URL');

-- --------------------------------------------------------
-- Table: `subjects`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_bn` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('core','religion','extra','optional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `color_class` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 's-bn',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subjects` (`id`,`code`,`name_bn`,`name_en`,`type`,`color_class`,`sort_order`,`is_active`) VALUES
('1','bn','বাংলা','Bangla','core','s-bn','0','1'),
('2','en','ইংরেজি','English','core','s-en','0','1'),
('3','math','গণিত','Math','core','s-math','0','1'),
('4','sci','বিজ্ঞান','Science','core','s-sci','0','1'),
('5','soc','সমাজ','Social Studies','core','s-soc','0','1'),
('6','rel','ধর্ম ও নৈতিক শিক্ষা','Religion & Moral','religion','s-rel','0','1'),
('7','ict','তথ্যপ্রযুক্তি','ICT','extra','s-ict','0','1'),
('8','art','চারুকলা','Art & Craft','extra','s-art','0','1'),
('9','pe','শারীরিক শিক্ষা','Physical Education','extra','s-pe','0','1');

-- --------------------------------------------------------
-- Table: `syllabus_chapters`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `syllabus_chapters`;
CREATE TABLE `syllabus_chapters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `chapter_no` tinyint(4) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topics` text COLLATE utf8mb4_unicode_ci,
  `is_exam` tinyint(1) NOT NULL DEFAULT '0',
  `is_important` tinyint(1) NOT NULL DEFAULT '0',
  `book_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `book_author` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `idx_class_subj` (`class_id`,`subject_id`),
  CONSTRAINT `syllabus_chapters_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `syllabus_chapters_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: `transactions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_type` enum('student','faculty','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `student_id` int(10) unsigned DEFAULT NULL,
  `faculty_id` int(10) unsigned DEFAULT NULL,
  `party_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `idx_type` (`type`),
  KEY `idx_category` (`category`),
  KEY `idx_date` (`transaction_date`),
  KEY `idx_student` (`student_id`),
  KEY `idx_faculty` (`faculty_id`),
  KEY `transactions_ibfk_admin` (`created_by`),
  CONSTRAINT `transactions_ibfk_admin` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`),
  CONSTRAINT `transactions_ibfk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_ibfk_student` FOREIGN KEY (`student_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `transactions` (`id`,`invoice_no`,`type`,`category`,`amount`,`transaction_date`,`payment_method`,`remark`,`party_type`,`student_id`,`faculty_id`,`party_name`,`created_by`,`created_at`,`updated_at`) VALUES
('1','INV-2026-00001','income','admission_fee','3000.00','2026-09-01','cash','total amdission fees = 5700/-tk','student','1',NULL,'','5','2026-09-02 01:07:13','2026-09-02 01:07:13'),
('2','INV-2026-00002','expense','salary','5000.00','2026-09-01','cash','due = 3000/-tk','faculty',NULL,'2','','5','2026-09-02 01:08:03','2026-09-02 01:08:03');

SET FOREIGN_KEY_CHECKS=1;
