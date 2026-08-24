<?php
/**
 * KMA — pages/academics.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'একাডেমিক | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'ক্লাস রুটিন, সিলেবাস, ছুটির তালিকা, ড্রেস কোড ও শ্রেণি তথ্য।';

$pdo = getDB();

/* Classes with subject counts */
$classes = $pdo->query(
    'SELECT c.id, c.class_key, c.class_name, c.class_name_en, c.age_range, c.description,
            COUNT(cs.subject_id) AS subj_count
     FROM classes c
     LEFT JOIN class_subjects cs ON cs.class_id = c.id
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.sort_order'
)->fetchAll();

/* Downloads */
$downloads = $pdo->query(
    'SELECT d.*, c.class_name FROM downloads d
     LEFT JOIN classes c ON c.id = d.class_id
     WHERE d.is_active = 1
     ORDER BY d.category, d.created_at DESC'
)->fetchAll();

/* Group downloads by category */
$dlBycat = [];
foreach ($downloads as $dl) {
    $dlBycat[$dl['category']][] = $dl;
}

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:280px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-book-half"></i> শিক্ষা কার্যক্রম</div>
      <h1 class="font-bn font-bold text-4xl mb-3">একাডেমিক <em style="font-style:normal;color:#c9a227">তথ্য</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">একাডেমিক</span>
      </nav>
    </div>
  </div>
</header>

<!-- Sticky Section Tabs -->
<div class="sticky top-[78px] z-[900] bg-white dark:bg-gray-900 border-b border-kma-border shadow-sm">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4">
      <?php
      $tabs = [
        ['sec-downloads', 'bi-download',      'রুটিন ও সিলেবাস'],
        ['sec-classes',   'bi-grid-3x3-gap',  'শ্রেণি ও বিষয়'],
        ['sec-dress',     'bi-person-badge',   'ড্রেস ও আচরণবিধি'],
        ['sec-calendar',  'bi-calendar3',      'ছুটির তালিকা'],
      ];
      foreach ($tabs as $i => $tab): ?>
      <button class="tab-btn <?php echo $i === 0 ? 'active' : ''; ?> w-full flex items-center justify-center gap-2
                     px-3 py-3.5 text-sm font-semibold border-b-[3px] transition-colors
                     <?php echo $i === 0
                       ? 'border-accent text-accent'
                       : 'border-transparent text-kma-muted hover:text-accent'; ?>"
              data-target="<?php echo h($tab[0]); ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
        <i class="bi <?php echo h($tab[1]); ?>"></i>
        <span class="hidden sm:inline"><?php echo h($tab[2]); ?></span>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<main id="main-content">

