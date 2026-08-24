<?php
/**
 * KMA — pages/admission.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'ভর্তি তথ্য | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'ভর্তির যোগ্যতা, কাগজপত্র, ফি কাঠামো ও অনলাইন আবেদন ফরম।';

$pdo     = getDB();
$classes = $pdo->query('SELECT id, class_key, class_name, age_range FROM classes WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$site    = getSiteSettings();

$success = false;
$errors  = [];
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'নিরাপত্তা যাচাই ব্যর্থ। পুনরায় চেষ্টা করুন।';
    } else {
        /* Sanitize all fields */
        $old = [
            'student_name_bn'   => sanitize(isset($_POST['student_name_bn'])   ? $_POST['student_name_bn']   : ''),
            'student_name_en'   => sanitize(isset($_POST['student_name_en'])   ? $_POST['student_name_en']   : ''),
            'dob'               => sanitize(isset($_POST['dob'])               ? $_POST['dob']               : ''),
            'gender'            => sanitize(isset($_POST['gender'])            ? $_POST['gender']            : ''),
            'religion'          => sanitize(isset($_POST['religion'])          ? $_POST['religion']          : 'islam'),
            'blood_group'       => sanitize(isset($_POST['blood_group'])       ? $_POST['blood_group']       : ''),
            'apply_class_id'    => (int)(isset($_POST['apply_class_id'])       ? $_POST['apply_class_id']    : 0),
            'prev_school'       => sanitize(isset($_POST['prev_school'])       ? $_POST['prev_school']       : ''),
            'birth_cert_no'     => sanitize(isset($_POST['birth_cert_no'])     ? $_POST['birth_cert_no']     : ''),
            'father_name'       => sanitize(isset($_POST['father_name'])       ? $_POST['father_name']       : ''),
            'mother_name'       => sanitize(isset($_POST['mother_name'])       ? $_POST['mother_name']       : ''),
            'father_occupation' => sanitize(isset($_POST['father_occupation']) ? $_POST['father_occupation'] : ''),
            'mother_occupation' => sanitize(isset($_POST['mother_occupation']) ? $_POST['mother_occupation'] : ''),
            'guardian_phone'    => sanitize(isset($_POST['guardian_phone'])    ? $_POST['guardian_phone']    : ''),
            'guardian_email'    => sanitize(isset($_POST['guardian_email'])    ? $_POST['guardian_email']    : ''),
            'father_nid'        => sanitize(isset($_POST['father_nid'])        ? $_POST['father_nid']        : ''),
            'annual_income'     => sanitize(isset($_POST['annual_income'])     ? $_POST['annual_income']     : ''),
            'address'           => sanitize(isset($_POST['address'])           ? $_POST['address']           : ''),
            'district'          => sanitize(isset($_POST['district'])          ? $_POST['district']          : ''),
            'upazila'           => sanitize(isset($_POST['upazila'])           ? $_POST['upazila']           : ''),
            'post_code'         => sanitize(isset($_POST['post_code'])         ? $_POST['post_code']         : ''),
            'scholarship_apply' => isset($_POST['scholarship_apply']) ? 1 : 0,
            'hear_about'        => sanitize(isset($_POST['hear_about'])        ? $_POST['hear_about']        : ''),
            'remarks'           => sanitize(isset($_POST['remarks'])           ? $_POST['remarks']           : ''),
        ];

        /* Validation */
        if (mb_strlen($old['student_name_bn']) < 2)  { $errors[] = 'শিক্ষার্থীর বাংলা নাম লিখুন।'; }
        if (mb_strlen($old['student_name_en']) < 2)  { $errors[] = 'শিক্ষার্থীর ইংরেজি নাম লিখুন।'; }
        if (empty($old['dob']))                       { $errors[] = 'জন্ম তারিখ দিন।'; }
        if (empty($old['gender']))                    { $errors[] = 'লিঙ্গ নির্বাচন করুন।'; }
        if ($old['apply_class_id'] < 1)              { $errors[] = 'শ্রেণি নির্বাচন করুন।'; }
        if (!preg_match('/^\d{17}$/', $old['birth_cert_no'])) { $errors[] = 'সঠিক ১৭ সংখ্যার জন্ম নিবন্ধন নম্বর দিন।'; }
        if (mb_strlen($old['father_name']) < 2)      { $errors[] = 'পিতার নাম লিখুন।'; }
        if (mb_strlen($old['mother_name']) < 2)      { $errors[] = 'মাতার নাম লিখুন।'; }
        if (!preg_match('/^01[3-9]\d{8}$/', $old['guardian_phone'])) { $errors[] = 'সঠিক মোবাইল নম্বর দিন।'; }
        if (!empty($old['guardian_email']) && !filter_var($old['guardian_email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'সঠিক ইমেইল দিন।'; }
        if (!preg_match('/^\d{10,17}$/', $old['father_nid'])) { $errors[] = 'সঠিক NID নম্বর দিন।'; }
        if (mb_strlen($old['address']) < 5)          { $errors[] = 'ঠিকানা লিখুন।'; }
        if (mb_strlen($old['district']) < 2)         { $errors[] = 'জেলার নাম লিখুন।'; }
        if (!isset($_POST['declaration']))            { $errors[] = 'ঘোষণাপত্রে সম্মতি দিন।'; }

        /* Verify class id exists */
        if ($old['apply_class_id'] > 0) {
            $chk = $pdo->prepare('SELECT id FROM classes WHERE id=? AND is_active=1');
            $chk->execute([$old['apply_class_id']]);
            if (!$chk->fetch()) { $errors[] = 'অবৈধ শ্রেণি নির্বাচন।'; }
        }

        if (empty($errors)) {
            /* Handle photo upload */
            $photoPath = null;
            if (!empty($_FILES['student_photo']['name'])) {
                $file = $_FILES['student_photo'];
                if ($file['size'] > MAX_IMG_SIZE) {
                    $errors[] = 'ছবির আকার ২ MB-এর বেশি হওয়া যাবে না।';
                } elseif (!in_array($file['type'], ALLOWED_IMG_TYPES)) {
                    $errors[] = 'শুধুমাত্র JPG, PNG বা WEBP ছবি আপলোড করুন।';
                } else {
                    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $photoPath = 'photo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_IMAGES . $photoPath);
                }
            }

            /* Handle birth cert upload */
            $certPath = null;
            if (!empty($_FILES['birth_cert_file']['name'])) {
                $file = $_FILES['birth_cert_file'];
                if ($file['size'] > MAX_PDF_SIZE) {
                    $errors[] = 'ফাইলের আকার ৫ MB-এর বেশি হওয়া যাবে না।';
                } else {
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $certPath = 'cert_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_PDFS . $certPath);
                }
            }

            if (empty($errors)) {
                $appNo = generateAppNo();
                $stmt  = $pdo->prepare(
                    'INSERT INTO admissions
                     (app_no, student_name_bn, student_name_en, dob, gender, religion,
                      blood_group, apply_class_id, prev_school, birth_cert_no,
                      father_name, mother_name, father_occupation, mother_occupation,
                      guardian_phone, guardian_email, father_nid, annual_income,
                      address, district, upazila, post_code,
                      scholarship_apply, hear_about, remarks,
                      photo_path, birth_cert_path, ip_address)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                );
                $stmt->execute([
                    $appNo,
                    $old['student_name_bn'], $old['student_name_en'],
                    $old['dob'], $old['gender'], $old['religion'],
                    $old['blood_group'], $old['apply_class_id'],
                    $old['prev_school'], $old['birth_cert_no'],
                    $old['father_name'], $old['mother_name'],
                    $old['father_occupation'], $old['mother_occupation'],
                    $old['guardian_phone'], $old['guardian_email'],
                    $old['father_nid'], $old['annual_income'],
                    $old['address'], $old['district'],
                    $old['upazila'], $old['post_code'],
                    $old['scholarship_apply'], $old['hear_about'],
                    $old['remarks'], $photoPath, $certPath,
                    isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                ]);

                $success = true;
                $old     = [];
                unset($_SESSION['csrf_token']);
            }
        }
    }
}

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:280px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-pencil-square"></i> ভর্তি ২০২৫–২৬</div>
      <h1 class="font-bn font-bold text-4xl mb-3">ভর্তি <em style="font-style:normal;color:#c9a227">তথ্য</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">ভর্তি তথ্য</span>
      </nav>
    </div>
  </div>
</header>

<!-- Urgency bar -->
<?php if (!empty($site['admission_open']) && $site['admission_open'] == '1'): ?>
<div class="bg-gold py-3 text-center" role="alert">
  <p class="text-kma-dark font-bold text-sm flex items-center justify-center gap-2 flex-wrap">
    <span class="inline-block w-2.5 h-2.5 bg-red-600 rounded-full animate-ping"></span>
    ২০২৫–২৬ শিক্ষাবর্ষে ভর্তি চলছে! আসন সীমিত।
    <a href="#sec-form" class="underline font-extrabold ml-2">এখনই আবেদন করুন →</a>
  </p>
</div>
<?php endif; ?>

<!-- Sticky tabs -->
<div class="sticky top-[78px] z-[900] bg-white dark:bg-gray-900 border-b border-kma-border shadow-sm">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex overflow-x-auto" style="scrollbar-width:none">
      <?php
      $adTabs = [
        ['sec-eligibility','bi-check2-circle','যোগ্যতা ও কাগজপত্র'],
        ['sec-fee','bi-cash-coin','ফি কাঠামো'],
        ['sec-process','bi-list-ol','ভর্তি প্রক্রিয়া'],
        ['sec-dates','bi-calendar-event','গুরুত্বপূর্ণ তারিখ'],
        ['sec-scholarship','bi-award','বৃত্তি'],
        ['sec-form','bi-file-earmark-text','আবেদন ফরম'],
        ['sec-faq','bi-question-circle','FAQ'],
      ];
      foreach ($adTabs as $i => $t): ?>
      <button class="tab-btn flex-shrink-0 flex items-center gap-1.5 px-4 py-3.5 text-xs font-semibold border-b-[3px] transition-colors whitespace-nowrap
                     <?php echo $i === 0 ? 'border-accent text-accent' : 'border-transparent text-kma-muted hover:text-accent'; ?>"
              data-target="<?php echo h($t[0]); ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
        <i class="bi <?php echo h($t[1]); ?>"></i> <?php echo h($t[2]); ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<main id="main-content">

<!-- ── Overview stats ── -->
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-stars"></i><span></span></div>
      <h2 class="section-title">একনজরে ভর্তি তথ্য</h2>
      <p class="text-kma-muted text-sm mt-1">২০২৫–২৬ শিক্ষাবর্ষের সামগ্রিক চিত্র</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php
      $ovCards = [
        ['bi-people-fill','৩০০','মোট আসন'],
        ['bi-grid-3x3-gap-fill','৬টি','শ্রেণি'],
        ['bi-calendar-check','৩০ জুন','আবেদনের শেষ তারিখ'],
        ['bi-award-fill','১৫%','মেধাবৃত্তি সুযোগ'],
      ];
      foreach ($ovCards as $i => $ov): ?>
      <div class="reveal reveal-d<?php echo $i+1; ?> bg-kma-bg dark:bg-gray-700 rounded-xl p-6 text-center shadow-sm border-b-4 border-transparent hover:border-accent hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="w-14 h-14 rounded-full bg-accent-light flex items-center justify-center text-2xl text-accent mx-auto mb-3">
          <i class="bi <?php echo h($ov[0]); ?>"></i>
        </div>
        <div class="font-display text-3xl font-bold text-accent"><?php echo h($ov[1]); ?></div>
        <div class="text-xs text-kma-muted font-semibold mt-1"><?php echo h($ov[2]); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Eligibility & Documents ── -->
<section id="sec-eligibility" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-check2-circle"></i><span></span></div>
      <h2 class="section-title">ভর্তির যোগ্যতা ও প্রয়োজনীয় কাগজপত্র</h2>
      <p class="text-kma-muted text-sm mt-1">শ্রেণিভেদে ন্যূনতম বয়স ও প্রয়োজনীয় দলিলাদি</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">

      <!-- Age eligibility -->
      <div class="reveal reveal-d1 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-12 h-12 rounded-xl bg-accent-light text-accent flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-person-check-fill"></i></div>
          <h3 class="font-bold text-kma-dark dark:text-white">বয়সসীমা ও যোগ্যতা</h3>
        </div>
        <?php foreach ($classes as $cls): ?>
        <div class="flex items-center gap-2 text-sm text-kma-muted py-1.5 border-b border-kma-border last:border-0">
          <i class="bi bi-dot text-accent text-lg leading-none"></i>
          <span><strong class="text-kma-dark dark:text-gray-200"><?php echo h($cls['class_name']); ?>:</strong> <?php echo h($cls['age_range']); ?></span>
        </div>
        <?php endforeach; ?>
        <div class="mt-3 pt-3 border-t border-kma-border flex items-start gap-2 text-xs text-kma-muted">
          <i class="bi bi-info-circle-fill text-accent flex-shrink-0 mt-0.5"></i>
          দ্বিতীয় শ্রেণি ও উপরে পূর্ববর্তী শ্রেণির পাশের সার্টিফিকেট আবশ্যক।
        </div>
      </div>

      <!-- Documents -->
      <div class="reveal reveal-d2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-12 h-12 rounded-xl bg-gold/15 text-gold flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-file-earmark-text-fill"></i></div>
          <h3 class="font-bold text-kma-dark dark:text-white">প্রয়োজনীয় কাগজপত্র</h3>
        </div>
        <?php
        $docs = [
          'শিক্ষার্থীর জন্ম নিবন্ধন সনদ (মূল + ফটোকপি)',
          'শিক্ষার্থীর ২ কপি পাসপোর্ট সাইজ ছবি',
          'পিতা ও মাতার NID ফটোকপি',
          'পিতা ও মাতার ১ কপি করে ছবি',
          'পূর্ববর্তী বিদ্যালয়ের ছাড়পত্র (প্রযোজ্য ক্ষেত্রে)',
          'পূর্ববর্তী শ্রেণির মার্কশিট/রেজাল্ট কার্ড',
          'টিকা কার্ড (ইপিআই/স্বাস্থ্য কার্ড)',
        ];
        foreach ($docs as $doc): ?>
        <div class="flex items-start gap-2 text-sm text-kma-muted py-1.5 border-b border-kma-border last:border-0">
          <i class="bi bi-check-circle-fill text-accent flex-shrink-0 mt-0.5"></i>
          <span><?php echo h($doc); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Important notes -->
      <div class="reveal reveal-d3 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 text-kma-dark dark:text-gray-300 flex items-center justify-center text-xl flex-shrink-0"><i class="bi bi-info-circle-fill"></i></div>
          <h3 class="font-bold text-kma-dark dark:text-white">গুরুত্বপূর্ণ নির্দেশনা</h3>
        </div>
        <?php
        $notes = [
          'আসন সংখ্যা সীমিত — প্রথম আসুন, প্রথম পাবেন ভিত্তিতে ভর্তি নিশ্চিত হবে।',
          'ভর্তি পরীক্ষায় শিক্ষার্থীকে অভিভাবকসহ উপস্থিত থাকতে হবে।',
          'ভুল তথ্য প্রদান করলে যেকোনো সময় ভর্তি বাতিল করা হবে।',
          'অনলাইন আবেদনের পর অফিসে এসে মূল কাগজপত্র যাচাই করতে হবে।',
          'ফি প্রদানের রশিদ সংরক্ষণ করতে হবে।',
        ];
        foreach ($notes as $note): ?>
        <div class="flex items-start gap-2 text-sm text-kma-muted py-1.5 border-b border-kma-border last:border-0">
          <i class="bi bi-exclamation-triangle-fill text-gold flex-shrink-0 mt-0.5"></i>
          <span><?php echo h($note); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<!-- ── Fee Structure ── -->
<section id="sec-fee" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-cash-coin"></i><span></span></div>
      <h2 class="section-title">ফি কাঠামো</h2>
      <p class="text-kma-muted text-sm mt-1">শ্রেণিভেদে বার্ষিক ফি-র বিস্তারিত বিবরণ</p>
    </div>

    <!-- Fee class tabs -->
    <div class="flex flex-wrap gap-2 justify-center mb-8 reveal" role="tablist">
      <?php foreach ($classes as $i => $cls): ?>
      <button class="fee-tab px-4 py-2 rounded-full text-sm font-semibold border transition-colors
                     <?php echo $i === 0 ? 'bg-accent border-accent text-white' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent dark:text-gray-300'; ?>"
              role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
              data-fee="<?php echo h($cls['class_key']); ?>">
        <?php echo h($cls['class_name']); ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Fee panels -->
    <?php
    $feeData = [
      'pk' => [['ভর্তি ফি','ভর্তির সময়','৳ ২,০০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ৬০০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ১,০০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ৫০০','বার্ষিক'],['ক্রীড়া ও সাংস্কৃতিক ফি','বার্ষিক','৳ ৩০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ১০,৯০০','']],
      'c1' => [['ভর্তি ফি','ভর্তির সময়','৳ ২,৫০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ৭০০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ১,২০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ৬০০','বার্ষিক'],['কম্পিউটার ল্যাব ফি','বার্ষিক','৳ ৪০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ১৩,৪০০','']],
      'c2' => [['ভর্তি ফি','ভর্তির সময়','৳ ২,৫০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ৭৫০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ১,৫০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ৭০০','বার্ষিক'],['কম্পিউটার ল্যাব ফি','বার্ষিক','৳ ৪০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ১৪,৯০০','']],
      'c3' => [['ভর্তি ফি','ভর্তির সময়','৳ ৩,০০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ৮০০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ১,৫০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ৮০০','বার্ষিক'],['কম্পিউটার ল্যাব ফি','বার্ষিক','৳ ৫০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ১৮,৮০০','']],
      'c4' => [['ভর্তি ফি','ভর্তির সময়','৳ ৩,০০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ৯০০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ১,৮০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ৯০০','বার্ষিক'],['কম্পিউটার ল্যাব ফি','বার্ষিক','৳ ৫০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ২১,৪০০','']],
      'c5' => [['ভর্তি ফি','ভর্তির সময়','৳ ৩,৫০০','একবার'],['মাসিক বেতন','প্রতি মাস','৳ ১,০০০','মাসিক'],['সেশন চার্জ','বার্ষিক','৳ ২,০০০','বার্ষিক'],['পরীক্ষা ফি','বার্ষিক','৳ ১,০০০','বার্ষিক'],['PECE প্রস্তুতি ফি','বার্ষিক','৳ ৫০০','বার্ষিক'],['বার্ষিক মোট ব্যয় (আনুমানিক)','—','৳ ২৬,৯০০','']],
    ];
    foreach ($classes as $i => $cls):
      $fd = isset($feeData[$cls['class_key']]) ? $feeData[$cls['class_key']] : [];
    ?>
    <div class="fee-panel <?php echo $i === 0 ? 'show' : ''; ?>" id="fee-<?php echo h($cls['class_key']); ?>" role="tabpanel">
      <div class="overflow-x-auto rounded-xl shadow-md">
        <table class="w-full border-collapse bg-white dark:bg-gray-700">
          <thead class="bg-accent text-white">
            <tr>
              <th class="px-5 py-3.5 text-left text-sm font-bold">ফি-র বিবরণ</th>
              <th class="px-5 py-3.5 text-left text-sm font-bold">ধরন</th>
              <th class="px-5 py-3.5 text-left text-sm font-bold">পরিমাণ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fd as $ri => $row):
              $isLast = ($ri === count($fd) - 1);
            ?>
            <tr class="<?php echo $isLast ? 'bg-accent-light dark:bg-green-900/20 font-bold' : ($ri % 2 === 1 ? 'bg-kma-bg dark:bg-gray-600' : 'bg-white dark:bg-gray-700'); ?> hover:bg-accent-light dark:hover:bg-green-900/20 transition-colors">
              <td class="px-5 py-3 text-sm text-kma-dark dark:text-gray-200"><?php echo h($row[0]); ?></td>
              <td class="px-5 py-3 text-sm text-kma-muted dark:text-gray-400"><?php echo h($row[1]); ?></td>
              <td class="px-5 py-3 text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($row[2]); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="notice-box mt-4">
        <i class="bi bi-info-circle-fill"></i>
        <span>মাসিক বেতন প্রতি মাসের ১০ তারিখের মধ্যে পরিশোধ করতে হবে। বার্ষিক একসাথে ফি পরিশোধ করলে ৫% ছাড় পাওয়া যাবে।</span>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

