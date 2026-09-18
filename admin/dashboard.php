<?php
/**
 * KMA — admin/dashboard.php  |  PHP 7.2
 * Role-aware landing page. Widgets are only shown/queried if the
 * current user actually has read access to that module.
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';
requireAdminLogin();

$pdo = getDB();
$currentAdminPage = 'dashboard';
$pageTitle = 'Dashboard | KMA Admin';

$flash = ''; $flashType = 'success';
if (!empty($_GET['flash'])) {
    $flash = sanitize($_GET['flash']);
    if (!empty($_GET['flashType'])) { $flashType = sanitize($_GET['flashType']); }
}

$canNotices    = hasPermission('notices', 'read');
$canAdmissions = hasPermission('admissions', 'read');
$canGallery    = hasPermission('gallery', 'read');
$canAccounts   = hasPermission('accounts', 'read');
$isSuper       = kmaIsSuperRole($_SESSION['admin_role'] ?? '');
$canMessages   = $isSuper || ($_SESSION['admin_role'] ?? '') === 'editor';

/* ── Stat counts (only run the query if the user can actually see that module) ── */
$stats = ['notices'=>0, 'admissions'=>0, 'messages'=>0, 'gallery'=>0];
try {
    if ($canNotices)    { $stats['notices']    = (int)$pdo->query('SELECT COUNT(*) FROM notices WHERE is_active=1')->fetchColumn(); }
    if ($canAdmissions) { $stats['admissions'] = (int)$pdo->query('SELECT COUNT(*) FROM admissions WHERE status="pending"')->fetchColumn(); }
    if ($canMessages)   { $stats['messages']   = (int)$pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read=0')->fetchColumn(); }
    if ($canGallery)    { $stats['gallery']    = (int)$pdo->query('SELECT COUNT(*) FROM gallery WHERE is_active=1')->fetchColumn(); }
} catch (Exception $e) { error_log('dashboard.php stats error: ' . $e->getMessage()); }

/* ── Accounts summary (only for those with access) ── */
$acctIncome = 0; $acctExpense = 0;
if ($canAccounts) {
    try {
        $acctIncome  = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
        $acctExpense = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
    } catch (Exception $e) { /* accounts tables may not be migrated yet */ }
}

/* ── Recent lists ── */
$recentAdmissions = [];
if ($canAdmissions) {
    try {
        $recentAdmissions = $pdo->query(
            'SELECT a.*, c.class_name FROM admissions a
             LEFT JOIN classes c ON c.id=a.apply_class_id
             ORDER BY a.created_at DESC LIMIT 5'
        )->fetchAll();
    } catch (Exception $e) {}
}

$recentMessages = [];
if ($canMessages) {
    try { $recentMessages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5')->fetchAll(); }
    catch (Exception $e) {}
}

$recentNotices = [];
if ($canNotices) {
    try { $recentNotices = $pdo->query('SELECT * FROM notices ORDER BY created_at DESC LIMIT 5')->fetchAll(); }
    catch (Exception $e) {}
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Page heading -->
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <div>
    <h1 class="text-xl font-bold text-kma-dark dark:text-white"><?php echo t('nav_dashboard'); ?></h1>
    <p class="text-kma-muted text-sm mt-0.5"><?php echo t('welcome'); ?>, <?php echo h(isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'); ?>!</p>
  </div>
  <div class="text-xs text-kma-muted bg-white dark:bg-gray-800 border border-kma-border dark:border-gray-700 px-3 py-1.5 rounded-lg">
    <i class="bi bi-clock"></i> <?php echo date('d M Y, h:i A'); ?>
  </div>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>

<?php if (!$canNotices && !$canAdmissions && !$canGallery && !$canAccounts): ?>
<div class="admin-card p-8 text-center text-kma-muted text-sm">
  <i class="bi bi-shield-lock text-3xl block mb-3 opacity-40"></i>
  <span data-i18n-en>You don't have access to any modules yet. Contact a Super Admin.</span>
  <span data-i18n-bn>আপনার এখনো কোনো মডিউল অ্যাক্সেস নেই। সুপার অ্যাডমিনের সাথে যোগাযোগ করুন।</span>
</div>
<?php endif; ?>

<!-- Stat cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $statCards = [];
  if ($canNotices)    { $statCards[] = ['bi-bell-fill', 'bg-blue-500', $stats['notices'], t_plain('nav_notices'), BASE_URL.'/admin/views/notices.php']; }
  if ($canAdmissions) { $statCards[] = ['bi-hourglass-split', 'bg-amber-500', $stats['admissions'], 'Pending Requests', BASE_URL.'/admin/views/admissions.php?status=pending']; }
  if ($canMessages)   { $statCards[] = ['bi-chat-dots-fill', 'bg-red-500', $stats['messages'], t_plain('nav_messages'), BASE_URL.'/admin/views/messages.php']; }
  if ($canGallery)    { $statCards[] = ['bi-images', 'bg-accent', $stats['gallery'], t_plain('nav_gallery'), BASE_URL.'/admin/views/gallery.php']; }
  foreach ($statCards as $i => $sc): ?>
  <a href="<?php echo h($sc[4]); ?>"
     class="admin-stat-card p-5 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all group" style="animation-delay:<?php echo $i*60; ?>ms">
    <div class="w-12 h-12 rounded-xl <?php echo h($sc[1]); ?> flex items-center justify-center text-white text-xl flex-shrink-0 group-hover:scale-110 transition-transform">
      <i class="bi <?php echo h($sc[0]); ?>"></i>
    </div>
    <div>
      <div class="font-display text-2xl font-bold text-kma-dark dark:text-white leading-none"><?php echo h($sc[2]); ?></div>
      <div class="text-xs text-kma-muted mt-0.5 font-semibold"><?php echo h($sc[3]); ?></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($canAccounts): ?>
<!-- Accounts summary -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="admin-stat-card p-5 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all">
    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-graph-up-arrow"></i></div>
    <div><div class="text-xl font-bold text-kma-dark dark:text-white leading-none">৳<?php echo number_format($acctIncome,0); ?></div><div class="text-xs text-kma-muted mt-0.5 font-semibold"><?php echo t('total_income'); ?></div></div>
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="admin-stat-card p-5 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all">
    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-graph-down-arrow"></i></div>
    <div><div class="text-xl font-bold text-kma-dark dark:text-white leading-none">৳<?php echo number_format($acctExpense,0); ?></div><div class="text-xs text-kma-muted mt-0.5 font-semibold"><?php echo t('total_expense'); ?></div></div>
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="admin-stat-card p-5 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all">
    <div class="w-12 h-12 rounded-xl <?php echo ($acctIncome-$acctExpense)>=0?'bg-blue-100 text-blue-600':'bg-red-100 text-red-600'; ?> flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-wallet2"></i></div>
    <div><div class="text-xl font-bold text-kma-dark dark:text-white leading-none">৳<?php echo number_format($acctIncome-$acctExpense,0); ?></div><div class="text-xs text-kma-muted mt-0.5 font-semibold"><?php echo t('balance'); ?></div></div>
  </a>
</div>
<?php endif; ?>

<!-- Quick actions -->
<?php
$quickActions = [];
if (hasPermission('notices', 'insert'))    { $quickActions[] = [BASE_URL.'/admin/views/notices.php?action=add', 'bi-plus-circle-fill', 'New Notice', 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800']; }
if (hasPermission('admissions', 'insert')) { $quickActions[] = [BASE_URL.'/admin/views/admission-form.php', 'bi-person-plus-fill', 'New Admission', 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800']; }
if (hasPermission('gallery', 'insert'))    { $quickActions[] = [BASE_URL.'/admin/views/gallery.php?action=add', 'bi-image-fill', 'Add Photo', 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800']; }
if (hasPermission('holidays', 'insert'))   { $quickActions[] = [BASE_URL.'/admin/views/holidays.php?action=add', 'bi-calendar-plus-fill', 'Add Holiday', 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800']; }
if (hasPermission('downloads', 'insert'))  { $quickActions[] = [BASE_URL.'/admin/views/downloads.php?action=add', 'bi-upload', 'Upload File', 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800']; }
if (hasPermission('accounts', 'insert'))   { $quickActions[] = [BASE_URL.'/admin/views/accounts.php?action=add', 'bi-cash-coin', 'New Transaction', 'bg-teal-50 text-teal-700 border-teal-200 hover:bg-teal-100 dark:bg-teal-900/20 dark:text-teal-400 dark:border-teal-800']; }
if ($isSuper) { $quickActions[] = [BASE_URL.'/admin/views/settings.php', 'bi-gear-fill', 'Settings', 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700']; }
if (!empty($quickActions)):
?>
<div class="admin-card p-5 mb-6">
  <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-3"><i class="bi bi-lightning-fill text-gold mr-1"></i> <span data-i18n-en>Quick Actions</span><span data-i18n-bn>দ্রুত অ্যাকশন</span></h2>
  <div class="flex flex-wrap gap-2">
    <?php foreach ($quickActions as $qa): ?>
    <a href="<?php echo h($qa[0]); ?>" class="flex items-center gap-1.5 border px-3 py-2 rounded-lg text-xs font-bold transition-colors <?php echo h($qa[3]); ?>">
      <i class="bi <?php echo h($qa[1]); ?>"></i> <?php echo h($qa[2]); ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Two-col tables -->
<?php if ($canAdmissions || $canMessages): ?>
<div class="grid lg:grid-cols-2 gap-5 mb-6">

  <?php if ($canAdmissions): ?>
  <div class="admin-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-kma-border dark:border-gray-700">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white"><i class="bi bi-person-plus-fill text-amber-500 mr-1"></i> <span data-i18n-en>Recent Admissions</span><span data-i18n-bn>সাম্প্রতিক আবেদন</span></h2>
      <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php" class="text-xs text-accent font-semibold hover:underline">All →</a>
    </div>
    <?php if (empty($recentAdmissions)): ?>
    <div class="px-5 py-8 text-center text-kma-muted text-sm"><i class="bi bi-inbox text-2xl block mb-2 opacity-40"></i>No admissions yet</div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table>
        <thead><tr><th>Student</th><th>Class</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recentAdmissions as $adm):
            $stBadge = ['pending'=>'badge bg-amber-100 text-amber-700','approved'=>'badge bg-green-100 text-green-700','rejected'=>'badge bg-red-100 text-red-700','enrolled'=>'badge bg-blue-100 text-blue-700'];
            $stLabel = ['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','enrolled'=>'Enrolled'];
            $bc = isset($stBadge[$adm['status']]) ? $stBadge[$adm['status']] : 'badge bg-gray-100 text-gray-600';
            $bl = isset($stLabel[$adm['status']]) ? $stLabel[$adm['status']] : $adm['status'];
          ?>
          <tr>
            <td>
              <div class="font-semibold text-kma-dark dark:text-gray-200 text-xs"><?php echo h($adm['student_name_bn']); ?></div>
              <div class="text-kma-muted text-[0.7rem]"><?php echo h($adm['app_no']); ?></div>
            </td>
            <td class="text-xs"><?php echo h($adm['class_name'] ?? '—'); ?></td>
            <td class="text-xs text-kma-muted"><?php echo date('d/m/y', strtotime($adm['created_at'])); ?></td>
            <td><span class="<?php echo h($bc); ?>"><?php echo h($bl); ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($canMessages): ?>
  <div class="admin-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-kma-border dark:border-gray-700">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white"><i class="bi bi-chat-dots-fill text-red-500 mr-1"></i> <span data-i18n-en>Recent Messages</span><span data-i18n-bn>সাম্প্রতিক বার্তা</span></h2>
      <a href="<?php echo BASE_URL; ?>/admin/views/messages.php" class="text-xs text-accent font-semibold hover:underline">All →</a>
    </div>
    <?php if (empty($recentMessages)): ?>
    <div class="px-5 py-8 text-center text-kma-muted text-sm"><i class="bi bi-inbox text-2xl block mb-2 opacity-40"></i>No messages yet</div>
    <?php else: ?>
    <div>
      <?php foreach ($recentMessages as $msg): ?>
      <div class="flex items-start gap-3 px-5 py-3.5 border-b border-kma-border dark:border-gray-700 last:border-0 <?php echo !$msg['is_read'] ? 'bg-blue-50 dark:bg-blue-900/10' : ''; ?> hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
        <div class="w-8 h-8 rounded-full bg-accent-light dark:bg-green-900/30 flex items-center justify-center text-accent font-bold text-xs flex-shrink-0 mt-0.5">
          <?php echo strtoupper(mb_substr($msg['name'], 0, 1)); ?>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <span class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($msg['name']); ?></span>
            <?php if (!$msg['is_read']): ?><span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span><?php endif; ?>
          </div>
          <div class="text-xs text-kma-muted truncate"><?php echo h($msg['subject']); ?></div>
          <div class="text-[0.7rem] text-kma-muted mt-0.5"><?php echo h($msg['phone']); ?> · <?php echo date('d/m/y', strtotime($msg['created_at'])); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
<?php endif; ?>

<?php if ($canNotices): ?>
<!-- Recent Notices -->
<div class="admin-card overflow-hidden">
  <div class="flex items-center justify-between px-5 py-4 border-b border-kma-border dark:border-gray-700">
    <h2 class="text-sm font-bold text-kma-dark dark:text-white"><i class="bi bi-bell-fill text-blue-500 mr-1"></i> <span data-i18n-en>Recent Notices</span><span data-i18n-bn>সাম্প্রতিক নোটিশ</span></h2>
    <a href="<?php echo BASE_URL; ?>/admin/views/notices.php" class="text-xs text-accent font-semibold hover:underline">All →</a>
  </div>
  <?php if (empty($recentNotices)): ?>
  <div class="px-5 py-8 text-center text-kma-muted text-sm"><i class="bi bi-bell-slash text-2xl block mb-2 opacity-40"></i>No notices yet</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($recentNotices as $nt): ?>
        <tr>
          <td>
            <div class="font-semibold text-xs text-kma-dark dark:text-gray-200 max-w-[220px] truncate"><?php echo h($nt['title']); ?></div>
            <?php if ($nt['is_pinned']): ?><span class="badge bg-gold/20 text-yellow-700 text-[0.6rem]"><i class="bi bi-pin-fill"></i> Pinned</span><?php endif; ?>
          </td>
          <td><span class="badge <?php echo h(noticeCategoryClass($nt['category'])); ?>"><?php echo h(noticeCategoryLabel($nt['category'])); ?></span></td>
          <td class="text-xs text-kma-muted"><?php echo date('d/m/y', strtotime($nt['notice_date'])); ?></td>
          <td><span class="badge <?php echo $nt['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $nt['is_active'] ? t_plain('active') : t_plain('inactive'); ?></span></td>
          <td><a href="<?php echo BASE_URL; ?>/admin/views/notices.php?action=edit&id=<?php echo (int)$nt['id']; ?>" class="text-accent hover:underline text-xs font-semibold"><?php echo t('edit'); ?></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($isSuper): ?>
<div class="mt-4 text-center">
  <a href="<?php echo BASE_URL; ?>/admin/views/error-log.php" class="text-xs text-kma-muted hover:text-accent"><i class="bi bi-bug-fill"></i> <?php echo t('nav_error_log'); ?></a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
