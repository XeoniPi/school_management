<?php
/**
 * KMA — academy/class-routine.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'ক্লাস রুটিন | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'সাপ্তাহিক শ্রেণিভিত্তিক পিরিয়ড সময়সূচি ও পিডিএফ ডাউনলোড।';

$pdo = getDB();

/* All active classes ordered */
$classes = $pdo->query(
    'SELECT * FROM classes WHERE is_active=1 ORDER BY sort_order'
)->fetchAll(PDO::FETCH_ASSOC);

/* Routines keyed by class_id */
$routineRows = $pdo->query(
    'SELECT cr.*, c.class_name, s.name_bn AS subject_bn, s.color_class
     FROM class_routines cr
     JOIN classes c  ON c.id  = cr.class_id
     JOIN subjects s ON s.id  = cr.subject_id
     WHERE c.is_active = 1
     ORDER BY cr.class_id, cr.day_of_week, cr.period_no'
)->fetchAll(PDO::FETCH_ASSOC);

/* Build nested array: routines[class_id][day][period] = row */
$routines = [];
foreach ($routineRows as $r) {
    $routines[$r['class_id']][$r['day_of_week']][$r['period_number']] = $r;
}

/* Download PDFs for routines */
$dlStmt = $pdo->prepare(
    "SELECT * FROM downloads WHERE is_active=1 AND category='routine' ORDER BY created_at DESC"
);
$dlStmt->execute();
$dlFiles = $dlStmt->fetchAll(PDO::FETCH_ASSOC);

$days    = ['শনিবার','রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার'];
$periods = [
    1 => ['time'=>'৮:০০–৮:৪৫',   'label'=>'১ম পিরিয়ড'],
    2 => ['time'=>'৮:৪৫–৯:৩০',   'label'=>'২য় পিরিয়ড'],
    3 => ['time'=>'৯:৩০–১০:১৫',  'label'=>'৩য় পিরিয়ড'],
    0 => ['time'=>'১০:১৫–১০:৩০', 'label'=>'বিরতি'],
    4 => ['time'=>'১০:৩০–১১:১৫', 'label'=>'৪র্থ পিরিয়ড'],
    5 => ['time'=>'১১:১৫–১২:০০', 'label'=>'৫ম পিরিয়ড'],
    6 => ['time'=>'১২:০০–১২:৪৫', 'label'=>'৬ষ্ঠ পিরিয়ড'],
    7 => ['time'=>'১২:৪৫–১:৩০',  'label'=>'৭ম পিরিয়ড'],
];

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:250px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-calendar-week"></i> সাপ্তাহিক সময়সূচি</div>
      <h1 class="font-bn font-bold text-4xl mb-3">ক্লাস <em style="font-style:normal;color:#c9a227">রুটিন</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="text-white/70 hover:text-gold">একাডেমিক</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">ক্লাস রুটিন</span>
      </nav>
      <div class="flex flex-wrap gap-3 mt-5">
        <div class="ph-badge"><i class="bi bi-clock"></i> প্রতিদিন ৭ পিরিয়ড</div>
        <div class="ph-badge"><i class="bi bi-calendar-check"></i> শনি – বৃহস্পতি</div>
      </div>
    </div>
  </div>
</header>

