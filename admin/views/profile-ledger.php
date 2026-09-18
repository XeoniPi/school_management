<?php
/**
 * KMA — admin/views/profile-ledger.php  |  PHP 7.2
 * Short profile + full transaction history for one student
 * (admissions.id) or one faculty/staff member (faculty.id).
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();
requirePermission('accounts', 'read');

$pdo  = getDB();
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$id   = isset($_GET['id'])   ? (int)$_GET['id']        : 0;

if (!in_array($type, ['student', 'faculty'], true) || !$id) {
    header('Location: ' . BASE_URL . '/admin/views/accounts.php'); exit;
}

if ($type === 'student') {
    $stmt = $pdo->prepare(
        'SELECT a.*, c.class_name FROM admissions a LEFT JOIN classes c ON c.id=a.apply_class_id WHERE a.id=?'
    );
    $stmt->execute([$id]);
    $person = $stmt->fetch();
    $txStmt = $pdo->prepare('SELECT * FROM transactions WHERE student_id=? ORDER BY transaction_date DESC, id DESC');
    $txStmt->execute([$id]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM faculty WHERE id=?');
    $stmt->execute([$id]);
    $person = $stmt->fetch();
    $txStmt = $pdo->prepare('SELECT * FROM transactions WHERE faculty_id=? ORDER BY transaction_date DESC, id DESC');
    $txStmt->execute([$id]);
}

if (!$person) { header('Location: ' . BASE_URL . '/admin/views/accounts.php'); exit; }

$txList = $txStmt->fetchAll();
$totalIn = 0; $totalOut = 0;
foreach ($txList as $r) {
    if ($r['type'] === 'income') { $totalIn += $r['amount']; } else { $totalOut += $r['amount']; }
}

$catLabels = [
    'admission_fee'=>'Admission Fee','monthly_fee'=>'Monthly Fee','exam_fee'=>'Exam Fee','donation'=>'Donation','other'=>'Other',
    'salary'=>'Salary','bonus'=>'Bonus','utilities'=>'Utilities','electric_bill'=>'Electric Bill','internet_bill'=>'Internet Bill',
    'office_expense'=>'Office Expense','event'=>'Event','meeting'=>'Meeting',
];

$currentAdminPage = 'accounts';
$pageTitle = 'Profile | KMA Admin';
require_once dirname(__DIR__) . '/includes/admin_header.php';

$displayName = $type === 'student' ? $person['student_name_bn'] : $person['name_bn'];
$photo = $type === 'faculty' && !empty($person['photo_path']) ? UPLOAD_IMAGES_URL . h($person['photo_path']) : '';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white"><?php echo t('view_profile'); ?></h1>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php" class="btn-outline"><i class="bi bi-arrow-left"></i> <?php echo t('back_to_list'); ?></a>
</div>

<div class="grid lg:grid-cols-3 gap-5">

  <!-- Profile card -->
  <div class="lg:col-span-1">
    <div class="admin-card p-5 text-center">
      <?php if ($photo): ?>
      <img src="<?php echo $photo; ?>" alt="<?php echo h($displayName); ?>" class="w-24 h-24 rounded-full object-cover mx-auto mb-3 border-4 border-accent-light" onerror="this.src='https://placehold.co/150x150/e8f4eb/2e6b3e?text=No+Photo'"/>
      <?php else: ?>
      <div class="w-24 h-24 rounded-full bg-accent-light text-accent flex items-center justify-center text-3xl mx-auto mb-3"><i class="bi bi-person-fill"></i></div>
      <?php endif; ?>
      <h2 class="font-bold text-kma-dark dark:text-white"><?php echo h($displayName); ?></h2>
      <?php if ($type === 'student'): ?>
      <p class="text-xs text-kma-muted mt-1"><?php echo h($person['app_no']); ?> · <?php echo h($person['class_name'] ?? '—'); ?></p>
      <p class="text-xs text-kma-muted mt-2"><i class="bi bi-telephone"></i> <?php echo h($person['guardian_phone']); ?></p>
      <?php else: ?>
      <p class="text-xs text-kma-muted mt-1"><?php echo h($person['designation']); ?></p>
      <?php if (!empty($person['phone'])): ?><p class="text-xs text-kma-muted mt-2"><i class="bi bi-telephone"></i> <?php echo h($person['phone']); ?></p><?php endif; ?>
      <?php endif; ?>

      <div class="grid grid-cols-2 gap-2 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
        <div>
          <div class="text-lg font-bold text-green-600">৳<?php echo number_format($totalIn,0); ?></div>
          <div class="text-[0.65rem] text-kma-muted"><?php echo $type==='student' ? 'Paid' : 'Received'; ?></div>
        </div>
        <div>
          <div class="text-lg font-bold text-red-600">৳<?php echo number_format($totalOut,0); ?></div>
          <div class="text-[0.65rem] text-kma-muted"><?php echo $type==='student' ? 'Refunded' : 'Total Salary Paid'; ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Transaction history -->
  <div class="lg:col-span-2">
    <div class="admin-card overflow-hidden">
      <div class="px-5 py-3.5 border-b border-kma-border dark:border-gray-700 font-bold text-sm text-kma-dark dark:text-white">
        <i class="bi bi-clock-history text-accent mr-1"></i> <?php echo t('transactions'); ?> (<?php echo count($txList); ?>)
      </div>
      <?php if (empty($txList)): ?>
      <div class="py-12 text-center text-kma-muted text-sm"><i class="bi bi-inbox text-3xl block mb-2 opacity-30"></i>No transactions yet</div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table>
          <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Invoice</th></tr></thead>
          <tbody>
            <?php foreach ($txList as $row): $isIncome = $row['type']==='income'; ?>
            <tr>
              <td class="text-xs whitespace-nowrap"><?php echo date('d M Y', strtotime($row['transaction_date'])); ?></td>
              <td class="text-xs"><?php echo h(isset($catLabels[$row['category']]) ? $catLabels[$row['category']] : $row['category']); ?></td>
              <td class="text-sm font-bold <?php echo $isIncome?'text-green-600':'text-red-600'; ?>"><?php echo $isIncome?'+':'-'; ?>৳<?php echo number_format($row['amount'],2); ?></td>
              <td><a href="<?php echo BASE_URL; ?>/admin/views/invoice.php?id=<?php echo (int)$row['id']; ?>" target="_blank" class="text-blue-500 hover:underline text-xs"><i class="bi bi-receipt"></i> <?php echo h($row['invoice_no']); ?></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
