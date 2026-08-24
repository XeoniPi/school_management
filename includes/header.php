<?php
/**
 * KMA — Global Site Header  |  PHP 7.2 compatible
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$site     = getSiteSettings();
$siteName = isset($site['school_name_bn']) ? $site['school_name_bn'] : 'খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি';

if (!isset($pageTitle))     { $pageTitle     = 'KMA | ' . $siteName; }
if (!isset($pageDesc))      { $pageDesc      = 'মানসম্পন্ন প্রাথমিক শিক্ষার আলোকিত প্রতিষ্ঠান'; }
if (!isset($canonicalPath)) { $canonicalPath = ''; }
?>
<!DOCTYPE html>
<html lang="bn" dir="ltr" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo h($pageTitle); ?></title>
  <meta name="description" content="<?php echo h($pageDesc); ?>" />
  <meta name="author"      content="Khalilullah Memorial Academy" />
  <meta name="robots"      content="index, follow" />
  <?php if ($canonicalPath): ?>
  <link rel="canonical" href="https://kma.edu.bd<?php echo h($canonicalPath); ?>" />
  <?php endif; ?>
  <meta property="og:title"       content="<?php echo h($pageTitle); ?>" />
  <meta property="og:description" content="<?php echo h($pageDesc); ?>" />
  <meta property="og:type"        content="website" />
  <meta property="og:locale"      content="bn_BD" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet" />

  <!-- Tailwind CSS v3 Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            accent:  { DEFAULT: '#2e6b3e', light: '#e8f4eb' },
            gold:    { DEFAULT: '#c9a227', light: 'rgba(201,162,39,0.12)' },
            'kma-bg':     '#f6f6e9',
            'kma-dark':   '#272727',
            'kma-muted':  '#6b6b5a',
            'kma-border': '#d8d8c4',
          },
          fontFamily: {
            bn:      ['"Hind Siliguri"', 'system-ui', 'sans-serif'],
            display: ['"Playfair Display"', 'serif'],
          },
        }
      }
    }
  </script>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

  <!-- Site CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/site.css" />

  <!-- Dark-mode flash prevention -->
  <script>
    (function() {
      var t = localStorage.getItem('kma-theme');
      if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      }
    })();
  </script>
</head>
<body class="font-bn bg-kma-bg text-kma-dark dark:bg-gray-900 dark:text-gray-100 overflow-x-hidden transition-colors duration-300">

<!-- Skip link -->
<a href="#main-content"
   class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0
          focus:z-[9999] focus:bg-accent focus:text-white focus:px-4
          focus:py-2 focus:rounded-br-lg focus:font-bold text-sm">
  মূল বিষয়বস্তুতে যান
</a>

<!-- ── TOP BAR ── -->
<div class="hidden md:block bg-accent text-white/90 text-xs py-1.5"
     role="complementary" aria-label="যোগাযোগ তথ্য">
  <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
    <div class="flex items-center gap-4">
      <?php $phone = isset($site['school_phone']) ? $site['school_phone'] : '+880 1866-751015'; ?>
      <?php $email = isset($site['school_email']) ? $site['school_email'] : 'info@kma.edu.bd'; ?>
      <a href="tel:<?php echo h($phone); ?>"
         class="flex items-center gap-1 hover:text-gold transition-colors">
        <i class="bi bi-telephone-fill"></i> <?php echo h($phone); ?>
      </a>
      <span class="opacity-40">|</span>
      <a href="mailto:<?php echo h($email); ?>"
         class="flex items-center gap-1 hover:text-gold transition-colors">
        <i class="bi bi-envelope-fill"></i> <?php echo h($email); ?>
      </a>
    </div>
    <span class="font-semibold tracking-wide">Modern Education, Timeless Values</span>
    <div class="flex items-center gap-1 text-white/80">
      <i class="bi bi-clock"></i>
      <?php $hours = isset($site['office_hours']) ? $site['office_hours'] : 'শনি–বৃহস্পতি: সকাল ৮:০০ – দুপুর ১:৩০'; ?>
      <span><?php echo h($hours); ?></span>
    </div>
  </div>
</div>

<!-- ── NAVBAR ── -->
<?php
$currentPath = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
$navItems = [
  ['href' => BASE_URL . '/index.php',           'label' => 'হোম',             'key' => 'index'],
  ['href' => BASE_URL . '/pages/about.php',     'label' => 'আমাদের সম্পর্কে', 'key' => 'about'],
  ['href' => BASE_URL . '/pages/academics.php', 'label' => 'একাডেমিক',        'key' => 'academics'],
  ['href' => BASE_URL . '/pages/admission.php', 'label' => 'ভর্তি তথ্য',      'key' => 'admission'],
  ['href' => BASE_URL . '/pages/notices.php',   'label' => 'নোটিশ',           'key' => 'notices'],
  ['href' => BASE_URL . '/pages/contact.php',   'label' => 'যোগাযোগ',         'key' => 'contact'],
];
?>
<nav id="mainNav"
     class="sticky top-0 z-[1050] bg-kma-dark dark:bg-gray-950
            shadow-[0_2px_16px_rgba(0,0,0,.35)] transition-colors duration-300"
     aria-label="প্রধান নেভিগেশন">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">

    <!-- Brand -->
    <a href="<?php echo BASE_URL; ?>/index.php"
       aria-label="<?php echo h($siteName); ?> – হোম"
       class="flex items-center gap-3 py-2.5 flex-shrink-0">
      <img src="https://placehold.co/56x56/2e6b3e/ffffff?text=KMA&font=open-sans"
           alt="KMA লোগো" width="56" height="56"
           class="w-12 h-12 md:w-14 md:h-14 rounded-full object-cover border-2 border-gold" />
      <div class="leading-tight">
        <div class="text-white font-bold text-sm md:text-base leading-snug">
          <?php echo h($siteName); ?>
          <span class="text-gold text-xs">(KMA)</span>
        </div>
        <div class="text-gold text-[0.65rem] uppercase tracking-widest hidden sm:block">
          Khalilullah Memorial Academy
        </div>
      </div>
    </a>

    <!-- Desktop Nav -->
    <ul class="hidden lg:flex items-center" role="list">
      <?php foreach ($navItems as $item):
        $active = (strpos($currentPath, $item['key']) !== false);
      ?>
      <li role="listitem">
        <a href="<?php echo h($item['href']); ?>"
           class="block px-3.5 py-[22px] text-[0.92rem] font-medium border-b-[3px] transition-colors duration-200
                  <?php echo $active
                    ? 'border-gold text-gold'
                    : 'border-transparent text-white/85 hover:text-gold hover:border-gold'; ?>"
           <?php echo $active ? 'aria-current="page"' : ''; ?>>
          <?php echo h($item['label']); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <!-- Dark mode toggle -->
      <button id="themeToggle" aria-label="ডার্ক/লাইট মোড টগল"
              class="w-9 h-9 flex items-center justify-center rounded-full
                     border border-white/20 text-white/80 hover:text-gold
                     hover:border-gold transition-colors duration-200">
        <i class="bi bi-sun-fill dark:hidden text-base"></i>
        <i class="bi bi-moon-fill hidden dark:block text-base"></i>
      </button>

      <!-- Hamburger (mobile) -->
      <button id="menuToggle"
              aria-label="মেনু খুলুন" aria-expanded="false" aria-controls="mobileMenu"
              class="lg:hidden w-10 h-10 flex flex-col justify-center items-center gap-[5px]">
        <span class="w-6 h-0.5 bg-white/80 rounded transition-all duration-300" id="hb1"></span>
        <span class="w-6 h-0.5 bg-white/80 rounded transition-all duration-300" id="hb2"></span>
        <span class="w-6 h-0.5 bg-white/80 rounded transition-all duration-300" id="hb3"></span>
      </button>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobileMenu"
       class="lg:hidden hidden bg-kma-dark dark:bg-gray-950
              border-t border-white/10 px-4 pb-4 transition-all duration-300">
    <ul class="flex flex-col gap-1 pt-2" role="list">
      <?php foreach ($navItems as $item):
        $active = (strpos($currentPath, $item['key']) !== false);
      ?>
      <li>
        <a href="<?php echo h($item['href']); ?>"
           class="block py-2.5 px-3 rounded text-sm font-medium transition-colors
                  <?php echo $active
                    ? 'bg-accent text-white'
                    : 'text-white/80 hover:bg-white/10 hover:text-white'; ?>">
          <?php echo h($item['label']); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>