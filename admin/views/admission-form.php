<?php
/**
 * KMA — admin/views/admission-form.php  |  PHP 7.2
 * Staff enters a walk-in admission directly here. Unlike a website
 * submission (which lands as a pending "request"), this is
 * immediately confirmed (status = enrolled, source = staff) and
 * shows straight in the Admission List.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();
requirePermission('admissions', 'insert');

$pdo = getDB();
$classes = $pdo->query('SELECT id, class_key, class_name FROM classes WHERE is_active=1 ORDER BY sort_order')->fetchAll();

$currentAdminPage = 'admission-form';
$pageTitle = 'New Admission | KMA Admin';
$errors = []; $old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Security check failed.';
    } else {
        $old = [
            'student_name_bn'   => sanitize($_POST['student_name_bn'] ?? ''),
            'student_name_en'   => sanitize($_POST['student_name_en'] ?? ''),
            'dob'               => sanitize($_POST['dob'] ?? ''),
            'gender'            => sanitize($_POST['gender'] ?? ''),
            'religion'          => sanitize($_POST['religion'] ?? 'islam'),
            'blood_group'       => sanitize($_POST['blood_group'] ?? ''),
            'apply_class_id'    => (int)($_POST['apply_class_id'] ?? 0),
            'prev_school'       => sanitize($_POST['prev_school'] ?? ''),
            'birth_cert_no'     => sanitize($_POST['birth_cert_no'] ?? ''),
            'father_name'       => sanitize($_POST['father_name'] ?? ''),
            'mother_name'       => sanitize($_POST['mother_name'] ?? ''),
            'father_occupation' => sanitize($_POST['father_occupation'] ?? ''),
            'mother_occupation' => sanitize($_POST['mother_occupation'] ?? ''),
            'guardian_phone'    => sanitize($_POST['guardian_phone'] ?? ''),
            'guardian_email'    => sanitize($_POST['guardian_email'] ?? ''),
            'father_nid'        => sanitize($_POST['father_nid'] ?? ''),
            'address'           => sanitize($_POST['address'] ?? ''),
            'district'          => sanitize($_POST['district'] ?? ''),
        ];

        if (mb_strlen($old['student_name_bn']) < 2) { $errors[] = 'Student name (Bangla) is required.'; }
        if (mb_strlen($old['student_name_en']) < 2) { $errors[] = 'Student name (English) is required.'; }
        if (empty($old['dob'])) { $errors[] = 'Date of birth is required.'; }
        if (empty($old['gender'])) { $errors[] = 'Gender is required.'; }
        if ($old['apply_class_id'] < 1) { $errors[] = 'Class is required.'; }
        if (!preg_match('/^\d{10,17}$/', $old['birth_cert_no'])) { $errors[] = 'Valid birth certificate number is required.'; }
        if (mb_strlen($old['father_name']) < 2) { $errors[] = 'Father\'s name is required.'; }
        if (mb_strlen($old['mother_name']) < 2) { $errors[] = 'Mother\'s name is required.'; }
        if (!preg_match('/^01[3-9]\d{8}$/', $old['guardian_phone'])) { $errors[] = 'Valid guardian phone is required.'; }
        if (!preg_match('/^\d{10,17}$/', $old['father_nid'])) { $errors[] = 'Valid father NID is required.'; }
        if (mb_strlen($old['address']) < 5) { $errors[] = 'Address is required.'; }
        if (mb_strlen($old['district']) < 2) { $errors[] = 'District is required.'; }

        if (empty($errors)) {
            $appNo = generateAppNo();
            $pdo->prepare(
                'INSERT INTO admissions
                 (app_no, student_name_bn, student_name_en, dob, gender, religion, blood_group,
                  apply_class_id, prev_school, birth_cert_no, father_name, mother_name,
                  father_occupation, mother_occupation, guardian_phone, guardian_email, father_nid,
                  address, district, ip_address, source, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $appNo, $old['student_name_bn'], $old['student_name_en'], $old['dob'], $old['gender'],
                $old['religion'], $old['blood_group'], $old['apply_class_id'], $old['prev_school'],
                $old['birth_cert_no'], $old['father_name'], $old['mother_name'], $old['father_occupation'],
                $old['mother_occupation'], $old['guardian_phone'], $old['guardian_email'], $old['father_nid'],
                $old['address'], $old['district'],
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'staff', 'enrolled',
            ]);
            header('Location: ' . BASE_URL . '/admin/views/admissions.php?flash=' . urlencode('Admission created and confirmed — ' . $appNo)); exit;
        }
    }
}

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white"><span data-i18n-en>New Admission (Staff Entry)</span><span data-i18n-bn>নতুন ভর্তি (স্টাফ এন্ট্রি)</span></h1>
  <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php" class="btn-outline"><i class="bi bi-arrow-left"></i> <?php echo t('back_to_list'); ?></a>
</div>

<div class="alert alert-success mb-4" style="background:#e8f4eb;border-color:#a7d7b5;color:#1a4a2a">
  <i class="bi bi-info-circle-fill"></i>
  <span data-i18n-en>This entry is confirmed immediately and will appear directly in the Admission List (no approval step needed).</span>
  <span data-i18n-bn>এই তথ্য সরাসরি নিশ্চিত হয়ে যাবে এবং সরাসরি Admission List-এ দেখাবে (আলাদা approve করার দরকার নেই)।</span>
</div>

<?php if ($errors): ?>
<div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/admission-form.php" class="admin-card p-6 max-w-4xl">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>

  <h2 class="text-xs font-bold uppercase tracking-wider text-kma-muted mb-3 pb-2 border-b border-kma-border">Student Info</h2>
  <div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div><label class="form-label">Name (Bangla) *</label><input type="text" name="student_name_bn" class="form-input" required value="<?php echo h($old['student_name_bn']??''); ?>"/></div>
    <div><label class="form-label">Name (English) *</label><input type="text" name="student_name_en" class="form-input" required value="<?php echo h($old['student_name_en']??''); ?>"/></div>
    <div><label class="form-label">Date of Birth *</label><input type="date" name="dob" class="form-input" required value="<?php echo h($old['dob']??''); ?>"/></div>
    <div><label class="form-label">Birth Certificate No. *</label><input type="text" name="birth_cert_no" class="form-input" required value="<?php echo h($old['birth_cert_no']??''); ?>"/></div>
    <div>
      <label class="form-label">Gender *</label>
      <select name="gender" class="form-input" required>
        <option value="">Select</option>
        <option value="male" <?php echo ($old['gender']??'')==='male'?'selected':''; ?>>Male</option>
        <option value="female" <?php echo ($old['gender']??'')==='female'?'selected':''; ?>>Female</option>
      </select>
    </div>
    <div>
      <label class="form-label">Applying for Class *</label>
      <select name="apply_class_id" class="form-input" required>
        <option value="">Select class</option>
        <?php foreach ($classes as $cls): ?>
        <option value="<?php echo (int)$cls['id']; ?>" <?php echo ((int)($old['apply_class_id']??0))===(int)$cls['id']?'selected':''; ?>><?php echo h($cls['class_name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label class="form-label">Blood Group</label>
      <select name="blood_group" class="form-input">
        <option value="">—</option>
        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
        <option value="<?php echo h($bg); ?>" <?php echo ($old['blood_group']??'')===$bg?'selected':''; ?>><?php echo h($bg); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label class="form-label">Previous School</label><input type="text" name="prev_school" class="form-input" value="<?php echo h($old['prev_school']??''); ?>"/></div>
  </div>

  <h2 class="text-xs font-bold uppercase tracking-wider text-kma-muted mb-3 pb-2 border-b border-kma-border">Guardian Info</h2>
  <div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div><label class="form-label">Father's Name *</label><input type="text" name="father_name" class="form-input" required value="<?php echo h($old['father_name']??''); ?>"/></div>
    <div><label class="form-label">Mother's Name *</label><input type="text" name="mother_name" class="form-input" required value="<?php echo h($old['mother_name']??''); ?>"/></div>
    <div><label class="form-label">Father's Occupation</label><input type="text" name="father_occupation" class="form-input" value="<?php echo h($old['father_occupation']??''); ?>"/></div>
    <div><label class="form-label">Mother's Occupation</label><input type="text" name="mother_occupation" class="form-input" value="<?php echo h($old['mother_occupation']??''); ?>"/></div>
    <div><label class="form-label">Guardian Phone *</label><input type="tel" name="guardian_phone" class="form-input" required placeholder="01XXXXXXXXX" value="<?php echo h($old['guardian_phone']??''); ?>"/></div>
    <div><label class="form-label">Guardian Email</label><input type="email" name="guardian_email" class="form-input" value="<?php echo h($old['guardian_email']??''); ?>"/></div>
    <div><label class="form-label">Father's NID *</label><input type="text" name="father_nid" class="form-input" required value="<?php echo h($old['father_nid']??''); ?>"/></div>
  </div>

  <h2 class="text-xs font-bold uppercase tracking-wider text-kma-muted mb-3 pb-2 border-b border-kma-border">Address</h2>
  <div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="sm:col-span-2"><label class="form-label">Address *</label><textarea name="address" class="form-input" rows="2" required><?php echo h($old['address']??''); ?></textarea></div>
    <div><label class="form-label">District *</label><input type="text" name="district" class="form-input" required value="<?php echo h($old['district']??''); ?>"/></div>
  </div>

  <div class="flex gap-3 pt-4 border-t border-kma-border">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> Create &amp; Confirm Admission</button>
    <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php" class="btn-outline"><?php echo t('cancel'); ?></a>
  </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
