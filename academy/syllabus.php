<?php
/**
 * KMA — academy/syllabus.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'সিলেবাস ২০২৫ | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'শ্রেণিভিত্তিক পাঠ্যক্রম, অধ্যায় তালিকা ও পিডিএফ ডাউনলোড।';

$pdo = getDB();

/* Classes */
$classes = $pdo->query(
    'SELECT * FROM classes WHERE is_active=1 ORDER BY sort_order'
)->fetchAll(PDO::FETCH_ASSOC);

/* Syllabus chapters keyed by class_id then subject_id */
$chapRows = $pdo->query(
    'SELECT sc.*, s.name_bn AS subject_bn, s.name_en AS subject_en, s.color_class, s.type AS subject_type
     FROM syllabus_chapters sc
     JOIN subjects s ON s.id = sc.subject_id
     ORDER BY sc.class_id, sc.subject_id, sc.chapter_no'
)->fetchAll(PDO::FETCH_ASSOC);

$chapters = [];
foreach ($chapRows as $ch) {
    $chapters[$ch['class_id']][$ch['subject_id']][] = $ch;
}

/* Subjects per class */
$csRows = $pdo->query(
    'SELECT cs.*, s.name_bn, s.name_en, s.color_class, s.type AS subject_type
     FROM class_subjects cs
     JOIN subjects s ON s.id = cs.subject_id
     ORDER BY cs.class_id, cs.sort_order'
)->fetchAll(PDO::FETCH_ASSOC);

$classSubs = [];
foreach ($csRows as $cs) {
    $classSubs[$cs['class_id']][] = $cs;
}

/* Download files */
$dlStmt = $pdo->prepare(
    "SELECT * FROM downloads WHERE is_active=1 AND category='syllabus' ORDER BY created_at DESC"
);
$dlStmt->execute();
$dlFiles = $dlStmt->fetchAll(PDO::FETCH_ASSOC);

/* Type badge helper */
function subjectTypeBadge($type) {
    $map = [
        'core'    => ['bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',   'মূল বিষয়'],
        'religion'=> ['bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400','ধর্ম'],
        'extra'   => ['bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', 'সহশিক্ষা'],
    ];
    $d = isset($map[$type]) ? $map[$type] : ['bg-gray-100 text-gray-600','অন্যান্য'];
    return '<span class="text-[0.65rem] font-bold px-2 py-0.5 rounded-full '.$d[0].'">'.$d[1].'</span>';
}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:250px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1544717305-2782549b5136?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-book-half"></i> পাঠ্যক্রম ২০২৫</div>
      <h1 class="font-bn font-bold text-4xl mb-3">সিলেবাস <em style="font-style:normal;color:#c9a227">২০২৫</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="text-white/70 hover:text-gold">একাডেমিক</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">সিলেবাস</span>
      </nav>
    </div>
  </div>
</header>