<!-- ══ SECTION 1: Downloads ══ -->
<section id="sec-downloads" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-download"></i><span></span></div>
      <h2 class="section-title">ক্লাস রুটিন ও সিলেবাস</h2>
      <p class="text-kma-muted text-sm mt-1">পিডিএফ ফাইল ডাউনলোড করুন</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">

      <!-- Routines -->
      <div class="reveal reveal-d1 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-4 px-5 py-4 border-b border-kma-border">
          <div class="w-12 h-12 rounded-xl bg-accent-light flex items-center justify-center text-2xl text-accent flex-shrink-0">
            <i class="bi bi-calendar-week"></i>
          </div>
          <div>
            <h3 class="font-bold text-kma-dark dark:text-white text-sm">ক্লাস রুটিন</h3>
            <p class="text-xs text-kma-muted">সাপ্তাহিক ক্লাসের সময়সূচি</p>
          </div>
        </div>
        <?php
        $routines = isset($dlBycat['routine']) ? $dlBycat['routine'] : [];
        if (empty($routines)):
          $routines = [
            ['title'=>'জুনিয়র লেভেল রুটিন ২০২৬','meta'=>'জানুয়ারি ২০২৬ · ৪৮ KB','file_path'=>'text.pdf'],
            ['title'=>'প্রথম-দ্বিতীয় লেভেল রুটিন','meta'=>'জানুয়ারি ২০২৬ · ৫২ KB','file_path'=>'text.pdf'],
            ['title'=>'তৃতীয়-পঞ্চম লেভেল রুটিন','meta'=>'জানুয়ারি ২০২৬ · ৫৫ KB','file_path'=>'text.pdf'],
          ];
          foreach ($routines as $dl): ?>
          <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-kma-border last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
            <div>
              <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($dl['title']); ?> <span class="text-xs bg-red-600 text-white px-1.5 py-0.5 rounded font-bold">PDF</span></div>
              <div class="text-xs text-kma-muted mt-0.5"><?php echo h($dl['meta']); ?></div>
            </div>
            <a href="<?php echo BASE_URL; ?>/files/<?php echo h($dl['file_path']); ?>"
               class="flex-shrink-0 flex items-center gap-1 bg-accent text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-gold hover:text-kma-dark transition-colors" download>
              <i class="bi bi-download"></i> ডাউনলোড
            </a>
          </div>
          <?php endforeach;
        else:
          foreach ($routines as $dl): ?>
          <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-kma-border last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
            <div>
              <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($dl['title']); ?> <span class="text-xs bg-red-600 text-white px-1.5 py-0.5 rounded font-bold">PDF</span></div>
              <div class="text-xs text-kma-muted mt-0.5"><?php echo h($dl['file_size'] ?? ''); ?></div>
            </div>
            <a href="<?php echo BASE_URL; ?>/uploads/pdfs/<?php echo h($dl['file_path']); ?>"
               class="flex-shrink-0 flex items-center gap-1 bg-accent text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-gold hover:text-kma-dark transition-colors" download>
              <i class="bi bi-download"></i> ডাউনলোড
            </a>
          </div>
          <?php endforeach;
        endif; ?>
      </div>

      <!-- Syllabus -->
      <div class="reveal reveal-d2 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-4 px-5 py-4 border-b border-kma-border">
          <div class="w-12 h-12 rounded-xl bg-gold/15 flex items-center justify-center text-2xl text-gold flex-shrink-0">
            <i class="bi bi-book-half"></i>
          </div>
          <div>
            <h3 class="font-bold text-kma-dark dark:text-white text-sm">সিলেবাস ২০২৬</h3>
            <p class="text-xs text-kma-muted">শ্রেণিওয়ারি পাঠ্যক্রম</p>
          </div>
        </div>
        <?php
        $syllabi = isset($dlBycat['syllabus']) ? $dlBycat['syllabus'] : [];
        if (empty($syllabi)):
          $syllabi = [
            ['title'=>'প্রথম লেভেল সিলেবাস','meta'=>'২০২৬ সংস্করণ · ১২০ KB','file_path'=>'text.pdf'],
            ['title'=>'দ্বিতীয় লেভেল সিলেবাস','meta'=>'২০২৬ সংস্করণ · ১৩০ KB','file_path'=>'text.pdf'],
            ['title'=>'তৃতীয়–পঞ্চম লেভেল সিলেবাস','meta'=>'২০২৬ সংস্করণ · ১৮০ KB','file_path'=>'text.pdf'],
          ];
        endif;
        foreach ($syllabi as $dl): ?>
        <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-kma-border last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
          <div>
            <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($dl['title']); ?> <span class="text-xs bg-red-600 text-white px-1.5 py-0.5 rounded font-bold">PDF</span></div>
            <div class="text-xs text-kma-muted mt-0.5"><?php echo h(isset($dl['meta']) ? $dl['meta'] : ($dl['file_size'] ?? '')); ?></div>
          </div>
          <a href="<?php echo BASE_URL; ?>/files/<?php echo h($dl['file_path']); ?>"
             class="flex-shrink-0 flex items-center gap-1 bg-gold text-kma-dark text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-accent hover:text-white transition-colors" download>
            <i class="bi bi-download"></i> ডাউনলোড
          </a>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Exam Schedule -->
      <div class="reveal reveal-d3 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="flex items-center gap-4 px-5 py-4 border-b border-kma-border">
          <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl text-kma-dark dark:text-gray-300 flex-shrink-0">
            <i class="bi bi-clipboard-check"></i>
          </div>
          <div>
            <h3 class="font-bold text-kma-dark dark:text-white text-sm">পরীক্ষার সময়সূচি</h3>
            <p class="text-xs text-kma-muted">মাসিক ও বার্ষিক পরীক্ষা</p>
          </div>
        </div>
        <?php
        $exams = isset($dlBycat['exam_schedule']) ? $dlBycat['exam_schedule'] : [];
        if (empty($exams)):
          $exams = [
            ['title'=>'প্রথম সাময়িক পরীক্ষা ২০২৬','meta'=>'মার্চ ২০২৬ · ৪৫ KB','file_path'=>'text.pdf'],
            ['title'=>'বার্ষিক পরীক্ষা ২০২৬','meta'=>'নভেম্বর ২০২৬ · ৫০ KB','file_path'=>'text.pdf'],
          ];
        endif;
        foreach ($exams as $dl): ?>
        <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-kma-border last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
          <div>
            <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($dl['title']); ?> <span class="text-xs bg-red-600 text-white px-1.5 py-0.5 rounded font-bold">PDF</span></div>
            <div class="text-xs text-kma-muted mt-0.5"><?php echo h(isset($dl['meta']) ? $dl['meta'] : ($dl['file_size'] ?? '')); ?></div>
          </div>
          <a href="<?php echo BASE_URL; ?>/files/<?php echo h($dl['file_path']); ?>"
             class="flex-shrink-0 flex items-center gap-1 bg-kma-dark text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-accent transition-colors" download>
            <i class="bi bi-download"></i> ডাউনলোড
          </a>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<!-- ══ SECTION 2: Classes & Subjects ══ -->
