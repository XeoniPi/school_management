<?php
/**
 * KMA — admin/views/profile.php  |  PHP 7.2
 * Any logged-in admin (regardless of role) can update their own
 * full name and password here — separate from the site-wide
 * Settings page, which stays restricted to super_admin/admin.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo = getDB();
$adminId = (int)$_SESSION['admin_id'];
$flash = ''; $flashType = 'success'; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Security check failed.';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        if ($pa === 'update_name') {
            $fullName = sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : '');
            if (mb_strlen($fullName) < 2) {
                $errors[] = 'Full name is required.';
            } else {
                $pdo->prepare('UPDATE admin_users SET full_name=? WHERE id=?')->execute([$fullName, $adminId]);
                $_SESSION['admin_name'] = $fullName;
                $flash = 'Profile updated.';
            }
        }

        if ($pa === 'change_password') {
            $current = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $newPwd  = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            $row = $pdo->prepare('SELECT password FROM admin_users WHERE id=?');
            $row->execute([$adminId]);
            $me = $row->fetch();

            if (!$me || !password_verify($current, $me['password'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (!kmaIsStrongPassword($newPwd)) {
                $errors[] = 'Password must be 8+ characters with at least one uppercase letter, one lowercase letter, and one number.';
            } elseif ($newPwd !== $confirm) {
                $errors[] = 'Password confirmation does not match.';
            } else {
                $pdo->prepare('UPDATE admin_users SET password=? WHERE id=?')
                    ->execute([password_hash($newPwd, PASSWORD_BCRYPT), $adminId]);
                $flash = 'Password changed successfully.';
            }
        }
    }
}

$row = $pdo->prepare('SELECT * FROM admin_users WHERE id=?');
$row->execute([$adminId]);
$me = $row->fetch();

$currentAdminPage = '';
$pageTitle = 'My Profile | KMA Admin';
$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<h1 class="text-lg font-bold text-kma-dark dark:text-white mb-5"><?php echo t('my_profile'); ?></h1>

<?php if ($flash): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="grid lg:grid-cols-2 gap-5 max-w-3xl">

  <form method="POST" class="admin-card p-5">
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
    <input type="hidden" name="post_action" value="update_name"/>
    <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4">Basic Info</h2>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" class="form-input" value="<?php echo h($me['username']); ?>" disabled/>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="text" class="form-input" value="<?php echo h($me['email']); ?>" disabled/>
    </div>
    <div class="mb-4">
      <label class="form-label">Full Name</label>
      <input type="text" name="full_name" class="form-input" value="<?php echo h($me['full_name']); ?>" required/>
    </div>
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo t('save'); ?></button>
  </form>

  <form method="POST" class="admin-card p-5">
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
    <input type="hidden" name="post_action" value="change_password"/>
    <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4"><?php echo t('change_password'); ?></h2>
    <div class="mb-3">
      <label class="form-label">Current Password</label>
      <input type="password" name="current_password" class="form-input" required autocomplete="current-password"/>
    </div>
    <div class="mb-3">
      <label class="form-label">New Password</label>
      <input type="password" name="new_password" class="form-input" required minlength="8" autocomplete="new-password"/>
      <p class="text-xs text-kma-muted mt-1">At least 8 characters, 1 uppercase, 1 lowercase, 1 number.</p>
    </div>
    <div class="mb-4">
      <label class="form-label">Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-input" required minlength="8" autocomplete="new-password"/>
    </div>
    <button type="submit" class="btn-primary"><i class="bi bi-lock-fill"></i> <?php echo t('change_password'); ?></button>
  </form>

</div>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