<!-- ── Admission Process ── -->
<section id="sec-process" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-list-ol"></i><span></span></div>
      <h2 class="section-title">ভর্তি প্রক্রিয়ার ধাপসমূহ</h2>
      <p class="text-kma-muted text-sm mt-1">ধাপে ধাপে সম্পন্ন করুন ভর্তির কার্যক্রম</p>
    </div>
    <div class="max-w-3xl mx-auto">
      <?php
      $steps = [
        ['১','bi-file-earmark-text','আবেদন ফরম পূরণ','অনলাইনে এই পেজেই আবেদন ফরম পূরণ করুন অথবা বিদ্যালয় অফিস থেকে সংগ্রহ করুন।','bg-blue-100 text-blue-700','অনলাইন'],
        ['২','bi-folder-check','কাগজপত্র জমা','প্রয়োজনীয় সকল কাগজপত্র বিদ্যালয় অফিসে জমা দিন।','bg-accent-light text-accent','সরাসরি অফিসে'],
        ['৩','bi-clipboard-check','ভর্তি পরীক্ষা / মৌখিক সাক্ষাৎকার','শিক্ষার্থীকে অভিভাবকসহ উপস্থিত হতে হবে।','bg-yellow-100 text-yellow-700','মূল্যায়ন'],
        ['৪','bi-cash-coin','ফি প্রদান','ভর্তি নিশ্চিত হলে ভর্তি ফি ও প্রথম মাসের বেতন পরিশোধ করুন।','bg-green-100 text-green-700','বিকাশ/নগদ'],
        ['৫','bi-patch-check-fill','ভর্তি নিশ্চিতকরণ','ফি প্রদানের পর ভর্তি রশিদ ও শিক্ষার্থীর আইডি কার্ড সংগ্রহ করুন।','bg-green-100 text-green-700','ভর্তি সম্পন্ন'],
      ];
      foreach ($steps as $i => $step): ?>
      <div class="reveal reveal-d<?php echo min($i+1,5); ?> flex gap-5 mb-6 last:mb-0">
        <div class="flex flex-col items-center flex-shrink-0">
          <div class="w-12 h-12 rounded-full bg-accent text-white flex items-center justify-center font-bold text-lg shadow-lg z-10">
            <?php echo h($step[0]); ?>
          </div>
          <?php if ($i < count($steps)-1): ?>
          <div class="w-0.5 flex-1 bg-kma-border mt-1"></div>
          <?php endif; ?>
        </div>
        <div class="flex-1 pb-6">
          <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-transparent hover:border-accent hover:shadow-md transition-all">
            <h3 class="font-bold text-kma-dark dark:text-white text-sm mb-1.5 flex items-center gap-2">
              <i class="bi <?php echo h($step[1]); ?> text-accent"></i> <?php echo h($step[2]); ?>
            </h3>
            <p class="text-kma-muted text-sm leading-relaxed mb-2"><?php echo h($step[3]); ?></p>
            <span class="inline-block text-xs font-bold px-2.5 py-0.5 rounded-full <?php echo h($step[4]); ?>">
              <?php echo h($step[5]); ?>
            </span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Important Dates ── -->