<section id="sec-classes" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-grid-3x3-gap-fill"></i><span></span></div>
      <h2 class="section-title">শ্রেণি ও বিষয় তালিকা</h2>
      <p class="text-kma-muted text-sm mt-1">শ্রেণি নির্বাচন করুন এবং বিষয়ের বিস্তারিত দেখুন</p>
    </div>

    <!-- Class selector -->
    <div class="flex flex-wrap gap-2 justify-center mb-8 reveal" role="tablist">
      <?php foreach ($classes as $i => $cls): ?>
      <button class="cls-tab px-5 py-2 rounded-full text-sm font-semibold border transition-colors
                     <?php echo $i === 0
                       ? 'bg-accent border-accent text-white shadow-md'
                       : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent dark:text-gray-300'; ?>"
              role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
              data-cls="<?php echo h($cls['class_key']); ?>"
              aria-controls="cls-<?php echo h($cls['class_key']); ?>">
        <?php echo h($cls['class_name']); ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Class panels -->
    <?php foreach ($classes as $i => $cls):
      /* Fetch subjects for this class */
      $subjects = $pdo->prepare(
          'SELECT s.name_bn, s.color_class, s.type, cs.teacher_name
           FROM class_subjects cs
           JOIN subjects s ON s.id = cs.subject_id
           WHERE cs.class_id = ? ORDER BY cs.sort_order'
      );
      $subjects->execute([$cls['id']]);
      $subjList = $subjects->fetchAll();
    ?>
    <div class="class-panel <?php echo $i === 0 ? 'show' : ''; ?>" id="cls-<?php echo h($cls['class_key']); ?>" role="tabpanel">
      <div class="grid lg:grid-cols-5 gap-6">
        <!-- Info card -->
        <div class="lg:col-span-2">
          <div class="rounded-xl p-7 text-white shadow-xl" style="background:linear-gradient(135deg,#2e6b3e,#1a4a2a)">
            <h3 class="font-bold text-xl mb-2"><?php echo h($cls['class_name']); ?></h3>
            <p class="text-white/80 text-sm mb-5"><?php echo h($cls['description'] ?? ''); ?></p>
            <div class="flex gap-6 flex-wrap">
              <div class="text-center">
                <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo count($subjList); ?></div>
                <div class="text-white/60 text-xs mt-1">বিষয়</div>
              </div>
              <div class="w-px bg-white/20 self-stretch"></div>
              <div class="text-center">
                <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo count($subjList); ?></div>
                <div class="text-white/60 text-xs mt-1">শিক্ষক</div>
              </div>
              <div class="w-px bg-white/20 self-stretch"></div>
              <div class="text-center">
                <div class="font-display text-3xl font-bold text-gold leading-none"><?php echo h($cls['age_range']); ?></div>
                <div class="text-white/60 text-xs mt-1">বয়স</div>
              </div>
            </div>
          </div>
        </div>
        <!-- Subject list -->
        <div class="lg:col-span-3">
          <div class="bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="bg-kma-dark text-white px-5 py-3.5 flex items-center gap-2 font-bold text-sm">
              <i class="bi bi-journal-bookmark-fill text-gold"></i> বিষয় তালিকা
            </div>
            <ul>
              <?php foreach ($subjList as $si => $sub):
                $typeCls = [
                  'core'     => 'bg-blue-100 text-blue-700',
                  'religion' => 'bg-yellow-100 text-yellow-800',
                  'extra'    => 'bg-green-100 text-green-700',
                  'optional' => 'bg-gray-100 text-gray-600',
                ];
                $typeLabel = [
                  'core'     => 'মূল বিষয়',
                  'religion' => 'ধর্ম',
                  'extra'    => 'সহশিক্ষা',
                  'optional' => 'ঐচ্ছিক',
                ];
                $tc = isset($typeCls[$sub['type']])  ? $typeCls[$sub['type']]   : 'bg-gray-100 text-gray-600';
                $tl = isset($typeLabel[$sub['type']]) ? $typeLabel[$sub['type']] : $sub['type'];
              ?>
              <li class="subj-item flex items-center gap-3 px-5 py-3 border-b border-kma-border last:border-0 hover:bg-white dark:hover:bg-gray-600 transition-colors">
                <div class="w-7 h-7 rounded-full bg-accent-light text-accent flex items-center justify-center text-xs font-bold flex-shrink-0">
                  <?php echo $si + 1; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($sub['name_bn']); ?></div>
                  <?php if (!empty($sub['teacher_name'])): ?>
                  <div class="text-xs text-kma-muted flex items-center gap-1 mt-0.5">
                    <i class="bi bi-person"></i> <?php echo h($sub['teacher_name']); ?>
                  </div>
                  <?php endif; ?>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full <?php echo h($tc); ?> flex-shrink-0">
                  <?php echo h($tl); ?>
                </span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

