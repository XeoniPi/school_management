<?php
/**
 * KMA — admin/views/accounts.php  |  PHP 7.2
 * Income/expense ledger for the school. Categories are fixed
 * (defined below). Every transaction can optionally be linked to
 * a student (admissions.id) or a faculty/staff member (faculty.id)
 * for per-person transaction history (see profile-ledger.php) and
 * can generate a printable A4 invoice (see invoice.php).
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();
requirePermission('accounts', 'read');

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'accounts';
$pageTitle = 'Accounts | KMA Admin';

$incomeCats  = [
    'admission_fee' => 'Admission Fee',
    'monthly_fee'   => 'Monthly Fee',
    'exam_fee'      => 'Exam Fee',
    'donation'      => 'Donation',
    'other'         => 'Other',
];
$expenseCats = [
    'salary'          => 'Salary',
    'bonus'           => 'Bonus',
    'utilities'       => 'Utilities',
    'electric_bill'   => 'Electric Bill',
    'internet_bill'   => 'Internet Bill',
    'office_expense'  => 'Office Expense',
    'event'           => 'Event',
    'meeting'         => 'Meeting',
];
$allCatLabels = $incomeCats + $expenseCats;

$flash = ''; $flashType = 'success'; $errors = [];
$tx = [
    'type'=>'income', 'category'=>'admission_fee', 'amount'=>'', 'transaction_date'=>date('Y-m-d'),
    'payment_method'=>'cash', 'remark'=>'', 'party_type'=>'other', 'student_id'=>'', 'faculty_id'=>'', 'party_name'=>'',
];

function generateInvoiceNo($pdo)
{
    $year  = date('Y');
    $count = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE YEAR(created_at) = " . (int)$year)->fetchColumn();
    return 'INV-' . $year . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
}

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'Security check failed.'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        if ($pa === 'delete') {
            if (!hasPermission('accounts', 'delete')) {
                $flash = 'You do not have permission to delete transactions.'; $flashType = 'error';
            } else {
                $tid = (int)(isset($_POST['tx_id']) ? $_POST['tx_id'] : 0);
                if ($tid) {
                    $pdo->prepare('DELETE FROM transactions WHERE id=?')->execute([$tid]);
                    $flash = 'Transaction deleted.';
                }
            }
            header('Location: ' . BASE_URL . '/admin/views/accounts.php?flash=' . urlencode($flash) . '&flashType=' . $flashType); exit;
        }

        if (in_array($pa, ['add', 'edit'])) {
            $canWrite = $pa === 'add' ? hasPermission('accounts', 'insert') : hasPermission('accounts', 'edit');
            if (!$canWrite) {
                $flash = 'You do not have permission to do that.'; $flashType = 'error';
            } else {
                $eid = (int)(isset($_POST['tx_id']) ? $_POST['tx_id'] : 0);
                $tx = [
                    'type'             => sanitize(isset($_POST['type']) ? $_POST['type'] : 'income'),
                    'category'         => sanitize(isset($_POST['category']) ? $_POST['category'] : ''),
                    'amount'           => (float)(isset($_POST['amount']) ? $_POST['amount'] : 0),
                    'transaction_date' => sanitize(isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d')),
                    'payment_method'   => sanitize(isset($_POST['payment_method']) ? $_POST['payment_method'] : ''),
                    'remark'           => sanitize(isset($_POST['remark']) ? $_POST['remark'] : ''),
                    'party_type'       => sanitize(isset($_POST['party_type']) ? $_POST['party_type'] : 'other'),
                    'student_id'       => (int)(isset($_POST['student_id']) ? $_POST['student_id'] : 0) ?: null,
                    'faculty_id'       => (int)(isset($_POST['faculty_id']) ? $_POST['faculty_id'] : 0) ?: null,
                    'party_name'       => sanitize(isset($_POST['party_name']) ? $_POST['party_name'] : ''),
                ];
                if ($tx['party_type'] !== 'student') { $tx['student_id'] = null; }
                if ($tx['party_type'] !== 'faculty')  { $tx['faculty_id'] = null; }

                if (!in_array($tx['type'], ['income','expense'])) { $errors[] = 'Invalid type.'; }
                $validCats = $tx['type'] === 'income' ? $incomeCats : $expenseCats;
                if (!array_key_exists($tx['category'], $validCats)) { $errors[] = 'Invalid category.'; }
                if ($tx['amount'] <= 0) { $errors[] = 'Amount must be greater than zero.'; }
                if (empty($tx['transaction_date'])) { $errors[] = 'Date is required.'; }

                if (empty($errors)) {
                    if ($pa === 'add') {
                        $invoiceNo = generateInvoiceNo($pdo);
                        $pdo->prepare(
                            'INSERT INTO transactions (invoice_no,type,category,amount,transaction_date,payment_method,remark,party_type,student_id,faculty_id,party_name,created_by)
                             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
                        )->execute([
                            $invoiceNo, $tx['type'], $tx['category'], $tx['amount'], $tx['transaction_date'],
                            $tx['payment_method'], $tx['remark'], $tx['party_type'], $tx['student_id'], $tx['faculty_id'],
                            $tx['party_name'], (int)$_SESSION['admin_id'],
                        ]);
                        $flash = 'Transaction added.';
                    } else {
                        $pdo->prepare(
                            'UPDATE transactions SET type=?,category=?,amount=?,transaction_date=?,payment_method=?,remark=?,party_type=?,student_id=?,faculty_id=?,party_name=? WHERE id=?'
                        )->execute([
                            $tx['type'], $tx['category'], $tx['amount'], $tx['transaction_date'],
                            $tx['payment_method'], $tx['remark'], $tx['party_type'], $tx['student_id'], $tx['faculty_id'],
                            $tx['party_name'], $eid,
                        ]);
                        $flash = 'Transaction updated.';
                    }
                    header('Location: ' . BASE_URL . '/admin/views/accounts.php?flash=' . urlencode($flash)); exit;
                }
            }
            $action = $pa === 'edit' ? 'edit' : 'add';
        }
    }
}

if (!empty($_GET['flash'])) {
    $flash = sanitize($_GET['flash']);
    if (!empty($_GET['flashType'])) { $flashType = sanitize($_GET['flashType']); }
}

if ($action === 'edit' && $id) {
    if (!hasPermission('accounts', 'edit')) { $action = 'list'; }
    else {
        $row = $pdo->prepare('SELECT * FROM transactions WHERE id=?');
        $row->execute([$id]);
        $f = $row->fetch();
        if ($f) { $tx = $f; } else { $action = 'list'; }
    }
}
if ($action === 'add' && !hasPermission('accounts', 'insert')) { $action = 'list'; }

/* Lookup lists for the party pickers */
$studentsList = $pdo->query("SELECT id, student_name_bn, app_no FROM admissions ORDER BY created_at DESC LIMIT 300")->fetchAll();
$facultyListAll = $pdo->query("SELECT id, name_bn, designation FROM faculty WHERE is_active=1 ORDER BY name_bn")->fetchAll();