<section id="sec-dates" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-calendar-event"></i><span></span></div>
      <h2 class="section-title">গুরুত্বপূর্ণ তারিখসমূহ</h2>
      <p class="text-kma-muted text-sm mt-1">মিস করবেন না এই তারিখগুলো</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      $dates = [
        ['bg-accent text-white','জানু.','০১','২০২৫','আবেদন শুরু','অনলাইন ও অফলাইন আবেদন গ্রহণ শুরু','bg-green-100 text-green-700','চলমান'],
        ['bg-red-600 text-white','জুন','৩০','২০২৫','আবেদনের শেষ তারিখ','এরপর কোনো আবেদন গ্রহণ করা হবে না','bg-red-100 text-red-700','শেষ তারিখ'],
        ['bg-kma-dark text-white','জুলাই','১০','২০২৫','ভর্তি পরীক্ষা','দ্বিতীয় শ্রেণি ও তদূর্ধ্বের মৌখিক মূল্যায়ন','bg-blue-100 text-blue-700','আসছে'],
        ['bg-gold text-kma-dark','জুলাই','১৫','২০২৫','ফলাফল প্রকাশ','নির্বাচিত শিক্ষার্থীদের তালিকা ও SMS','bg-blue-100 text-blue-700','আসছে'],
        ['bg-blue-700 text-white','জুলাই','১৬–২৫','২০২৫','ফি প্রদান','এই সময়ের মধ্যে ফি না দিলে আসন বাতিল হবে','bg-blue-100 text-blue-700','আসছে'],
        ['bg-accent text-white','আগস্ট','০১','২০২৫','ক্লাস শুরু','ওরিয়েন্টেশন ও প্রথম দিনের ক্লাস','bg-blue-100 text-blue-700','আসছে'],
      ];
      foreach ($dates as $i => $d): ?>
      <div class="reveal reveal-d<?php echo min($i+1,5); ?> flex items-stretch bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="<?php echo h($d[0]); ?> flex flex-col items-center justify-center px-4 py-4 text-center flex-shrink-0" style="min-width:78px">
          <span class="text-xs font-bold opacity-80"><?php echo h($d[1]); ?></span>
          <span class="font-display text-2xl font-bold leading-none"><?php echo h($d[2]); ?></span>
          <span class="text-xs opacity-70"><?php echo h($d[3]); ?></span>
        </div>
        <div class="p-4 flex-1">
          <h3 class="font-bold text-sm text-kma-dark dark:text-gray-200 mb-1"><?php echo h($d[4]); ?></h3>
          <p class="text-xs text-kma-muted mb-2"><?php echo h($d[5]); ?></p>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full <?php echo h($d[6]); ?>"><?php echo h($d[7]); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Scholarship ── -->
