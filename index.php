<?php
/**
 * KMA – Homepage  |  PHP 7.2 compatible
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/app.php';

$pageTitle     = 'KMA | খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি';
$pageDesc      = 'মানসম্পন্ন প্রাথমিক শিক্ষার আলোকিত প্রতিষ্ঠান — ভর্তি, নোটিশ ও একাডেমিক তথ্য।';
$canonicalPath = '/';

/* ── DB queries ── */
$notices = [];
try {
    $stmt = getDB()->prepare(
        'SELECT * FROM notices WHERE is_active = 1
         ORDER BY is_pinned DESC, notice_date DESC LIMIT 6'
    );
    $stmt->execute();
    $notices = $stmt->fetchAll();
} catch (Exception $e) {}

$gallery = [];
try {
    $stmt = getDB()->prepare(
        'SELECT id, title, image_path, category
         FROM gallery
         WHERE is_active = 1
         ORDER BY sort_order ASC, id DESC
         LIMIT 5'
    );
    $stmt->execute();
    $gallery = $stmt->fetchAll();
} catch (Exception $e) {}

/* ── Build a lightweight JSON payload for the Notice modal.
     Notices are already fetched above via SELECT *, so we simply
     reshape the same rows for client-side use — no extra query,
     no extra AJAX round-trip, instant modal open on click. ── */
$noticesJson = [];
foreach ($notices as $n) {
    $attFile = isset($n['attachment']) ? trim((string) $n['attachment']) : '';
    $attData = null;
    if ($attFile !== '') {
        $attBase = defined('UPLOAD_NOTICE_URL') ? UPLOAD_NOTICE_URL : (BASE_URL . '/uploads/notices/');
        $ext     = strtolower(pathinfo($attFile, PATHINFO_EXTENSION));
        $type    = 'file';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $type = 'image';
        } elseif ($ext === 'pdf') {
            $type = 'pdf';
        }
        $attData = [
            'url'  => $attBase . $attFile,
            'name' => $attFile,
            'ext'  => strtoupper($ext),
            'type' => $type,
        ];
    }

    $rawContent = '';
    if (isset($n['content']) && $n['content'] !== '') {
        $rawContent = $n['content'];
    } elseif (isset($n['description']) && $n['description'] !== '') {
        $rawContent = $n['description'];
    } elseif (isset($n['title'])) {
        $rawContent = $n['title'];
    }

    $noticesJson[(int) $n['id']] = [
        'title'   => h($n['title']),
        'date'    => h(date('d F, Y', strtotime($n['notice_date']))),
        'tagCls'  => noticeCategoryClass($n['category']),
        'tagLbl'  => h(noticeCategoryLabel($n['category'])),
        'content' => nl2br(h($rawContent)),
        'att'     => $attData,
    ];
}

/* ── Stats (with DB fallback) ── */
$stats = ['students' => '৮০+', 'teachers' => '৩+', 'pass_rate' => '৯৮%', 'years' => '১+'];
try {
    $n = getDB()->query('SELECT COUNT(*) FROM admissions WHERE status = "approved"')->fetchColumn();
    if ($n > 0) { $stats['students'] = $n . '+'; }
} catch (Exception $e) {}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════════
     HERO CAROUSEL
     Bootstrap 5 JS drives carousel-fade crossfade.
     No Bootstrap CSS — Tailwind handles all styling.
     Custom JS adds: progress bar, pill dots,
     Ken Burns reset, pause-on-hover, keyboard nav.
══════════════════════════════════════════════════ -->
<section id="hero" class="relative overflow-hidden" aria-label="হিরো ব্যানার">

  <div id="heroCarousel" class="relative">

    <!-- Slides wrapper — only active slide is visible -->
    <div class="relative" id="heroInner">

      <!-- SLIDE 1 -->
      <div class="hero-slide carousel-item active" data-index="0">
        <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600&q=80');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-stripe"></div>
        <div class="hero-emblem">
          <img src="https://placehold.co/220x220/2e6b3e/ffffff?text=KMA&font=open-sans" alt="একাডেমি প্রতীক" />
        </div>
        <div class="relative z-[4] max-w-7xl mx-auto px-4 w-full">
          <div class="hero-content max-w-xl">
            <div class="hero-badge"><span class="hero-dot-pulse"></span>স্বাগতম</div>
            <h1>জ্ঞান আলোয় আলোকিত<br><em class="not-italic text-gold">আগামীর পথ</em></h1>
            <p class="hero-desc">মানসম্পন্ন শিক্ষা, নৈতিক মূল্যবোধ এবং সৃজনশীল বিকাশের মাধ্যমে শিশুদের উজ্জ্বল ভবিষ্যৎ গড়ে তুলতে আমরা প্রতিশ্রুতিবদ্ধ।</p>
            <div class="hero-btns">
              <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-hero-primary">
                <i class="bi bi-pencil-square"></i> ভর্তি হোন
              </a>
              <a href="<?php echo BASE_URL; ?>/pages/about.php" class="btn-hero-outline">
                <i class="bi bi-arrow-right-circle"></i> আরও জানুন
              </a>
            </div>
          </div>
        </div>
      </div><!-- /slide 1 -->

      <!-- SLIDE 2 -->
      <div class="hero-slide carousel-item" data-index="1">
        <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-stripe"></div>
        <div class="hero-emblem">
          <img src="https://placehold.co/220x220/c9a227/272727?text=KMA&font=open-sans" alt="একাডেমি প্রতীক" />
        </div>
        <div class="relative z-[4] max-w-7xl mx-auto px-4 w-full">
          <div class="hero-content max-w-xl">
            <div class="hero-badge"><span class="hero-dot-pulse"></span>একাডেমিক উৎকর্ষ</div>
            <h1>দক্ষ শিক্ষকমণ্ডলী,<br><em class="not-italic text-gold">উজ্জ্বল শিক্ষার্থী</em></h1>
            <p class="hero-desc">অভিজ্ঞ ও প্রশিক্ষিত শিক্ষকদের নিবিড় তত্ত্বাবধানে প্রতিটি শিশু পাচ্ছে সর্বোত্তম শিক্ষার সুযোগ।</p>
            <div class="hero-btns">
              <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="btn-hero-primary">
                <i class="bi bi-book-half"></i> একাডেমিক
              </a>
              <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-hero-outline">
                <i class="bi bi-telephone"></i> যোগাযোগ করুন
              </a>
            </div>
          </div>
        </div>
      </div><!-- /slide 2 -->

      <!-- SLIDE 3 -->
      <div class="hero-slide carousel-item" data-index="2">
        <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1544717305-2782549b5136?w=1600&q=80');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-stripe"></div>
        <div class="hero-emblem">
          <img src="https://placehold.co/220x220/1a4a2a/f6f6e9?text=KMA&font=open-sans" alt="একাডেমি প্রতীক" />
        </div>
        <div class="relative z-[4] max-w-7xl mx-auto px-4 w-full">
          <div class="hero-content max-w-xl">
            <div class="hero-badge"><span class="hero-dot-pulse"></span>সহশিক্ষা কার্যক্রম</div>
            <h1>পড়ালেখার পাশাপাশি<br><em class="not-italic text-gold">সৃজনশীল বিকাশ</em></h1>
            <p class="hero-desc">খেলাধুলা, সাংস্কৃতিক কার্যক্রম এবং সহপাঠ্যক্রমিক কার্যকলাপের মাধ্যমে শিশুর সামগ্রিক বিকাশ নিশ্চিত করি আমরা।</p>
            <div class="hero-btns">
              <a href="<?php echo BASE_URL; ?>/pages/about.php" class="btn-hero-primary">
                <i class="bi bi-people-fill"></i> আমাদের সম্পর্কে
              </a>
              <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-hero-outline">
                <i class="bi bi-file-earmark-text"></i> ভর্তি তথ্য
              </a>
            </div>
          </div>
        </div>
      </div><!-- /slide 3 -->

    </div><!-- /heroInner -->

    <!-- Arrow controls -->
    <button class="hero-ctrl hero-ctrl-prev" id="heroPrev" aria-label="পূর্ববর্তী স্লাইড">
      <i class="bi bi-chevron-left"></i>
    </button>
    <button class="hero-ctrl hero-ctrl-next" id="heroNext" aria-label="পরবর্তী স্লাইড">
      <i class="bi bi-chevron-right"></i>
    </button>

    <!-- Dot indicators -->
    <div class="hero-indicators" role="tablist" aria-label="স্লাইড নেভিগেশন">
      <button class="hero-ind active" data-slide="0" aria-label="স্লাইড ১" role="tab"></button>
      <button class="hero-ind"        data-slide="1" aria-label="স্লাইড ২" role="tab"></button>
      <button class="hero-ind"        data-slide="2" aria-label="স্লাইড ৩" role="tab"></button>
    </div>

    <!-- Progress bar -->
    <div class="hero-progress"><div id="heroProgressFill" class="hero-progress-fill"></div></div>

    <!-- Scroll-down cue -->
    <div class="hero-scroll-cue" id="heroScrollCue" title="নিচে স্ক্রোল করুন">
      <span>স্ক্রোল</span>
      <div class="scroll-line"></div>
    </div>

  </div><!-- /heroCarousel -->