<!-- ══ SECTION 3: Dress Code ══ -->
<section id="sec-dress" class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-person-badge"></i><span></span></div>
      <h2 class="section-title">স্কুল ড্রেস ও আচরণবিধি</h2>
      <p class="text-kma-muted text-sm mt-1">পোশাক বিধিমালা ও শিষ্টাচারের নির্দেশিকা</p>
    </div>

    <!-- B&W Banner -->
    <div class="reveal bg-kma-dark text-white rounded-xl p-6 flex flex-wrap items-center gap-5 mb-8 shadow-lg">
      <div class="flex gap-3">
        <?php
        $swatches = [['#111','সাদা','white','#fff'],['#fff','কালো','dark','#111'],['#2e6b3e','KMA','badge','#fff']];
        foreach ($swatches as $sw): ?>
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xs font-black border-2 border-white/20 shadow"
             style="background:<?php echo $sw[0]; ?>;color:<?php echo $sw[3]; ?>">
          <?php echo h($sw[2] === 'badge' ? 'KMA' : $sw[1]); ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div>
        <h3 class="font-bold text-lg"><i class="bi bi-palette2 me-2 text-gold"></i>Black &amp; White ড্রেস কোড</h3>
        <p class="text-white/70 text-sm mt-0.5">অফিসিয়াল পোশাক সম্পূর্ণ কালো ও সাদা রঙের সমন্বয়ে তৈরি। বুকে KMA মনোগ্রাম সংবলিত পোশাক পরা বাধ্যতামূলক।</p>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-10">
      <?php
      $dressCards = [
        [
          'title'  => 'ছেলেদের পোশাক',
          'sub'    => 'কালো ও সাদা ড্রেস কোড',
          'rules'  => [
            [true,  'সাদা ফুল স্লিভ শার্ট (বুকে KMA মনোগ্রাম)'],
            [true,  'কালো প্যান্ট (ফ্ল্যাট ফ্রন্ট, বেল্ট সহ)'],
            [true,  'কালো ও সাদা টাই (শীতকালে কালো সোয়েটার)'],
            [true,  'কালো চামড়ার জুতা ও সাদা মোজা'],
            [false, 'রঙিন পোশাক, স্পোর্টস শু বা স্যান্ডেল নিষিদ্ধ।'],
          ],
        ],
        [
          'title'  => 'মেয়েদের পোশাক',
          'sub'    => 'কালো ও সাদা ড্রেস কোড',
          'rules'  => [
            [true,  'কালো বোরখা (বুকে KMA মনোগ্রাম)'],
            [true,  'সাদা হিজাব (মাথা ঢেকে পরিধান করতে হবে)'],
            [true,  'শীতকালে কালো কার্ডিগান বা সোয়েটার'],
            [true,  'কালো জুতা ও সাদা মোজা'],
            [false, 'অতিরিক্ত অলঙ্কার, মেকআপ বা রঙিন পোশাক নিষিদ্ধ।'],
          ],
        ],
      ];
      foreach ($dressCards as $di => $dc): ?>
      <div class="reveal reveal-d<?php echo $di+1; ?> dress-card bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 px-6 py-8 text-center">
          <span class="inline-block bg-accent text-white text-xs font-bold px-3 py-1 rounded-full mb-4"><?php echo h($dc['title']); ?></span>
          <div class="flex justify-center gap-3 mt-3">
            <div class="flex flex-col items-center gap-1"><div class="w-8 h-8 rounded-full bg-white border-2 border-gray-300"></div><span class="text-xs text-kma-muted">সাদা</span></div>
            <div class="flex flex-col items-center gap-1"><div class="w-8 h-8 rounded-full bg-gray-900 border-2 border-gray-700"></div><span class="text-xs text-kma-muted">কালো</span></div>
            <div class="flex flex-col items-center gap-1"><div class="w-8 h-8 rounded-full bg-accent border-2 border-accent flex items-center justify-center text-[9px] text-white font-bold">KMA</div><span class="text-xs text-kma-muted">ব্যাজ</span></div>
          </div>
        </div>
        <div class="p-5">
          <h3 class="font-bold text-kma-dark dark:text-white mb-1"><?php echo h($dc['title']); ?></h3>
          <p class="text-accent text-xs font-semibold mb-3 flex items-center gap-1">
            <i class="bi bi-circle-fill" style="font-size:.4rem"></i> <?php echo h($dc['sub']); ?>
          </p>
          <?php foreach ($dc['rules'] as $rule): ?>
          <div class="flex items-start gap-2 text-sm text-kma-muted py-1.5 border-b border-kma-border last:border-0">
            <?php if ($rule[0]): ?>
            <i class="bi bi-check-circle-fill text-accent mt-0.5 flex-shrink-0"></i>
            <?php else: ?>
            <i class="bi bi-x-circle-fill text-red-500 mt-0.5 flex-shrink-0"></i>
            <?php endif; ?>
            <span><?php echo h($rule[1]); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Code of Conduct -->
    <div class="reveal mb-6">
      <h3 class="section-title text-xl">আচরণবিধি (Code of Conduct)</h3>
      <p class="text-kma-muted text-sm mt-1">শিক্ষার্থীদের মেনে চলতে হবে এমন নিয়মকানুন</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 reveal">
      <?php
      $conducts = [
        ['green','bi-mortarboard-fill','শ্রেণিকক্ষ আচরণ','সময়মতো শ্রেণিকক্ষে উপস্থিত থাকতে হবে। শিক্ষক পাঠদান করার সময় মনোযোগ দিয়ে শুনতে হবে এবং অনুমতি ছাড়া কথা বলা যাবে না।',''],
        ['gold','bi-clock-fill','সময়ানুবর্তিতা','বিদ্যালয় শুরুর ১০ মিনিট আগে উপস্থিত থাকা বাধ্যতামূলক। বিনা কারণে অনুপস্থিতি গ্রহণযোগ্য নয়।','border-gold'],
        ['green','bi-person-heart','শিক্ষকের প্রতি শ্রদ্ধা','সকল শিক্ষক ও কর্মকর্তাকে যথাযথ সম্মান প্রদর্শন করতে হবে।',''],
        ['red','bi-ban','নিষিদ্ধ কার্যকলাপ','মোবাইল ফোন ব্যবহার, মারামারি, অশ্লীল ভাষা প্রয়োগ সম্পূর্ণ নিষিদ্ধ।','border-red-400'],
        ['green','bi-stars','পরিষ্কার-পরিচ্ছন্নতা','শ্রেণিকক্ষ ও বিদ্যালয় প্রাঙ্গণ সর্বদা পরিষ্কার রাখতে হবে।',''],
        ['gold','bi-pencil-square','হোমওয়ার্ক ও প্রস্তুতি','প্রতিদিনের হোমওয়ার্ক সময়মতো সম্পন্ন করতে হবে।','border-gold'],
      ];
      $iconColors = ['green'=>'bg-accent-light text-accent','gold'=>'bg-gold/15 text-gold','red'=>'bg-red-100 text-red-600'];
      foreach ($conducts as $ci => $cc):
        $ic = isset($iconColors[$cc[0]]) ? $iconColors[$cc[0]] : $iconColors['green'];
        $bc = !empty($cc[4]) ? $cc[4] : 'border-accent';
      ?>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 <?php echo h($bc); ?> hover:shadow-md hover:translate-x-1 transition-all">
        <div class="flex items-center gap-3 mb-2.5">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center <?php echo h($ic); ?> flex-shrink-0">
            <i class="bi <?php echo h($cc[1]); ?>"></i>
          </div>
          <h4 class="font-bold text-sm text-kma-dark dark:text-white"><?php echo h($cc[2]); ?></h4>
        </div>
        <p class="text-kma-muted text-sm leading-relaxed"><?php echo h($cc[3]); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ SECTION 4: Holiday Calendar ══ -->
