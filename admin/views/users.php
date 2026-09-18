<?php
/**
 * KMA — admin/views/users.php  |  PHP 7.2
 * Super-admin only. Create staff users with role-based access
 * (accounts / moderator / editor / admin), block/activate them,
 * and fine-tune their exact module permissions via a toggle
 * matrix (overrides the role's default permissions per user).
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

if (!kmaIsSuperRole($_SESSION['admin_role'] ?? '')) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php?flash=' . urlencode('Super Admin only.') . '&flashType=error');
    exit;
}

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'users';
$pageTitle = 'Staff Users | KMA Admin';

$roles = [
    'admin'     => 'Admin',
    'editor'    => 'Editor',
    'accounts'  => 'Accounts',
    'moderator' => 'Moderator',
];
$modules = kmaModuleList();
$moduleLabels = [
    'notices'=>'Notices','admissions'=>'Admissions','classes'=>'Classes & Subjects','faculty'=>'Faculty & Staff',
    'holidays'=>'Holidays','exam_schedule'=>'Exam Schedule','downloads'=>'Downloads','gallery'=>'Gallery',
    'accounts'=>'Accounts','settings'=>'Settings','users'=>'Staff Users',
];

$flash = ''; $flashType = 'success'; $errors = [];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'Security check failed.'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        if ($pa === 'toggle_active') {
            $tid = (int)(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
            if ($tid && $tid !== (int)$_SESSION['admin_id']) {
                $pdo->prepare('UPDATE admin_users SET is_active = NOT is_active WHERE id=?')->execute([$tid]);
            }
            header('Location: ' . BASE_URL . '/admin/views/users.php'); exit;
        }

        if ($pa === 'create') {
            $username = sanitize(isset($_POST['username']) ? $_POST['username'] : '');
            $email    = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
            $fullName = sanitize(isset($_POST['full_name']) ? $_POST['full_name'] : '');
            $password = (string)(isset($_POST['password']) ? $_POST['password'] : '');
            $role     = sanitize(isset($_POST['role']) ? $_POST['role'] : 'moderator');
            if (!array_key_exists($role, $roles)) { $role = 'moderator'; }

            if (mb_strlen($username) < 3) { $errors[] = 'Username must be at least 3 characters.'; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email required.'; }
            if (mb_strlen($fullName) < 2) { $errors[] = 'Full name required.'; }
            if (!kmaIsStrongPassword($password)) { $errors[] = kmaPasswordRuleText(); }

            if (empty($errors)) {
                $chk = $pdo->prepare('SELECT id FROM admin_users WHERE username=? OR email=?');
                $chk->execute([$username, $email]);
                if ($chk->fetch()) {
                    $errors[] = 'Username or email already in use.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $pdo->prepare('INSERT INTO admin_users (username,email,password,full_name,role,is_active) VALUES (?,?,?,?,?,1)')
                        ->execute([$username, $email, $hash, $fullName, $role]);
                    $newId = (int)$pdo->lastInsertId();
                    kmaSeedDefaultPermissions($pdo, $newId, $role);
                    header('Location: ' . BASE_URL . '/admin/views/users.php?flash=' . urlencode('Staff user created.')); exit;
                }
            }
            $action = 'add';
        }

        if ($pa === 'save_permissions') {
            $tid = (int)(isset($_POST['user_id']) ? $_POST['user_id'] : 0);
            $row = $pdo->prepare('SELECT role FROM admin_users WHERE id=?');
            $row->execute([$tid]);
            $target = $row->fetch();
            if ($tid && $target && !kmaIsSuperRole($target['role'])) {
                $stmt = $pdo->prepare(
                    'INSERT INTO admin_permissions (admin_id, module_key, can_read, can_insert, can_edit, can_delete)
                     VALUES (?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE can_read=VALUES(can_read), can_insert=VALUES(can_insert), can_edit=VALUES(can_edit), can_delete=VALUES(can_delete)'
                );
                foreach ($modules as $m) {
                    if ($m === 'users') { continue; } /* users module is always super-admin only, never delegable */
                    $canRead   = isset($_POST['perm'][$m]['read'])   ? 1 : 0;
                    $canInsert = isset($_POST['perm'][$m]['insert']) ? 1 : 0;
                    $canEdit   = isset($_POST['perm'][$m]['edit'])   ? 1 : 0;
                    $canDelete = 0; /* accounts/moderator can NEVER delete — enforced regardless of this form */
                    $stmt->execute([$tid, $m, $canRead, $canInsert, $canEdit, $canDelete]);
                }
                header('Location: ' . BASE_URL . '/admin/views/users.php?flash=' . urlencode('Permissions updated.')); exit;
            }
        }
    }
}