<main id="main-content">
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">

    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-calendar-week-fill"></i><span></span></div>
      <h2 class="section-title">সাপ্তাহিক ক্লাস রুটিন ২০২৫</h2>
      <p class="text-kma-muted text-sm mt-1">শ্রেণি নির্বাচন করুন এবং পিরিয়ড সূচি দেখুন</p>
    </div>

    <!-- Period legend -->
    <div class="flex flex-wrap gap-2 justify-center mb-7 reveal">
      <?php foreach ($periods as $pn => $pd): ?>
      <?php if ($pn === 0): ?>
      <div class="flex items-center gap-1.5 text-xs bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 text-yellow-700 dark:text-yellow-400 px-3 py-1.5 rounded-full font-semibold">
        <span class="w-5 h-5 rounded-full bg-gold text-white text-[0.6rem] font-bold flex items-center justify-center">বি</span>
        <?php echo h($pd['time']); ?>
      </div>
      <?php else: ?>
      <div class="flex items-center gap-1.5 text-xs bg-kma-bg dark:bg-gray-700 border border-kma-border dark:border-gray-600 text-kma-muted px-3 py-1.5 rounded-full">
        <span class="w-5 h-5 rounded-full bg-accent text-white text-[0.6rem] font-bold flex items-center justify-center"><?php echo $pn; ?></span>
        <?php echo h($pd['time']); ?>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if (empty($classes)): ?>
    <p class="text-center text-kma-muted py-16">রুটিন তথ্য এখনো যুক্ত করা হয়নি।</p>
    <?php else: ?>

    <!-- Class tabs -->
    <div class="flex flex-wrap gap-2 justify-center mb-7 reveal" role="tablist" aria-label="শ্রেণি নির্বাচন">
      <?php foreach ($classes as $i => $cls): ?>
      <button class="cls-tab px-4 py-2 rounded-full text-sm font-bold border transition-all
                     bg-white dark:bg-gray-800 text-kma-muted border-kma-border dark:border-gray-600
                     hover:border-accent hover:text-accent"
              role="tab"
              aria-selected="<?php echo $i===0 ? 'true' : 'false'; ?>"
              aria-controls="r-<?php echo h($cls['class_key']); ?>"
              data-cls="<?php echo h($cls['class_key']); ?>">
        <?php echo h($cls['class_name']); ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Panels -->
    <?php foreach ($classes as $i => $cls): ?>
    <div class="cls-panel <?php echo $i===0 ? 'show' : ''; ?> reveal"
         id="r-<?php echo h($cls['class_key']); ?>"
         role="tabpanel">
      <div class="table-responsive routine-wrap">
        <table class="routine-table" aria-label="<?php echo h($cls['class_name']); ?> সাপ্তাহিক রুটিন">
          <thead>
            <tr>
              <th scope="col" class="text-left pl-4" style="min-width:100px">পিরিয়ড</th>
              <?php foreach ($days as $day): ?>
              <th scope="col"><?php echo h($day); ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $periodOrder = [1,2,3,0,4,5,6,7];
            foreach ($periodOrder as $pn):
                $pd = $periods[$pn];
                $isBreak = ($pn === 0);
            ?>
            <tr>
              <td class="text-left pl-4 font-bold text-kma-dark dark:text-white" style="background:rgba(46,107,62,.04);border-right:2px solid #2e6b3e">
                <?php echo h($pd['label']); ?>
                <div class="text-xs font-normal text-kma-muted"><?php echo h($pd['time']); ?></div>
              </td>
              <?php if ($isBreak): ?>
              <td colspan="6" class="text-center">
                <span class="subj" style="background:#f1f5f9;color:#64748b;font-style:italic;padding:3px 12px;border-radius:20px;font-size:.76rem;font-weight:700">বিরতি</span>
              </td>
              <?php else: ?>
              <?php for ($d = 1; $d <= 6; $d++): ?>
              <td class="text-center">
                <?php
                $cell = isset($routines[$cls['id']][$d][$pn]) ? $routines[$cls['id']][$d][$pn] : null;
                if ($cell):
                    $colorClass = !empty($cell['color_class']) ? $cell['color_class'] : 's-other';
                ?>
                <span class="subj <?php echo h($colorClass); ?>"><?php echo h($cell['subject_bn']); ?></span>
                <?php else: ?>
                <span class="text-kma-border dark:text-gray-600 text-xs">—</span>
                <?php endif; ?>
              </td>
              <?php endfor; ?>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Notice -->
    <div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 flex items-start gap-3 text-sm text-kma-muted mt-8 reveal">
      <i class="bi bi-info-circle-fill text-gold mt-0.5 flex-shrink-0 text-lg"></i>
      <span>রুটিন পরিবর্তনের ক্ষেত্রে বিদ্যালয় থেকে আলাদাভাবে নোটিশ দেওয়া হবে। সর্বশেষ আপডেটের জন্য নোটিশ বোর্ড দেখুন।</span>
    </div>

  </div>
</section>

<!-- Downloads -->
<?php if (!empty($dlFiles)): ?>
<section class="py-12 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-7 reveal">
      <h2 class="section-title">রুটিন ডাউনলোড করুন</h2>
      <p class="text-kma-muted text-sm mt-1">পিডিএফ ফরম্যাটে সম্পূর্ণ রুটিন সংরক্ষণ করুন</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      $catMeta = [
        'routine' => ['label'=>'ক্লাস রুটিন','icon'=>'bi-calendar-week-fill','color'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
        'syllabus'=>['label'=>'সিলেবাস','icon'=>'bi-journal-bookmark-fill','color'=>'bg-gold/15 text-yellow-700'],
        'exam_schedule'=>['label'=>'পরীক্ষার সময়সূচি','icon'=>'bi-clipboard-check-fill','color'=>'bg-purple-100 text-purple-700'],
        'holiday'=>['label'=>'ছুটির তালিকা','icon'=>'bi-calendar3','color'=>'bg-green-100 text-green-700'],
        'other'=>['label'=>'অন্যান্য','icon'=>'bi-file-earmark-fill','color'=>'bg-gray-100 text-gray-600'],
      ];
      foreach ($dlFiles as $dl):
        include dirname(__DIR__) . '/includes/download_card.php';
      endforeach;
      ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-book-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">অন্য একাডেমিক তথ্য দেখুন</h2>
    <p class="text-white/80 mb-6 text-sm">সিলেবাস, ছুটির তালিকা ও পরীক্ষার সময়সূচি।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/academy/syllabus.php" class="btn-gold"><i class="bi bi-book-half"></i> সিলেবাস</a>
      <a href="<?php echo BASE_URL; ?>/academy/exam-schedule.php" class="btn-outline"><i class="bi bi-clipboard-check"></i> পরীক্ষার সময়সূচি</a>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>