</section>


<!-- ── STATS BAR ──
     background is hardcoded to #1e2a24 so it never
     turns white in dark mode or any CSS-variable state -->
<div id="statsBar" style="background:#1e2a24;" class="py-5">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
      <div>
        <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo h($stats['years']); ?></div>
        <div class="text-xs mt-1" style="color:rgba(255,255,255,.75);">বছরের অভিজ্ঞতা</div>
      </div>
      <div>
        <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo h($stats['students']); ?></div>
        <div class="text-xs mt-1" style="color:rgba(255,255,255,.75);">বর্তমান শিক্ষার্থী</div>
      </div>
      <div>
        <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo h($stats['teachers']); ?></div>
        <div class="text-xs mt-1" style="color:rgba(255,255,255,.75);">অভিজ্ঞ শিক্ষক</div>
      </div>
      <div>
        <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo h($stats['pass_rate']); ?></div>
        <div class="text-xs mt-1" style="color:rgba(255,255,255,.75);">পাশের হার</div>
      </div>
    </div>
  </div>
</div>


<!-- ── MESSAGES ── -->
<section class="bg-white dark:bg-gray-800 py-20" aria-label="বাণী">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-12 reveal">
      <div class="ornament"><span></span><i class="bi bi-star-fill"></i><span></span></div>
      <h2 class="section-title">গুরুত্বপূর্ণ বাণী</h2>
      <p class="section-subtitle">প্রতিষ্ঠাতা ও প্রধান শিক্ষকের অনুপ্রেরণামূলক কথা</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">

      <!-- Founder -->
      <div class="reveal delay-1">
        <div class="message-card border-l-[5px] border-accent">
          <div class="flex justify-center mb-5">
            <img src="https://placehold.co/130x130/2e6b3e/ffffff?text=Owner&font=open-sans"
                 alt="প্রতিষ্ঠাতা" class="w-32 h-32 rounded-full object-cover border-4 border-accent shadow-md" />
          </div>
          <div class="text-4xl text-gold leading-none mb-2">"</div>
          <p class="text-kma-muted dark:text-gray-300 text-sm leading-7 text-justify">
            আমাদের স্বপ্ন ছিল একটি এমন প্রতিষ্ঠান গড়ে তোলার, যেখানে প্রতিটি শিশু কেবল বই-এর পাঠ নয়, জীবনের পাঠও নেবে। খলিলুল্লাহ মেমোরিয়াল একাডেমি আজ সেই স্বপ্নের বাস্তব রূপ। আমাদের শিক্ষার্থীরা যেন দেশপ্রেমিক, নৈতিক এবং দক্ষ মানুষ হয়ে ওঠে — এটাই আমার প্রধান প্রার্থনা।
          </p>
          <div class="mt-4">
            <div class="font-bold text-kma-dark dark:text-white text-center">এডভোকেট মো. হারুনউর রশিদ হারুন, <br> আব্দুর রহমান রকি</div>
            <div class="text-accent text-sm font-semibold text-center mt-1">
              <i class="bi bi-award me-1"></i>প্রতিষ্ঠাতা ও পরিচালক
            </div>
          </div>
        </div>
      </div>

      <!-- Headmaster -->
      <div class="reveal delay-2">
        <div class="message-card border-l-[5px] border-gold">
          <div class="flex justify-center mb-5">
            <img src="https://placehold.co/130x130/c9a227/ffffff?text=HM&font=open-sans"
                 alt="প্রধান শিক্ষক" class="w-32 h-32 rounded-full object-cover border-4 border-gold shadow-md" />
          </div>
          <div class="text-4xl text-accent leading-none mb-2">"</div>
          <p class="text-kma-muted dark:text-gray-300 text-sm leading-7 text-justify">
            শিক্ষা শুধু পরীক্ষায় ভালো ফলাফল অর্জনের হাতিয়ার নয় — শিক্ষা হলো মানুষ হওয়ার পথ। এই প্রতিষ্ঠানে আমরা প্রতিটি শিশুর মেধা, মনন ও মানবিকতার বিকাশে অক্লান্ত পরিশ্রম করে যাচ্ছি। অভিভাবকদের আস্থা ও শিক্ষার্থীদের অদম্য মনোবল আমাদের সবচেয়ে বড় অনুপ্রেরণা।
          </p>
          <div class="mt-4">
            <div class="font-bold text-kma-dark dark:text-white text-center">মো. মহিন উদ্দিন</div>
            <div class="text-accent text-sm font-semibold text-center mt-1">
              <i class="bi bi-person-badge me-1"></i>প্রধান শিক্ষক
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ── ABOUT & FEATURES ── -->
<section class="bg-kma-bg dark:bg-gray-900 py-20" aria-label="বিদ্যালয় পরিচিতি">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">

      <!-- Image -->
      <div class="reveal relative">
        <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=700&q=75"
             alt="বিদ্যালয় ভবন"
             class="w-full rounded-xl shadow-lg aspect-[4/3] object-cover" />
        <div class="absolute -bottom-5 -right-5 bg-accent text-white rounded-xl px-6 py-4 shadow text-center">
          <div class="font-display text-3xl font-bold text-gold leading-none">১+</div>
          <div class="text-xs mt-1">বছরের বিশ্বস্ততা</div>
        </div>
      </div>

      <!-- Text -->
      <div class="reveal delay-1">
        <div class="ornament justify-start mb-3"><span></span><i class="bi bi-building"></i><span></span></div>
        <h2 class="section-title">বিদ্যালয় পরিচিতি</h2>
        <p class="section-subtitle">আমাদের সংক্ষিপ্ত ইতিহাস</p>
        <p class="text-kma-muted dark:text-gray-300 text-sm leading-7 mb-4 text-justify">
          খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি ২৯-অক্টোবর ২০২৫ সালে স্থাপন করার জন্য পারিবারিক ভাবে সিদ্ধান্ত নেওয়া হয় এবং ০৩-নভেম্বর ২০২৫ সালে প্রস্তাব টি সামাজিক পর্যায় উপস্থাপন করা হয়। রবিবার ০৯-নভেম্বর ২০২৫ সালে সমাজের সচেতন নাগরিক বৃন্দ ও এলাকার সকল শ্রেণীর মানুষের উপস্থিতিতে একটি গুরুত্বপূর্ণ ও চূড়ান্ত সিদ্ধান্ত গ্রহণের জন্য সভা অনুষ্ঠিত হয়।
        </p>
        <p class="text-kma-muted dark:text-gray-300 text-sm leading-7 mb-6 text-justify">
          এই বিদ্যালয়ে সরকারি পাঠ্যক্রম অনুসরণ করে আধুনিক শিক্ষার পাশাপাশি ধর্মীয় ও নৈতিক শিক্ষার উপর গুরুত্ব দেওয়া হয়। সুসজ্জিত শ্রেণিকক্ষ, অভিজ্ঞ শিক্ষকমণ্ডলী এবং বন্ধুত্বপূর্ণ পরিবেশ এই বিদ্যালয়কে করে তুলেছে অনন্য।
        </p>
        <a href="<?php echo BASE_URL; ?>/pages/about.php" class="btn-hero-primary inline-flex rounded-lg">
          <i class="bi bi-arrow-right"></i> বিস্তারিত জানুন
        </a>
      </div>

    </div>

    <!-- Features -->
    <div class="reveal mb-8 text-center">
      <h2 class="section-title">আমাদের বৈশিষ্ট্য</h2>
      <p class="section-subtitle">কেন আমরা আলাদা?</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <?php
      $features = [
        ['bi-book-half',         'মানসম্পন্ন পাঠ্যক্রম', 'মডার্ণ শিক্ষাক্রম অনুযায়ী আধুনিক পাঠদান।'],
        ['bi-people-fill',       'দক্ষ শিক্ষকমণ্ডলী',  'প্রশিক্ষিত ও অভিজ্ঞ শিক্ষক-শিক্ষিকাগণ।'],
        ['bi-shield-check',      'নিরাপদ পরিবেশ',      'শিশুদের জন্য সম্পূর্ণ নিরাপদ পরিবেশ।'],
        ['bi-trophy-fill',       'পুরস্কার ও স্বীকৃতি', 'অঞ্চলীয় পুরস্কারে ভূষিত প্রতিষ্ঠান।'],
        ['bi-heart-fill',        'নৈতিক শিক্ষা',       'ধর্মীয় ও মানবিক মূল্যবোধ চর্চার সুযোগ।'],
        ['bi-music-note-beamed', 'সহশিক্ষা কার্যক্রম', 'সাংস্কৃতিক, ক্রীড়া ও সৃজনশীল কার্যক্রম।'],
      ];
      foreach ($features as $i => $f): ?>
      <div class="reveal delay-<?php echo ($i % 4) + 1; ?>">
        <div class="feature-card">
          <div class="w-14 h-14 rounded-full bg-accent/10 text-accent text-2xl flex items-center justify-center mx-auto mb-3">
            <i class="bi <?php echo h($f[0]); ?>"></i>
          </div>
          <h5 class="font-bold text-sm text-kma-dark dark:text-white mb-1"><?php echo h($f[1]); ?></h5>
          <p class="text-xs text-kma-muted dark:text-gray-400"><?php echo h($f[2]); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ── NOTICE BOARD + GALLERY ── -->