<section id="sec-calendar" class="py-16 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-calendar3"></i><span></span></div>
      <h2 class="section-title">ছুটির তালিকা ২০২৬</h2>
      <p class="text-kma-muted text-sm mt-1">বাংলাদেশ সরকারি ছুটি ও বিদ্যালয়ের বার্ষিক ক্যালেন্ডার</p>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-3 justify-center mb-6 reveal">
      <?php
      $legends = [
        ['bg-red-100 border-red-300','সরকারি ছুটি'],
        ['bg-green-100 border-green-300','বিদ্যালয় ছুটি'],
        ['bg-yellow-100 border-yellow-300','পরীক্ষা'],
        ['bg-purple-100 border-purple-300','বিশেষ অনুষ্ঠান'],
      ];
      foreach ($legends as $lg): ?>
      <div class="flex items-center gap-2 text-xs text-kma-muted font-semibold bg-white dark:bg-gray-700 border border-kma-border px-3 py-1.5 rounded-full shadow-sm">
        <span class="w-3 h-3 rounded-sm border <?php echo h($lg[0]); ?>"></span>
        <?php echo h($lg[1]); ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Download button -->
    <div class="text-center mb-8 reveal">
      <a href="<?php echo BASE_URL; ?>/files/holiday.pdf" download
         class="inline-flex items-center gap-2 bg-accent text-white px-6 py-2.5 rounded-full font-semibold text-sm hover:bg-gold hover:text-kma-dark transition-colors shadow-md">
        <i class="bi bi-download"></i> সম্পূর্ণ ক্যালেন্ডার ডাউনলোড করুন (PDF)
      </a>
    </div>

    <!-- Accordion by month -->
    <?php
    $pdo2 = getDB();
    $holidays = $pdo2->query(
        'SELECT * FROM holidays WHERE year = 2026 AND is_active = 1 ORDER BY start_date'
    )->fetchAll();

    /* Group by month */
    $byMonth = [];
    foreach ($holidays as $hol) {
        $m = date('n', strtotime($hol['start_date']));
        $byMonth[$m][] = $hol;
    }

    $monthNames = ['১','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];

    if (empty($byMonth)): ?>
    <div class="text-center text-kma-muted py-8 text-sm">ছুটির তালিকা পাওয়া যায়নি। অ্যাডমিন প্যানেল থেকে যোগ করুন।</div>
    <?php else:
    foreach ($byMonth as $m => $mHols):
      $mn = isset($monthNames[$m]) ? $monthNames[$m] : $m;
    ?>
    <div class="reveal mb-2 rounded-xl overflow-hidden shadow-sm border border-kma-border">
      <button class="w-full flex items-center justify-between px-5 py-4 bg-kma-bg dark:bg-gray-700 font-bold text-sm text-kma-dark dark:text-white hover:bg-accent hover:text-white transition-colors group"
              onclick="this.nextElementSibling.classList.toggle('hidden');this.querySelector('.acc-icon').classList.toggle('rotate-180')">
        <span class="flex items-center gap-2"><i class="bi bi-calendar-month text-accent group-hover:text-white"></i> <?php echo h($mn); ?> ২০২৬</span>
        <i class="bi bi-chevron-down acc-icon transition-transform text-kma-muted group-hover:text-white"></i>
      </button>
      <div class="hidden bg-white dark:bg-gray-800">
        <?php foreach ($mHols as $hol):
          $dt  = new DateTime($hol['start_date']);
          $day = $dt->format('d');
          $typeCss = holidayTypeTailwind($hol['type']);
          $typeLabel = holidayTypeLabel($hol['type']);
        ?>
        <div class="flex items-start gap-4 px-5 py-3.5 border-b border-kma-border last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
          <div class="bg-accent text-white rounded-lg text-center px-2 py-1.5 flex-shrink-0 min-w-[52px]">
            <span class="block font-display text-xl font-bold leading-none"><?php echo h($day); ?></span>
            <span class="text-[0.65rem] font-semibold"><?php echo h($mn); ?></span>
          </div>
          <div class="flex-1 min-w-0">
            <span class="text-xs font-bold px-2 py-0.5 rounded <?php echo h($typeCss); ?> inline-block mb-1">
              <?php echo h($typeLabel); ?>
            </span>
            <div class="text-sm font-semibold text-kma-dark dark:text-gray-200"><?php echo h($hol['title']); ?></div>
            <?php if (!empty($hol['description'])): ?>
            <div class="text-xs text-kma-muted mt-0.5"><?php echo h($hol['description']); ?></div>
            <?php endif; ?>
            <?php if (!empty($hol['duration'])): ?>
            <span class="text-xs bg-kma-bg dark:bg-gray-700 border border-kma-border text-kma-muted px-2 py-0.5 rounded mt-1 inline-block"><?php echo h($hol['duration']); ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach;
    endif; ?>

  </div>
</section>

</main>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-mortarboard-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">২০২৬ শিক্ষাবর্ষে ভর্তি নিন</h2>
    <p class="text-white/80 mb-6 text-sm">সীমিত আসনে ভর্তি চলছে। আজই আবেদন করুন।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-gold"><i class="bi bi-pencil-square"></i> ভর্তির আবেদন করুন</a>
      <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-outline"><i class="bi bi-telephone"></i> যোগাযোগ করুন</a>
    </div>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>