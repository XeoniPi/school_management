<?php
/**
 * KMA — pages/about.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'আমাদের সম্পর্কে | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'বিদ্যালয়ের ইতিহাস, লক্ষ্য-উদ্দেশ্য ও শিক্ষকমণ্ডলী সম্পর্কে জানুন।';

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:280px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-building"></i> বিদ্যালয় পরিচিতি</div>
      <h1 class="font-bn font-bold text-4xl mb-3">আমাদের <em style="font-style:normal;color:#c9a227">সম্পর্কে</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">আমাদের সম্পর্কে</span>
      </nav>
    </div>
  </div>
</header>

<main id="main-content">

<!-- ── History ── -->
<section class="py-20 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid lg:grid-cols-5 gap-12 items-center">
      <div class="lg:col-span-2 reveal">
        <div class="relative">
          <div class="absolute -top-4 -right-4 w-24 h-24 rounded-xl z-0"
               style="background-image:radial-gradient(#e8f4eb 2px,transparent 2px);background-size:14px 14px"></div>
          <img src="../assets/images/about-us.jpeg"
               alt="বিদ্যালয় ভবন" class="relative z-10 w-full rounded-xl shadow-xl object-cover" style="aspect-ratio:4/3" />
          <div class="absolute -bottom-5 left-0 bg-accent text-white rounded-xl px-5 py-3 shadow-md z-10 text-center">
            <div class="font-display text-2xl font-bold text-gold leading-none">২০২৬</div>
            <div class="text-xs mt-0.5">প্রতিষ্ঠা সাল</div>
          </div>
        </div>
      </div>
      <div class="lg:col-span-3 reveal reveal-d1">
        <div class="ornament justify-start mb-3"><span></span><i class="bi bi-clock-history"></i><span></span></div>
        <h2 class="section-title">খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি</h2>
        <p class="text-kma-muted text-sm mb-4">কীভাবে শুরু হয়েছিল এই মহৎ যাত্রার সূচনা</p>
        <p class="text-kma-muted leading-relaxed text-justify text-sm mb-4">
          শিক্ষার আলো ছড়িয়ে দেওয়ার মহৎ লক্ষ্যকে সামনে রেখে <strong class="text-accent">খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি</strong> প্রতিষ্ঠার সিদ্ধান্ত নেওয়া হয় ২৯ অক্টোবর ২০২৫ সালে। ০৩ নভেম্বর ২০২৫ তারিখে প্রস্তাবটি সামাজিক পর্যায়ে উপস্থাপন করা হয়। ০৯ নভেম্বর ২০২৫ তারিখে চূড়ান্ত সিদ্ধান্ত গৃহীত হয় এবং ২০২৬ সাল থেকে একাডেমিক কার্যক্রম শুরু হয়।
        </p>
        <p class="text-kma-muted leading-relaxed text-justify text-sm mb-6">
          প্রতিষ্ঠাতা অ্যাডভোকেট মো. হারুন উর রশীদ হারুন এবং আব্দুর রহমান রকির অক্লান্ত প্রচেষ্টায় আজ এই বিদ্যালয় থেকে মেধাবী শিক্ষার্থীরা উজ্জ্বল ভবিষ্যতের পথে এগিয়ে যাচ্ছে।
        </p>
        <!-- Timeline -->
        <div class="border-l-2 border-kma-border pl-6 space-y-5">
          <?php
          $timeline = [
            ['২০২৬','বিদ্যালয় প্রতিষ্ঠা — প্রায় ৬০ জন শিক্ষার্থী ও ১ জন শিক্ষক নিয়ে যাত্রা শুরু।'],
            ['২০২৭','নতুন ভবন নির্মাণ ও শিক্ষার্থী সংখ্যা ১৫০ ছাড়িয়ে যায়।'],
            ['২০২৮','ইনশা-আল্লাহ্ কম্পিউটার ল্যাব উদ্বোধন করা হবে।'],
          ];
          foreach ($timeline as $tl): ?>
          <div class="relative">
            <div class="absolute -left-[29px] top-1.5 w-3 h-3 rounded-full border-2 border-accent bg-white dark:bg-gray-900"></div>
            <div class="text-xs font-bold text-accent uppercase tracking-wide mb-1"><?php echo h($tl[0]); ?></div>
            <div class="text-sm text-kma-muted"><?php echo h($tl[1]); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Memorial ── -->
<section class="py-20 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid lg:grid-cols-5 gap-12 items-center">
      <div class="lg:col-span-3 reveal">
        <div class="ornament justify-start mb-3"><span></span><i class="bi bi-heart-fill"></i><span></span></div>
        <h2 class="section-title">এক আলোকিত জীবন</h2>
        <p class="text-kma-muted text-sm mb-4">জনাব মরহুম খলিলুল্লাহ খলিল (মেম্বার)</p>
        <p class="text-kma-muted leading-relaxed text-justify text-sm mb-4">
          এই প্রতিষ্ঠানটির নামকরণ করা হয়েছে <strong class="text-accent">জনাব মরহুম খলিলুল্লাহ খলিল (মেম্বার)</strong>-এর নামে। তিনি একজন সৎ, নিষ্ঠাবান ও নির্মল হৃদয়ের মানুষ ছিলেন। ২০১০ সালে রাজনীতিতে প্রবেশ করে চর-জুবলী এলাকার মানুষের জন্য দিন-রাত পরিশ্রম করেছেন।
        </p>
        <p class="text-kma-muted leading-relaxed text-justify text-sm mb-6">
          তাঁর স্বপ্ন ছিল একটি আধুনিক শিক্ষা প্ল্যাটফর্ম তৈরি করা। ২০২৪ সালের ২৪ জানুয়ারি তিনি আমাদের মাঝ থেকে বিদায় নেন। তাঁর স্মৃতি ও অবদানের প্রতি কৃতজ্ঞতা জানাতে তাঁর নামে এই প্রতিষ্ঠান।
        </p>
        <div class="border-l-2 border-kma-border pl-6 space-y-4">
          <div class="relative">
            <div class="absolute -left-[29px] top-1.5 w-3 h-3 rounded-full border-2 border-accent bg-white dark:bg-gray-800"></div>
            <div class="text-xs font-bold text-accent uppercase tracking-wide mb-1">জন্ম</div>
            <div class="text-sm text-kma-muted">১৯৭০ সাল — সুবর্ণচর উপজেলা, নোয়াখালী।</div>
          </div>
          <div class="relative">
            <div class="absolute -left-[29px] top-1.5 w-3 h-3 rounded-full border-2 border-red-400 bg-white dark:bg-gray-800"></div>
            <div class="text-xs font-bold text-red-500 uppercase tracking-wide mb-1">মৃত্যু</div>
            <div class="text-sm text-kma-muted">২৪ জানুয়ারি ২০২৪ — মধ্যম বাগ্যা, চরজুবলী, সুবর্ণচর।</div>
          </div>
        </div>
      </div>
      <div class="lg:col-span-2 reveal reveal-d2">
        <div class="relative">
          <img src="../assets/images/khalilullah(mejubhai).png"
               alt="খলিলুল্লাহ খলিল" class="w-full rounded-xl shadow-xl object-contain bg-gray-100 dark:bg-gray-700" style="aspect-ratio:4/3" />
          <div class="absolute -bottom-4 right-0 bg-kma-dark text-white rounded-xl px-4 py-3 shadow-md text-center text-xs">
            <div class="font-bold text-gold">মৃত্যু সাল</div>
            <div class="mt-0.5">২৪ জানুয়ারি ২০২৪</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Mission Vision Values ── -->
<section class="py-20 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-12 reveal">
      <div class="ornament"><span></span><i class="bi bi-trophy-fill"></i><span></span></div>
      <h2 class="section-title">লক্ষ্য, উদ্দেশ্য ও মূল্যবোধ</h2>
      <p class="text-kma-muted text-sm mt-2">আমাদের শিক্ষাদর্শন যা আমাদের পথ দেখায়</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <!-- Mission -->
      <div class="reveal reveal-d1 rounded-xl p-8 text-white relative overflow-hidden shadow-xl"
           style="background:linear-gradient(135deg,#2e6b3e,#1a4a2a)">
        <div class="absolute top-0 right-0 w-28 h-28 rounded-full bg-white/5 -mr-8 -mt-8"></div>
        <div class="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center text-3xl text-gold mb-5">
          <i class="bi bi-rocket-takeoff-fill"></i>
        </div>
        <h3 class="font-bold text-xl mb-3 text-white">আমাদের লক্ষ্য</h3>
        <p class="text-white/80 text-sm leading-relaxed mb-5">
          প্রতিটি শিশুকে জ্ঞান, দক্ষতা ও নৈতিকতায় সমৃদ্ধ করে একটি আলোকিত প্রজন্ম গড়ে তোলা।
        </p>
        <div class="flex gap-6 pt-4 border-t border-white/20">
          <div><div class="font-display text-2xl font-bold text-gold">১০০+</div><div class="text-white/60 text-xs">শিক্ষার্থী</div></div>
          <div><div class="font-display text-2xl font-bold text-gold">৯৮%</div><div class="text-white/60 text-xs">পাশের হার</div></div>
        </div>
      </div>
      <!-- Vision -->
      <div class="reveal reveal-d2 bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
        <div class="w-16 h-16 rounded-2xl bg-accent-light flex items-center justify-center text-3xl text-accent mb-5">
          <i class="bi bi-eye-fill"></i>
        </div>
        <h3 class="font-bold text-xl mb-3 text-kma-dark dark:text-white">আমাদের উদ্দেশ্য</h3>
        <p class="text-kma-muted text-sm leading-relaxed mb-5">
          অন্তর্ভুক্তিমূলক, সমতাভিত্তিক ও টেকসই শিক্ষাব্যবস্থার মাধ্যমে দায়িত্বশীল নাগরিক তৈরি করা।
        </p>
        <div class="flex flex-wrap gap-3 pt-4 border-t border-kma-border">
          <span class="text-xs font-semibold text-accent flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> আধুনিক পাঠ্যক্রম</span>
          <span class="text-xs font-semibold text-accent flex items-center gap-1"><i class="bi bi-check-circle-fill"></i> সর্বজনীন শিক্ষা</span>
        </div>
      </div>
      <!-- Values -->
      <div class="reveal reveal-d3 rounded-xl p-8 text-white shadow-xl"
           style="background:linear-gradient(135deg,#272727,#1a2a1a)">
        <div class="w-16 h-16 rounded-2xl bg-gold/15 flex items-center justify-center text-3xl text-gold mb-5">
          <i class="bi bi-heart-fill"></i>
        </div>
        <h3 class="font-bold text-xl mb-4 text-white">আমাদের মূল্যবোধ</h3>
        <ul class="space-y-2.5">
          <?php
          $vals = [
            'সততা ও নৈতিকতা',
            'শ্রেষ্ঠত্বের সাধনা',
            'সহমর্মিতা ও মমতা',
            'উদ্ভাবন ও সৃজনশীলতা',
            'অন্তর্ভুক্তি ও সমান সুযোগ',
          ];
          foreach ($vals as $v): ?>
          <li class="flex items-start gap-2 text-white/80 text-sm">
            <i class="bi bi-check2-circle text-gold mt-0.5 flex-shrink-0"></i>
            <span><?php echo h($v); ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ── Faculty ── -->
<section class="py-20 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-12 reveal">
      <div class="ornament"><span></span><i class="bi bi-people-fill"></i><span></span></div>
      <h2 class="section-title">শিক্ষক ও প্রশাসন</h2>
      <p class="text-kma-muted text-sm mt-2">আমাদের অভিজ্ঞ ও নিবেদিতপ্রাণ দল</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $staff = [
        ['https://scontent.fdac41-2.fna.fbcdn.net/v/t39.30808-6/492481134_3660657364174905_3838434338311209740_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=127cfc&_nc_ohc=1PvI5KgYOF4Q7kNvwGstLXv&_nc_zt=23&_nc_ht=scontent.fdac41-2.fna&oh=00_Af4K58VNGcrsmKjQkY11J4f2fOWOk2UJuxM7eozfPeltLA&oe=6A164A07','এডভোকেট মো. হারুনউর রশিদ হারুন','মালিক ও প্রতিষ্ঠাতা','এল.এল.বি, এলএলএম','administration'],
        ['https://scontent.fdac41-2.fna.fbcdn.net/v/t39.30808-6/536010067_3747105478931463_8510761137296571616_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=3tu2VIG-ZBwQ7kNvwEbPr6y&_nc_zt=23&_nc_ht=scontent.fdac41-2.fna&oh=00_Af4ki55NtRtDDFdRNqq25nGdaABagyfQzEvX6I6R3Zog9w&oe=6A164C56','আব্দুর রহমান রকি','মালিক ও প্রতিষ্ঠাতা','বিএসসি ইন সিএসই','administration'],
        ['https://scontent.fdac41-1.fna.fbcdn.net/v/t39.30808-6/514884732_4227713767481204_665926271554994724_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=VELHQy-I3-cQ7kNvwHH9qZZ&_nc_zt=23&_nc_ht=scontent.fdac41-1.fna&oh=00_Af4mPec5T2pmtR5HpiJRDlL7kdTEBGREcNy1NDzGiUVZcw&oe=6A167A89','মো. মহিন উদ্দিন','প্রধান শিক্ষক','বাংলা, ইংরেজি, গণিত, আরবি','teacher'],
        ['https://scontent.fdac41-2.fna.fbcdn.net/v/t1.6435-9/116349197_1220142081666429_8946123292666278940_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=a5f93a&_nc_ohc=B-g483MLNIIQ7kNvwHVtyKT&_nc_zt=23&_nc_ht=scontent.fdac41-2.fna&oh=00_Af5vMrjH6vWmBq9AFXH_6RTEN-cV5_yfZD2-DBlsbMG9mg&oe=6A37E440','মো. আনোয়ার হোসেন পারভেজ','প্রতিষ্ঠাতা','বি.এ (ট্যুরিজম)','administration'],
      ];
      foreach ($staff as $idx => $s): ?>
      <div class="reveal reveal-d<?php echo min($idx+1,5); ?> bg-kma-bg dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-2 transition-all duration-300">
        <div class="relative overflow-hidden" style="aspect-ratio:1">
          <img src="<?php echo h($s[0]); ?>" alt="<?php echo h($s[1]); ?>"
               class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" loading="lazy" />
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-3 pb-3 pt-8">
            <span class="text-xs font-bold bg-gold text-kma-dark px-2 py-0.5 rounded"><?php echo h($s[4] === 'administration' ? 'প্রশাসন' : 'শিক্ষক'); ?></span>
          </div>
        </div>
        <div class="p-4">
          <div class="font-bold text-kma-dark dark:text-white text-sm mb-0.5"><?php echo h($s[1]); ?></div>
          <div class="text-accent text-xs font-semibold mb-2 flex items-center gap-1">
            <i class="bi bi-patch-check-fill"></i> <?php echo h($s[2]); ?>
          </div>
          <div class="text-kma-muted text-xs flex items-start gap-1">
            <i class="bi bi-mortarboard-fill text-accent mt-0.5 flex-shrink-0"></i>
            <?php echo h($s[3]); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal">
    <div class="max-w-2xl mx-auto text-center">
      <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-mortarboard-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
      <h2 class="font-bold mb-3" style="font-size:clamp(1.6rem,3.5vw,2.3rem)">আজই ভর্তির আবেদন করুন</h2>
      <p class="text-white/80 mb-7 text-sm">২০২৫-২৬ শিক্ষাবর্ষে সীমিত আসনে ভর্তি চলছে।</p>
      <div class="flex flex-wrap gap-3 justify-center">
        <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-gold"><i class="bi bi-pencil-square"></i> ভর্তির আবেদন করুন</a>
        <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn-outline"><i class="bi bi-telephone"></i> যোগাযোগ করুন</a>
      </div>
    </div>
  </div>
</section>

</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>