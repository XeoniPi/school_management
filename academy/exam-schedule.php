<?php
/**
 * KMA — academy/exam-schedule.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'পরীক্ষার সময়সূচি ২০২৬ | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'প্রথম সাময়িক, অর্ধ-বার্ষিক ও বার্ষিক পরীক্ষার সম্পূর্ণ সময়সূচি।';

$pdo = getDB();

/* Exam types — keys MUST match the `exam_type` ENUM in the exam_schedules table */
$examTypes = [
    'first_term' => ['label'=>'প্রথম সাময়িক পরীক্ষা', 'short'=>'১ম সাময়িক', 'icon'=>'bi-1-circle-fill',   'grad'=>'from-blue-700 to-blue-900'],
    'mid_term'   => ['label'=>'অর্ধ-বার্ষিক পরীক্ষা',  'short'=>'অর্ধ-বার্ষিক','icon'=>'bi-half',           'grad'=>'from-orange-600 to-orange-900'],
    'annual'     => ['label'=>'বার্ষিক পরীক্ষা',        'short'=>'বার্ষিক',     'icon'=>'bi-trophy-fill',    'grad'=>'from-accent to-[#1a4a2a]'],
    'monthly'    => ['label'=>'মাসিক মূল্যায়ন',        'short'=>'মাসিক',       'icon'=>'bi-calendar-month', 'grad'=>'from-gray-700 to-gray-900'],
];

/* Active type tab */
$activeType = isset($_GET['exam']) ? sanitize($_GET['exam']) : 'first_term';
if (!array_key_exists($activeType, $examTypes)) { $activeType = 'first_term'; }

