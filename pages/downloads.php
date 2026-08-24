<?php
/**
 * KMA — pages/downloads.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'ডাউনলোড | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'ক্লাস রুটিন, সিলেবাস, পরীক্ষার সময়সূচি ও ছুটির তালিকা ডাউনলোড করুন।';

$pdo  = getDB();
$site = getSiteSettings();

/* Active category filter */
$cat = isset($_GET['cat']) ? sanitize($_GET['cat']) : 'all';
$allowed = ['all','routine','syllabus','exam_schedule','holiday','other'];
if (!in_array($cat, $allowed)) { $cat = 'all'; }

/* Fetch downloads */
if ($cat === 'all') {
    $stmt = $pdo->prepare('SELECT * FROM downloads WHERE is_active=1 ORDER BY category, sort_order, created_at DESC');
    $stmt->execute();
} else {
    $stmt = $pdo->prepare('SELECT * FROM downloads WHERE is_active=1 AND category=? ORDER BY sort_order, created_at DESC');
    $stmt->execute([$cat]);
}
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Group by category */
$grouped = [];
foreach ($downloads as $d) {
    $grouped[$d['category']][] = $d;
}

/* Category meta */
$catMeta = [
    'routine'       => ['label'=>'ক্লাস রুটিন',        'icon'=>'bi-calendar-week-fill',    'color'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
    'syllabus'      => ['label'=>'সিলেবাস',             'icon'=>'bi-journal-bookmark-fill', 'color'=>'bg-gold/15 text-yellow-700 dark:text-yellow-400'],
    'exam_schedule' => ['label'=>'পরীক্ষার সময়সূচি',  'icon'=>'bi-clipboard-check-fill',  'color'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'],
    'holiday'       => ['label'=>'ছুটির তালিকা',        'icon'=>'bi-calendar3',             'color'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'],
    'other'         => ['label'=>'অন্যান্য',            'icon'=>'bi-file-earmark-fill',     'color'=>'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
];

/* Count per category for filter buttons */
$counts = [];
foreach ($downloads as $d) {
    $counts[$d['category']] = isset($counts[$d['category']]) ? $counts[$d['category']] + 1 : 1;
}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:260px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1544717305-2782549b5136?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-download"></i> ডাউনলোড সেন্টার</div>
      <h1 class="font-bn font-bold text-4xl mb-3">
        <em style="font-style:normal;color:#c9a227">ডাউনলোড</em> করুন
      </h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">ডাউনলোড</span>
      </nav>
      <div class="flex flex-wrap gap-3 mt-5">
        <div class="ph-badge"><i class="bi bi-file-earmark-pdf"></i> পিডিএফ ফরম্যাট</div>
        <div class="ph-badge"><i class="bi bi-cloud-download"></i> বিনামূল্যে</div>
        <div class="ph-badge"><i class="bi bi-shield-check"></i> অফিসিয়াল ডকুমেন্ট</div>
      </div>
    </div>
  </div>
</header>

<main id="main-content">
<section class="py-14 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">

    <!-- Category filter -->
    <div class="flex flex-wrap gap-2 justify-center mb-10 reveal">
      <a href="<?php echo BASE_URL; ?>/pages/downloads.php"
         class="px-4 py-2 rounded-full text-sm font-bold border transition-all
                <?php echo $cat==='all' ? 'bg-accent text-white border-accent shadow-md' : 'bg-white dark:bg-gray-800 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        সব ফাইল
        <span class="ml-1 text-xs opacity-70">(<?php echo count($downloads); ?>)</span>
      </a>
      <?php foreach ($catMeta as $ck => $cm): ?>
      <?php $cnt = isset($counts[$ck]) ? $counts[$ck] : 0; if ($cnt === 0) continue; ?>
      <a href="<?php echo BASE_URL; ?>/pages/downloads.php?cat=<?php echo h($ck); ?>"
         class="px-4 py-2 rounded-full text-sm font-bold border transition-all
                <?php echo $cat===$ck ? 'bg-accent text-white border-accent shadow-md' : 'bg-white dark:bg-gray-800 text-kma-muted border-kma-border dark:border-gray-600 hover:border-accent hover:text-accent'; ?>">
        <i class="bi <?php echo h($cm['icon']); ?> mr-1"></i>
        <?php echo h($cm['label']); ?>
        <span class="ml-1 text-xs opacity-70">(<?php echo $cnt; ?>)</span>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($downloads)): ?>
    <!-- Empty state -->
    <div class="text-center py-20 reveal">
      <div class="w-20 h-20 bg-kma-border/50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-5">
        <i class="bi bi-file-earmark-x text-kma-muted text-4xl"></i>
      </div>
      <h3 class="font-bold text-kma-dark dark:text-white text-lg mb-2">কোনো ফাইল পাওয়া যায়নি</h3>
      <p class="text-kma-muted text-sm">এই বিভাগে এখনো কোনো ডকুমেন্ট যুক্ত করা হয়নি।</p>
      <?php if ($cat !== 'all'): ?>
      <a href="<?php echo BASE_URL; ?>/pages/downloads.php" class="mt-4 inline-block text-accent text-sm font-semibold hover:underline">সব ফাইল দেখুন →</a>
      <?php endif; ?>
    </div>

    <?php else: ?>

    <?php if ($cat === 'all'): ?>
    <!-- Grouped view -->
    <?php foreach ($catMeta as $ck => $cm): ?>
    <?php if (!isset($grouped[$ck])) continue; ?>
    <div class="mb-12 reveal">
      <!-- Category heading -->
      <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl <?php echo h($cm['color']); ?> flex items-center justify-center flex-shrink-0">
          <i class="bi <?php echo h($cm['icon']); ?>"></i>
        </div>
        <div>
          <h2 class="font-bold text-kma-dark dark:text-white text-lg leading-tight"><?php echo h($cm['label']); ?></h2>
          <p class="text-kma-muted text-xs"><?php echo count($grouped[$ck]); ?>টি ফাইল</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/downloads.php?cat=<?php echo h($ck); ?>"
           class="ml-auto text-xs text-accent font-semibold hover:underline flex-shrink-0">
          সব দেখুন <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <!-- File grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($grouped[$ck] as $dl): ?>
        <?php include __DIR__ . '/../includes/download_card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <!-- Filtered flat grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 reveal">
      <?php foreach ($downloads as $dl): ?>
      <?php include __DIR__ . '/../includes/download_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>

  </div>
</section>
</main>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-mortarboard-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">আজই ভর্তির আবেদন করুন</h2>
    <p class="text-white/80 mb-6 text-sm">২০২৫–২৬ শিক্ষাবর্ষে সীমিত আসনে ভর্তি চলছে।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-gold"><i class="bi bi-pencil-square"></i> ভর্তির আবেদন করুন</a>
      <a href="<?php echo BASE_URL; ?>/pages/contact.php"   class="btn-outline"><i class="bi bi-telephone"></i> যোগাযোগ করুন</a>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>