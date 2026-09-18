<?php
/**
 * KMA — admin/views/backup.php  |  PHP 7.2
 * Super-admin only. Exports the entire database (structure + data)
 * as a downloadable .sql file, using pure PHP/PDO — no dependency
 * on shell_exec/mysqldump, so it works on locked-down shared hosting.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

if (!kmaIsSuperRole($_SESSION['admin_role'] ?? '')) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php?flash=' . urlencode('Super Admin only.') . '&flashType=error');
    exit;
}

function kmaBackupDatabase(PDO $pdo, $dbName)
{
    $output = "-- ============================================================\n";
    $output .= "-- KMA Database Backup\n";
    $output .= "-- Database: {$dbName}\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- ============================================================\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $output .= "SET NAMES utf8mb4;\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        /* Structure */
        $output .= "-- --------------------------------------------------------\n";
        $output .= "-- Table: `{$table}`\n";
        $output .= "-- --------------------------------------------------------\n";
        $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $createSql = isset($createRow['Create Table']) ? $createRow['Create Table'] : '';
        $output .= $createSql . ";\n\n";

        /* Data */
        $rowCount = (int)$pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        if ($rowCount > 0) {
            $colsStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
            $columns = [];
            foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $c) { $columns[] = $c['Field']; }
            $colList = '`' . implode('`,`', $columns) . '`';

            $batchSize = 200;
            for ($offset = 0; $offset < $rowCount; $offset += $batchSize) {
                $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)) { break; }
                $valueRows = [];
                foreach ($rows as $row) {
                    $vals = [];
                    foreach ($columns as $col) {
                        $v = $row[$col];
                        $vals[] = $v === null ? 'NULL' : $pdo->quote($v);
                    }
                    $valueRows[] = '(' . implode(',', $vals) . ')';
                }
                $output .= "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valueRows) . ";\n";
            }
            $output .= "\n";
        }
    }

    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $output;
}

if (isset($_GET['download']) && $_GET['download'] === '1') {
    if (!verifyCsrfToken(isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')) {
        die('Security check failed.');
    }
    $pdo = getDB();
    $sql = kmaBackupDatabase($pdo, DB_NAME);
    $filename = 'kma_backup_' . date('Y-m-d_His') . '.sql';

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

$pdo = getDB();
$tableCount = 0; $totalRows = 0; $dbSizeMb = 0;
try {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);
    foreach ($tables as $t) { $totalRows += (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn(); }
    $sizeRow = $pdo->query(
        "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
         FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'"
    )->fetch();
    $dbSizeMb = $sizeRow && $sizeRow['size_mb'] ? $sizeRow['size_mb'] : 0;
} catch (Exception $e) { error_log('backup.php info error: ' . $e->getMessage()); }

$currentAdminPage = 'backup';
$pageTitle = 'Database Backup | KMA Admin';
$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<h1 class="text-lg font-bold text-kma-dark dark:text-white mb-5"><span data-i18n-en>Database Backup</span><span data-i18n-bn>ডাটাবেজ ব্যাকআপ</span></h1>

<div class="grid lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2">
    <div class="admin-card p-6">
      <div class="flex items-center gap-4 mb-5">
        <div class="w-14 h-14 rounded-2xl bg-accent-light text-accent flex items-center justify-center text-2xl flex-shrink-0"><i class="bi bi-database-fill-down"></i></div>
        <div>
          <h2 class="font-bold text-kma-dark dark:text-white"><?php echo h(DB_NAME); ?></h2>
          <p class="text-xs text-kma-muted"><?php echo $tableCount; ?> tables · <?php echo number_format($totalRows); ?> rows · ~<?php echo $dbSizeMb; ?> MB</p>
        </div>
      </div>
      <p class="text-sm text-kma-muted mb-5">
        <span data-i18n-en>Download a full backup (structure + data) as a single .sql file. Keep it somewhere safe — you can restore it later via phpMyAdmin's Import tab.</span>
        <span data-i18n-bn>পুরো ডাটাবেজের (গঠন + ডেটা) একটি .sql ফাইল ডাউনলোড করুন। নিরাপদ জায়গায় রাখুন — পরে phpMyAdmin-এর Import ট্যাব দিয়ে পুনরুদ্ধার করা যাবে।</span>
      </p>
      <a href="?download=1&csrf_token=<?php echo h($csrf); ?>" class="btn-primary">
        <i class="bi bi-download"></i> <span data-i18n-en>Download Backup Now</span><span data-i18n-bn>এখনই ব্যাকআপ ডাউনলোড করুন</span>
      </a>
    </div>
  </div>
  <div class="lg:col-span-1">
    <div class="admin-card p-5 bg-gold/10 border-gold/30">
      <h3 class="text-sm font-bold text-kma-dark dark:text-white mb-2"><i class="bi bi-info-circle-fill text-gold mr-1"></i> <span data-i18n-en>Recommendation</span><span data-i18n-bn>পরামর্শ</span></h3>
      <p class="text-xs text-kma-muted leading-relaxed">
        <span data-i18n-en>Take a backup weekly, and always before running a new SQL migration. Store copies outside the server (Google Drive, email to yourself, etc.).</span>
        <span data-i18n-bn>প্রতি সপ্তাহে একবার এবং নতুন কোনো SQL migration চালানোর আগে অবশ্যই ব্যাকআপ নিন। সার্ভারের বাইরেও (Google Drive, নিজের ইমেইলে) একটা কপি রাখুন।</span>
      </p>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