<main id="main-content">
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">

    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-journal-bookmark-fill"></i><span></span></div>
      <h2 class="section-title">শ্রেণিভিত্তিক সিলেবাস</h2>
      <p class="text-kma-muted text-sm mt-1">শ্রেণি নির্বাচন করে বিষয়ভিত্তিক অধ্যায় ও পাঠ্যবই দেখুন</p>
    </div>

    <?php if (empty($classes)): ?>
    <p class="text-center text-kma-muted py-16">সিলেবাস তথ্য এখনো যুক্ত করা হয়নি।</p>
    <?php else: ?>

    <!-- Class tabs -->
    <div class="flex flex-wrap gap-2 justify-center mb-8 reveal" role="tablist" aria-label="শ্রেণি নির্বাচন">
      <?php foreach ($classes as $i => $cls): ?>
      <button class="cls-tab px-4 py-2 rounded-full text-sm font-bold border transition-all
                     bg-white dark:bg-gray-800 text-kma-muted border-kma-border dark:border-gray-600
                     hover:border-accent hover:text-accent"
              role="tab"
              aria-selected="<?php echo $i===0 ? 'true' : 'false'; ?>"
              aria-controls="s-<?php echo h($cls['class_key']); ?>"
              data-cls="<?php echo h($cls['class_key']); ?>">
        <?php echo h($cls['class_name']); ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Panels -->
    <?php foreach ($classes as $i => $cls): ?>
    <div class="cls-panel <?php echo $i===0 ? 'show' : ''; ?> reveal"
         id="s-<?php echo h($cls['class_key']); ?>"
         role="tabpanel">

      <!-- Class header -->
      <div class="bg-gradient-to-r from-accent to-[#1a4a2a] rounded-xl p-6 text-white mb-6 shadow-md">
        <div class="flex items-start justify-between flex-wrap gap-4">
          <div>
            <h3 class="font-bold text-xl mb-1"><?php echo h($cls['class_name']); ?></h3>
            <?php if (!empty($cls['description'])): ?>
            <p class="text-white/75 text-sm"><?php echo h($cls['description']); ?></p>
            <?php endif; ?>
          </div>
          <div class="flex gap-6 flex-wrap">
            <?php $subCount = isset($classSubs[$cls['id']]) ? count($classSubs[$cls['id']]) : 0; ?>
            <div class="text-center">
              <div class="font-display text-2xl font-bold text-gold"><?php echo $subCount; ?></div>
              <div class="text-white/60 text-xs">বিষয়</div>
            </div>
            <?php if (!empty($cls['age_range'])): ?>
            <div class="text-center">
              <div class="font-display text-2xl font-bold text-gold"><?php echo h($cls['age_range']); ?></div>
              <div class="text-white/60 text-xs">বয়স</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Subject accordion -->
      <?php if (!empty($classSubs[$cls['id']])): ?>
      <div class="space-y-3" id="acc-<?php echo h($cls['class_key']); ?>">
        <?php foreach ($classSubs[$cls['id']] as $j => $sub):
            $subChapters = isset($chapters[$cls['id']][$sub['subject_id']]) ? $chapters[$cls['id']][$sub['subject_id']] : [];
            $accId = 'ac-'.$cls['class_key'].'-'.$sub['subject_id'];
        ?>
        <div class="bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
          <!-- Accordion header -->
          <button type="button"
                  onclick="toggleAcc('<?php echo h($accId); ?>')"
                  class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-kma-border/30 dark:hover:bg-gray-600 transition-colors"
                  aria-expanded="<?php echo $j===0 ? 'true' : 'false'; ?>"
                  aria-controls="<?php echo h($accId); ?>">
            <?php
            $colorCls = !empty($sub['color_class']) ? $sub['color_class'] : 's-other';
            /* Map subject color class to icon background */
            $iconBgs = ['s-bn'=>'bg-blue-100 text-blue-600','s-en'=>'bg-green-100 text-green-600','s-math'=>'bg-yellow-100 text-yellow-700','s-sci'=>'bg-purple-100 text-purple-600','s-soc'=>'bg-orange-100 text-orange-600','s-rel'=>'bg-red-100 text-red-500','s-ict'=>'bg-cyan-100 text-cyan-600','s-art'=>'bg-pink-100 text-pink-600','s-pe'=>'bg-teal-100 text-teal-600'];
            $iconBg = isset($iconBgs[$colorCls]) ? $iconBgs[$colorCls] : 'bg-gray-100 text-gray-600';
            ?>
            <div class="w-9 h-9 rounded-lg <?php echo h($iconBg); ?> flex items-center justify-center flex-shrink-0">
              <i class="bi bi-journal-text text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <span class="font-bold text-kma-dark dark:text-white text-sm"><?php echo h($sub['name_bn']); ?></span>
              <?php if (!empty($sub['name_en'])): ?>
              <span class="text-kma-muted text-xs ml-2">(<?php echo h($sub['name_en']); ?>)</span>
              <?php endif; ?>
            </div>
            <?php echo subjectTypeBadge($sub['subject_type']); ?>
            <?php if (!empty($subChapters)): ?>
            <span class="text-xs text-kma-muted ml-1"><?php echo count($subChapters); ?> অধ্যায়</span>
            <?php endif; ?>
            <i class="bi bi-chevron-down text-kma-muted transition-transform acc-arrow text-sm ml-1"></i>
          </button>

          <!-- Accordion body -->
          <div id="<?php echo h($accId); ?>"
               class="acc-body <?php echo $j===0 ? 'show' : ''; ?>"
               style="<?php echo $j===0 ? '' : 'display:none'; ?>">
            <div class="px-5 pb-5 pt-1">
              <?php if (!empty($subChapters)): ?>
              <div class="space-y-2">
                <?php foreach ($subChapters as $ch): ?>
                <div class="flex items-start gap-3 py-2 border-b border-kma-border dark:border-gray-600 last:border-0">
                  <div class="w-7 h-7 rounded-full bg-accent text-white text-[0.7rem] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                    <?php echo (int)$ch['chapter_number']; ?>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-kma-dark dark:text-white"><?php echo h($ch['title_bn']); ?></div>
                    <?php if (!empty($ch['topics'])): ?>
                    <div class="text-xs text-kma-muted mt-0.5"><?php echo h($ch['topics']); ?></div>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($ch['is_important'])): ?>
                  <span class="text-[0.65rem] font-bold bg-accent-light text-accent dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-full flex-shrink-0">গুরুত্বপূর্ণ</span>
                  <?php endif; ?>
                  <?php if (!empty($ch['in_exam'])): ?>
                  <span class="text-[0.65rem] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 px-2 py-0.5 rounded-full flex-shrink-0">পরীক্ষায় আসবে</span>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
              <?php else: ?>
              <p class="text-kma-muted text-xs py-3">এই বিষয়ের অধ্যায় তথ্য এখনো যুক্ত করা হয়নি।</p>
              <?php endif; ?>
              <?php if (!empty($sub['teacher_name'])): ?>
              <div class="mt-3 text-xs text-kma-muted"><i class="bi bi-person-fill text-accent mr-1"></i>শিক্ষক: <?php echo h($sub['teacher_name']); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-kma-muted text-sm py-6 text-center">এই শ্রেণির বিষয় তথ্য এখনো যুক্ত করা হয়নি।</p>
      <?php endif; ?>

    </div><!-- /panel -->
    <?php endforeach; ?>

    <div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 flex items-start gap-3 text-sm text-kma-muted mt-8 reveal">
      <i class="bi bi-info-circle-fill text-gold mt-0.5 flex-shrink-0 text-lg"></i>
      <span>সিলেবাস পরিবর্তনের ক্ষেত্রে বিদ্যালয় কর্তৃপক্ষ আলাদা নোটিশ প্রদান করবে।</span>
    </div>

    <?php endif; ?>
  </div>
</section>

<!-- Downloads -->
<?php if (!empty($dlFiles)): ?>
<section class="py-12 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-7 reveal">
      <h2 class="section-title">সিলেবাস ডাউনলোড</h2>
      <p class="text-kma-muted text-sm mt-1">পিডিএফ ফরম্যাটে সম্পূর্ণ সিলেবাস সংরক্ষণ করুন</p>
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
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-clipboard-check"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">পরীক্ষার সময়সূচি দেখুন</h2>
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
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>