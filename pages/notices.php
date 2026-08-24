<?php
/**
 * KMA — pages/notices.php
 * PHP 7.2 compatible
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'নোটিশ বোর্ড | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'সর্বশেষ নোটিশ, পরীক্ষার তারিখ, ছুটি ও ইভেন্ট সংক্রান্ত সকল বিজ্ঞপ্তি।';

$pdo = getDB();

/* Pagination */
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$cat     = isset($_GET['cat']) ? sanitize($_GET['cat']) : 'all';
$perPage = NOTICES_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$allowedCats = ['all', 'exam', 'notice', 'holiday', 'event', 'general'];
if (!in_array($cat, $allowedCats)) { $cat = 'all'; }

/* Count */
if ($cat === 'all') {
    $countStmt = $pdo->query('SELECT COUNT(*) FROM notices WHERE is_active = 1');
} else {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM notices WHERE is_active = 1 AND category = ?');
    $countStmt->execute([$cat]);
}
$total     = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

/* Fetch */
if ($cat === 'all') {
    $stmt = $pdo->prepare(
        'SELECT id, title, category, notice_date, is_pinned
         FROM notices WHERE is_active = 1
         ORDER BY is_pinned DESC, notice_date DESC, id DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$perPage, $offset]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, title, category, notice_date, is_pinned
         FROM notices WHERE is_active = 1 AND category = ?
         ORDER BY is_pinned DESC, notice_date DESC, id DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$cat, $perPage, $offset]);
}
$notices = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Page Hero -->
<header class="page-hero" style="min-height:260px" aria-label="নোটিশ বোর্ড হেডার">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1568667256549-094345857637?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-bell-fill"></i> সর্বশেষ বিজ্ঞপ্তি</div>
      <h1 class="font-bn font-bold text-4xl mb-3">নোটিশ <em style="font-style:normal;color:#c9a227">বোর্ড</em></h1>
      <nav class="flex items-center gap-2 flex-wrap text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors">
          <i class="bi bi-house-fill"></i> হোম
        </a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">নোটিশ বোর্ড</span>
      </nav>
    </div>
  </div>
</header>

<main id="main-content" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-5xl mx-auto px-4">

    <!-- Category filter tabs -->
    <div class="flex flex-wrap gap-2 mb-8 justify-center reveal">
      <?php
      $cats = [
        'all'     => 'সব নোটিশ',
        'exam'    => 'পরীক্ষা',
        'notice'  => 'বিজ্ঞপ্তি',
        'holiday' => 'ছুটি',
        'event'   => 'ইভেন্ট',
        'general' => 'সাধারণ',
      ];
      foreach ($cats as $key => $label):
        $active = ($cat === $key);
        $url    = BASE_URL . '/pages/notices.php?cat=' . urlencode($key);
      ?>
      <a href="<?php echo h($url); ?>"
         class="px-4 py-2 rounded-full text-sm font-semibold border transition-colors
                <?php echo $active
                  ? 'bg-accent border-accent text-white'
                  : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent dark:text-gray-300'; ?>">
        <?php echo h($label); ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Notice List -->
    <?php if (empty($notices)): ?>
    <div class="text-center py-16 text-kma-muted">
      <i class="bi bi-bell-slash text-5xl mb-3 block text-kma-border"></i>
      <p>এই বিভাগে কোনো নোটিশ নেই।</p>
    </div>
    <?php else: ?>
    <div class="space-y-3 mb-10">
      <?php foreach ($notices as $i => $n):
        $dt  = new DateTime($n['notice_date']);
        $day = $dt->format('d');
        $mon = $dt->format('M');
        $yr  = $dt->format('Y');
      ?>
      <div class="notice-item rounded-xl bg-kma-bg dark:bg-gray-700 shadow-sm hover:shadow-md
                  transition-all reveal reveal-d<?php echo min($i+1,5); ?>"
           data-notice-id="<?php echo (int)$n['id']; ?>"
           role="button" tabindex="0"
           aria-label="<?php echo h($n['title']); ?>">
        <!-- Date badge -->
        <div class="notice-date flex-shrink-0">
          <span class="day"><?php echo h($day); ?></span>
          <?php echo h($mon); ?><br><?php echo h($yr); ?>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="notice-tag <?php echo noticeCategoryClass($n['category']); ?>">
              <?php echo noticeCategoryLabel($n['category']); ?>
            </span>
            <?php if ($n['is_pinned']): ?>
            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold">
              <i class="bi bi-pin-fill"></i> পিন করা
            </span>
            <?php endif; ?>
          </div>
          <p class="text-sm font-semibold text-kma-dark dark:text-gray-100 leading-snug mb-1">
            <?php echo h($n['title']); ?>
          </p>
          <span class="text-xs text-kma-muted">বিস্তারিত জানতে ক্লিক করুন →</span>
        </div>
        <div class="flex-shrink-0 hidden sm:block">
          <i class="bi bi-chevron-right text-kma-border text-lg"></i>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex justify-center gap-2 flex-wrap" aria-label="পেজিনেশন">
      <?php for ($p = 1; $p <= $totalPages; $p++):
        $url = BASE_URL . '/pages/notices.php?cat=' . urlencode($cat) . '&page=' . $p;
      ?>
      <a href="<?php echo h($url); ?>"
         class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors
                <?php echo ($p === $page)
                  ? 'bg-accent text-white'
                  : 'bg-kma-bg dark:bg-gray-700 text-kma-muted hover:bg-accent hover:text-white'; ?>">
        <?php echo $p; ?>
      </a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

  </div>
</main>

<!-- Notice Modal (shared) -->
<div id="noticeModalBackdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-notice-title">
  <div class="modal-box">
    <div class="bg-accent px-7 py-5 flex items-center justify-between">
      <h3 id="modal-notice-title" class="text-white font-bold text-lg leading-snug pr-4"></h3>
      <button id="noticeModalClose" aria-label="বন্ধ করুন"
              class="text-white/80 hover:text-white w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors flex-shrink-0">
        <i class="bi bi-x-lg text-lg"></i>
      </button>
    </div>
    <div class="px-7 py-4 bg-kma-bg dark:bg-gray-700 border-b border-kma-border flex items-center gap-3 flex-wrap">
      <span id="modal-notice-cat" class="notice-tag tag-general"></span>
      <span class="text-xs text-kma-muted flex items-center gap-1">
        <i class="bi bi-calendar3"></i>
        <span id="modal-notice-date"></span>
      </span>
    </div>
    <div class="px-7 py-6">
      <div id="modal-notice-content" class="text-kma-muted text-sm leading-relaxed"></div>
      <div id="modal-notice-file-row" class="mt-5 pt-4 border-t border-kma-border" style="display:none">
        <a id="modal-notice-file" href="#" download
           class="inline-flex items-center gap-2 bg-accent text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gold hover:text-kma-dark transition-colors">
          <i class="bi bi-download"></i> সংযুক্ত ফাইল ডাউনলোড করুন
        </a>
      </div>
    </div>
    <div class="px-7 py-4 border-t border-kma-border flex justify-end">
      <button onclick="document.getElementById('noticeModalBackdrop').classList.remove('open');document.body.style.overflow=''"
              class="bg-kma-dark text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-accent transition-colors">
        <i class="bi bi-x-circle me-1"></i> বন্ধ করুন
      </button>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>