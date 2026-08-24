<?php
/**
 * KMA — academy/dress-code.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'ড্রেস কোড | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'বিদ্যালয়ের পোশাক বিধিমালা, আচরণবিধি ও সাধারণ নির্দেশিকা।';

$pdo  = getDB();
$site = getSiteSettings();

/* Download PDF for dress code */
$dlStmt = $pdo->prepare(
    "SELECT * FROM downloads WHERE is_active=1 AND category='other' ORDER BY created_at DESC LIMIT 3"
);
$dlStmt->execute();
$dlFiles = $dlStmt->fetchAll(PDO::FETCH_ASSOC);

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:270px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-person-badge"></i> পোশাক বিধিমালা</div>
      <h1 class="font-bn font-bold text-4xl mb-3">স্কুল <em style="font-style:normal;color:#c9a227">ড্রেস কোড</em></h1>
      <p class="text-white/80 text-sm max-w-lg mb-3">বিদ্যালয়ের পোশাক বিধিমালা, আচরণবিধি ও সাধারণ নির্দেশিকা।</p>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <a href="<?php echo BASE_URL; ?>/pages/academics.php" class="text-white/70 hover:text-gold">একাডেমিক</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">ড্রেস কোড</span>
      </nav>
      <div class="flex flex-wrap gap-3 mt-5">
        <div class="ph-badge"><i class="bi bi-palette2"></i> কালো ও সাদা থিম</div>
        <div class="ph-badge"><i class="bi bi-people"></i> ছেলে ও মেয়ে উভয়</div>
        <div class="ph-badge"><i class="bi bi-check2-circle"></i> বাধ্যতামূলক</div>
      </div>
    </div>
  </div>
</header>

<main id="main-content">