if (!empty($_GET['flash'])) {
    $flash = sanitize($_GET['flash']);
    if (!empty($_GET['flashType'])) { $flashType = sanitize($_GET['flashType']); }
}

$usersList = $pdo->query('SELECT * FROM admin_users ORDER BY FIELD(role,"super_admin","admin","editor","accounts","moderator"), full_name')->fetchAll();

$editUser = null; $editPerms = [];
if ($action === 'permissions' && $id) {
    $row = $pdo->prepare('SELECT * FROM admin_users WHERE id=?');
    $row->execute([$id]);
    $editUser = $row->fetch();
    if (!$editUser || kmaIsSuperRole($editUser['role'])) { $action = 'list'; $editUser = null; }
    else {
        $pStmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id=?');
        $pStmt->execute([$id]);
        foreach ($pStmt->fetchAll() as $p) { $editPerms[$p['module_key']] = $p; }
        /* Fill in role defaults for any module without a stored row yet */
        $defaults = kmaDefaultPermissions($editUser['role']);
        foreach ($modules as $m) {
            if (!isset($editPerms[$m])) { $editPerms[$m] = $defaults[$m]; }
        }
    }
}

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white"><?php echo t('nav_users'); ?></h1>
  <?php if ($action === 'list'): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-person-plus-fill"></i> <?php echo t('add_user'); ?></a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/users.php" class="btn-outline"><i class="bi bi-arrow-left"></i> <?php echo t('back_to_list'); ?></a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<div class="admin-card overflow-hidden">
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>Name</th><th>Username</th><th><?php echo t('role'); ?></th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($usersList as $u): $isSuper = kmaIsSuperRole($u['role']); ?>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center text-xs font-bold flex-shrink-0"><?php echo strtoupper(mb_substr($u['full_name'],0,1)); ?></div>
              <div>
                <div class="text-xs font-semibold text-kma-dark dark:text-gray-200"><?php echo h($u['full_name']); ?></div>
                <div class="text-[0.65rem] text-kma-muted"><?php echo h($u['email']); ?></div>
              </div>
            </div>
          </td>
          <td class="text-xs font-mono"><?php echo h($u['username']); ?></td>
          <td><span class="badge bg-blue-100 text-blue-700 capitalize"><?php echo h(str_replace('_',' ',$u['role'])); ?></span></td>
          <td>
            <span class="badge <?php echo $u['is_active']?'bg-green-100 text-green-700':'bg-gray-200 text-gray-600'; ?>">
              <?php echo $u['is_active'] ? t_plain('active') : t_plain('blocked'); ?>
            </span>
          </td>
          <td>
            <div class="flex items-center gap-2">
              <?php if (!$isSuper): ?>
              <a href="?action=permissions&id=<?php echo (int)$u['id']; ?>" class="text-accent hover:underline text-xs font-semibold"><i class="bi bi-sliders"></i> <?php echo t('permissions'); ?></a>
              <?php endif; ?>
              <?php if ((int)$u['id'] !== (int)$_SESSION['admin_id']): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="toggle_active"/>
                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>"/>
                <button type="submit" class="text-xs <?php echo $u['is_active']?'text-red-500 hover:text-red-700':'text-green-600 hover:text-green-800'; ?> font-semibold">
                  <?php echo $u['is_active'] ? t_plain('block') : t_plain('activate'); ?>
                </button>
              </form>
              <?php else: ?>
              <span class="text-[0.65rem] text-kma-muted">(you)</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif ($action === 'add'): ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/users.php" class="admin-card p-6 max-w-lg">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="create"/>
  <div class="space-y-4">
    <div>
      <label class="form-label">Username <span class="text-red-500">*</span></label>
      <input type="text" name="username" class="form-input" required minlength="3"/>
    </div>
    <div>
      <label class="form-label">Email <span class="text-red-500">*</span></label>
      <input type="email" name="email" class="form-input" required/>
    </div>
    <div>
      <label class="form-label">Full Name <span class="text-red-500">*</span></label>
      <input type="text" name="full_name" class="form-input" required/>
    </div>
    <div>
      <label class="form-label">Password <span class="text-red-500">*</span></label>
      <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password"/>
      <p class="text-xs text-kma-muted mt-1">কমপক্ষে ৮ অক্ষর, ১টি বড় হাতের অক্ষর, ১টি ছোট হাতের অক্ষর ও ১টি সংখ্যা থাকতে হবে।</p>
    </div>
    <div>
      <label class="form-label"><?php echo t('role'); ?></label>
      <select name="role" class="form-input">
        <?php foreach ($roles as $rv=>$rl): ?>
        <option value="<?php echo h($rv); ?>" <?php echo $rv==='moderator'?'selected':''; ?>><?php echo h($rl); ?></option>
        <?php endforeach; ?>
      </select>
      <p class="text-xs text-kma-muted mt-1">You can fine-tune exact module access afterward from the Permissions screen.</p>
    </div>
  </div>
  <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> Create User</button>
    <a href="<?php echo BASE_URL; ?>/admin/views/users.php" class="btn-outline"><?php echo t('cancel'); ?></a>
  </div>
