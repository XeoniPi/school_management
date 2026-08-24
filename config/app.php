<?php
/**
 * KMA — Application Constants & Global Settings
 * PHP 7.2 compatible
 */

define('BASE_URL',  '/kma');
define('BASE_PATH', dirname(__DIR__));

define('UPLOAD_DIR',          BASE_PATH . '/uploads/');
define('UPLOAD_NOTICES',      BASE_PATH . '/uploads/notices/');
define('UPLOAD_PDFS',         BASE_PATH . '/uploads/pdfs/');
define('UPLOAD_IMAGES',       BASE_PATH . '/uploads/images/');

define('UPLOAD_URL',          BASE_URL . '/uploads/');
define('UPLOAD_NOTICES_URL',  BASE_URL . '/uploads/notices/');
define('UPLOAD_PDFS_URL',     BASE_URL . '/uploads/pdfs/');
define('UPLOAD_IMAGES_URL',   BASE_URL . '/uploads/images/');

define('MAX_PDF_SIZE',        5 * 1024 * 1024);
define('MAX_IMG_SIZE',        2 * 1024 * 1024);
define('ALLOWED_IMG_TYPES',   ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_PDF_TYPES',   ['application/pdf']);
define('SESSION_LIFETIME',    7200);
define('NOTICES_PER_PAGE',    10);
define('ADMIN_PER_PAGE',      15);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

function getSiteSettings()
{
    static $settings = null;
    if ($settings === null) {
        try {
            $pdo  = getDB();
            $rows = $pdo->query('SELECT key_name, value FROM site_settings')->fetchAll();
            $settings = array_column($rows, 'value', 'key_name');
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings;
}

function h($str)
{
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize($str)
{
    return trim(strip_tags((string)$str));
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function isAdminLoggedIn()
{
    return !empty($_SESSION['admin_id'])
        && !empty($_SESSION['admin_role']);
}

function requireAdminLogin()
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function jsonResponse(array $data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function generateAppNo()
{
    $year  = date('Y');
    $pdo   = getDB();
    $stmt  = $pdo->query("SELECT COUNT(*) FROM admissions WHERE YEAR(created_at) = " . (int)$year);
    $count = (int)$stmt->fetchColumn();
    return 'KMA-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function noticeCategoryLabel($cat)
{
    $map = [
        'exam'    => 'পরীক্ষা',
        'notice'  => 'বিজ্ঞপ্তি',
        'holiday' => 'ছুটি',
        'event'   => 'ইভেন্ট',
        'general' => 'সাধারণ',
    ];
    return isset($map[$cat]) ? $map[$cat] : 'সাধারণ';
}

function noticeCategoryClass($cat)
{
    $map = [
        'exam'    => 'tag-exam',
        'notice'  => 'tag-notice',
        'holiday' => 'tag-holiday',
        'event'   => 'tag-event',
        'general' => 'tag-general',
    ];
    return isset($map[$cat]) ? $map[$cat] : 'tag-general';
}

function noticeCategoryTailwind($cat)
{
    $map = [
        'exam'    => 'bg-yellow-100 text-yellow-800',
        'notice'  => 'bg-blue-100 text-blue-800',
        'holiday' => 'bg-green-100 text-green-800',
        'event'   => 'bg-rose-100 text-rose-800',
        'general' => 'bg-gray-100 text-gray-700',
    ];
    return isset($map[$cat]) ? $map[$cat] : 'bg-gray-100 text-gray-700';
}

function holidayTypeTailwind($type)
{
    $map = [
        'govt'   => 'bg-red-100 text-red-700',
        'school' => 'bg-green-100 text-green-700',
        'exam'   => 'bg-yellow-100 text-yellow-800',
        'event'  => 'bg-purple-100 text-purple-700',
    ];
    return isset($map[$type]) ? $map[$type] : 'bg-gray-100 text-gray-700';
}

function holidayTypeLabel($type)
{
    $map = [
        'govt'   => 'সরকারি',
        'school' => 'বিদ্যালয়',
        'exam'   => 'পরীক্ষা',
        'event'  => 'বিশেষ অনুষ্ঠান',
    ];
    return isset($map[$type]) ? $map[$type] : $type;
}