/* ── Summary cards (all-time + this month) ── */
$sumTotalIncome  = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
$sumTotalExpense = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$sumMonthIncome  = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'  AND MONTH(transaction_date)=MONTH(CURDATE()) AND YEAR(transaction_date)=YEAR(CURDATE())")->fetchColumn();
$sumMonthExpense = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND MONTH(transaction_date)=MONTH(CURDATE()) AND YEAR(transaction_date)=YEAR(CURDATE())")->fetchColumn();
$balance = $sumTotalIncome - $sumTotalExpense;

/* ── List + filters ── */
$fType = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$fCat  = isset($_GET['cat'])  ? sanitize($_GET['cat'])  : '';
$fFrom = isset($_GET['from']) ? sanitize($_GET['from']) : '';
$fTo   = isset($_GET['to'])   ? sanitize($_GET['to'])   : '';

$where = '1=1'; $params = [];
if ($fType === 'income' || $fType === 'expense') { $where .= ' AND t.type=?'; $params[] = $fType; }
if ($fCat !== '') { $where .= ' AND t.category=?'; $params[] = $fCat; }
if ($fFrom !== '') { $where .= ' AND t.transaction_date >= ?'; $params[] = $fFrom; }
if ($fTo !== '') { $where .= ' AND t.transaction_date <= ?'; $params[] = $fTo; }