<!-- ══ DRESS CODE SECTION ══ -->
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">

    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-person-badge-fill"></i><span></span></div>
      <h2 class="section-title">পোশাক বিধিমালা</h2>
      <p class="text-kma-muted text-sm mt-1">বিদ্যালয়ে প্রতিদিন নির্ধারিত পোশাকে আসা বাধ্যতামূলক</p>
    </div>

    <!-- B&W theme banner -->
    <div class="bg-kma-dark rounded-2xl p-6 flex items-center gap-6 flex-wrap mb-10 shadow-lg reveal">
      <div class="flex gap-3 flex-shrink-0" aria-label="অনুমোদিত রঙের নমুনা">
        <div class="w-14 h-14 rounded-full bg-black border-2 border-gray-600 flex items-center justify-center shadow-lg">
          <span class="text-white text-xs font-bold">কালো</span>
        </div>
        <div class="w-14 h-14 rounded-full bg-white border-2 border-gray-300 flex items-center justify-center shadow-lg">
          <span class="text-kma-dark text-xs font-bold">সাদা</span>
        </div>
        <div class="w-14 h-14 rounded-full bg-accent border-2 border-gold flex items-center justify-center shadow-lg">
          <span class="text-white text-xs font-bold">KMA</span>
        </div>
      </div>
      <div>
        <h3 class="text-white font-bold text-lg mb-1"><i class="bi bi-palette2 mr-2 text-gold"></i>Black &amp; White ড্রেস কোড</h3>
        <p class="text-white/70 text-sm">খলিলুল্লাহ মেমোরিয়াল একাডেমির অফিসিয়াল পোশাক সম্পূর্ণ কালো ও সাদা রঙের সমন্বয়ে তৈরি। বুকের বাম পাশে KMA মনোগ্রাম সংবলিত পোশাক পরিধান করা বাধ্যতামূলক।</p>
      </div>
    </div>

    <!-- Boys & Girls cards -->
    <div class="grid md:grid-cols-2 gap-6 mb-10">

      <!-- Boys -->
      <article class="bg-kma-bg dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all reveal reveal-d1" aria-label="ছেলেদের পোশাক বিধি">
        <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-500 flex flex-col items-center justify-center py-10 px-6 relative" style="min-height:260px">
          <span class="absolute top-3 left-3 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">ছেলেদের পোশাক</span>
          <!-- Inline SVG figure -->
          <svg width="110" height="185" viewBox="0 0 110 185" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="ছেলেদের স্কুল ড্রেস">
            <circle cx="55" cy="24" r="20" fill="#e8d5c4" stroke="#ccc" stroke-width="1.5"/>
            <path d="M35 22 Q37 8 55 6 Q73 8 75 22" fill="#2a1a0a"/>
            <path d="M20 62 L14 46 L32 38 L55 50 L78 38 L96 46 L90 62 L84 104 L26 104 Z" fill="#ffffff" stroke="#bbb" stroke-width="1.5"/>
            <path d="M47 38 L55 56 L63 38" fill="none" stroke="#ccc" stroke-width="1.2"/>
            <path d="M52 42 L55 80 L58 42 Z" fill="#111"/>
            <circle cx="55" cy="66" r="1.8" fill="#ddd"/>
            <circle cx="55" cy="75" r="1.8" fill="#ddd"/>
            <rect x="61" y="52" width="16" height="10" rx="2.5" fill="#2e6b3e"/>
            <text x="69" y="60" font-size="5.5" fill="#fff" text-anchor="middle" font-family="sans-serif" font-weight="bold">KMA</text>
            <rect x="26" y="102" width="58" height="5" rx="2" fill="#333"/>
            <rect x="51" y="102" width="8" height="5" rx="1" fill="#c9a227"/>
            <path d="M26 107 L31 178 L51 178 L55 140 L59 178 L79 178 L84 107 Z" fill="#1a1a1a" stroke="#333" stroke-width="1"/>
            <line x1="39" y1="112" x2="36" y2="177" stroke="#2a2a2a" stroke-width="1"/>
            <line x1="71" y1="112" x2="74" y2="177" stroke="#2a2a2a" stroke-width="1"/>
            <rect x="31" y="175" width="20" height="7" rx="3" fill="#eee" stroke="#ccc" stroke-width="1"/>
            <rect x="59" y="175" width="20" height="7" rx="3" fill="#eee" stroke="#ccc" stroke-width="1"/>
            <ellipse cx="41" cy="184" rx="12" ry="4.5" fill="#111"/>
            <ellipse cx="69" cy="184" rx="12" ry="4.5" fill="#111"/>
          </svg>
          <!-- Color swatches -->
          <div class="flex gap-3 mt-4" role="list" aria-label="পোশাকের রঙ">
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-white border-2 border-gray-300 shadow"></div>সাদা শার্ট
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-[#1a1a1a] border-2 border-gray-500 shadow"></div>কালো প্যান্ট
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-[#111] border-2 border-gray-600 shadow"></div>কালো জুতা
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-gray-300 shadow"></div>সাদা মোজা
            </div>
          </div>
        </div>

        <div class="p-5">
          <h3 class="font-bold text-kma-dark dark:text-white text-base mb-1">ছেলেদের পোশাক</h3>
          <p class="text-accent text-xs font-semibold mb-4 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-accent inline-block"></span> কালো ও সাদা ড্রেস কোড
          </p>

          <!-- Seasonal tabs -->
          <div class="flex gap-2 mb-4" role="tablist">
            <button class="season-tab flex-1 py-2 rounded-lg text-xs font-bold border transition-all bg-accent text-white border-accent"
                    role="tab" aria-selected="true" data-season="boys-summer">গ্রীষ্মকাল</button>
            <button class="season-tab flex-1 py-2 rounded-lg text-xs font-bold border transition-all bg-white dark:bg-gray-600 text-kma-muted border-kma-border dark:border-gray-500 hover:border-accent hover:text-accent"
                    role="tab" aria-selected="false" data-season="boys-winter">শীতকাল</button>
          </div>

          <div id="boys-summer" class="season-panel space-y-2">
            <?php
            $boysS = [
              [true,  'সাদা ফুল স্লিভ শার্ট (বুকে KMA মনোগ্রাম)'],
              [true,  'কালো ফ্ল্যাট ফ্রন্ট প্যান্ট, চামড়ার বেল্ট সহ'],
              [true,  'কালো ও সাদা টাই (বিদ্যালয় থেকে সংগ্রহ)'],
              [true,  'কালো চামড়ার জুতা ও সাদা মোজা'],
              [false, 'রঙিন পোশাক, স্পোর্টস শু বা স্যান্ডেল নিষিদ্ধ'],
              [false, 'হাফ স্লিভ শার্ট গ্রহণযোগ্য নয়'],
            ];
            foreach ($boysS as $r): ?>
            <div class="flex items-start gap-2 text-sm text-kma-muted">
              <i class="bi <?php echo $r[0] ? 'bi-check-circle-fill text-accent' : 'bi-x-circle-fill text-red-500'; ?> mt-0.5 flex-shrink-0"></i>
              <span><?php echo h($r[1]); ?></span>
            </div>
            <?php endforeach; ?>
          </div>

          <div id="boys-winter" class="season-panel space-y-2" style="display:none">
            <?php
            $boysW = [
              [true,  'সাদা শার্টের উপর কালো V-neck সোয়েটার বা ব্লেজার'],
              [true,  'কালো ফুল স্লিভ শার্টের অনুমতি আছে শীতকালে'],
              [true,  'কালো প্যান্ট, টাই ও জুতা একই থাকবে'],
              [null,  'সোয়েটারে বিদ্যালয়ের মনোগ্রাম থাকলে ভালো'],
              [false, 'রঙিন সোয়েটার বা জ্যাকেট পরিধান নিষেধ'],
            ];
            foreach ($boysW as $r): ?>
            <div class="flex items-start gap-2 text-sm text-kma-muted">
              <i class="bi <?php echo $r[0]===true ? 'bi-check-circle-fill text-accent' : ($r[0]===false ? 'bi-x-circle-fill text-red-500' : 'bi-info-circle-fill text-gold'); ?> mt-0.5 flex-shrink-0"></i>
              <span><?php echo h($r[1]); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </article>

      <!-- Girls -->
      <article class="bg-kma-bg dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all reveal reveal-d2" aria-label="মেয়েদের পোশাক বিধি">
        <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-500 flex flex-col items-center justify-center py-10 px-6 relative" style="min-height:260px">
          <span class="absolute top-3 left-3 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">মেয়েদের পোশাক</span>
          <svg width="110" height="185" viewBox="0 0 110 185" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="মেয়েদের স্কুল ড্রেস">
            <circle cx="55" cy="24" r="20" fill="#e8d5c4" stroke="#ccc" stroke-width="1.5"/>
            <path d="M35 22 Q37 4 55 4 Q73 4 75 22 Q68 12 55 14 Q42 12 35 22Z" fill="#1a0a0a"/>
            <path d="M75 22 Q83 32 81 50 Q73 47 69 38" fill="#1a0a0a"/>
            <path d="M35 22 Q27 32 29 50 Q37 47 41 38" fill="#1a0a0a"/>
            <path d="M20 68 L14 50 L32 42 L55 52 L78 42 L96 50 L90 68 L86 115 L24 115 Z" fill="#ffffff" stroke="#bbb" stroke-width="1.5"/>
            <ellipse cx="55" cy="48" rx="9" ry="6" fill="none" stroke="#bbb" stroke-width="1.2"/>
            <rect x="62" y="56" width="16" height="10" rx="2.5" fill="#2e6b3e"/>
            <text x="70" y="64" font-size="5.5" fill="#fff" text-anchor="middle" font-family="sans-serif" font-weight="bold">KMA</text>
            <path d="M18 50 Q55 42 92 50 L90 72 Q55 62 20 72 Z" fill="#111" opacity=".88"/>
            <path d="M30 115 L25 178 L47 178 L55 140 L63 178 L85 178 L80 115 Z" fill="#f0f0f0" stroke="#ccc" stroke-width="1"/>
            <rect x="25" y="175" width="22" height="7" rx="3" fill="#eee" stroke="#ccc" stroke-width="1"/>
            <rect x="63" y="175" width="22" height="7" rx="3" fill="#eee" stroke="#ccc" stroke-width="1"/>
            <ellipse cx="36" cy="184" rx="11" ry="4.5" fill="#111"/>
            <ellipse cx="74" cy="184" rx="11" ry="4.5" fill="#111"/>
          </svg>
          <div class="flex gap-3 mt-4" role="list" aria-label="পোশাকের রঙ">
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-white border-2 border-gray-300 shadow"></div>সাদা কামিজ
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-[#111] border-2 border-gray-600 shadow"></div>কালো ওড়না
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-gray-300 shadow"></div>সাদা সালোয়ার
            </div>
            <div class="flex flex-col items-center gap-1 text-[0.65rem] text-kma-muted dark:text-gray-300" role="listitem">
              <div class="w-8 h-8 rounded-full bg-[#111] border-2 border-gray-600 shadow"></div>কালো জুতা
            </div>
          </div>
        </div>

        <div class="p-5">
          <h3 class="font-bold text-kma-dark dark:text-white text-base mb-1">মেয়েদের পোশাক</h3>
          <p class="text-accent text-xs font-semibold mb-4 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-accent inline-block"></span> কালো ও সাদা ড্রেস কোড
          </p>

          <div class="flex gap-2 mb-4" role="tablist">
            <button class="season-tab flex-1 py-2 rounded-lg text-xs font-bold border transition-all bg-accent text-white border-accent"
                    role="tab" aria-selected="true" data-season="girls-summer">গ্রীষ্মকাল</button>
            <button class="season-tab flex-1 py-2 rounded-lg text-xs font-bold border transition-all bg-white dark:bg-gray-600 text-kma-muted border-kma-border dark:border-gray-500 hover:border-accent hover:text-accent"
                    role="tab" aria-selected="false" data-season="girls-winter">শীতকাল</button>
          </div>

          <div id="girls-summer" class="season-panel space-y-2">
            <?php
            $girlsS = [
              [true,  'সাদা সালোয়ার-কামিজ (বুকে KMA মনোগ্রাম)'],
              [true,  'কালো ওড়না (মাথা ঢেকে পরিধান করতে হবে)'],
              [true,  'কালো চামড়ার সমতল জুতা ও সাদা মোজা'],
              [true,  'চুল পরিষ্কার ও পিছনে বাঁধা থাকতে হবে'],
              [false, 'অতিরিক্ত অলঙ্কার, মেকআপ বা রঙিন পোশাক নিষিদ্ধ'],
              [false, 'নেলপলিশ বা হাতের অলঙ্কার ব্যবহার নিষিদ্ধ'],
            ];
            foreach ($girlsS as $r): ?>
            <div class="flex items-start gap-2 text-sm text-kma-muted">
              <i class="bi <?php echo $r[0] ? 'bi-check-circle-fill text-accent' : 'bi-x-circle-fill text-red-500'; ?> mt-0.5 flex-shrink-0"></i>
              <span><?php echo h($r[1]); ?></span>
            </div>
            <?php endforeach; ?>
          </div>

          <div id="girls-winter" class="season-panel space-y-2" style="display:none">
            <?php
            $girlsW = [
              [true,  'সাদা কামিজের উপর কালো কার্ডিগান বা সোয়েটার'],
              [true,  'কালো ওড়না ও সাদা সালোয়ার একই থাকবে'],
              [true,  'কালো মাফলার বা স্কার্ফ ব্যবহার করা যাবে'],
              [null,  'শীতকালেও মাথায় ওড়না রাখতে হবে'],
              [false, 'রঙিন কার্ডিগান বা জ্যাকেট পরিধান নিষেধ'],
            ];
            foreach ($girlsW as $r): ?>
            <div class="flex items-start gap-2 text-sm text-kma-muted">
              <i class="bi <?php echo $r[0]===true ? 'bi-check-circle-fill text-accent' : ($r[0]===false ? 'bi-x-circle-fill text-red-500' : 'bi-info-circle-fill text-gold'); ?> mt-0.5 flex-shrink-0"></i>
              <span><?php echo h($r[1]); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </article>

    </div><!-- /dress cards -->

    <div class="bg-gold/10 border-l-4 border-gold rounded-xl p-4 flex items-start gap-3 text-sm text-kma-muted reveal">
      <i class="bi bi-info-circle-fill text-gold mt-0.5 flex-shrink-0 text-lg"></i>
      <span>বিদ্যালয়ের পোশাক অফিসিয়াল সরবরাহকারী থেকে সংগ্রহ করা বাধ্যতামূলক নয়, তবে KMA মনোগ্রাম সহ নির্ধারিত ডিজাইনের পোশাক পরতে হবে। পোশাক অনুমোদনের জন্য ভর্তি অফিসে নমুনা দেখুন।</span>
    </div>
  </div>
</section>

<!-- ══ ACCESSORIES GUIDE ══ -->
<section class="py-12 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-bag-fill"></i><span></span></div>
      <h2 class="section-title">আনুষঙ্গিক সামগ্রী নির্দেশিকা</h2>
      <p class="text-kma-muted text-sm mt-1">কী পরবেন এবং কী পরবেন না</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 reveal">
      <?php
      $accs = [
        ['bi-bag-fill',      'bg-accent-light text-accent', 'স্কুল ব্যাগ',      'যেকোনো রঙ',      'বেশি বড় নয়'],
        ['bi-watch',         'bg-gold/15 text-yellow-700',  'ঘড়ি',              'সাদামাটা ঘড়ি',   'স্মার্টওয়াচ নয়'],
        ['bi-eyeglasses',    'bg-blue-100 text-blue-600',   'চশমা',              'প্রেসক্রিপশন',    'রোদচশমা নয়'],
        ['bi-phone-fill',    'bg-red-100 text-red-500',     'মোবাইল ফোন',       'বন্ধ রাখুন',     'ক্লাসে নিষিদ্ধ'],
        ['bi-gem',           'bg-purple-100 text-purple-600','গহনা',             'ছোট দুল (মেয়ে)', 'বড় গহনা নয়'],
        ['bi-card-text',     'bg-accent-light text-accent', 'পরিচয়পত্র',        'সবসময় পরিধান',  'ছাড়া আসা নিষেধ'],
      ];
      foreach ($accs as $acc): ?>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm text-center hover:shadow-md hover:-translate-y-1 transition-all border-b-4 border-transparent hover:border-accent">
        <div class="w-12 h-12 rounded-full <?php echo h($acc[1]); ?> flex items-center justify-center mx-auto mb-3 text-lg">
          <i class="bi <?php echo h($acc[0]); ?>"></i>
        </div>
        <h4 class="font-bold text-xs text-kma-dark dark:text-white mb-2"><?php echo h($acc[2]); ?></h4>
        <p class="text-[0.65rem] text-green-600 font-semibold flex items-center justify-center gap-1">
          <i class="bi bi-check-circle-fill"></i><?php echo h($acc[3]); ?>
        </p>
        <p class="text-[0.65rem] text-red-500 flex items-center justify-center gap-1 mt-0.5">
          <i class="bi bi-x-circle-fill"></i><?php echo h($acc[4]); ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ID card section -->
    <div class="grid lg:grid-cols-2 gap-6 mt-10 reveal">
      <!-- Mock ID card -->
      <div class="bg-kma-dark rounded-2xl p-6 shadow-lg">
        <h3 class="text-white font-bold mb-4"><i class="bi bi-card-text text-gold mr-2"></i>পরিচয়পত্র (ID Card) বহন বাধ্যতামূলক</h3>
        <div class="bg-white rounded-xl overflow-hidden shadow-xl max-w-[260px] mx-auto">
          <div class="bg-accent px-4 py-3 flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-bold">KMA</div>
            <span class="text-white text-xs font-bold">খলিলুল্লাহ মেমোরিয়াল একাডেমি</span>
          </div>
          <div class="p-3 flex gap-3">
            <div class="w-16 h-16 rounded-lg bg-kma-bg border-2 border-kma-border flex items-center justify-center text-kma-muted flex-shrink-0">
              <i class="bi bi-person-fill text-3xl"></i>
            </div>
            <div>
              <div class="font-bold text-sm text-kma-dark">শিক্ষার্থীর নাম</div>
              <div class="text-xs text-kma-muted">শ্রেণি: পঞ্চম · রোল: ০১</div>
              <div class="text-xs text-accent font-semibold mt-1">Session: 2025</div>
            </div>
          </div>
          <div class="px-3 pb-3 flex gap-0.5 justify-center">
            <?php
            $bwidths = [3,2,3,1.5,3,2.5,1.5,3,2,3,1.5,2.5,3,2,3];
            foreach ($bwidths as $bw): ?>
            <div style="width:<?php echo $bw; ?>px;height:18px;background:#272727;border-radius:1px"></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- ID rules -->
      <div class="flex flex-col justify-center space-y-3">
        <?php
        $idRules = [
          [true,  'প্রতিদিন বিদ্যালয়ে আসার সময় আইডি কার্ড গলায় ঝুলিয়ে আসতে হবে।'],
          [true,  'হারিয়ে গেলে ৫০ টাকা ফি দিয়ে অফিস থেকে নতুন কার্ড সংগ্রহ করতে হবে।'],
          [false, 'আইডি কার্ড ছাড়া বিদ্যালয়ে প্রবেশের অনুমতি নেই।'],
          [false, 'অন্যের আইডি কার্ড ব্যবহার করা শাস্তিযোগ্য অপরাধ।'],
        ];
        foreach ($idRules as $r): ?>
        <div class="flex items-start gap-3 bg-kma-bg dark:bg-gray-700 rounded-xl p-4">
          <i class="bi <?php echo $r[0] ? 'bi-check-circle-fill text-accent' : 'bi-x-circle-fill text-red-500'; ?> text-lg flex-shrink-0 mt-0.5"></i>
          <p class="text-sm text-kma-muted"><?php echo h($r[1]); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ══ CODE OF CONDUCT ══ -->
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-shield-check-fill"></i><span></span></div>
      <h2 class="section-title">আচরণবিধি (Code of Conduct)</h2>
      <p class="text-kma-muted text-sm mt-1">বিদ্যালয়ে মেনে চলতে হবে এমন নিয়মকানুন</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 reveal">
      <?php
      $conducts = [
        ['bi-mortarboard-fill','bg-accent-light text-accent','','শ্রেণিকক্ষ আচরণ','সময়মতো শ্রেণিকক্ষে উপস্থিত থাকতে হবে। শিক্ষক পাঠদান করার সময় মনোযোগ দিয়ে শুনতে হবে। অনুমতি ছাড়া কথা বলা বা আসন ত্যাগ করা যাবে না।'],
        ['bi-clock-fill','bg-gold/15 text-yellow-700','border-gold','সময়ানুবর্তিতা','বিদ্যালয় শুরুর ১০ মিনিট আগে উপস্থিত থাকা বাধ্যতামূলক। বিনা কারণে অনুপস্থিতি গ্রহণযোগ্য নয়।'],
        ['bi-person-heart','bg-accent-light text-accent','','শ্রদ্ধাবোধ','সকল শিক্ষক ও কর্মকর্তাকে যথাযথ সম্মান প্রদর্শন করতে হবে। সালাম বা আদাব দিয়ে শিক্ষকের মনোযোগ আকর্ষণ করতে হবে।'],
        ['bi-ban','bg-red-100 text-red-500','border-red-400','নিষিদ্ধ কার্যকলাপ','বিদ্যালয় প্রাঙ্গণে মোবাইল ফোন ব্যবহার, মারামারি, অশ্লীল ভাষা প্রয়োগ এবং সম্পত্তির ক্ষতিসাধন সম্পূর্ণ নিষিদ্ধ।'],
        ['bi-stars','bg-accent-light text-accent','','পরিচ্ছন্নতা','শ্রেণিকক্ষ ও বিদ্যালয় প্রাঙ্গণ সর্বদা পরিষ্কার রাখতে হবে। যত্রতত্র ময়লা ফেলা নিষিদ্ধ।'],
        ['bi-pencil-square','bg-gold/15 text-yellow-700','border-gold','হোমওয়ার্ক ও প্রস্তুতি','প্রতিদিনের হোমওয়ার্ক সময়মতো সম্পন্ন করতে হবে। প্রয়োজনীয় বই, খাতা ও শিক্ষা উপকরণ প্রতিদিন সাথে আনতে হবে।'],
        ['bi-people-fill','bg-accent-light text-accent','','সহপাঠীর প্রতি আচরণ','সকল সহপাঠীর সাথে বন্ধুত্বপূর্ণ আচরণ করতে হবে। কাউকে উপহাস বা বৈষম্যমূলক আচরণ করা শাস্তিযোগ্য।'],
        ['bi-building','bg-red-100 text-red-500','border-red-400','বিদ্যালয় সম্পদ','বিদ্যালয়ের সম্পদ ও আসবাবপত্র সযত্নে ব্যবহার করতে হবে। ক্ষতি করলে ক্ষতিপূরণ দিতে হবে।'],
      ];
      foreach ($conducts as $c):
        $borderCls = !empty($c[2]) ? 'border-l-4 '.$c[2] : 'border-l-4 border-accent';
      ?>
      <div class="bg-kma-bg dark:bg-gray-700 rounded-xl p-5 shadow-sm <?php echo $borderCls; ?> hover:shadow-md hover:translate-x-1 transition-all">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-9 h-9 rounded-lg <?php echo h($c[1]); ?> flex items-center justify-center flex-shrink-0">
            <i class="bi <?php echo h($c[0]); ?> text-sm"></i>
          </div>
          <h4 class="font-bold text-sm text-kma-dark dark:text-white"><?php echo h($c[3]); ?></h4>
        </div>
        <p class="text-xs text-kma-muted leading-relaxed"><?php echo h($c[4]); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ REWARD & PENALTY ══ -->
<section class="py-12 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-award-fill"></i><span></span></div>
      <h2 class="section-title">পুরস্কার ও শাস্তির বিধান</h2>
      <p class="text-kma-muted text-sm mt-1">ভালো আচরণ পুরস্কৃত, খারাপ আচরণ শাস্তিযোগ্য</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6 reveal">
      <!-- Rewards -->
      <div class="bg-gradient-to-br from-accent to-[#1a4a2a] rounded-2xl p-6 text-white shadow-md">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2"><i class="bi bi-trophy-fill text-gold"></i> পুরস্কার (Rewards)</h3>
        <?php
        $rewards = [
          'নিয়মিত উপস্থিতি ও সময়মতো আসার জন্য মাসিক "সেরা শিক্ষার্থী" পুরস্কার।',
          'পরিষ্কার ও সুশৃঙ্খল পোশাকের জন্য সাপ্তাহিক প্রশংসাপত্র।',
          'পরীক্ষায় সর্বোচ্চ নম্বর পেলে বার্ষিক পুরস্কার বিতরণীতে সম্মানিত করা হবে।',
          'সামাজিক দায়বদ্ধতামূলক কাজের জন্য বিশেষ সনদপত্র প্রদান।',
          'সম্পূর্ণ শিক্ষাবর্ষে অনুপস্থিতি শূন্য হলে "পারফেক্ট অ্যাটেনডেন্স" পুরস্কার।',
        ];
        foreach ($rewards as $rw): ?>
        <div class="flex items-start gap-2 mb-3 text-sm text-white/85">
          <i class="bi bi-star-fill text-gold mt-0.5 flex-shrink-0"></i>
          <span><?php echo h($rw); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <!-- Penalties -->
      <div class="bg-white dark:bg-gray-800 border-2 border-kma-border dark:border-gray-600 rounded-2xl p-6 shadow-md">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-kma-dark dark:text-white">
          <i class="bi bi-exclamation-triangle-fill text-red-500"></i> শাস্তির বিধান (Penalties)
        </h3>
        <?php
        $penalties = [
          'নির্ধারিত পোশাক না পরলে প্রথমবার সতর্কতা, দ্বিতীয়বার অভিভাবককে জানানো হবে।',
          'হোমওয়ার্ক না করলে বিশেষ ক্লাসে অতিরিক্ত কাজ করতে হবে।',
          'বারবার দেরিতে আসলে অভিভাবক সমন ও লিখিত কারণ দর্শানো।',
          'মোবাইল ফোন ক্লাসে ব্যবহার করলে ফোন জব্দ ও অভিভাবককে ডাকা।',
          'গুরুতর অপরাধে সাময়িক বা স্থায়ী বহিষ্কারের বিধান রয়েছে।',
        ];
        foreach ($penalties as $pen): ?>
        <div class="flex items-start gap-2 mb-3 text-sm text-kma-muted">
          <i class="bi bi-exclamation-circle-fill text-red-500 mt-0.5 flex-shrink-0"></i>
          <span><?php echo h($pen); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

</main>

<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-person-badge-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">অন্য একাডেমিক তথ্য দেখুন</h2>
    <div class="flex flex-wrap gap-3 justify-center mt-5">
      <a href="<?php echo BASE_URL; ?>/academy/class-routine.php"   class="btn-gold"><i class="bi bi-calendar-week"></i> ক্লাস রুটিন</a>
      <a href="<?php echo BASE_URL; ?>/academy/exam-schedule.php"   class="btn-outline"><i class="bi bi-clipboard-check"></i> পরীক্ষার সময়সূচি</a>
    </div>
  </div>
</section>

<script>
/* Seasonal tabs — independent per card */
document.querySelectorAll('.season-tab').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var card = btn.closest('article');
    card.querySelectorAll('.season-tab').forEach(function(t) {
      t.classList.remove('bg-accent','text-white','border-accent');
      t.classList.add('bg-white','dark:bg-gray-600','text-kma-muted','border-kma-border');
      t.setAttribute('aria-selected','false');
    });
    btn.classList.add('bg-accent','text-white','border-accent');
    btn.classList.remove('bg-white','dark:bg-gray-600','text-kma-muted','border-kma-border');
    btn.setAttribute('aria-selected','true');
    var target = btn.dataset.season;
    card.querySelectorAll('.season-panel').forEach(function(p) {
      p.style.display = p.id === target ? 'block' : 'none';
    });
  });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>