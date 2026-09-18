<?php
/**
 * KMA — admin/views/invoice.php  |  PHP 7.2
 * Printable A4 invoice for a single transaction. Two copies on
 * one page (office copy + the transacting party's copy), split
 * by a dashed "cut here" line so it can be separated after print.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();
requirePermission('accounts', 'read');

$pdo = getDB();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT t.*, s.student_name_bn, s.student_name_en, s.app_no, f.name_bn AS faculty_name, f.designation
     FROM transactions t
     LEFT JOIN admissions s ON s.id = t.student_id
     LEFT JOIN faculty f ON f.id = t.faculty_id
     WHERE t.id = ?'
);
$stmt->execute([$id]);
$tx = $stmt->fetch();
if (!$tx) { die('Invoice not found.'); }

$catLabels = [
    'admission_fee'=>'Admission Fee','monthly_fee'=>'Monthly Fee','exam_fee'=>'Exam Fee','donation'=>'Donation','other'=>'Other',
    'salary'=>'Salary','bonus'=>'Bonus','utilities'=>'Utilities','electric_bill'=>'Electric Bill','internet_bill'=>'Internet Bill',
    'office_expense'=>'Office Expense','event'=>'Event','meeting'=>'Meeting',
];

$partyName = '—';
if ($tx['party_type'] === 'student' && $tx['student_id']) {
    $partyName = $tx['student_name_bn'] . (!empty($tx['app_no']) ? ' (' . $tx['app_no'] . ')' : '');
} elseif ($tx['party_type'] === 'faculty' && $tx['faculty_id']) {
    $partyName = $tx['faculty_name'] . (!empty($tx['designation']) ? ' — ' . $tx['designation'] : '');
} elseif (!empty($tx['party_name'])) {
    $partyName = $tx['party_name'];
}

$site      = getSiteSettings();
$schoolBn  = isset($site['school_name_bn']) ? $site['school_name_bn'] : 'খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$schoolEn  = isset($site['school_name_en']) ? $site['school_name_en'] : 'Khalilullah Memorial Academy';
$phone     = isset($site['school_phone'])   ? $site['school_phone']   : '+880 1866-751015';
$email     = isset($site['school_email'])   ? $site['school_email']   : 'info@kma.edu.bd';
$address   = isset($site['school_address']) ? $site['school_address'] : '';
$website   = 'kma.edu.bd';

function renderInvoiceCopy($copyLabel, $tx, $partyName, $catLabels, $schoolBn, $schoolEn, $phone, $email, $address, $website)
{
    $isIncome = $tx['type'] === 'income';
    ?>
    <div class="inv-copy">
      <div class="inv-head">
        <div class="inv-brand">
          <div class="inv-logo">KMA</div>
          <div>
            <div class="inv-school-bn"><?php echo h($schoolBn); ?></div>
            <div class="inv-school-en"><?php echo h($schoolEn); ?></div>
            <div class="inv-contact">
              <?php if ($address): ?><span><i class="bi bi-geo-alt-fill"></i> <?php echo h($address); ?></span><?php endif; ?>
              <span><i class="bi bi-telephone-fill"></i> <?php echo h($phone); ?></span>
              <span><i class="bi bi-envelope-fill"></i> <?php echo h($email); ?></span>
              <span><i class="bi bi-globe2"></i> <?php echo h($website); ?></span>
            </div>
          </div>
        </div>
        <div class="inv-meta">
          <div class="inv-copy-tag"><?php echo h($copyLabel); ?></div>
          <div class="inv-no">Invoice: <?php echo h($tx['invoice_no']); ?></div>
          <div class="inv-date">Date: <?php echo date('d M Y', strtotime($tx['transaction_date'])); ?></div>
        </div>
      </div>

      <table class="inv-table">
        <tr><th>Type</th><td><?php echo $isIncome ? 'Income (Received)' : 'Expense (Paid)'; ?></td></tr>
        <tr><th>Category</th><td><?php echo h(isset($catLabels[$tx['category']]) ? $catLabels[$tx['category']] : $tx['category']); ?></td></tr>
        <tr><th><?php echo $isIncome ? 'Received From' : 'Paid To'; ?></th><td><?php echo h($partyName); ?></td></tr>
        <tr><th>Payment Method</th><td><?php echo h(ucfirst($tx['payment_method'])); ?></td></tr>
        <?php if (!empty($tx['remark'])): ?>
        <tr><th>Remark</th><td><?php echo h($tx['remark']); ?></td></tr>
        <?php endif; ?>
        <tr class="inv-amount-row"><th>Amount</th><td>৳ <?php echo number_format($tx['amount'], 2); ?></td></tr>
      </table>

      <div class="inv-sign-row">
        <div class="inv-sign"><div class="inv-sign-line"></div>Received/Paid by</div>
        <div class="inv-sign"><div class="inv-sign-line"></div>Authorized Signature</div>
      </div>

      <p class="inv-note">
        <i class="bi bi-info-circle-fill"></i>
        এটি একটি কম্পিউটার-জেনারেটেড ইনভয়েস, তাই কোনো স্বাক্ষরের প্রয়োজন নেই। এই ইনভয়েসে কোনো সমস্যা থাকলে অনুগ্রহ করে <strong>৭ (সাত) দিনের মধ্যে</strong> অফিসে যোগাযোগ করুন। এই সময়ের পর কোনো অভিযোগ গ্রহণযোগ্য হবে না।
      </p>
    </div>
    <?php
}

$pageTitle = 'Invoice ' . $tx['invoice_no'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title><?php echo h($pageTitle); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/invoice.css"/>
</head>
<body>

<div class="inv-toolbar no-print">
  <button onclick="window.print()"><i class="bi bi-printer-fill"></i> Print Invoice</button>
  <a href="<?php echo BASE_URL; ?>/admin/views/accounts.php"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="inv-page">
  <?php renderInvoiceCopy('OFFICE COPY', $tx, $partyName, $catLabels, $schoolBn, $schoolEn, $phone, $email, $address, $website); ?>

  <div class="inv-cut-line"><span><i class="bi bi-scissors"></i> cut here</span></div>

  <?php renderInvoiceCopy('CUSTOMER COPY', $tx, $partyName, $catLabels, $schoolBn, $schoolEn, $phone, $email, $address, $website); ?>
</div>

</body>
</html>