$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t WHERE $where");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$listSql = "SELECT t.*,
                   s.student_name_bn AS student_name, s.app_no,
                   f.name_bn AS faculty_name
            FROM transactions t
            LEFT JOIN admissions s ON s.id = t.student_id
            LEFT JOIN faculty f ON f.id = t.faculty_id
            WHERE $where
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT $perPage OFFSET $offset";
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($params);
$txList = $listStmt->fetchAll();

$csrf = generateCsrfToken();
$canInsert = hasPermission('accounts', 'insert');
$canEdit   = hasPermission('accounts', 'edit');
$canDelete = hasPermission('accounts', 'delete');

require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white"><?php echo t('nav_accounts'); ?></h1>
    <p class="text-kma-muted text-xs mt-0.5"><span data-i18n-en>Income, expenses &amp; invoices</span><span data-i18n-bn>আয়, ব্যয় ও ইনভয়েস</span></p>
  </div>
  <?php if ($action === 'list' && $canInsert): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-plus-lg"></i> <?php echo t('add_transaction'); ?></a>
  <?php elseif ($action !== 'list'): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="btn-outline"><i class="bi bi-arrow-left"></i> <?php echo t('back_to_list'); ?></a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- Summary cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="admin-stat-card" style="animation-delay:0ms">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-lg flex-shrink-0"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="min-w-0">
        <div class="text-lg font-bold text-kma-dark dark:text-white truncate">৳ <?php echo number_format($sumTotalIncome, 0); ?></div>
        <div class="text-xs text-kma-muted"><?php echo t('total_income'); ?></div>
      </div>
    </div>
  </div>
  <div class="admin-stat-card" style="animation-delay:60ms">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg flex-shrink-0"><i class="bi bi-graph-down-arrow"></i></div>
      <div class="min-w-0">
        <div class="text-lg font-bold text-kma-dark dark:text-white truncate">৳ <?php echo number_format($sumTotalExpense, 0); ?></div>
        <div class="text-xs text-kma-muted"><?php echo t('total_expense'); ?></div>
      </div>
    </div>
  </div>
  <div class="admin-stat-card" style="animation-delay:120ms">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl <?php echo $balance>=0?'bg-blue-100 text-blue-600':'bg-red-100 text-red-600'; ?> flex items-center justify-center text-lg flex-shrink-0"><i class="bi bi-wallet2"></i></div>
      <div class="min-w-0">
        <div class="text-lg font-bold text-kma-dark dark:text-white truncate">৳ <?php echo number_format($balance, 0); ?></div>
        <div class="text-xs text-kma-muted"><?php echo t('balance'); ?></div>
      </div>
    </div>
  </div>
  <div class="admin-stat-card" style="animation-delay:180ms">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-gold/20 text-yellow-700 flex items-center justify-center text-lg flex-shrink-0"><i class="bi bi-calendar-month"></i></div>
      <div class="min-w-0">
        <div class="text-sm font-bold text-kma-dark dark:text-white truncate">+৳<?php echo number_format($sumMonthIncome,0); ?> / -৳<?php echo number_format($sumMonthExpense,0); ?></div>
        <div class="text-xs text-kma-muted"><?php echo t('this_month'); ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<form method="GET" class="admin-card p-4 mb-4 flex flex-wrap items-end gap-3">
  <div>
    <label class="form-label"><span data-i18n-en>Type</span><span data-i18n-bn>ধরন</span></label>
    <select name="type" class="form-input text-sm py-2" style="min-width:120px">
      <option value="">All</option>
      <option value="income"  <?php echo $fType==='income'?'selected':''; ?>><?php echo t_plain('income'); ?></option>
      <option value="expense" <?php echo $fType==='expense'?'selected':''; ?>><?php echo t_plain('expense'); ?></option>
    </select>
  </div>
  <div>
    <label class="form-label"><?php echo t('category'); ?></label>
    <select name="cat" class="form-input text-sm py-2" style="min-width:150px">
      <option value="">All</option>
      <?php foreach ($allCatLabels as $cv=>$cl): ?>
      <option value="<?php echo h($cv); ?>" <?php echo $fCat===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="form-label">From</label>
    <input type="date" name="from" class="form-input text-sm py-2" value="<?php echo h($fFrom); ?>"/>
  </div>
  <div>
    <label class="form-label">To</label>
    <input type="date" name="to" class="form-input text-sm py-2" value="<?php echo h($fTo); ?>"/>
  </div>
  <button type="submit" class="btn-primary text-sm py-2"><i class="bi bi-funnel-fill"></i> <?php echo t('search'); ?></button>
  <?php if ($fType || $fCat || $fFrom || $fTo): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="btn-outline text-sm py-2"><i class="bi bi-x-lg"></i></a>
  <?php endif; ?>