<section class="bg-white dark:bg-gray-800 py-20" aria-label="নোটিশ ও গ্যালারি">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid lg:grid-cols-5 gap-10">

      <!-- Notice Board (5/12 width) -->
      <div class="lg:col-span-2 reveal">
        <div class="ornament justify-start mb-3"><span></span><i class="bi bi-bell-fill"></i><span></span></div>
        <h2 class="section-title">নোটিশ বোর্ড</h2>
        <p class="section-subtitle mb-4">সর্বশেষ বিজ্ঞপ্তি ও নোটিশ</p>
        <div class="rounded-xl overflow-hidden shadow bg-kma-bg dark:bg-gray-900">
          <div class="bg-accent text-white px-5 py-3 flex items-center gap-2 font-bold text-sm">
            <span class="w-2 h-2 rounded-full bg-gold animate-pulse"></span>
            সর্বশেষ নোটিশ
          </div>
          <div class="overflow-y-auto" style="max-height:360px;">
            <?php if (!empty($notices)): ?>
              <?php foreach ($notices as $n): $hasAtt = !empty($n['attachment']); ?>
              <div class="notice-item flex gap-3 items-start px-4 py-3 border-b border-kma-border dark:border-gray-700 hover:bg-accent/5 active:bg-accent/10 cursor-pointer transition-colors"
                   role="button" tabindex="0"
                   data-id="<?php echo (int) $n['id']; ?>"
                   aria-haspopup="dialog"
                   aria-label="<?php echo h($n['title']); ?> — বিস্তারিত দেখুন">
                <div class="min-w-[50px] bg-accent text-white rounded-lg text-center py-1.5 text-xs font-semibold leading-tight">
                  <span class="block text-lg font-bold leading-none"><?php echo h(date('d', strtotime($n['notice_date']))); ?></span>
                  <?php echo h(date('M Y', strtotime($n['notice_date']))); ?>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="notice-tag <?php echo noticeCategoryClass($n['category']); ?>">
                      <?php echo noticeCategoryLabel($n['category']); ?>
                    </span>
                    <?php if ($hasAtt): ?>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-accent bg-accent/10 px-1.5 py-0.5 rounded">
                      <i class="bi bi-paperclip"></i> সংযুক্তি
                    </span>
                    <?php endif; ?>
                  </div>
                  <p class="text-sm font-medium text-kma-dark dark:text-white mt-1 leading-snug"><?php echo h($n['title']); ?></p>
                  <span class="text-xs text-accent font-semibold inline-flex items-center gap-1">বিস্তারিত জানতে ক্লিক করুন <i class="bi bi-arrow-right"></i></span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="px-5 py-6 text-sm text-kma-muted dark:text-gray-400 text-center">
                <i class="bi bi-inbox text-2xl block mb-2"></i>
                কোনো নোটিশ নেই।
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="mt-3 text-right">
          <a href="<?php echo BASE_URL; ?>/pages/notices.php"
             class="text-accent font-semibold text-sm hover:text-gold transition-colors">
            সব নোটিশ দেখুন <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Gallery (7/12 width) -->
      <div class="lg:col-span-3 reveal delay-2">
        <div class="ornament justify-start mb-3"><span></span><i class="bi bi-images"></i><span></span></div>
        <h2 class="section-title">ফটো গ্যালারি</h2>
        <p class="section-subtitle mb-4">বিদ্যালয়ের কিছু স্মরণীয় মুহূর্ত</p>
        <?php if (empty($gallery)): ?>
        <!-- Empty state — DB তে কোনো ছবি নেই -->
        <div class="flex flex-col items-center justify-center py-14 text-center text-kma-muted dark:text-gray-400 rounded-xl border border-dashed border-kma-border dark:border-gray-700">
          <i class="bi bi-images text-4xl mb-3 opacity-30"></i>
          <p class="text-sm font-medium">এখনো কোনো ছবি আপলোড করা হয়নি।</p>
          <p class="text-xs mt-1 opacity-70">নতুন ছবি শীঘ্রই আপলোড করা হবে।</p>
        </div>
        <?php else: ?>
        <div class="gallery-grid">
          <?php foreach ($gallery as $gi => $img):
            $isLarge  = ($gi === 0);
            $imgSrc   = h(BASE_URL . '/' . ltrim($img['image_path'], '/'));
            $imgTitle = !empty($img['title']) ? h($img['title']) : 'গ্যালারি ছবি';
          ?>
          <div class="gallery-item<?php echo $isLarge ? ' large' : ''; ?>"
               role="button" tabindex="0"
               data-img="<?php echo $imgSrc; ?>"
               data-caption="<?php echo $imgTitle; ?>"
               aria-haspopup="dialog"
               aria-label="ছবি বড় করে দেখুন — <?php echo $imgTitle; ?>">
            <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgTitle; ?>" loading="lazy" />
            <div class="gallery-overlay"><i class="bi bi-zoom-in text-white text-2xl"></i></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="mt-3 text-right">
          <a href="<?php echo BASE_URL; ?>/pages/gallery.php"
             class="text-accent font-semibold text-sm hover:text-gold transition-colors">
            সব ছবি দেখুন <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

    </div>
  </div>

  <!-- Notice data for the modal — already fetched above, reshaped for JS -->
  <script type="application/json" id="noticesData"><?php echo json_encode($noticesJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
</section>


<!-- ══════════════════════════════════════════════════
     NOTICE DETAIL MODAL
     Mobile-first bottom-sheet; centered dialog on sm+.
     Content is already in-page (noticesData JSON above),
     so opening is instant — no network round-trip.
══════════════════════════════════════════════════ -->
<div id="noticeModal" class="kma-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="noticeModalTitle">
  <div class="kma-modal-dialog" data-close="notice">
    <div class="kma-modal-panel" id="noticeModalPanel">
      <div class="kma-modal-handle" aria-hidden="true"></div>
      <div class="kma-modal-header">
        <div class="min-w-0 pr-3">
          <span id="noticeModalTag" class="notice-tag mb-2 inline-block"></span>
          <h3 id="noticeModalTitle" class="text-base sm:text-lg font-bold text-kma-dark dark:text-white leading-snug"></h3>
          <div class="text-xs text-kma-muted dark:text-gray-400 mt-1.5 inline-flex items-center gap-1.5">
            <i class="bi bi-calendar3"></i><span id="noticeModalDate"></span>
          </div>
        </div>
        <button type="button" class="kma-modal-close" data-close="notice" aria-label="বন্ধ করুন">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="kma-modal-body">
        <div id="noticeModalText" class="text-sm leading-7 text-kma-dark dark:text-gray-200"></div>
        <div id="noticeModalAttWrap" class="mt-5 hidden"></div>
      </div>
      <div class="kma-modal-footer">
        <a href="<?php echo BASE_URL; ?>/pages/notices.php"
           class="text-accent text-sm font-semibold hover:text-gold inline-flex items-center gap-1">
          সব নোটিশ দেখুন <i class="bi bi-arrow-right"></i>
        </a>
        <button type="button" class="kma-modal-btn-close" data-close="notice">বন্ধ করুন</button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════
     GALLERY IMAGE MODAL
     Shows the clicked image + a short description
     underneath, next/prev navigation, and a link to
     the full gallery page. Close button included.
══════════════════════════════════════════════════ -->
<div id="galleryModal" class="kma-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="galleryModalCaption">
  <div class="kma-modal-dialog kma-modal-dialog-lg" data-close="gallery">
    <div class="kma-modal-panel kma-modal-panel-media" id="galleryModalPanel">
      <button type="button" class="kma-modal-close kma-modal-close-media" data-close="gallery" aria-label="বন্ধ করুন">
        <i class="bi bi-x-lg"></i>
      </button>
      <button type="button" class="kma-modal-nav kma-modal-nav-prev" id="galleryPrevBtn" aria-label="পূর্ববর্তী ছবি">
        <i class="bi bi-chevron-left"></i>
      </button>
      <button type="button" class="kma-modal-nav kma-modal-nav-next" id="galleryNextBtn" aria-label="পরবর্তী ছবি">
        <i class="bi bi-chevron-right"></i>
      </button>
      <div class="kma-modal-media">
        <img id="galleryModalImg" src="" alt="" />
      </div>
      <div class="kma-modal-caption">
        <p id="galleryModalCaption" class="text-sm sm:text-base text-white/95 leading-6"></p>
        <a href="<?php echo BASE_URL; ?>/pages/gallery.php" class="kma-modal-gallery-link">
          <i class="bi bi-images"></i> সম্পূর্ণ গ্যালারি দেখুন
        </a>
      </div>
    </div>
  </div>
</div>


<!-- ── CTA ── -->
<section class="cta-section py-20 text-center" aria-label="ভর্তি কল-টু-অ্যাকশন">
  <div class="max-w-3xl mx-auto px-4 reveal">
    <div class="ornament mb-3" style="--orn-clr:rgba(255,255,255,.3)">
      <span style="background:rgba(255,255,255,.3)"></span>
      <i class="bi bi-mortarboard-fill text-gold"></i>
      <span style="background:rgba(255,255,255,.3)"></span>
    </div>
    <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">আজই ভর্তির আবেদন করুন</h2>
    <p class="text-white/80 mb-8">২০২৫-২৬ শিক্ষাবর্ষে সীমিত আসনে ভর্তি চলছে। দ্রুত আবেদন করুন এবং আপনার সন্তানের উজ্জ্বল ভবিষ্যৎ নিশ্চিত করুন।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-cta">
        <i class="bi bi-pencil-square me-1"></i> ভর্তির আবেদন করুন
      </a>
      <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-cta-outline">
        <i class="bi bi-telephone me-1"></i> যোগাযোগ করুন
      </a>
    </div>
  </div>
</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>


<!-- Bootstrap 5 JS — for carousel fade transitions only -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/modal.js"></script>

<style>
/* ══════════════════════════════════════════
   HERO CAROUSEL — custom styles
   (Tailwind can't animate arbitrary keyframes
   inline; these are minimal and scoped to hero)
══════════════════════════════════════════ */

/* Slide base: full-viewport, stacks slides */
.hero-slide {
  position: absolute; inset: 0;
  height: 92vh; min-height: 560px;
  display: flex; align-items: center;
  overflow: hidden;
  opacity: 0;
  transition: opacity .85s cubic-bezier(.4,0,.2,1);
  pointer-events: none;
  z-index: 0;
}
.hero-slide.active {
  position: relative;   /* active slide takes up space */
  opacity: 1;
  pointer-events: auto;
  z-index: 1;
}

/* Ken Burns zoom on active slide's bg */
.hero-bg {
  position: absolute; inset: -6%;
  background-size: cover; background-position: center;
  will-change: transform;
}
.hero-slide.active .hero-bg {
  animation: kenBurns 7s ease-out both;
}
@keyframes kenBurns {
  from { transform: scale(1.08); }
  to   { transform: scale(1.0); }
}

/* Overlays */
.hero-overlay {
  position: absolute; inset: 0; z-index: 1;
  background: linear-gradient(110deg,
    rgba(20,40,25,.80) 0%,
    rgba(46,107,62,.55) 55%,
    rgba(39,39,39,.35) 100%);
}
.hero-slide::after {
  content: ''; position: absolute; inset: 0; z-index: 1;
  background: radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,.45) 100%);
  pointer-events: none;
}