<section id="sec-scholarship" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-award-fill"></i><span></span></div>
      <h2 class="section-title">বৃত্তি ও আর্থিক সহায়তা</h2>
      <p class="text-kma-muted text-sm mt-1">মেধাবী ও সুবিধাবঞ্চিত শিক্ষার্থীদের জন্য বিশেষ সুযোগ</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php
      $schCards = [
        ['bg-gradient-to-br from-accent to-[#1a4a2a] text-white','bi-trophy-fill','text-gold','১০০%','মেধাবৃত্তি','বার্ষিক পরীক্ষায় ১ম–৩য় স্থান অধিকারীদের পরের বছর সম্পূর্ণ বেতন মওকুফ।'],
        ['bg-white dark:bg-gray-800 border-2 border-kma-border','bi-heart-fill','text-accent','৫০%','আর্থিক সহায়তা','অসচ্ছল ও দরিদ্র পরিবারের মেধাবী শিক্ষার্থীদের মাসিক বেতন ছাড়।'],
        ['bg-kma-dark text-white','bi-people-fill','text-gold','১৫%','ভাইবোন ছাড়','একই পরিবারের দুই বা ততোধিক শিক্ষার্থী পড়লে দ্বিতীয় সন্তানের বেতনে ছাড়।'],
        ['bg-gradient-to-br from-blue-700 to-blue-900 text-white','bi-lightning-fill','text-blue-200','৫%','আর্লি বার্ড ছাড়','আবেদনের প্রথম মাসে ভর্তি নিশ্চিত করলে ভর্তি ফিতে বিশেষ ছাড়।'],
      ];
      foreach ($schCards as $i => $sc): ?>
      <div class="reveal reveal-d<?php echo $i+1; ?> rounded-xl p-6 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all <?php echo h($sc[0]); ?>">
        <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center text-3xl <?php echo h($sc[2]); ?> mb-4">
          <i class="bi <?php echo h($sc[1]); ?>"></i>
        </div>
        <div class="font-display text-4xl font-bold <?php echo h($sc[2]); ?> mb-1"><?php echo h($sc[3]); ?></div>
        <div class="font-bold text-base mb-2"><?php echo h($sc[4]); ?></div>
        <p class="text-sm opacity-80 leading-relaxed"><?php echo h($sc[5]); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Application Form ── -->