/* Classes for selector */
$classes = [];
try {
    $classes = $pdo->query('SELECT * FROM classes WHERE is_active=1 ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $classes = []; }

/* Active class filter */
$activeClass = isset($_GET['class']) ? (int)$_GET['class'] : 0;

/* Fetch exam schedules — defensive: table may not exist yet on some installs */
$schedules = [];
try {
    $params = [$activeType];
    $sql = 'SELECT es.*, es.time_start AS start_time, es.time_end AS end_time,
                   es.full_marks AS total_marks,
                   s.name_bn AS subject_bn, s.name_en AS subject_en,
                   c.class_name, c.id AS class_id
            FROM exam_schedules es
            LEFT JOIN subjects s ON s.id = es.subject_id
            JOIN classes  c ON c.id = es.class_id
            WHERE es.exam_type = ? AND es.is_active = 1';
    if ($activeClass) {
        $sql .= ' AND es.class_id = ?';
        $params[] = $activeClass;
    }
    $sql .= ' ORDER BY es.exam_date, es.class_id, es.sort_order';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('exam-schedule.php DB error: ' . $e->getMessage());
    $schedules = [];
}

/* Group by date */
$byDate = [];
foreach ($schedules as $s) {
    $byDate[$s['exam_date']][] = $s;
}

/* Download PDFs */
$dlFiles = [];
try {
    $dlStmt = $pdo->prepare(
        "SELECT * FROM downloads WHERE is_active=1 AND category='exam_schedule' ORDER BY created_at DESC LIMIT 4"
    );
    $dlStmt->execute();
    $dlFiles = $dlStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $dlFiles = []; }

/* Marks badge helper */
function marksBadge($marks) {
    $m = (int)$marks;
    if ($m >= 100) { return '<span class="text-[0.65rem] font-bold bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-full">'.$m.'</span>'; }
    if ($m >= 50)  { return '<span class="text-[0.65rem] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 px-2 py-0.5 rounded-full">'.$m.'</span>'; }
    return '<span class="text-[0.65rem] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-full">মৌখিক</span>';
}

$bnDays = ['Sun'=>'রবিবার','Mon'=>'সোমবার','Tue'=>'মঙ্গলবার','Wed'=>'বুধবার','Thu'=>'বৃহস্পতিবার','Fri'=>'শুক্রবার','Sat'=>'শনিবার'];
$bnMonths = ['01'=>'জানু.','02'=>'ফেব্রু.','03'=>'মার্চ','04'=>'এপ্রিল','05'=>'মে','06'=>'জুন','07'=>'জুলাই','08'=>'আগস্ট','09'=>'সেপ্টে.','10'=>'অক্টো.','11'=>'নভে.','12'=>'ডিসে.'];

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:260px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1588072432836-e10032774350?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-clipboard-check"></i> শিক্ষাবর্ষ ২০২৫</div>
      <h1 class="font-bn font-bold text-4xl mb-3">পরীক্ষার <em style="font-style:normal;color:#c9a227">সময়সূচি</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="text-white/70 hover:text-gold">একাডেমিক</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">পরীক্ষার সময়সূচি</span>
      </nav>
      <div class="flex flex-wrap gap-3 mt-5">
        <div class="ph-badge"><i class="bi bi-pencil-square"></i> ৩টি প্রধান পরীক্ষা</div>
        <div class="ph-badge"><i class="bi bi-people"></i> সকল শ্রেণি</div>
        <div class="ph-badge"><i class="bi bi-award"></i> পূর্ণমান ১০০</div>
      </div>
      <a href="#sec-download" class="inline-flex items-center gap-2 bg-gold text-kma-dark font-bold px-5 py-2.5 rounded-full text-sm hover:bg-white hover:text-accent transition-all mt-5">
        <i class="bi bi-download"></i> পিডিএফ ডাউনলোড
      </a>
    </div>
  </div>
</header>

<main id="main-content">
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">

    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-clipboard-data-fill"></i><span></span></div>
      <h2 class="section-title">পরীক্ষার সময়সূচি ২০২৫</h2>
      <p class="text-kma-muted text-sm mt-1">পরীক্ষার ধরন ও শ্রেণি নির্বাচন করে সময়সূচি দেখুন</p>
    </div>

    <!-- Exam type tabs -->
    <div class="bg-white dark:bg-gray-700 rounded-xl shadow-sm overflow-hidden mb-6 reveal" role="tablist" aria-label="পরীক্ষার ধরন">
      <div class="flex flex-wrap">
        <?php foreach ($examTypes as $ek => $em): ?>
        <a href="?exam=<?php echo h($ek); ?><?php echo $activeClass ? '&class='.$activeClass : ''; ?>"
           role="tab"
           aria-selected="<?php echo $activeType===$ek ? 'true' : 'false'; ?>"
           class="flex-1 min-w-[120px] flex items-center justify-center gap-2 px-4 py-4 text-sm font-bold transition-all
                  <?php echo $activeType===$ek
                      ? 'bg-accent text-white border-b-4 border-gold'
                      : 'text-kma-muted hover:text-accent hover:bg-kma-bg dark:hover:bg-gray-600 border-b-4 border-transparent'; ?>">
          <i class="bi <?php echo h($em['icon']); ?>"></i>
          <span class="hidden sm:inline"><?php echo h($em['label']); ?></span>
          <span class="sm:hidden"><?php echo h($em['short']); ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Active exam header -->
    <?php $activeExam = $examTypes[$activeType]; ?>
    <div class="bg-gradient-to-r <?php echo h($activeExam['grad']); ?> rounded-xl p-6 text-white mb-6 shadow-md reveal">
      <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
          <h3 class="font-bold text-xl mb-1"><i class="bi <?php echo h($activeExam['icon']); ?> mr-2"></i><?php echo h($activeExam['label']); ?></h3>
          <p class="text-white/75 text-sm">সকাল ১০:০০ থেকে শুরু। সঠিক সময় প্রতিটি সারিতে দেওয়া আছে।</p>
        </div>
        <div class="flex gap-6 flex-wrap text-center">
          <div><div class="font-display text-2xl font-bold text-gold"><?php echo count($byDate); ?></div><div class="text-white/60 text-xs">পরীক্ষার দিন</div></div>
          <div><div class="font-display text-2xl font-bold text-gold"><?php echo count($schedules); ?></div><div class="text-white/60 text-xs">মোট পেপার</div></div>
        </div>
      </div>
    </div>

    <!-- Class filter -->
    <?php if (!empty($classes)): ?>
    <div class="flex flex-wrap gap-2 mb-6 reveal" role="group" aria-label="শ্রেণি ফিল্টার">
      <a href="?exam=<?php echo h($activeType); ?>"
         class="px-4 py-2 rounded-full text-xs font-bold border transition-all
                <?php echo !$activeClass ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        সব শ্রেণি
      </a>
      <?php foreach ($classes as $cls): ?>
      <a href="?exam=<?php echo h($activeType); ?>&class=<?php echo $cls['id']; ?>"
         class="px-4 py-2 rounded-full text-xs font-bold border transition-all
                <?php echo $activeClass===$cls['id'] ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        <?php echo h($cls['class_name']); ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Schedule table -->
    <?php if (empty($schedules)): ?>
    <div class="text-center py-16 reveal">
      <i class="bi bi-clipboard-x text-kma-border text-5xl block mb-4"></i>
      <h3 class="font-bold text-kma-dark dark:text-white mb-2">সময়সূচি এখনো প্রকাশিত হয়নি</h3>
      <p class="text-kma-muted text-sm">এই পরীক্ষার সময়সূচি শীঘ্রই প্রকাশ করা হবে। নোটিশ বোর্ড দেখুন।</p>
    </div>
    <?php else: ?>

    <div class="space-y-4 reveal">
      <?php foreach ($byDate as $date => $rows):
        $ts      = strtotime($date);
        $dayKey  = date('D', $ts);
        $dayBn   = isset($bnDays[$dayKey]) ? $bnDays[$dayKey] : $dayKey;
        $monKey  = date('m', $ts);
        $monBn   = isset($bnMonths[$monKey]) ? $bnMonths[$monKey] : date('M', $ts);
        $dayNum  = date('d', $ts);
        $isToday = date('Y-m-d') === $date;
      ?>
      <div class="bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm <?php echo $isToday ? 'ring-2 ring-accent' : ''; ?>">
        <!-- Date header -->
        <div class="flex items-center gap-4 px-5 py-3.5 border-b border-kma-border dark:border-gray-600 bg-white dark:bg-gray-800">
          <div class="bg-accent text-white rounded-xl text-center px-4 py-2.5 flex-shrink-0">
            <div class="font-display text-2xl font-bold leading-none"><?php echo $dayNum; ?></div>
            <div class="text-[0.62rem] font-semibold opacity-80 mt-0.5"><?php echo h($monBn); ?></div>
          </div>
          <div>
            <div class="font-bold text-kma-dark dark:text-white"><?php echo h($dayBn); ?>, <?php echo date('d M Y', $ts); ?></div>
            <div class="text-xs text-kma-muted"><?php echo count($rows); ?>টি পেপার</div>
          </div>
          <?php if ($isToday): ?>
          <span class="ml-auto bg-accent text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse">আজ</span>
          <?php endif; ?>
        </div>

        <!-- Papers for this date -->
        <div class="divide-y divide-kma-border dark:divide-gray-600">
          <?php foreach ($rows as $row): ?>
          <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-white dark:hover:bg-gray-800 transition-colors flex-wrap sm:flex-nowrap">
            <!-- Subject -->
            <div class="flex-1 min-w-0">
              <div class="font-bold text-sm text-kma-dark dark:text-white"><?php echo h($row['subject_bn']); ?></div>
              <?php if (!empty($row['subject_en'])): ?>
              <div class="text-xs text-kma-muted"><?php echo h($row['subject_en']); ?></div>
              <?php endif; ?>
            </div>
            <!-- Class -->
            <span class="text-xs bg-accent-light text-accent dark:bg-green-900/30 dark:text-green-400 font-bold px-2.5 py-1 rounded-full flex-shrink-0">
              <?php echo h($row['class_name']); ?>
            </span>
            <!-- Time -->
            <?php if (!empty($row['start_time'])): ?>
            <div class="flex items-center gap-1.5 bg-kma-dark text-white text-xs font-bold px-3 py-1.5 rounded-full flex-shrink-0">
              <i class="bi bi-clock text-gold text-xs"></i>
              <?php echo h($row['start_time']); ?>
              <?php if (!empty($row['end_time'])): ?>– <?php echo h($row['end_time']); ?><?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- Marks -->
            <?php if (!empty($row['total_marks'])): ?>
            <?php echo marksBadge($row['total_marks']); ?>
            <?php endif; ?>
            <!-- Note -->
            <?php if (!empty($row['notes'])): ?>
            <div class="w-full sm:w-auto text-xs text-kma-muted italic sm:max-w-[160px] truncate" title="<?php echo h($row['notes']); ?>">
              <i class="bi bi-info-circle mr-0.5"></i><?php echo h($row['notes']); ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Notice -->
    <div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 flex items-start gap-3 text-sm text-kma-muted mt-8 reveal">
      <i class="bi bi-info-circle-fill text-gold mt-0.5 flex-shrink-0 text-lg"></i>
      <span>পরীক্ষার তারিখ পরিবর্তনের ক্ষেত্রে বিদ্যালয় থেকে আলাদাভাবে নোটিশ দেওয়া হবে। প্রাক-প্রাথমিক শ্রেণির পরীক্ষা মৌখিকভাবে নেওয়া হবে।</span>
    </div>

    <?php endif; ?>

    <!-- Preparation tips -->
    <div class="mt-12 reveal">
      <h2 class="section-title mb-6">পরীক্ষার প্রস্তুতির টিপস</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
        $tips = [
          ['bi-journal-check','ci-green','নিয়মিত পড়াশোনা করুন','পরীক্ষার আগে শেষ মুহূর্তে না পড়ে নিয়মিত প্রতিদিন নির্দিষ্ট সময় পড়ার অভ্যাস গড়ে তুলুন।',''],
          ['bi-pencil-fill','ci-gold','বেশি বেশি লিখুন','হাতে লিখে অনুশীলন করুন। গণিতের অঙ্ক এবং বাংলা-ইংরেজি রচনা নিজে লিখে দেখুন।','border-gold'],
          ['bi-clock-history','ci-blue','সময় ব্যবস্থাপনা','প্রতিটি বিষয়ের জন্য সমান সময় দিন। পরীক্ষার হলে সময় বণ্টন করে উত্তর করুন।','border-blue-400'],
          ['bi-heart-pulse-fill','ci-green','স্বাস্থ্যকর থাকুন','পরীক্ষার সময় যথেষ্ট ঘুমান এবং সঠিক খাবার খান। মানসিক চাপ থেকে দূরে থাকুন।',''],
          ['bi-people-fill','ci-gold','শিক্ষকের পরামর্শ নিন','কোনো বিষয় বুঝতে সমস্যা হলে শিক্ষকের কাছে জিজ্ঞেস করুন। নির্দ্বিধায় সাহায্য চান।','border-gold'],
          ['bi-exclamation-triangle-fill','ci-red','গুরুত্বপূর্ণ বিষয়','প্রতিটি বিষয়ে "পরীক্ষায় আসবে" চিহ্নিত অধ্যায়গুলো ভালোভাবে পড়ুন ও মুখস্থ করুন।','border-red-400'],
        ];
        $iconBgs = ['ci-green'=>'bg-accent-light text-accent','ci-gold'=>'bg-gold/15 text-yellow-700','ci-blue'=>'bg-blue-100 text-blue-600','ci-red'=>'bg-red-100 text-red-500'];
        foreach ($tips as $tip):
          $ibg = isset($iconBgs[$tip[1]]) ? $iconBgs[$tip[1]] : 'bg-gray-100 text-gray-600';
          $borderCls = !empty($tip[4]) ? 'border-l-4 '.$tip[4] : 'border-l-4 border-accent';
        ?>
        <div class="bg-kma-bg dark:bg-gray-700 rounded-xl p-5 shadow-sm <?php echo $borderCls; ?> hover:shadow-md hover:translate-x-1 transition-all">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-lg <?php echo $ibg; ?> flex items-center justify-center flex-shrink-0">
              <i class="bi <?php echo h($tip[0]); ?> text-sm"></i>
            </div>
            <h4 class="font-bold text-sm text-kma-dark dark:text-white"><?php echo h($tip[2]); ?></h4>
          </div>
          <p class="text-xs text-kma-muted leading-relaxed"><?php echo h($tip[3]); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>

<!-- Downloads -->
<?php if (!empty($dlFiles)): ?>
<section class="py-12 bg-kma-bg dark:bg-gray-900" id="sec-download">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-7 reveal">
      <h2 class="section-title">সময়সূচি ডাউনলোড করুন</h2>
      <p class="text-kma-muted text-sm mt-1">পিডিএফ ফরম্যাটে পরীক্ষার সময়সূচি সংরক্ষণ করুন</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 reveal">
      <?php
      $catMeta = ['routine'=>['label'=>'ক্লাস রুটিন','icon'=>'bi-calendar-week-fill','color'=>'bg-blue-100 text-blue-700'],'syllabus'=>['label'=>'সিলেবাস','icon'=>'bi-journal-bookmark-fill','color'=>'bg-gold/15 text-yellow-700'],'exam_schedule'=>['label'=>'পরীক্ষার সময়সূচি','icon'=>'bi-clipboard-check-fill','color'=>'bg-purple-100 text-purple-700'],'holiday'=>['label'=>'ছুটির তালিকা','icon'=>'bi-calendar3','color'=>'bg-green-100 text-green-700'],'other'=>['label'=>'অন্যান্য','icon'=>'bi-file-earmark-fill','color'=>'bg-gray-100 text-gray-600']];
      foreach ($dlFiles as $dl): include dirname(__DIR__) . '/includes/download_card.php'; endforeach;
      ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-book-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">অন্য একাডেমিক তথ্য দেখুন</h2>
    <div class="flex flex-wrap gap-3 justify-center mt-5">
      <a href="<?php echo BASE_URL; ?>/academy/syllabus.php"      class="btn-gold"><i class="bi bi-book-half"></i> সিলেবাস</a>
      <a href="<?php echo BASE_URL; ?>/academy/holiday-list.php"  class="btn-outline"><i class="bi bi-calendar3"></i> ছুটির তালিকা</a>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>