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

    /* Idle session timeout (30 minutes of inactivity) */
    $idleLimit = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idleLimit) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login.php?flash=' . urlencode('নিষ্ক্রিয়তার কারণে সেশন শেষ হয়ে গেছে। আবার লগইন করুন।') . '&flashType=error');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function jsonResponse(array $data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Simple session-based rate limiter for public forms (contact, admission).
 */
function kmaRateLimit($key, $minSeconds = 20)
{
    $sessionKey = 'kma_rl_' . $key;
    $now = time();
    if (isset($_SESSION[$sessionKey]) && ($now - $_SESSION[$sessionKey]) < $minSeconds) {
        return false;
    }
    $_SESSION[$sessionKey] = $now;
    return true;
}

/**
 * Verify an uploaded file's REAL content type (via magic bytes, using the
 * fileinfo extension) rather than trusting the client-supplied
 * $_FILES[...]['type'], which can be spoofed.
 */
function kmaVerifyFileContent($tmpPath, array $allowedMimes)
{
    if (!is_uploaded_file($tmpPath)) { return false; }
    if (!function_exists('finfo_open')) { return true; }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) { return true; }
    $realType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    return in_array($realType, $allowedMimes, true);
}

/** Standard image upload validation: size + declared MIME + real content bytes. */
function kmaValidateImageUpload($file)
{
    if ($file['size'] > MAX_IMG_SIZE) { return 'ছবির আকার সর্বোচ্চ ২ MB।'; }
    if (!in_array($file['type'], ALLOWED_IMG_TYPES, true)) { return 'শুধুমাত্র JPG, PNG বা WEBP ছবি আপলোড করুন।'; }
    if (!kmaVerifyFileContent($file['tmp_name'], ALLOWED_IMG_TYPES)) { return 'ফাইলের প্রকৃত বিষয়বস্তু একটি বৈধ ছবির সাথে মেলে না।'; }
    return null;
}

/** Password strength: 8+ chars, at least one uppercase, one lowercase, one digit. */
function kmaIsStrongPassword($password)
{
    if (mb_strlen($password) < 8) { return false; }
    if (!preg_match('/[A-Z]/', $password)) { return false; }
    if (!preg_match('/[a-z]/', $password)) { return false; }
    if (!preg_match('/[0-9]/', $password)) { return false; }
    return true;
}
function kmaPasswordRuleText()
{
    return 'কমপক্ষে ৮ অক্ষর, এবং অন্তত ১টি বড় হাতের অক্ষর, ১টি ছোট হাতের অক্ষর ও ১টি সংখ্যা থাকতে হবে।';
}

