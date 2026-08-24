<?php
/**
 * KMA — academy/holiday-list.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'ছুটির তালিকা ২০২৬ | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'বাংলাদেশ সরকারি ছুটি ও বিদ্যালয়ের সম্পূর্ণ বার্ষিক ছুটির সূচি।';

$pdo = getDB();

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($year < 2020 || $year > 2035) { $year = (int)date('Y'); }

$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : 'all';
$allowedTypes = ['all','govt','school','exam','event'];
if (!in_array($typeFilter, $allowedTypes)) { $typeFilter = 'all'; }

/* Fetch holidays */
if ($typeFilter === 'all') {
    $stmt = $pdo->prepare(
        'SELECT * FROM holidays WHERE year=? AND is_active=1 ORDER BY start_date'
    );
    $stmt->execute([$year]);
} else {
    $stmt = $pdo->prepare(
        'SELECT * FROM holidays WHERE year=? AND type=? AND is_active=1 ORDER BY start_date'
    );
    $stmt->execute([$year, $typeFilter]);
}
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Count by type */
$counts = ['govt'=>0,'school'=>0,'exam'=>0,'event'=>0,'total'=>0];
$allStmt = $pdo->prepare('SELECT type, COUNT(*) as cnt FROM holidays WHERE year=? AND is_active=1 GROUP BY type');
$allStmt->execute([$year]);
foreach ($allStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $counts[$row['type']] = (int)$row['cnt'];
    $counts['total'] += (int)$row['cnt'];
}

/* Group by month */
$byMonth = [];
foreach ($holidays as $h) {
    $m = (int)date('n', strtotime($h['start_date']));
    $byMonth[$m][] = $h;
}

/* Download PDF for holiday */
$dlStmt = $pdo->prepare(
    "SELECT * FROM downloads WHERE is_active=1 AND category='holiday' ORDER BY created_at DESC LIMIT 3"
);
$dlStmt->execute();
$dlFiles = $dlStmt->fetchAll(PDO::FETCH_ASSOC);

