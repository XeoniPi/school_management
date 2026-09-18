<?php
/**
 * KMA — admin/views/error-log.php  |  PHP 7.2
 * Super-admin only. Shows the tail of logs/php_errors.log so real
 * PHP errors (fatal DB errors, etc.) are visible from the dashboard
 * without needing FTP/file access.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

if (!kmaIsSuperRole($_SESSION['admin_role'] ?? '')) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php?flash=' . urlencode('Super Admin only.') . '&flashType=error');
    exit;
}

$currentAdminPage = 'error-log';
$pageTitle = 'Error Logs | KMA Admin';
$logPath = dirname(dirname(__DIR__)) . '/logs/php_errors.log';

$flash = ''; $flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'Security check failed.'; $flashType = 'error';
    } elseif (isset($_POST['post_action']) && $_POST['post_action'] === 'clear') {
        if (file_exists($logPath) && is_writable($logPath)) {
            file_put_contents($logPath, '');
            $flash = 'Log cleared.';
        }
        header('Location: ' . BASE_URL . '/admin/views/error-log.php?flash=' . urlencode($flash)); exit;
    }
}
if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

/* Read the last N lines efficiently */
$lines = [];
$fileSize = 0;
if (file_exists($logPath)) {
    $fileSize = filesize($logPath);
    $maxLines = 300;
    $content = file_get_contents($logPath);
    $allLines = explode("\n", $content);
    $lines = array_slice($allLines, -$maxLines);
    $lines = array_reverse(array_filter($lines, function ($l) { return trim($l) !== ''; }));
}

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white">Error Logs</h1>
    <p class="text-kma-muted text-xs mt-0.5">
      <?php echo file_exists($logPath) ? 'logs/php_errors.log · ' . number_format($fileSize/1024, 1) . ' KB · showing last ' . count($lines) . ' lines (newest first)' : 'Log file not found yet — no errors logged.'; ?>
    </p>
  </div>
  <div class="flex gap-2">
    <a href="<?php echo BASE_URL; ?>/admin/views/error-log.php" class="btn-outline text-sm"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
    <?php if (!empty($lines)): ?>
    <form method="POST" onsubmit="return confirm('Clear the entire log file?')">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
      <input type="hidden" name="post_action" value="clear"/>
      <button type="submit" class="btn-danger text-sm"><i class="bi bi-trash-fill"></i> Clear Log</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>

<div class="admin-card overflow-hidden">
  <?php if (empty($lines)): ?>
  <div class="py-16 text-center text-kma-muted text-sm"><i class="bi bi-shield-check text-4xl block mb-3 text-green-500"></i>No errors logged. Everything looks healthy.</div>
  <?php else: ?>
  <div class="max-h-[70vh] overflow-y-auto">
    <?php foreach ($lines as $i => $line):
      $isError = (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false || stripos($line, 'exception') !== false);
    ?>
    <div class="px-4 py-2.5 text-xs font-mono border-b border-kma-border dark:border-gray-700 <?php echo $isError ? 'bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400' : 'text-kma-muted'; ?> break-all">
      <?php echo h($line); ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