<section id="sec-form" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-4xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-file-earmark-text"></i><span></span></div>
      <h2 class="section-title">অনলাইন ভর্তি আবেদন ফরম</h2>
      <p class="text-kma-muted text-sm mt-1">সকল তথ্য সঠিকভাবে পূরণ করুন</p>
    </div>

    <div class="reveal bg-white dark:bg-gray-900 rounded-2xl shadow-xl overflow-hidden">
      <!-- Form header -->
      <div class="bg-gradient-to-r from-accent to-[#1a4a2a] px-8 py-6">
        <h2 class="text-white font-bold text-lg mb-1"><i class="bi bi-file-earmark-person-fill me-2"></i>ভর্তি আবেদন ফরম – ২০২৫–২৬</h2>
        <p class="text-white/80 text-sm">খলিলুল্লাহ মেমোরিয়াল একাডেমি | Khalilullah Memorial Academy</p>
      </div>

      <div class="p-8">
        <?php if ($success): ?>
        <div class="text-center py-12">
          <div class="w-20 h-20 bg-accent-light rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="bi bi-check-lg text-accent text-4xl"></i>
          </div>
          <h3 class="font-bold text-accent text-xl mb-2">আবেদন সফলভাবে জমা হয়েছে!</h3>
          <p class="text-kma-muted text-sm">আপনার আবেদন আমরা পেয়েছি। ৪৮ ঘণ্টার মধ্যে আপনার মোবাইলে কল করা হবে।</p>
          <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="mt-5 inline-flex btn-primary" style="border-radius:8px">নতুন আবেদন করুন</a>
        </div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-5 py-4 mb-6">
          <ul class="text-red-700 dark:text-red-400 text-sm space-y-1">
            <?php foreach ($errors as $err): ?>
            <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle-fill"></i> <?php echo h($err); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form id="admissionForm" method="POST"
              action="<?php echo BASE_URL; ?>/pages/admission.php"
              enctype="multipart/form-data" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>" />

          <!-- Section A: Student -->
          <div class="mb-4 text-xs font-bold uppercase tracking-wider text-kma-dark dark:text-gray-300 pb-2 border-b-2 border-accent-light flex items-center gap-2">
            <i class="bi bi-person-fill text-accent"></i> অংশ-ক: শিক্ষার্থীর তথ্য
          </div>
          <div class="grid sm:grid-cols-2 gap-4 mb-6">
            <?php
            function fv($old, $key) { return isset($old[$key]) ? htmlspecialchars($old[$key], ENT_QUOTES | ENT_HTML5, 'UTF-8') : ''; }
            $sFields = [
              ['text','student_name_bn','শিক্ষার্থীর পূর্ণ নাম (বাংলায়)','যেমন: মো. আরিফ হোসেন',true],
              ['text','student_name_en','শিক্ষার্থীর পূর্ণ নাম (ইংরেজিতে)','e.g. Md. Arif Hossain',true],
              ['date','dob','জন্ম তারিখ','',true],
              ['text','birth_cert_no','জন্ম নিবন্ধন নম্বর (১৭ সংখ্যা)','১৭ সংখ্যা',true],
            ];
            foreach ($sFields as $f): ?>
            <div>
              <label class="form-label" for="<?php echo h($f[1]); ?>"><?php echo h($f[2]); ?> <?php if ($f[4]): ?><span class="req">*</span><?php endif; ?></label>
              <input type="<?php echo h($f[0]); ?>" id="<?php echo h($f[1]); ?>" name="<?php echo h($f[1]); ?>"
                     class="form-input" placeholder="<?php echo h($f[3]); ?>"
                     value="<?php echo fv($old, $f[1]); ?>"
                     <?php if ($f[4]) echo 'required'; ?> />
            </div>
            <?php endforeach; ?>

            <div>
              <label class="form-label" for="gender">লিঙ্গ <span class="req">*</span></label>
              <select id="gender" name="gender" class="form-input" required>
                <option value="">নির্বাচন করুন</option>
                <option value="male"   <?php echo fv($old,'gender')==='male'   ?'selected':''; ?>>ছেলে</option>
                <option value="female" <?php echo fv($old,'gender')==='female' ?'selected':''; ?>>মেয়ে</option>
              </select>
            </div>
            <div>
              <label class="form-label" for="apply_class_id">আবেদনকৃত শ্রেণি <span class="req">*</span></label>
              <select id="apply_class_id" name="apply_class_id" class="form-input" required>
                <option value="">শ্রেণি নির্বাচন করুন</option>
                <?php foreach ($classes as $cls): ?>
                <option value="<?php echo (int)$cls['id']; ?>"
                  <?php echo (isset($old['apply_class_id']) && (int)$old['apply_class_id'] === (int)$cls['id']) ? 'selected' : ''; ?>>
                  <?php echo h($cls['class_name']); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="religion">ধর্ম</label>
              <select id="religion" name="religion" class="form-input">
                <?php
                $rels = ['islam'=>'ইসলাম','hinduism'=>'হিন্দু','christianity'=>'খ্রিস্টান','buddhism'=>'বৌদ্ধ','other'=>'অন্যান্য'];
                foreach ($rels as $rv => $rl): ?>
                <option value="<?php echo h($rv); ?>" <?php echo fv($old,'religion')===$rv?'selected':''; ?>><?php echo h($rl); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="blood_group">রক্তের গ্রুপ</label>
              <select id="blood_group" name="blood_group" class="form-input">
                <option value="">নির্বাচন করুন</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?php echo h($bg); ?>" <?php echo fv($old,'blood_group')===$bg?'selected':''; ?>><?php echo h($bg); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="prev_school">পূর্ববর্তী বিদ্যালয়</label>
              <input type="text" id="prev_school" name="prev_school" class="form-input"
                     placeholder="পূর্ববর্তী বিদ্যালয়ের নাম" value="<?php echo fv($old,'prev_school'); ?>" />
            </div>
          </div>

          <!-- Section B: Guardian -->
          <div class="mb-4 mt-6 text-xs font-bold uppercase tracking-wider text-kma-dark dark:text-gray-300 pb-2 border-b-2 border-accent-light flex items-center gap-2">
            <i class="bi bi-people-fill text-accent"></i> অংশ-খ: অভিভাবকের তথ্য
          </div>
          <div class="grid sm:grid-cols-2 gap-4 mb-6">
            <?php
            $gFields = [
              ['text','father_name','পিতার নাম (বাংলায়)','পিতার পূর্ণ নাম',true],
              ['text','mother_name','মাতার নাম (বাংলায়)','মাতার পূর্ণ নাম',true],
              ['text','father_occupation','পিতার পেশা','যেমন: ব্যবসায়ী',false],
              ['text','mother_occupation','মাতার পেশা','যেমন: গৃহিণী',false],
              ['tel','guardian_phone','যোগাযোগের নম্বর','01XXXXXXXXX',true],
              ['email','guardian_email','ইমেইল ঠিকানা','example@email.com',false],
              ['text','father_nid','পিতার NID নম্বর','জাতীয় পরিচয়পত্র নম্বর',true],
            ];
            foreach ($gFields as $f): ?>
            <div>
              <label class="form-label" for="<?php echo h($f[1]); ?>"><?php echo h($f[2]); ?> <?php if ($f[4]): ?><span class="req">*</span><?php endif; ?></label>
              <input type="<?php echo h($f[0]); ?>" id="<?php echo h($f[1]); ?>" name="<?php echo h($f[1]); ?>"
                     class="form-input" placeholder="<?php echo h($f[3]); ?>"
                     value="<?php echo fv($old,$f[1]); ?>"
                     <?php if ($f[4]) echo 'required'; ?> />
            </div>
            <?php endforeach; ?>
            <div>
              <label class="form-label" for="annual_income">পারিবারিক বার্ষিক আয়</label>
              <select id="annual_income" name="annual_income" class="form-input">
                <option value="">নির্বাচন করুন</option>
                <?php
                $incomes = ['low'=>'১ লক্ষের নিচে','medium'=>'১–৩ লক্ষ','high'=>'৩–৬ লক্ষ','higher'=>'৬ লক্ষের উপরে'];
                foreach ($incomes as $iv => $il): ?>
                <option value="<?php echo h($iv); ?>" <?php echo fv($old,'annual_income')===$iv?'selected':''; ?>><?php echo h($il); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Section C: Address -->
          <div class="mb-4 mt-6 text-xs font-bold uppercase tracking-wider text-kma-dark dark:text-gray-300 pb-2 border-b-2 border-accent-light flex items-center gap-2">
            <i class="bi bi-geo-alt-fill text-accent"></i> অংশ-গ: ঠিকানা
          </div>
          <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <div class="sm:col-span-3">
              <label class="form-label" for="address">বর্তমান ঠিকানা <span class="req">*</span></label>
              <textarea id="address" name="address" class="form-input" rows="2"
                        placeholder="গ্রাম/মহল্লা, ডাকঘর, উপজেলা, জেলা" required><?php echo fv($old,'address'); ?></textarea>
            </div>
            <div>
              <label class="form-label" for="district">জেলা <span class="req">*</span></label>
              <input type="text" id="district" name="district" class="form-input" required
                     placeholder="জেলার নাম" value="<?php echo fv($old,'district'); ?>" />
            </div>
            <div>
              <label class="form-label" for="upazila">উপজেলা</label>
              <input type="text" id="upazila" name="upazila" class="form-input"
                     placeholder="উপজেলার নাম" value="<?php echo fv($old,'upazila'); ?>" />
            </div>
            <div>
              <label class="form-label" for="post_code">পোস্ট কোড</label>
              <input type="text" id="post_code" name="post_code" class="form-input"
                     placeholder="পোস্ট কোড" maxlength="4" value="<?php echo fv($old,'post_code'); ?>" />
            </div>
          </div>

          <!-- Section D: Extra -->
          <div class="mb-4 mt-6 text-xs font-bold uppercase tracking-wider text-kma-dark dark:text-gray-300 pb-2 border-b-2 border-accent-light flex items-center gap-2">
            <i class="bi bi-paperclip text-accent"></i> অংশ-ঘ: অতিরিক্ত তথ্য
          </div>
          <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="form-label">বৃত্তির জন্য আবেদন করছেন কি?</label>
              <div class="flex gap-5 mt-2">
                <label class="flex items-center gap-2 text-sm text-kma-muted cursor-pointer">
                  <input type="checkbox" name="scholarship_apply" value="1" class="accent-accent"
                         <?php echo (isset($old['scholarship_apply']) && $old['scholarship_apply']) ? 'checked' : ''; ?> />
                  হ্যাঁ
                </label>
              </div>
            </div>
            <div>
              <label class="form-label" for="hear_about">কীভাবে জানলেন?</label>
              <select id="hear_about" name="hear_about" class="form-input">
                <option value="">নির্বাচন করুন</option>
                <?php
                $hears = ['friend'=>'বন্ধু/পরিচিত','social'=>'সোশ্যাল মিডিয়া','banner'=>'ব্যানার/পোস্টার','newspaper'=>'পত্রিকা','other'=>'অন্যান্য'];
                foreach ($hears as $hv => $hl): ?>
                <option value="<?php echo h($hv); ?>" <?php echo fv($old,'hear_about')===$hv?'selected':''; ?>><?php echo h($hl); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="form-label" for="remarks">অতিরিক্ত মন্তব্য</label>
              <textarea id="remarks" name="remarks" class="form-input" rows="2"
                        placeholder="শিক্ষার্থীর বিশেষ কোনো তথ্য..."><?php echo fv($old,'remarks'); ?></textarea>
            </div>

            <!-- Photo upload -->
            <div>
              <label class="form-label">শিক্ষার্থীর ছবি</label>
              <label class="block border-2 border-dashed border-kma-border rounded-xl p-5 text-center cursor-pointer hover:border-accent hover:bg-accent-light transition-colors">
                <input type="file" name="student_photo" accept="image/jpeg,image/png,image/webp"
                       data-label="photo-label" class="hidden" />
                <i class="bi bi-image text-3xl text-kma-muted block mb-1"></i>
                <p id="photo-label" class="text-xs text-kma-muted">ক্লিক করে ছবি বেছে নিন</p>
                <p class="text-[0.65rem] text-kma-muted mt-1">JPG, PNG, WEBP · সর্বোচ্চ ২ MB</p>
              </label>
            </div>
            <!-- Cert upload -->
            <div>
              <label class="form-label">জন্ম নিবন্ধন সনদ</label>
              <label class="block border-2 border-dashed border-kma-border rounded-xl p-5 text-center cursor-pointer hover:border-accent hover:bg-accent-light transition-colors">
                <input type="file" name="birth_cert_file" accept="application/pdf,image/jpeg,image/png"
                       data-label="cert-label" class="hidden" />
                <i class="bi bi-file-earmark-pdf text-3xl text-kma-muted block mb-1"></i>
                <p id="cert-label" class="text-xs text-kma-muted">ক্লিক করে ফাইল বেছে নিন</p>
                <p class="text-[0.65rem] text-kma-muted mt-1">PDF, JPG, PNG · সর্বোচ্চ ৫ MB</p>
              </label>
            </div>
          </div>

          <!-- Declaration -->
          <div class="mb-6">
            <label class="flex items-start gap-3 cursor-pointer">
              <input type="checkbox" id="declaration" name="declaration" required class="accent-accent mt-1 flex-shrink-0" />
              <span class="text-sm text-kma-muted">
                আমি ঘোষণা করছি যে উপরে প্রদত্ত সকল তথ্য সত্য ও সঠিক। মিথ্যা তথ্য প্রদানের ক্ষেত্রে ভর্তি বাতিল করা হতে পারে।
                <span class="req">*</span>
              </span>
            </label>
          </div>

          <button type="submit" id="admissionSubmitBtn"
                  class="w-full bg-accent text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2
                         hover:bg-[#1a4a2a] hover:-translate-y-0.5 hover:shadow-xl transition-all font-bn text-base">
            <i class="bi bi-send-fill"></i> আবেদন জমা দিন
          </button>
          <p class="text-center text-xs text-kma-muted mt-3">
            আবেদন সংক্রান্ত যেকোনো সমস্যায় কল করুন:
            <a href="tel:<?php echo h(isset($site['school_phone']) ? $site['school_phone'] : ''); ?>" class="text-accent font-semibold">
              <?php echo h(isset($site['school_phone']) ? $site['school_phone'] : '+880 1866-751015'); ?>
            </a>
          </p>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── FAQ ── -->
<section id="sec-faq" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-3xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-question-circle-fill"></i><span></span></div>
      <h2 class="section-title">প্রায়শই জিজ্ঞাসিত প্রশ্নসমূহ</h2>
      <p class="text-kma-muted text-sm mt-1">ভর্তি সংক্রান্ত সাধারণ প্রশ্নের উত্তর</p>
    </div>
    <?php
    $faqs = [
      ['ভর্তির জন্য কোন কাগজপত্র লাগবে?','জন্ম নিবন্ধন সনদ, ছবি, পিতা-মাতার NID কপি, পূর্ববর্তী বিদ্যালয়ের ছাড়পত্র (প্রযোজ্য), টিকা কার্ড।'],
      ['ফি কি কিস্তিতে দেওয়া যাবে?','হ্যাঁ, মাসিক বেতন প্রতি মাসে পরিশোধ করা যাবে। বিশেষ আর্থিক সংকটে অধ্যক্ষের সাথে আলোচনা করে কিস্তির ব্যবস্থা সম্ভব।'],
      ['ভর্তি পরীক্ষায় কী কী থাকে?','প্রাক-প্রাথমিক ও প্রথম শ্রেণিতে শুধু মৌখিক সাক্ষাৎকার। দ্বিতীয়-পঞ্চম শ্রেণিতে বাংলা ও গণিতে সংক্ষিপ্ত লিখিত পরীক্ষা।'],
      ['অনলাইনে আবেদন করলে কি অফিসে যেতে হবে?','হ্যাঁ, মূল কাগজপত্র যাচাই ও পরীক্ষার জন্য সরাসরি অফিসে আসতে হবে।'],
      ['বৃত্তির জন্য আলাদাভাবে আবেদন করতে হবে?','হ্যাঁ, মেধাবৃত্তি ছাড়া অন্য সহায়তার জন্য আলাদা আবেদন প্রয়োজন।'],
      ['ভর্তি বাতিল করলে ফি ফেরত পাওয়া যাবে?','ক্লাস শুরুর ৭ দিন আগে বাতিল করলে বেতন ও সেশন চার্জ ফেরত পাওয়া যাবে, তবে ভর্তি ফি অফেরতযোগ্য।'],
    ];
    foreach ($faqs as $i => $faq): ?>
    <div class="reveal reveal-d<?php echo min($i+1,5); ?> mb-2 rounded-xl overflow-hidden shadow-sm border border-kma-border">
      <button class="w-full flex items-center justify-between px-5 py-4 bg-kma-bg dark:bg-gray-700 text-sm font-bold text-kma-dark dark:text-white hover:bg-accent hover:text-white transition-colors group text-left"
              onclick="var b=this.nextElementSibling;b.classList.toggle('hidden');this.querySelector('.faq-icon').classList.toggle('rotate-180')">
        <span><?php echo h($faq[0]); ?></span>
        <i class="bi bi-chevron-down faq-icon transition-transform text-kma-muted group-hover:text-white flex-shrink-0 ml-3"></i>
      </button>
      <div class="hidden px-5 py-4 bg-white dark:bg-gray-800 text-sm text-kma-muted leading-relaxed">
        <?php echo h($faq[1]); ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

</main>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-telephone-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">আরও তথ্য দরকার?</h2>
    <p class="text-white/80 mb-6 text-sm">হেল্পলাইনে কল করুন বা সরাসরি বিদ্যালয়ে আসুন।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="tel:<?php echo h(isset($site['school_phone']) ? $site['school_phone'] : ''); ?>" class="btn-gold">
        <i class="bi bi-telephone-fill"></i> হেল্পলাইনে কল করুন
      </a>
      <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-outline">
        <i class="bi bi-map"></i> আমাদের খুঁজুন
      </a>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>