</form>

<!-- Transaction table -->
<div class="admin-card overflow-hidden">
  <?php if (empty($txList)): ?>
  <div class="py-14 text-center text-kma-muted text-sm"><i class="bi bi-inbox text-3xl block mb-2 opacity-30"></i>
    <span data-i18n-en>No transactions found</span><span data-i18n-bn>কোনো লেনদেন পাওয়া যায়নি</span>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr>
        <th><?php echo t('transaction_date'); ?></th>
        <th><span data-i18n-en>Type</span><span data-i18n-bn>ধরন</span></th>
        <th><?php echo t('category'); ?></th>
        <th><?php echo t('party'); ?></th>
        <th><?php echo t('amount'); ?></th>
        <th><?php echo t('invoice'); ?></th>
        <th><span data-i18n-en>Action</span><span data-i18n-bn>অ্যাকশন</span></th>
      </tr></thead>
      <tbody>
        <?php foreach ($txList as $row):
          $isIncome = $row['type'] === 'income';
          $partyLabel = '—';
          $partyLink = null;
          if ($row['party_type'] === 'student' && $row['student_id']) {
              $partyLabel = $row['student_name'] ? $row['student_name'] : ('#'.$row['student_id']);
              $partyLink = BASE_URL.'/admin/views/profile-ledger.php?type=student&id='.(int)$row['student_id'];
          } elseif ($row['party_type'] === 'faculty' && $row['faculty_id']) {
              $partyLabel = $row['faculty_name'] ? $row['faculty_name'] : ('#'.$row['faculty_id']);
              $partyLink = BASE_URL.'/admin/views/profile-ledger.php?type=faculty&id='.(int)$row['faculty_id'];
          } elseif (!empty($row['party_name'])) {
              $partyLabel = $row['party_name'];
          }
        ?>
        <tr>
          <td class="text-xs whitespace-nowrap"><?php echo date('d M Y', strtotime($row['transaction_date'])); ?></td>
          <td><span class="badge <?php echo $isIncome ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>"><?php echo $isIncome ? 'Income' : 'Expense'; ?></span></td>
          <td class="text-xs"><?php echo h(isset($allCatLabels[$row['category']]) ? $allCatLabels[$row['category']] : $row['category']); ?></td>
          <td class="text-xs">
            <?php if ($partyLink): ?>
            <a href="<?php echo h($partyLink); ?>" class="text-accent hover:underline font-semibold"><?php echo h($partyLabel); ?></a>
            <?php else: ?>
            <?php echo h($partyLabel); ?>
            <?php endif; ?>
          </td>
          <td class="text-sm font-bold <?php echo $isIncome ? 'text-green-600' : 'text-red-600'; ?>">
            <?php echo $isIncome ? '+' : '-'; ?>৳<?php echo number_format($row['amount'], 2); ?>
          </td>
          <td class="text-xs font-mono text-kma-muted"><?php echo h($row['invoice_no']); ?></td>
          <td>
            <div class="flex items-center gap-2">
              <a href="<?php echo BASE_URL; ?>/admin/views/invoice.php?id=<?php echo (int)$row['id']; ?>" target="_blank" class="text-blue-500 hover:text-blue-700 text-xs" title="Invoice"><i class="bi bi-receipt"></i></a>
              <?php if ($canEdit): ?>
              <a href="?action=edit&id=<?php echo (int)$row['id']; ?>" class="text-accent hover:underline text-xs"><i class="bi bi-pencil-fill"></i></a>
              <?php endif; ?>
              <?php if ($canDelete): ?>
              <form method="POST" class="inline" onsubmit="return confirm('Delete this transaction?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete"/>
                <input type="hidden" name="tx_id" value="<?php echo (int)$row['id']; ?>"/>
                <button type="submit" class="text-red-400 hover:text-red-600 text-xs"><i class="bi bi-trash-fill"></i></button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="flex items-center justify-between px-5 py-3 border-t border-kma-border dark:border-gray-700">
    <span class="text-xs text-kma-muted">Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>
    <div class="flex gap-1">
      <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
      <a href="?page=<?php echo $p; ?><?php echo $fType?'&type='.$fType:''; ?><?php echo $fCat?'&cat='.$fCat:''; ?>"
         class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors <?php echo $p===$page?'bg-accent text-white':'bg-kma-bg dark:bg-gray-700 text-kma-muted hover:bg-accent hover:text-white'; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php else: /* ── ADD / EDIT FORM ── */ ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="admin-card p-6 max-w-3xl" id="txForm">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action==='edit'?'edit':'add'; ?>"/>
  <?php if ($action==='edit'): ?><input type="hidden" name="tx_id" value="<?php echo (int)($tx['id']??$id); ?>"/><?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
      <label class="form-label"><span data-i18n-en>Type</span><span data-i18n-bn>ধরন</span> <span class="text-red-500">*</span></label>
      <div class="flex gap-2" id="typeToggle">
        <button type="button" data-type="income" class="type-btn flex-1 py-2.5 rounded-lg text-sm font-bold border-2 <?php echo ($tx['type']??'income')==='income'?'bg-green-50 border-green-500 text-green-700':'border-kma-border text-kma-muted'; ?>"><i class="bi bi-graph-up-arrow"></i> Income</button>
        <button type="button" data-type="expense" class="type-btn flex-1 py-2.5 rounded-lg text-sm font-bold border-2 <?php echo ($tx['type']??'')==='expense'?'bg-red-50 border-red-500 text-red-700':'border-kma-border text-kma-muted'; ?>"><i class="bi bi-graph-down-arrow"></i> Expense</button>
      </div>
      <input type="hidden" name="type" id="typeInput" value="<?php echo h($tx['type']??'income'); ?>"/>
    </div>

    <div>
      <label class="form-label"><?php echo t('category'); ?> <span class="text-red-500">*</span></label>
      <select name="category" id="categorySelect" class="form-input" required>
        <optgroup label="Income" class="income-group">
          <?php foreach ($incomeCats as $cv=>$cl): ?>
          <option value="<?php echo h($cv); ?>" <?php echo ($tx['category']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
          <?php endforeach; ?>
        </optgroup>
        <optgroup label="Expense" class="expense-group">
          <?php foreach ($expenseCats as $cv=>$cl): ?>
          <option value="<?php echo h($cv); ?>" <?php echo ($tx['category']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
          <?php endforeach; ?>
        </optgroup>
      </select>
    </div>
    <div>
      <label class="form-label"><?php echo t('amount'); ?> (৳) <span class="text-red-500">*</span></label>
      <input type="number" step="0.01" min="0.01" name="amount" class="form-input" required value="<?php echo h($tx['amount']??''); ?>"/>
    </div>
    <div>
      <label class="form-label"><?php echo t('transaction_date'); ?> <span class="text-red-500">*</span></label>
      <input type="date" name="transaction_date" class="form-input" required value="<?php echo h($tx['transaction_date']??date('Y-m-d')); ?>"/>
    </div>
    <div>
      <label class="form-label"><?php echo t('payment_method'); ?></label>
      <select name="payment_method" class="form-input">
        <?php foreach (['cash'=>'Cash','bkash'=>'bKash','nagad'=>'Nagad','bank'=>'Bank Transfer','cheque'=>'Cheque'] as $pv=>$pl): ?>
        <option value="<?php echo h($pv); ?>" <?php echo ($tx['payment_method']??'cash')===$pv?'selected':''; ?>><?php echo h($pl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sm:col-span-2">
      <label class="form-label"><?php echo t('party'); ?></label>
      <div class="flex gap-2 mb-2" id="partyToggle">
        <button type="button" data-party="other"   class="party-btn flex-1 py-2 rounded-lg text-xs font-bold border <?php echo ($tx['party_type']??'other')==='other'?'bg-accent text-white border-accent':'border-kma-border text-kma-muted'; ?>">Other / N-A</button>
        <button type="button" data-party="student" class="party-btn flex-1 py-2 rounded-lg text-xs font-bold border <?php echo ($tx['party_type']??'')==='student'?'bg-accent text-white border-accent':'border-kma-border text-kma-muted'; ?>">Student</button>
        <button type="button" data-party="faculty" class="party-btn flex-1 py-2 rounded-lg text-xs font-bold border <?php echo ($tx['party_type']??'')==='faculty'?'bg-accent text-white border-accent':'border-kma-border text-kma-muted'; ?>">Faculty</button>
      </div>
      <input type="hidden" name="party_type" id="partyTypeInput" value="<?php echo h($tx['party_type']??'other'); ?>"/>

      <div id="partyOtherWrap" class="party-field">
        <input type="text" name="party_name" class="form-input" placeholder="Name (optional)" value="<?php echo h($tx['party_name']??''); ?>"/>
      </div>
      <div id="partyStudentWrap" class="party-field hidden">
        <select name="student_id" class="form-input">
          <option value="">— Select student —</option>
          <?php foreach ($studentsList as $st): ?>
          <option value="<?php echo (int)$st['id']; ?>" <?php echo ((int)($tx['student_id']??0))===(int)$st['id']?'selected':''; ?>><?php echo h($st['student_name_bn'].' ('.$st['app_no'].')'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div id="partyFacultyWrap" class="party-field hidden">
        <select name="faculty_id" class="form-input">
          <option value="">— Select faculty/staff —</option>
          <?php foreach ($facultyListAll as $fc): ?>
          <option value="<?php echo (int)$fc['id']; ?>" <?php echo ((int)($tx['faculty_id']??0))===(int)$fc['id']?'selected':''; ?>><?php echo h($fc['name_bn'].' — '.$fc['designation']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="sm:col-span-2">
      <label class="form-label"><?php echo t('remark'); ?> <span class="text-kma-muted font-normal">(optional)</span></label>
      <textarea name="remark" class="form-input" rows="2" placeholder="e.g. source/reason details..."><?php echo h($tx['remark']??''); ?></textarea>
    </div>
  </div>

  <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo t('save'); ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="btn-outline"><?php echo t('cancel'); ?></a>
  </div>
</form>

<script src="<?php echo BASE_URL; ?>/assets/js/accounts.js"></script>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