$bnMonths = ['','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
$typeMeta = [
    'govt'   => ['label'=>'সরকারি ছুটি',    'tw'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',    'cal'=>'bg-red-500 text-white',    'dot'=>'bg-red-400'],
    'school' => ['label'=>'বিদ্যালয় ছুটি', 'tw'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400','cal'=>'bg-accent text-white',  'dot'=>'bg-green-400'],
    'exam'   => ['label'=>'পরীক্ষা',         'tw'=>'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400','cal'=>'bg-gold text-kma-dark', 'dot'=>'bg-yellow-400'],
    'event'  => ['label'=>'বিশেষ অনুষ্ঠান', 'tw'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400','cal'=>'bg-purple-600 text-white','dot'=>'bg-purple-400'],
];

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:260px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1568667256549-094345857637?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-calendar3"></i> শিক্ষাবর্ষ <?php echo $year; ?></div>
      <h1 class="font-bn font-bold text-4xl mb-3">ছুটির <em style="font-style:normal;color:#c9a227">তালিকা <?php echo $year; ?></em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="text-white/70 hover:text-gold">একাডেমিক</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">ছুটির তালিকা</span>
      </nav>
      <div class="flex flex-wrap gap-3 mt-5">
        <div class="ph-badge"><i class="bi bi-calendar-check"></i> মোট <?php echo $counts['total']; ?>+ ছুটি</div>
        <div class="ph-badge"><i class="bi bi-flag-fill"></i> সরকারি <?php echo $counts['govt']; ?>টি</div>
        <div class="ph-badge"><i class="bi bi-building"></i> বিদ্যালয় <?php echo $counts['school']; ?>টি</div>
      </div>
    </div>
  </div>
</header>

<!-- Stats strip -->
<div class="bg-kma-dark py-4">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
      <div><div class="font-display text-2xl font-bold text-gold"><?php echo $counts['govt']; ?></div><div class="text-white/60 text-xs">সরকারি ছুটি</div></div>
      <div><div class="font-display text-2xl font-bold text-gold"><?php echo $counts['school']; ?></div><div class="text-white/60 text-xs">বিদ্যালয় ছুটি</div></div>
      <div><div class="font-display text-2xl font-bold text-gold"><?php echo $counts['exam']; ?></div><div class="text-white/60 text-xs">পরীক্ষা কার্যক্রম</div></div>
      <div><div class="font-display text-2xl font-bold text-gold"><?php echo $counts['event']; ?></div><div class="text-white/60 text-xs">বিশেষ অনুষ্ঠান</div></div>
    </div>
  </div>
</div>

<main id="main-content">
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">

    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-calendar3"></i><span></span></div>
      <h2 class="section-title">বার্ষিক ছুটির তালিকা <?php echo $year; ?></h2>
      <p class="text-kma-muted text-sm mt-1">মাস বা ধরন অনুযায়ী ফিল্টার করে দেখুন</p>
    </div>

    <!-- Year selector + type filter -->
    <div class="flex flex-wrap items-center justify-center gap-3 mb-8 reveal">
      <!-- Year -->
      <form method="GET" class="flex items-center gap-2">
        <input type="hidden" name="type" value="<?php echo h($typeFilter); ?>" />
        <select name="year" onchange="this.form.submit()"
                class="form-input text-sm py-2 px-3" style="width:auto">
          <?php for ($y = (int)date('Y')+1; $y >= 2025; $y--): ?>
          <option value="<?php echo $y; ?>" <?php echo $y===$year ? 'selected' : ''; ?>><?php echo $y; ?> শিক্ষাবর্ষ</option>
          <?php endfor; ?>
        </select>
      </form>

      <!-- Type filter -->
      <a href="?year=<?php echo $year; ?>&type=all"
         class="px-4 py-2 rounded-full text-sm font-bold border transition-all
                <?php echo $typeFilter==='all' ? 'bg-kma-dark text-white border-kma-dark' : 'bg-white dark:bg-gray-700 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        সব দেখুন
      </a>
      <?php foreach ($typeMeta as $tk => $tm): ?>
      <a href="?year=<?php echo $year; ?>&type=<?php echo h($tk); ?>"
         class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold border transition-all
                <?php echo $typeFilter===$tk ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        <span class="w-2.5 h-2.5 rounded-full <?php echo h($tm['dot']); ?>"></span>
        <?php echo h($tm['label']); ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-3 justify-center mb-8 reveal" role="list" aria-label="রঙের পরিচিতি">
      <?php foreach ($typeMeta as $tk => $tm): ?>
      <div class="flex items-center gap-2 text-xs text-kma-muted bg-kma-bg dark:bg-gray-700 border border-kma-border dark:border-gray-600 px-3 py-1.5 rounded-full" role="listitem">
        <span class="w-3 h-3 rounded-sm <?php echo h($tm['dot']); ?>"></span>
        <?php echo h($tm['label']); ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Download button -->
    <?php if (!empty($dlFiles)): ?>
    <div class="text-center mb-8 reveal">
      <a href="<?php echo h(UPLOAD_PDFS_URL . $dlFiles[0]['file_path']); ?>" download
         class="inline-flex items-center gap-2 bg-accent text-white font-bold px-6 py-3 rounded-xl hover:bg-[#1a4a2a] hover:shadow-md transition-all text-sm">
        <i class="bi bi-download"></i> সম্পূর্ণ ক্যালেন্ডার ডাউনলোড করুন (PDF)
      </a>
    </div>
    <?php endif; ?>

    <?php if (empty($holidays)): ?>
    <div class="text-center py-16">
      <i class="bi bi-calendar-x text-kma-border text-5xl block mb-4"></i>
      <p class="text-kma-muted text-sm">নির্বাচিত ফিল্টারে কোনো ছুটি নেই।</p>
      <a href="?year=<?php echo $year; ?>" class="mt-3 inline-block text-accent text-sm font-semibold hover:underline">সব ছুটি দেখুন →</a>
    </div>
    <?php else: ?>

    <!-- Month-wise accordion -->
    <div class="space-y-3 reveal" id="calAccordion">
      <?php foreach ($byMonth as $m => $mHolidays): ?>
      <?php $accId = 'cal-month-'.$m; $isFirst = ($m === array_key_first($byMonth)); ?>
      <div class="bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
        <!-- Month header -->
        <button type="button"
                onclick="toggleAcc('<?php echo $accId; ?>')"
                class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-kma-border/30 dark:hover:bg-gray-600 transition-colors"
                aria-expanded="<?php echo $isFirst ? 'true' : 'false'; ?>">
          <i class="bi bi-calendar-month text-accent"></i>
          <span class="font-bold text-kma-dark dark:text-white"><?php echo h($bnMonths[$m]); ?> <?php echo $year; ?></span>
          <span class="ml-2 text-xs text-kma-muted"><?php echo count($mHolidays); ?>টি</span>
          <i class="bi bi-chevron-down text-kma-muted ml-auto transition-transform acc-arrow text-sm"></i>
        </button>

        <!-- Holiday rows -->
        <div id="<?php echo $accId; ?>" style="<?php echo $isFirst ? '' : 'display:none'; ?>">
          <?php foreach ($mHolidays as $hol):
            $meta  = isset($typeMeta[$hol['type']]) ? $typeMeta[$hol['type']] : $typeMeta['event'];
            $start = date('d', strtotime($hol['start_date']));
            $end   = !empty($hol['end_date']) && $hol['end_date'] !== $hol['start_date']
                     ? '–'.date('d', strtotime($hol['end_date'])) : '';
            $dur   = '';
            if (!empty($hol['duration_days']) && $hol['duration_days'] > 1) {
                $dur = $hol['duration_days'].' দিন';
            } elseif ($end) {
                $dur = 'একাধিক দিন';
            } else {
                $dur = '১ দিন';
            }
          ?>
          <div class="flex items-start gap-4 px-5 py-3.5 border-t border-kma-border dark:border-gray-600 hover:bg-white dark:hover:bg-gray-800 transition-colors">
            <!-- Date pill -->
            <div class="<?php echo h($meta['cal']); ?> rounded-xl text-center px-3 py-2 flex-shrink-0 min-w-[60px]">
              <div class="font-display text-xl font-bold leading-none"><?php echo $start.$end; ?></div>
              <div class="text-[0.62rem] font-semibold opacity-80 mt-0.5"><?php echo h($bnMonths[$m]); ?></div>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-[0.65rem] font-bold px-2 py-0.5 rounded-full <?php echo h($meta['tw']); ?>"><?php echo h($meta['label']); ?></span>
              </div>
              <div class="font-bold text-sm text-kma-dark dark:text-white leading-snug"><?php echo h($hol['title']); ?></div>
              <?php if (!empty($hol['description'])): ?>
              <div class="text-xs text-kma-muted mt-0.5"><?php echo h($hol['description']); ?></div>
              <?php endif; ?>
            </div>
            <!-- Duration -->
            <div class="flex-shrink-0 text-right">
              <span class="text-xs bg-kma-bg dark:bg-gray-700 border border-kma-border dark:border-gray-500 text-kma-muted font-bold px-2 py-1 rounded-lg">
                <?php echo h($dur); ?>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 flex items-start gap-3 text-sm text-kma-muted mt-8 reveal">
      <i class="bi bi-info-circle-fill text-gold mt-0.5 flex-shrink-0 text-lg"></i>
      <span>ঈদ ও ধর্মীয় ছুটির তারিখ চাঁদ দেখার উপর নির্ভরশীল, তাই পরিবর্তিত হতে পারে। সরকারি গেজেট ও নোটিশ বোর্ড সর্বশেষ তথ্যের জন্য দেখুন।</span>
    </div>

    <?php endif; ?>

  </div>
</section>

<!-- PDF Downloads -->
<?php if (!empty($dlFiles)): ?>
<section class="py-12 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-7 reveal">
      <h2 class="section-title">ডাউনলোড করুন</h2>
      <p class="text-kma-muted text-sm mt-1">পিডিএফ ও প্রিন্টযোগ্য ফরম্যাটে ছুটির তালিকা</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 reveal">
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
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-calendar-event"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">অন্য একাডেমিক তথ্য দেখুন</h2>
    <div class="flex flex-wrap gap-3 justify-center mt-5">
      <a href="<?php echo BASE_URL; ?>/academy/exam-schedule.php" class="btn-gold"><i class="bi bi-clipboard-check"></i> পরীক্ষার সময়সূচি</a>
      <a href="<?php echo BASE_URL; ?>/academy/class-routine.php" class="btn-outline"><i class="bi bi-calendar-week"></i> ক্লাস রুটিন</a>
    </div>
  </div>
</section>

<script>
function toggleAcc(id) {
  var body  = document.getElementById(id);
  var btn   = body ? body.previousElementSibling : null;
  var arrow = btn ? btn.querySelector('.acc-arrow') : null;
  if (!body) return;
  var open = body.style.display !== 'none';
  body.style.display = open ? 'none' : 'block';
  if (btn)   btn.setAttribute('aria-expanded', open ? 'false' : 'true');
  if (arrow) arrow.style.transform = open ? '' : 'rotate(180deg)';
}
/* Open first month arrow on load */
document.addEventListener('DOMContentLoaded', function() {
  var first = document.querySelector('.acc-arrow');
  if (first) first.style.transform = 'rotate(180deg)';
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>