/* Diagonal stripe */
.hero-stripe {
  position: absolute; bottom: 0; right: 0;
  width: 38%; height: 100%; z-index: 2; pointer-events: none; overflow: hidden;
}
.hero-stripe::before {
  content: ''; position: absolute; top: 0; right: -40%; width: 100%; height: 100%;
  background: linear-gradient(135deg, transparent 50%, rgba(201,162,39,.10) 50%);
}
.hero-stripe::after {
  content: ''; position: absolute; top: 0; right: -20%; width: 100%; height: 100%;
  background: linear-gradient(135deg, transparent 50%, rgba(46,107,62,.08) 50%);
}

/* Floating emblem — desktop only */
.hero-emblem {
  position: absolute; right: 6%; top: 50%;
  transform: translateY(-50%);
  z-index: 3; opacity: 0;
  transition: opacity 1s ease .6s;
  pointer-events: none;
}
.hero-slide.active .hero-emblem { opacity: 1; }
.hero-emblem img {
  width: 220px; height: 220px; border-radius: 50%; object-fit: cover;
  border: 4px solid rgba(201,162,39,.55);
  box-shadow: 0 0 0 12px rgba(201,162,39,.12), 0 20px 60px rgba(0,0,0,.4);
  filter: brightness(.85) saturate(.9);
}
@media (max-width: 991px) { .hero-emblem { display: none; } }

