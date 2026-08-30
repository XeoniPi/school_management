<?php
/**
 * KMA — admin/register.php  |  PHP 7.2
 * Create a new admin user. LOCKED: only a logged-in super_admin may use this.
 * (Previously this page had NO auth check at all — anyone could create a
 *  super_admin account. Fixed as part of the security pass.)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';
requireAdminLogin();

if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
    http_response_code(403);
    require_once __DIR__ . '/includes/admin_header.php';
    echo '<div class="alert alert-error"><i class="bi bi-shield-lock-fill"></i> এই পাতা শুধুমাত্র Super Admin-দের জন্য।</div>';
    require_once __DIR__ . '/includes/admin_footer.php';
    exit;
}

$pdo = getDB();
$flash = ''; $flashType = 'success'; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'নিরাপত্তা যাচাই ব্যর্থ। পুনরায় চেষ্টা করুন।';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $fullName = sanitize($_POST['full_name'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role     = sanitize($_POST['role'] ?? 'editor');
        $allowedRoles = ['super_admin', 'admin', 'editor'];
        if (!in_array($role, $allowedRoles, true)) { $role = 'editor'; }

        if (mb_strlen($username) < 3)          { $errors[] = 'ইউজারনেম কমপক্ষে ৩ অক্ষরের হতে হবে।'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'সঠিক ইমেইল দিন।'; }
        if (mb_strlen($fullName) < 2)           { $errors[] = 'পূর্ণ নাম লিখুন।'; }
        if (mb_strlen($password) < 8)           { $errors[] = 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।'; }

        if (empty($errors)) {
            $chk = $pdo->prepare('SELECT id FROM admin_users WHERE username=? OR email=?');
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $errors[] = 'এই ইউজারনেম বা ইমেইল ইতিমধ্যে ব্যবহৃত হচ্ছে।';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare(
                    'INSERT INTO admin_users (username, email, password, full_name, role, is_active) VALUES (?,?,?,?,?,1)'
                )->execute([$username, $email, $hash, $fullName, $role]);
                header('Location: ' . BASE_URL . '/admin/views/settings.php?tab=admins&flash=' . urlencode('নতুন অ্যাডমিন তৈরি হয়েছে।'));
                exit;
            }
        }
    }
}

$csrf = generateCsrfToken();
$currentAdminPage = 'settings';
$pageTitle = 'নতুন অ্যাডমিন | KMA Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>
<div class="max-w-md">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white mb-4"><i class="bi bi-person-plus-fill text-accent"></i> নতুন অ্যাডমিন তৈরি করুন</h1>

  <?php if ($errors): ?>
  <div class="alert alert-error flex-col items-start">
    <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?php echo BASE_URL; ?>/admin/register.php" class="admin-card p-6">
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
    <div class="space-y-4">
      <div>
        <label class="form-label">ইউজারনেম <span class="text-red-500">*</span></label>
        <input type="text" name="username" class="form-input" required minlength="3"/>
      </div>
      <div>
        <label class="form-label">ইমেইল <span class="text-red-500">*</span></label>
        <input type="email" name="email" class="form-input" required/>
      </div>
      <div>
        <label class="form-label">পূর্ণ নাম <span class="text-red-500">*</span></label>
        <input type="text" name="full_name" class="form-input" required/>
      </div>
      <div>
        <label class="form-label">পাসওয়ার্ড <span class="text-red-500">*</span></label>
        <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password"/>
      </div>
      <div>
        <label class="form-label">ভূমিকা (Role)</label>
        <select name="role" class="form-input">
          <option value="editor">Editor</option>
          <option value="admin">Admin</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
    </div>
    <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
      <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> তৈরি করুন</button>
      <a href="<?php echo BASE_URL; ?>/admin/views/settings.php?tab=admins" class="btn-outline">বাতিল</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
