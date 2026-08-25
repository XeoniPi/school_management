-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2026 at 10:06 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kma_school`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','editor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(5, 'admin', 'mdroky2016s.s.c@gmail.com', '$2y$10$q5XQIYp.imnZj8bCKMQvT.PjpYAEra.SE85Yjsl8clIk8Vhw3DAzC', 'ABDUR RAHMAN ROKY', 'super_admin', 1, '2026-08-19 15:02:31', '2026-08-19 09:02:04', '2026-08-19 09:02:31');

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `app_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name_bn` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name_en` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `religion` enum('islam','hinduism','christianity','buddhism','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'islam',
  `blood_group` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apply_class_id` int(10) UNSIGNED NOT NULL,
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
  `status` enum('pending','shortlisted','admitted','rejected','waitlisted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_key` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name_en` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_range` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `total_seats` smallint(6) NOT NULL DEFAULT '60',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_key`, `class_name`, `class_name_en`, `age_range`, `description`, `total_seats`, `sort_order`, `is_active`) VALUES
(1, 'pk', 'প্রাক-প্রাথমিক', 'Pre-Primary', '৪–৫ বছর', 'খেলার ছলে শেখার মাধ্যমে মৌলিক দক্ষতা অর্জন।', 60, 0, 1),
(2, 'c1', 'প্রথম শ্রেণি', 'Class One', '৫–৬ বছর', 'জাতীয় পাঠ্যক্রম অনুসরণ করে মূল বিষয়ে ভিত্তি গড়ে তোলা।', 60, 1, 1),
(3, 'c2', 'দ্বিতীয় শ্রেণি', 'Class Two', '৬–৭ বছর', 'ভাষাগত দক্ষতা ও বিজ্ঞান সচেতনতা বিকাশ।', 50, 2, 1),
(4, 'c3', 'তৃতীয় শ্রেণি', 'Class Three', '৭–৮ বছর', 'পূর্ণাঙ্গ বিষয় পাঠদান ও মানসিক বিকাশ।', 50, 3, 1),
(5, 'c4', 'চতুর্থ শ্রেণি', 'Class Four', '৮–৯ বছর', 'পঞ্চম শ্রেণির প্রস্তুতিপর্ব।', 40, 4, 1),
(6, 'c5', 'পঞ্চম শ্রেণি', 'Class Five', '৯–১০ বছর', 'PECE প্রস্তুতি শ্রেণি।', 40, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `class_routines`
--

CREATE TABLE `class_routines` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `period_no` tinyint(4) NOT NULL,
  `period_type` enum('assembly','subject','break') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'subject',
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `teacher_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`, `teacher_name`, `sort_order`) VALUES
(1, 1, 1, 'মোছা. শাহিদা পারভীন', 0),
(2, 1, 2, 'মোছা. সুমাইয়া বেগম', 1),
(3, 1, 3, 'মো. কামরুল ইসলাম', 2),
(4, 1, 6, 'মোছা. নাসরিন আক্তার', 3),
(5, 2, 1, 'মোছা. শাহিদা পারভীন', 0),
(6, 2, 2, 'মো. জাহিদ হাসান', 1),
(7, 2, 3, 'মো. কামরুল ইসলাম', 2),
(8, 2, 6, 'মোছা. নাসরিন আক্তার', 3),
(9, 3, 1, 'মোছা. শাহিদা পারভীন', 0),
(10, 3, 2, 'মো. জাহিদ হাসান', 1),
(11, 3, 3, 'মো. কামরুল ইসলাম', 2),
(12, 3, 5, 'মোছা. নাসরিন আক্তার', 3),
(13, 3, 6, 'মোছা. নাসরিন আক্তার', 4),
(14, 4, 1, 'মোছা. শাহিদা পারভীন', 0),
(15, 4, 2, 'মো. জাহিদ হাসান', 1),
(16, 4, 3, 'মো. আবদুর রহিম', 2),
(17, 4, 5, 'মোছা. নাসরিন আক্তার', 3),
(18, 4, 6, 'মোছা. নাসরিন আক্তার', 4),
(19, 4, 7, 'মো. জাহিদ হাসান', 5),
(20, 5, 1, 'মোছা. শাহিদা পারভীন', 0),
(21, 5, 2, 'মো. জাহিদ হাসান', 1),
(22, 5, 3, 'মো. আবদুর রহিম', 2),
(23, 5, 4, 'মো. কামরুল ইসলাম', 3),
(24, 5, 5, 'মোছা. নাসরিন আক্তার', 4),
(25, 5, 6, 'মোছা. নাসরিন আক্তার', 5),
(26, 5, 7, 'মো. জাহিদ হাসান', 6),
(27, 6, 1, 'মোছা. শাহিদা পারভীন', 0),
(28, 6, 2, 'মো. জাহিদ হাসান', 1),
(29, 6, 3, 'মো. আবদুর রহিম', 2),
(30, 6, 4, 'মো. কামরুল ইসলাম', 3),
(31, 6, 5, 'মোছা. নাসরিন আক্তার', 4),
(32, 6, 6, 'মোছা. নাসরিন আক্তার', 5),
(33, 6, 7, 'মো. জাহিদ হাসান', 6),
(34, 6, 9, 'মো. রফিকুল ইসলাম', 7);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relation` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_method` enum('phone','email','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'phone',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('routine','syllabus','exam_schedule','holiday','form','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf',
  `download_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedules`
--

CREATE TABLE `exam_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `exam_type` enum('first_term','mid_term','annual','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `day_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `subject_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `full_marks` smallint(6) DEFAULT NULL,
  `mark_type` enum('full','half','oral','practical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full',
  `class_id` int(10) UNSIGNED NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint(6) NOT NULL DEFAULT '2026',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_schedules`
--

INSERT INTO `exam_schedules` (`id`, `exam_type`, `exam_date`, `day_name`, `subject_id`, `subject_label`, `time_start`, `time_end`, `full_marks`, `mark_type`, `class_id`, `notes`, `year`, `is_active`, `sort_order`) VALUES
(1, 'first_term', '2026-03-10', NULL, 1, NULL, '10:00:00', '12:00:00', 100, 'full', 2, NULL, 2026, 1, 1),
(2, 'first_term', '2026-03-11', NULL, 2, NULL, '10:00:00', '12:00:00', 100, 'full', 2, NULL, 2026, 1, 2),
(3, 'first_term', '2026-03-12', NULL, 3, NULL, '10:00:00', '12:00:00', 100, 'full', 2, NULL, 2026, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image_path`, `category`, `sort_order`, `is_active`, `uploaded_by`, `created_at`) VALUES
(1, 'জ্ঞানের আলোয় জীবন গড়ি', 'gallery1.jpg', 'শ্রেণিকক্ষ', 1, 1, 5, '2026-08-19 01:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('govt','school','exam','event') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'govt',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint(6) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `title`, `description`, `type`, `start_date`, `end_date`, `duration`, `year`, `is_active`) VALUES
(1, 'ইংরেজি নববর্ষ', NULL, 'govt', '2026-01-01', '2026-01-01', '১ দিন', 2026, 1),
(2, 'শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস', NULL, 'govt', '2026-02-21', '2026-02-21', '১ দিন', 2026, 1),
(3, 'ঐতিহাসিক ৭ই মার্চ দিবস', NULL, 'govt', '2026-03-07', '2026-03-07', '১ দিন', 2026, 1),
(4, 'বঙ্গবন্ধুর জন্মবার্ষিকী ও জাতীয় শিশু দিবস', NULL, 'govt', '2026-03-17', '2026-03-17', '১ দিন', 2026, 1),
(5, 'মহান স্বাধীনতা ও জাতীয় দিবস', NULL, 'govt', '2026-03-26', '2026-03-26', '১ দিন', 2026, 1),
(6, 'পহেলা বৈশাখ – বাংলা নববর্ষ', NULL, 'govt', '2026-04-14', '2026-04-14', '১ দিন', 2026, 1),
(7, 'মে দিবস – আন্তর্জাতিক শ্রম দিবস', NULL, 'govt', '2026-05-01', '2026-05-01', '১ দিন', 2026, 1),
(8, 'বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান', NULL, 'event', '2026-05-28', '2026-05-28', '১ দিন', 2026, 1),
(9, 'ঈদুল আযহার বিদ্যালয় ছুটি', NULL, 'school', '2026-06-10', '2026-06-18', '৯ দিন', 2026, 1),
(10, 'জাতীয় শোক দিবস', NULL, 'govt', '2026-08-15', '2026-08-15', '১ দিন', 2026, 1),
(11, 'বিদ্যালয় বার্ষিক ক্রীড়া প্রতিযোগিতা', NULL, 'event', '2026-09-05', '2026-09-05', '১ দিন', 2026, 1),
(12, 'জেলহত্যা দিবস', NULL, 'govt', '2026-11-03', '2026-11-03', '১ দিন', 2026, 1),
(13, 'মহান বিজয় দিবস', NULL, 'govt', '2026-12-16', '2026-12-16', '১ দিন', 2026, 1),
(14, 'শীতকালীন ছুটি', NULL, 'school', '2026-12-22', '2026-12-31', '১০ দিন', 2026, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('exam','notice','holiday','event','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `notice_date` date NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `content`, `category`, `notice_date`, `file_path`, `is_active`, `is_pinned`, `views`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে', 'সকল শিক্ষার্থীকে জানানো যাচ্ছে যে, ২০২৬ সালের অর্ধ-বার্ষিক পরীক্ষার সময়সূচি প্রকাশিত হয়েছে। অফিস থেকে সংগ্রহ করতে বলা হচ্ছে।', 'exam', '2025-05-22', NULL, 1, 1, 4, 1, '2026-06-14 20:56:02', '2026-07-24 12:10:15'),
(2, '২০২৫-২৬ শিক্ষাবর্ষের ভর্তি কার্যক্রম শুরু', 'আসন সীমিত, দ্রুত ভর্তি নিশ্চিত করুন। বিস্তারিত জানতে অফিসে যোগাযোগ করুন।', 'notice', '2025-05-18', NULL, 1, 0, 0, 1, '2026-06-14 20:56:02', '2026-06-14 20:56:02'),
(3, 'ঈদুল আযহা উপলক্ষে ছুটি', '১০ জুন থেকে ১৮ জুন পর্যন্ত বিদ্যালয় বন্ধ থাকবে।', 'holiday', '2025-05-15', NULL, 1, 0, 0, 1, '2026-06-14 20:56:02', '2026-06-14 20:56:02'),
(4, 'বার্ষিক পুরস্কার বিতরণী অনুষ্ঠান', 'বার্ষিক পুরস্কার বিতরণী ও সাংস্কৃতিক অনুষ্ঠান আগামী ২৮ মে অনুষ্ঠিত হবে।', 'event', '2025-05-10', NULL, 1, 0, 0, 1, '2026-06-14 20:56:02', '2026-06-14 20:56:02'),
(5, 'মাসিক মূল্যায়ন পরীক্ষা', '২ জুন থেকে মাসিক মূল্যায়ন পরীক্ষা শুরু হবে।', 'exam', '2025-05-05', NULL, 1, 0, 0, 1, '2026-06-14 20:56:02', '2026-06-14 20:56:02'),
(6, 'অভিভাবক সমাবেশ', '২৫ মে রবিবার সকাল ১০টায় বিদ্যালয় মিলনায়তনে অভিভাবক সমাবেশ অনুষ্ঠিত হবে।', 'notice', '2025-05-01', NULL, 1, 0, 0, 1, '2026-06-14 20:56:02', '2026-06-14 20:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `key_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`key_name`, `value`, `description`) VALUES
('admission_open', '1', '1=open, 0=closed'),
('current_year', '2026', 'Academic year'),
('office_hours', 'শনিবার – বৃহস্পতিবার: সকাল ৮:০০ – দুপুর ১:৩০', 'Office hours'),
('school_address', 'মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী, বাংলাদেশ।', 'Address'),
('school_email', 'info@kma.edu.bd', 'Email address'),
('school_facebook', 'https://www.facebook.com/KhalilullahMemorialAcademy', 'Facebook URL'),
('school_name_bn', 'খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি', 'বিদ্যালয়ের বাংলা নাম'),
('school_name_en', 'Khalilullah Memorial Academy (KMA)', 'School name in English'),
('school_phone', '+880 1866-751015', 'Phone number'),
('school_whatsapp', '+8801866751015', 'WhatsApp number'),
('school_youtube', 'https://www.youtube.com/@KhalilullahMemorialAcademy', 'YouTube URL');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_bn` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('core','religion','extra','optional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'core',
  `color_class` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 's-bn',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name_bn`, `name_en`, `type`, `color_class`, `is_active`) VALUES
(1, 'bn', 'বাংলা', 'Bangla', 'core', 's-bn', 1),
(2, 'en', 'ইংরেজি', 'English', 'core', 's-en', 1),
(3, 'math', 'গণিত', 'Math', 'core', 's-math', 1),
(4, 'sci', 'বিজ্ঞান', 'Science', 'core', 's-sci', 1),
(5, 'soc', 'সমাজ', 'Social Studies', 'core', 's-soc', 1),
(6, 'rel', 'ধর্ম ও নৈতিক শিক্ষা', 'Religion & Moral', 'religion', 's-rel', 1),
(7, 'ict', 'তথ্যপ্রযুক্তি', 'ICT', 'extra', 's-ict', 1),
(8, 'art', 'চারুকলা', 'Art & Craft', 'extra', 's-art', 1),
(9, 'pe', 'শারীরিক শিক্ষা', 'Physical Education', 'extra', 's-pe', 1);

-- --------------------------------------------------------

--
-- Table structure for table `syllabus_chapters`
--

CREATE TABLE `syllabus_chapters` (
  `id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `chapter_no` tinyint(4) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topics` text COLLATE utf8mb4_unicode_ci,
  `is_exam` tinyint(1) NOT NULL DEFAULT '0',
  `is_important` tinyint(1) NOT NULL DEFAULT '0',
  `book_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `book_author` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_no` (`app_no`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_class` (`apply_class_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_key` (`class_key`);

--
-- Indexes for table `class_routines`
--
ALTER TABLE `class_routines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_routine` (`class_id`,`day_of_week`,`period_no`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class_subj` (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_type` (`exam_type`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_notice_date` (`notice_date`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`key_name`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `syllabus_chapters`
--
ALTER TABLE `syllabus_chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_class_subj` (`class_id`,`subject_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class_routines`
--
ALTER TABLE `class_routines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `syllabus_chapters`
--
ALTER TABLE `syllabus_chapters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`apply_class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `class_routines`
--
ALTER TABLE `class_routines`
  ADD CONSTRAINT `class_routines_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_routines_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD CONSTRAINT `exam_schedules_ibfk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedules_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `syllabus_chapters`
--
ALTER TABLE `syllabus_chapters`
  ADD CONSTRAINT `syllabus_chapters_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabus_chapters_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