/* Content */
.hero-content {
  position: relative; z-index: 4;
  color: #fff; padding-top: 2rem;
}

/* Staggered text reveal — fires only on active slide */
.hero-content .hero-badge,
.hero-content h1,
.hero-content .hero-desc,
.hero-content .hero-btns {
  opacity: 0; transform: translateY(32px);
}
.hero-slide.active .hero-content .hero-badge {
  animation: slideUp .65s cubic-bezier(.22,1,.36,1) .15s both;
}
.hero-slide.active .hero-content h1 {
  animation: slideUp .70s cubic-bezier(.22,1,.36,1) .32s both;
}
.hero-slide.active .hero-content .hero-desc {
  animation: slideUp .70s cubic-bezier(.22,1,.36,1) .50s both;
}
.hero-slide.active .hero-content .hero-btns {
  animation: slideUp .65s cubic-bezier(.22,1,.36,1) .68s both;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(32px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Badge pill */
.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(201,162,39,.2); border: 1px solid rgba(201,162,39,.55);
  color: #c9a227; font-size: .8rem; font-weight: 600;
  letter-spacing: .1em; text-transform: uppercase;
  padding: 6px 18px; border-radius: 30px; margin-bottom: 20px;
  backdrop-filter: blur(6px);
}
.hero-dot-pulse {
  display: inline-block; width: 7px; height: 7px; border-radius: 50%;
  background: #c9a227;
  animation: pulseDot 1.6s ease-in-out infinite;
}
@keyframes pulseDot {
  0%,100% { transform: scale(1); opacity: 1; }
  50%      { transform: scale(1.5); opacity: .6; }
}

/* Headline */
.hero-content h1 {
  font-family: 'Hind Siliguri', sans-serif;
  font-weight: 700;
  font-size: clamp(2rem, 5.5vw, 3.5rem);
  line-height: 1.22; text-shadow: 0 3px 16px rgba(0,0,0,.5);
  margin-bottom: 18px; color: #fff;
}
.hero-desc {
  font-size: clamp(.92rem, 1.9vw, 1.12rem);
  color: rgba(255,255,255,.84);
  max-width: 520px; margin-bottom: 32px; line-height: 1.85;
}

/* Buttons */
.hero-btns { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.btn-hero-primary {
  background: #2e6b3e; color: #fff;
  border: 2px solid #2e6b3e;
  padding: 13px 32px; border-radius: 50px;
  font-weight: 700; font-size: .95rem;
  text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
  box-shadow: 0 6px 24px rgba(46,107,62,.45);
  transition: background .3s, color .3s, transform .3s, box-shadow .3s;
}
.btn-hero-primary:hover {
  background: #c9a227; border-color: #c9a227; color: #272727;
  transform: translateY(-3px); box-shadow: 0 10px 32px rgba(201,162,39,.4);
}
.btn-hero-outline {
  background: rgba(255,255,255,.08); color: #fff;
  border: 2px solid rgba(255,255,255,.55);
  padding: 12px 30px; border-radius: 50px;
  font-weight: 600; font-size: .95rem;
  text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
  backdrop-filter: blur(4px);
  transition: background .3s, border-color .3s, transform .3s;
}
.btn-hero-outline:hover {
  background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.85);
  color: #fff; transform: translateY(-3px);
}

/* Progress bar */
.hero-progress {
  position: absolute; bottom: 0; left: 0;
  height: 3px; width: 100%; z-index: 10;
  background: rgba(255,255,255,.15);
}
.hero-progress-fill {
  height: 100%; width: 0%;
  background: linear-gradient(90deg, #2e6b3e, #c9a227);
  transition: width linear;
}

/* Arrow controls */
.hero-ctrl {
  position: absolute; top: 50%; transform: translateY(-50%);
  z-index: 10; width: 50px; height: 50px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,.35);
  background: rgba(255,255,255,.08); backdrop-filter: blur(8px);
  color: #fff; font-size: 1.2rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .3s ease;
}
.hero-ctrl:hover {
  background: #2e6b3e; border-color: #2e6b3e;
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 20px rgba(46,107,62,.5);
}
.hero-ctrl-prev { left: 24px; }
.hero-ctrl-next { right: 24px; }

/* Dot indicators */
.hero-indicators {
  position: absolute; bottom: 28px; left: 50%;
  transform: translateX(-50%);
  z-index: 10; display: flex; gap: 10px; align-items: center;
}
.hero-ind {
  width: 10px; height: 10px; border-radius: 50%;
  background: rgba(255,255,255,.4); border: none;
  cursor: pointer; transition: all .35s ease; padding: 0;
}
.hero-ind.active {
  background: #c9a227; width: 28px; border-radius: 5px;
  box-shadow: 0 0 8px rgba(201,162,39,.6);
}

/* Scroll cue */
.hero-scroll-cue {
  position: absolute; bottom: 52px; right: 5%; z-index: 10;
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  color: rgba(255,255,255,.55); font-size: .72rem;
  letter-spacing: .12em; text-transform: uppercase;
  cursor: pointer; transition: color .3s;
}
.hero-scroll-cue:hover { color: #c9a227; }
.scroll-line {
  width: 1px; height: 40px;
  background: linear-gradient(to bottom, rgba(255,255,255,.6), transparent);
  animation: scrollLine 2s ease-in-out infinite;
}
@keyframes scrollLine {
  0%   { transform: scaleY(0); transform-origin: top; }
  50%  { transform: scaleY(1); transform-origin: top; }
  51%  { transform: scaleY(1); transform-origin: bottom; }
  100% { transform: scaleY(0); transform-origin: bottom; }
}
@media (max-width: 767px) {
  .hero-slide { height: 75vh; min-height: 400px; }
  .hero-scroll-cue { display: none; }
  .hero-ctrl { width: 38px; height: 38px; font-size: 1rem; }
  .hero-ctrl-prev { left: 10px; }
  .hero-ctrl-next { right: 10px; }
  .hero-btns { justify-content: center; }
}

/* ── Shared card / section utilities ── */
.ornament { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.ornament span { height: 1px; width: 60px; background: #c9a227; }
.ornament i    { color: #c9a227; }

.section-title {
  font-family: 'Hind Siliguri', sans-serif;
  font-weight: 700; font-size: clamp(1.6rem, 3.5vw, 2.2rem);
  color: #272727; display: inline-block; margin-bottom: 10px; position: relative;
}
.dark .section-title { color: #fff; }
.section-title::after {
  content: ''; display: block; height: 3px; width: 55px;
  background: #2e6b3e; border-radius: 2px; margin-top: 8px;
}
.section-subtitle { font-size: 1rem; color: #6b6b5a; margin-bottom: 40px; }
.dark .section-subtitle { color: #9ca3af; }

.message-card {
  background: #f6f6e9; border-radius: 12px;
  padding: 40px 36px; box-shadow: 0 2px 12px rgba(39,39,39,.08);
  height: 100%; transition: all .3s ease;
}
.dark .message-card { background: #1f2937; }
.message-card:hover { box-shadow: 0 6px 30px rgba(39,39,39,.13); transform: translateY(-4px); }

.feature-card {
  background: #fff; border-radius: 12px; padding: 26px 22px;
  box-shadow: 0 2px 12px rgba(39,39,39,.08);
  text-align: center; transition: all .3s ease;
  border-bottom: 3px solid transparent;
}
.dark .feature-card { background: #1f2937; }
.feature-card:hover {
  box-shadow: 0 6px 30px rgba(39,39,39,.13);
  border-bottom-color: #2e6b3e; transform: translateY(-5px);
}

/* Notice tags */
.notice-tag {
  display: inline-block; font-size: .68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  padding: 2px 8px; border-radius: 3px;
}
.tag-exam    { background: #fde68a; color: #92400e; }
.tag-notice  { background: #bfdbfe; color: #1e40af; }
.tag-holiday { background: #bbf7d0; color: #166534; }
.tag-event   { background: #fecdd3; color: #9f1239; }

/* Gallery */
.gallery-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.gallery-item { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 1; cursor: pointer; }
.gallery-item.large { grid-column: span 2; aspect-ratio: 2/1; }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
.gallery-item:hover img { transform: scale(1.08); }
.gallery-overlay {
  position: absolute; inset: 0; background: rgba(46,107,62,.7);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity .3s ease;
}
.gallery-item:hover .gallery-overlay { opacity: 1; }
@media (max-width: 767px) {
  .gallery-grid { grid-template-columns: repeat(2,1fr); }
  .gallery-item.large { grid-column: span 2; }
}

/* CTA section */
.cta-section { background: linear-gradient(135deg, #2e6b3e 0%, #1a4a2a 100%); }
.btn-cta {
  background: #c9a227; color: #272727; border: none;
  padding: 13px 34px; border-radius: 30px; font-weight: 700; font-size: 1rem;
  text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
  transition: all .3s ease;
}
.btn-cta:hover { background: #fff; color: #2e6b3e; transform: translateY(-3px); }
.btn-cta-outline {
  background: transparent; border: 2px solid rgba(255,255,255,.6); color: #fff;
  padding: 12px 32px; border-radius: 30px; font-weight: 600; font-size: 1rem;
  text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
  transition: all .3s ease;
}
.btn-cta-outline:hover { background: rgba(255,255,255,.15); }

/* Scroll reveal */
.reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
.delay-1 { transition-delay: .1s; }
.delay-2 { transition-delay: .2s; }
.delay-3 { transition-delay: .3s; }
.delay-4 { transition-delay: .4s; }

/* Keyboard focus for clickable notice/gallery items */
.notice-item:focus-visible,
.gallery-item:focus-visible {
  outline: 2px solid #2e6b3e; outline-offset: -2px; border-radius: 4px;
}
/* NOTE: .kma-modal system (notice modal, gallery lightbox, notice
   attachment styles) now lives in assets/css/site.css — shared across
   index.php, pages/gallery.php and pages/about.php. Nothing page-specific
   left to declare here. */
</style>

<script>
/* ── Scroll reveal ── */
(function () {
    var els = document.querySelectorAll('.reveal');
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    els.forEach(function (el) { obs.observe(el); });
}());

/* ── Hero Carousel ──
   No Bootstrap CSS loaded — we manage slide
   visibility ourselves with .active class and
   CSS opacity transitions. ES5 only. */
(function () {
    var INTERVAL  = 6000;
    var slides    = document.querySelectorAll('.hero-slide');
    var dots      = document.querySelectorAll('.hero-ind');
    var fill      = document.getElementById('heroProgressFill');
    var prevBtn   = document.getElementById('heroPrev');
    var nextBtn   = document.getElementById('heroNext');
    var scrollCue = document.getElementById('heroScrollCue');
    var total     = slides.length;
    var current   = 0;
    var autoTimer = null;

    function showSlide(idx) {
        /* remove active from current */
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');

        /* reset text animations on outgoing slide */
        var outEls = slides[current].querySelectorAll('.hero-badge, h1, .hero-desc, .hero-btns');
        for (var i = 0; i < outEls.length; i++) {
            outEls[i].style.animation = 'none';
        }

        current = (idx + total) % total;

        /* activate new slide */
        slides[current].classList.add('active');
        dots[current].classList.add('active');

        /* force reflow so animations retrigger */
        var inEls = slides[current].querySelectorAll('.hero-badge, h1, .hero-desc, .hero-btns');
        for (var j = 0; j < inEls.length; j++) {
            void inEls[j].offsetWidth;
            inEls[j].style.animation = '';
        }
    }

    function startProgress() {
        if (!fill) { return; }
        fill.style.transition = 'none';
        fill.style.width = '0%';
        void fill.offsetWidth;
        fill.style.transition = 'width ' + INTERVAL + 'ms linear';
        fill.style.width = '100%';
    }

    function stopProgress() {
        if (!fill) { return; }
        fill.style.transition = 'none';
        fill.style.width = '0%';
    }

    function startAuto() {
        clearInterval(autoTimer);
        startProgress();
        autoTimer = setInterval(function () { showSlide(current + 1); startProgress(); }, INTERVAL);
    }

    function stopAuto() { clearInterval(autoTimer); stopProgress(); }

    if (prevBtn) { prevBtn.addEventListener('click', function () { stopAuto(); showSlide(current - 1); startAuto(); }); }
    if (nextBtn) { nextBtn.addEventListener('click', function () { stopAuto(); showSlide(current + 1); startAuto(); }); }

    for (var d = 0; d < dots.length; d++) {
        (function (dot, idx) {
            dot.addEventListener('click', function () { stopAuto(); showSlide(idx); startAuto(); });
        })(dots[d], d);
    }

    var heroEl = document.getElementById('heroCarousel');
    if (heroEl) {
        heroEl.addEventListener('mouseenter', stopAuto);
        heroEl.addEventListener('mouseleave', startAuto);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft')  { stopAuto(); showSlide(current - 1); startAuto(); }
        if (e.key === 'ArrowRight') { stopAuto(); showSlide(current + 1); startAuto(); }
    });

    if (scrollCue) {
        window.addEventListener('scroll', function () {
            scrollCue.style.opacity = window.scrollY > 80 ? '0' : '1';
        }, { passive: true });
        scrollCue.addEventListener('click', function () {
            var sb = document.getElementById('statsBar');
            if (sb) { sb.scrollIntoView({ behavior: 'smooth' }); }
        });
    }

    /* kick off */
    startAuto();
}());

/* ══════════════════════════════════════════════════
   NOTICE + GALLERY MODALS
   ES5 only. Notice content is already embedded on the
   page (noticesData JSON) — no AJAX round-trip needed,
   so the modal opens instantly on click/tap.
══════════════════════════════════════════════════ */
(function () {
    var noticesData = {};
    var dataEl = document.getElementById('noticesData');
    if (dataEl) {
        try { noticesData = JSON.parse(dataEl.textContent || dataEl.innerText || '{}'); }
        catch (e) { noticesData = {}; }
    }

    function safe(s) { return (s === null || s === undefined) ? '' : String(s); }

    /* ── Notice Modal ── */
    var noticeModal   = document.getElementById('noticeModal');
    var noticeTag     = document.getElementById('noticeModalTag');
    var noticeTitle   = document.getElementById('noticeModalTitle');
    var noticeDate    = document.getElementById('noticeModalDate');
    var noticeText    = document.getElementById('noticeModalText');
    var noticeAttWrap = document.getElementById('noticeModalAttWrap');

    function attIcon(type) {
        if (type === 'pdf')   { return '<div class="notice-att-icon pdf"><i class="bi bi-file-earmark-pdf-fill"></i></div>'; }
        if (type === 'image') { return '<div class="notice-att-icon"><i class="bi bi-file-earmark-image-fill"></i></div>'; }
        return '<div class="notice-att-icon"><i class="bi bi-file-earmark-arrow-down-fill"></i></div>';
    }

    function buildAttachment(att) {
        if (!att) { return ''; }
        var html = '';
        if (att.type === 'image') {
            html += '<div class="notice-att-image"><img src="' + safe(att.url) + '" alt="সংযুক্ত ছবি" loading="lazy" /></div>';
        }
        html += '<div class="notice-att-card">' + attIcon(att.type) +
                '<div class="notice-att-info">' +
                    '<div class="notice-att-name">' + safe(att.name) + '</div>' +
                    '<div class="notice-att-meta">' + safe(att.ext) + ' ফাইল সংযুক্ত আছে</div>' +
                '</div></div>' +
                '<div class="notice-att-actions">' +
                    '<a class="notice-att-btn primary" href="' + safe(att.url) + '" target="_blank" rel="noopener noreferrer"><i class="bi bi-eye"></i> দেখুন</a>' +
                    '<a class="notice-att-btn ghost" href="' + safe(att.url) + '" download><i class="bi bi-download"></i> ডাউনলোড</a>' +
                '</div>';
        return html;
    }

    function openNotice(id) {
        var n = noticesData[id];
        if (!n || !noticeModal) { return; }
        noticeTag.className = 'notice-tag mb-2 inline-block ' + n.tagCls;
        noticeTag.textContent = n.tagLbl;
        noticeTitle.textContent = n.title;
        noticeDate.textContent = n.date;
        noticeText.innerHTML = n.content;
        if (n.att) {
            noticeAttWrap.innerHTML = buildAttachment(n.att);
            noticeAttWrap.classList.remove('hidden');
        } else {
            noticeAttWrap.innerHTML = '';
            noticeAttWrap.classList.add('hidden');
        }
        var bodyBox = noticeModal.querySelector('.kma-modal-body');
        if (bodyBox) { bodyBox.scrollTop = 0; }
        window.KmaModal.open(noticeModal);
    }

    var noticeItems = document.querySelectorAll('.notice-item');
    for (var i = 0; i < noticeItems.length; i++) {
        (function (item) {
            var id = item.getAttribute('data-id');
            item.addEventListener('click', function () { openNotice(id); });
            item.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                    openNotice(id);
                }
            });
        })(noticeItems[i]);
    }

    /* ── Gallery Modal ── */
    var galleryModal   = document.getElementById('galleryModal');
    var galleryImg     = document.getElementById('galleryModalImg');
    var galleryCaption = document.getElementById('galleryModalCaption');
    var galleryPrevBtn = document.getElementById('galleryPrevBtn');
    var galleryNextBtn = document.getElementById('galleryNextBtn');

    var galleryEls  = document.querySelectorAll('.gallery-item');
    var galleryList = [];
    for (var g = 0; g < galleryEls.length; g++) {
        galleryList.push({
            img: galleryEls[g].getAttribute('data-img'),
            caption: galleryEls[g].getAttribute('data-caption')
        });
    }
    var galleryCurrent = 0;

    function renderGallery(idx) {
        if (!galleryList.length) { return; }
        if (idx < 0) { idx = galleryList.length - 1; }
        if (idx >= galleryList.length) { idx = 0; }
        galleryCurrent = idx;
        var g = galleryList[galleryCurrent];
        galleryImg.src = g.img;
        galleryImg.alt = g.caption;
        galleryCaption.textContent = g.caption;
        var multi = galleryList.length > 1;
        if (galleryPrevBtn) { galleryPrevBtn.style.display = multi ? 'flex' : 'none'; }
        if (galleryNextBtn) { galleryNextBtn.style.display = multi ? 'flex' : 'none'; }
    }

    function openGallery(idx) {
        renderGallery(idx);
        window.KmaModal.open(galleryModal);
    }

    for (var gi = 0; gi < galleryEls.length; gi++) {
        (function (el, idx) {
            el.addEventListener('click', function () { openGallery(idx); });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                    openGallery(idx);
                }
            });
        })(galleryEls[gi], gi);
    }

    if (galleryPrevBtn) { galleryPrevBtn.addEventListener('click', function () { renderGallery(galleryCurrent - 1); }); }
    if (galleryNextBtn) { galleryNextBtn.addEventListener('click', function () { renderGallery(galleryCurrent + 1); }); }

    /* Arrow-key navigation while the gallery modal is open.
       Close-on-click / close-on-Escape is handled centrally by modal.js */
    document.addEventListener('keydown', function (e) {
        if (galleryModal && galleryModal.classList.contains('show')) {
            if (e.key === 'ArrowLeft')  { renderGallery(galleryCurrent - 1); }
            if (e.key === 'ArrowRight') { renderGallery(galleryCurrent + 1); }
        }
    });
}());
</script>