/** Lightweight self-hosted math CAPTCHA (no external API/keys needed). */
function kmaCaptchaGenerate()
{
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['kma_captcha_answer'] = $a + $b;
    return $a . ' + ' . $b;
}
function kmaCaptchaVerify($submitted)
{
    $expected = isset($_SESSION['kma_captcha_answer']) ? (int)$_SESSION['kma_captcha_answer'] : null;
    unset($_SESSION['kma_captcha_answer']);
    if ($expected === null) { return false; }
    return ((int)$submitted) === $expected;
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

/* ═══════════════════════════════════════════════════════════
   ROLE-BASED ACCESS CONTROL (RBAC)
   Roles: super_admin, admin  -> always full access, bypass checks
          editor              -> broad content access (legacy role)
          accounts            -> 'accounts' module only, never delete
          moderator           -> content modules only, never delete,
                                  no access to accounts/users/settings
   Per-user overrides live in `admin_permissions` and always win
   over the role default (so a super_admin can grant/restrict any
   single user's access precisely).
   ═══════════════════════════════════════════════════════════ */

/** All permission-gated modules in the admin panel. */
function kmaModuleList()
{
    return [
        'notices', 'admissions', 'classes', 'faculty', 'holidays',
        'exam_schedule', 'downloads', 'gallery', 'accounts', 'settings', 'users',
    ];
}

/** Roles that always have unrestricted access to every module. */
function kmaIsSuperRole($role)
{
    return in_array($role, ['super_admin', 'admin'], true);
}

/** Default permission matrix for a role, used the first time a user is created
 *  and whenever no explicit per-user override exists for a module. */
function kmaDefaultPermissions($role)
{
    $contentModules = ['notices','admissions','classes','faculty','holidays','exam_schedule','downloads','gallery'];
    $matrix = [];
    foreach (kmaModuleList() as $m) {
        $matrix[$m] = ['can_read' => 0, 'can_insert' => 0, 'can_edit' => 0, 'can_delete' => 0];
    }

    if ($role === 'editor') {
        foreach ($contentModules as $m) {
            $matrix[$m] = ['can_read' => 1, 'can_insert' => 1, 'can_edit' => 1, 'can_delete' => 0];
        }
    } elseif ($role === 'moderator') {
        foreach ($contentModules as $m) {
            $matrix[$m] = ['can_read' => 1, 'can_insert' => 1, 'can_edit' => 1, 'can_delete' => 0];
        }
    } elseif ($role === 'accounts') {
        $matrix['accounts'] = ['can_read' => 1, 'can_insert' => 1, 'can_edit' => 1, 'can_delete' => 0];
    }

    return $matrix;
}

/**
 * Check whether the CURRENTLY LOGGED-IN admin may perform $action
 * ('read'|'insert'|'edit'|'delete') on $module.
 * super_admin/admin always pass. Others: per-user DB override if
 * present, otherwise the role default. Delete is hard-blocked for
 * accounts/moderator regardless of any stored override, matching
 * the product requirement that these roles can never delete data.
 */
function hasPermission($module, $action = 'read')
{
    $role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '';
    if (kmaIsSuperRole($role)) { return true; }

    if ($action === 'delete' && in_array($role, ['accounts', 'moderator'], true)) {
        return false;
    }

    $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
    if (!$adminId) { return false; }

    static $cache = [];
    if (!isset($cache[$adminId])) {
        $cache[$adminId] = [];
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('SELECT module_key, can_read, can_insert, can_edit, can_delete FROM admin_permissions WHERE admin_id = ?');
            $stmt->execute([$adminId]);
            foreach ($stmt->fetchAll() as $row) {
                $cache[$adminId][$row['module_key']] = $row;
            }
        } catch (Exception $e) {
            $cache[$adminId] = [];
        }
    }

    if (isset($cache[$adminId][$module])) {
        $row = $cache[$adminId][$module];
        $col = 'can_' . $action;
        return !empty($row[$col]);
    }

    /* No stored override yet — fall back to the role default */
    $defaults = kmaDefaultPermissions($role);
    if (isset($defaults[$module])) {
        $col = 'can_' . $action;
        return !empty($defaults[$module][$col]);
    }
    return false;
}

/** Redirect to the dashboard with a flash message if the current admin lacks access. */
function requirePermission($module, $action = 'read')
{
    if (!hasPermission($module, $action)) {
        header('Location: ' . BASE_URL . '/admin/dashboard.php?flash=' . urlencode('এই অংশে আপনার প্রবেশাধিকার নেই।') . '&flashType=error');
        exit;
    }
}

/** Seed admin_permissions rows for a newly created user based on their role's defaults. */
function kmaSeedDefaultPermissions($pdo, $adminId, $role)
{
    $defaults = kmaDefaultPermissions($role);
    $stmt = $pdo->prepare(
        'INSERT INTO admin_permissions (admin_id, module_key, can_read, can_insert, can_edit, can_delete)
         VALUES (?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE can_read=VALUES(can_read), can_insert=VALUES(can_insert), can_edit=VALUES(can_edit), can_delete=VALUES(can_delete)'
    );
    foreach ($defaults as $module => $perm) {
        $stmt->execute([$adminId, $module, $perm['can_read'], $perm['can_insert'], $perm['can_edit'], $perm['can_delete']]);
    }
}