</form>

<?php elseif ($action === 'permissions' && $editUser): ?>

<div class="admin-card p-5 mb-4 flex items-center gap-3">
  <div class="w-10 h-10 rounded-full bg-accent text-white flex items-center justify-center font-bold flex-shrink-0"><?php echo strtoupper(mb_substr($editUser['full_name'],0,1)); ?></div>
  <div>
    <div class="font-bold text-sm text-kma-dark dark:text-white"><?php echo h($editUser['full_name']); ?></div>
    <div class="text-xs text-kma-muted capitalize"><?php echo h(str_replace('_',' ',$editUser['role'])); ?> · <?php echo h($editUser['email']); ?></div>
  </div>
</div>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/users.php" class="admin-card p-5">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="save_permissions"/>
  <input type="hidden" name="user_id" value="<?php echo (int)$editUser['id']; ?>"/>

  <div class="overflow-x-auto">
    <table>
      <thead><tr>
        <th><?php echo t('module'); ?></th>
        <th class="text-center"><?php echo t('read'); ?></th>
        <th class="text-center"><?php echo t('insert'); ?></th>
        <th class="text-center"><?php echo t('edit'); ?></th>
        <th class="text-center">Delete</th>
      </tr></thead>
      <tbody>
        <?php foreach ($modules as $m):
          if ($m === 'users') { continue; }
          $p = $editPerms[$m];
        ?>
        <tr>
          <td class="text-xs font-semibold"><?php echo h(isset($moduleLabels[$m]) ? $moduleLabels[$m] : $m); ?></td>
          <td class="text-center">
            <label class="perm-switch"><input type="checkbox" name="perm[<?php echo h($m); ?>][read]" <?php echo !empty($p['can_read'])?'checked':''; ?>><span></span></label>
          </td>
          <td class="text-center">
            <label class="perm-switch"><input type="checkbox" name="perm[<?php echo h($m); ?>][insert]" <?php echo !empty($p['can_insert'])?'checked':''; ?>><span></span></label>
          </td>
          <td class="text-center">
            <label class="perm-switch"><input type="checkbox" name="perm[<?php echo h($m); ?>][edit]" <?php echo !empty($p['can_edit'])?'checked':''; ?>><span></span></label>
          </td>
          <td class="text-center">
            <span class="text-xs text-kma-muted" title="Delete is never allowed for staff roles"><i class="bi bi-lock-fill"></i></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-xs text-kma-muted mt-3"><i class="bi bi-info-circle-fill text-gold"></i> Delete permission is permanently disabled for the Accounts and Moderator roles, for every module, regardless of these toggles.</p>

  <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo t('save'); ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/users.php" class="btn-outline"><?php echo t('cancel'); ?></a>
  </div>
</form>

<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
