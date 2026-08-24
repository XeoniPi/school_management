<?php
/**
 * KMA admin layout header — included by every admin page.
 * Requires: $pageTitle (string), session already checked via requireAdminLogin().
 */
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$adminRole = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '';
$currentAdminPage = isset($currentAdminPage) ? $currentAdminPage : '';

$navItems = [
  ['dashboard',  BASE_URL.'/admin/dashboard.php',        'bi-speedometer2',     'ড্যাশবোর্ড'],
  ['notices',    BASE_URL.'/admin/views/notices.php',    'bi-bell-fill',        'নোটিশ'],
  ['admissions', BASE_URL.'/admin/views/admissions.php', 'bi-person-plus-fill', 'ভর্তি আবেদন'],
  ['classes',    BASE_URL.'/admin/views/classes.php',    'bi-grid-3x3-gap-fill','শ্রেণি ও বিষয়'],
  ['holidays',   BASE_URL.'/admin/views/holidays.php',   'bi-calendar3',        'ছুটির তালিকা'],
  ['downloads',  BASE_URL.'/admin/views/downloads.php',  'bi-download',         'ডাউনলোড'],
  ['gallery',    BASE_URL.'/admin/views/gallery.php',    'bi-images',           'গ্যালারি'],
  ['settings',   BASE_URL.'/admin/views/settings.php',   'bi-gear-fill',        'সেটিংস'],
];
?>
<!DOCTYPE html>
<html lang="bn" class="light">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?php echo h(isset($pageTitle) ? $pageTitle : 'Admin | KMA'); ?></title>
<script>try{if(localStorage.getItem('kma_theme')==='dark')document.documentElement.classList.replace('light','dark')}catch(e){}</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={darkMode:'class',theme:{extend:{colors:{accent:'#2e6b3e',gold:'#c9a227','kma-dark':'#1a1a2e','kma-muted':'#6b7280','kma-bg':'#f8faf8','kma-border':'#e5e7eb'},fontFamily:{bn:['"Hind Siliguri"','sans-serif']}}}};
</script>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
<style>
body{font-family:'Hind Siliguri',sans-serif;}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:#2e6b3e;border-radius:3px}
.form-input{width:100%;padding:.65rem .9rem;border:1.5px solid #e5e7eb;border-radius:.5rem;font-size:.875rem;background:white;color:#1a1a2e;transition:border-color .2s,box-shadow .2s}
.form-input:focus{outline:none;border-color:#2e6b3e;box-shadow:0 0 0 3px rgba(46,107,62,.12)}
.dark .form-input{background:#1f2937;border-color:#374151;color:#f3f4f6}
.dark .form-input:focus{border-color:#2e6b3e}
.form-label{display:block;font-size:.78rem;font-weight:700;color:#374151;margin-bottom:.3rem}
.dark .form-label{color:#d1d5db}
.btn-primary{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;background:#2e6b3e;color:white;font-weight:700;border-radius:.5rem;font-size:.85rem;transition:background .2s,transform .15s;border:none;cursor:pointer}
.btn-primary:hover{background:#1a4a2a;transform:translateY(-1px)}
.btn-danger{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#dc2626;color:white;font-weight:700;border-radius:.5rem;font-size:.8rem;transition:background .2s;border:none;cursor:pointer}
.btn-danger:hover{background:#b91c1c}
.btn-outline{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border:1.5px solid #e5e7eb;color:#6b7280;font-weight:600;border-radius:.5rem;font-size:.8rem;transition:all .2s;background:white;cursor:pointer}
.btn-outline:hover{border-color:#2e6b3e;color:#2e6b3e}
.dark .btn-outline{background:#1f2937;border-color:#374151;color:#9ca3af}
.dark .btn-outline:hover{border-color:#2e6b3e;color:#4ade80}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700}
.admin-card{background:white;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.dark .admin-card{background:#1f2937}
table{width:100%;border-collapse:collapse}
thead th{padding:.7rem 1rem;text-align:left;font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;background:#f8faf8;border-bottom:2px solid #e5e7eb;color:#374151}
.dark thead th{background:#111827;border-color:#374151;color:#9ca3af}
tbody tr{border-bottom:1px solid #f3f4f6;transition:background .15s}
tbody tr:hover{background:#f8faf8}
.dark tbody tr{border-color:#1f2937}
.dark tbody tr:hover{background:#111827}
tbody td{padding:.7rem 1rem;font-size:.83rem;color:#374151;vertical-align:middle}
.dark tbody td{color:#d1d5db}
.alert{padding:.75rem 1rem;border-radius:.6rem;margin-bottom:1rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem}
.alert-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46}
.alert-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b}
.dark .alert-success{background:#064e3b;border-color:#065f46;color:#6ee7b7}
.dark .alert-error{background:#7f1d1d;border-color:#991b1b;color:#fca5a5}
.sidebar-link{display:flex;align-items:center;gap:.6rem;padding:.55rem .85rem;border-radius:.45rem;font-size:.84rem;font-weight:600;transition:all .15s;color:#6b7280;text-decoration:none}
.sidebar-link:hover,.sidebar-link.active{background:#2e6b3e;color:white}
.dark .sidebar-link{color:#9ca3af}
.dark .sidebar-link:hover,.dark .sidebar-link.active{background:#2e6b3e;color:white}
</style>
</head>
<body class="bg-kma-bg dark:bg-gray-900 min-h-screen">

<!-- Top bar -->
<header class="fixed top-0 left-0 right-0 z-50 h-[60px] bg-gradient-to-r from-accent to-[#1a4a2a] shadow-lg flex items-center px-4">
  <div class="flex items-center justify-between w-full">
    <div class="flex items-center gap-3">
      <button id="sidebarToggle" class="text-white/80 hover:text-white w-9 h-9 rounded-lg hover:bg-white/10 flex items-center justify-center transition-colors lg:hidden">
        <i class="bi bi-list text-xl"></i>
      </button>
      <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
          <i class="bi bi-shield-check text-white text-sm"></i>
        </div>
        <span class="text-white font-bold text-sm hidden sm:block">KMA Admin</span>
      </a>
    </div>
    <div class="flex items-center gap-2">
      <button onclick="var h=document.documentElement;var d=h.classList.toggle('dark');localStorage.setItem('kma_theme',d?'dark':'light')"
              class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
        <i class="bi bi-moon-fill dark:hidden text-sm"></i>
        <i class="bi bi-sun-fill hidden dark:block text-sm"></i>
      </button>
      <a href="<?php echo BASE_URL; ?>/index.php" target="_blank"
         class="hidden sm:flex w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 items-center justify-center text-white transition-colors">
        <i class="bi bi-box-arrow-up-right text-sm"></i>
      </a>
      <div class="relative" id="userMenuWrap">
        <button id="userMenuBtn" class="flex items-center gap-2 bg-white/15 hover:bg-white/25 transition-colors rounded-lg px-3 py-1.5">
          <div class="w-7 h-7 rounded-full bg-gold flex items-center justify-center text-kma-dark font-bold text-xs">
            <?php echo strtoupper(mb_substr($adminName,0,1)); ?>
          </div>
          <span class="text-white text-sm font-semibold hidden sm:block"><?php echo h($adminName); ?></span>
          <i class="bi bi-chevron-down text-white/60 text-xs"></i>
        </button>
        <div id="userMenuDrop" class="hidden absolute right-0 top-full mt-1.5 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-kma-border dark:border-gray-700 overflow-hidden z-50">
          <div class="px-4 py-3 border-b border-kma-border dark:border-gray-700">
            <div class="text-xs font-bold text-kma-dark dark:text-white"><?php echo h($adminName); ?></div>
            <div class="text-xs text-kma-muted capitalize"><?php echo h($adminRole); ?></div>
          </div>
          <a href="<?php echo BASE_URL; ?>/admin/views/settings.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-kma-muted hover:bg-kma-bg dark:hover:bg-gray-700 hover:text-accent transition-colors">
            <i class="bi bi-gear-fill"></i> সেটিংস
          </a>
          <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border-t border-kma-border dark:border-gray-700">
            <i class="bi bi-box-arrow-right"></i> লগআউট
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

<aside id="adminSidebar" class="fixed top-[60px] left-0 bottom-0 w-56 bg-white dark:bg-gray-900 border-r border-kma-border dark:border-gray-800 z-40 overflow-y-auto -translate-x-full lg:translate-x-0 transition-transform duration-200">
  <nav class="p-3 space-y-0.5">
    <?php foreach ($navItems as $item): ?>
    <a href="<?php echo h($item[1]); ?>" class="sidebar-link <?php echo $currentAdminPage===$item[0]?'active':''; ?>">
      <i class="bi <?php echo h($item[2]); ?> text-base leading-none"></i>
      <?php echo h($item[3]); ?>
    </a>
    <?php endforeach; ?>
    <div class="my-2 border-t border-kma-border dark:border-gray-700"></div>
    <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="sidebar-link text-red-500 hover:!bg-red-600 hover:!text-white">
      <i class="bi bi-box-arrow-right text-base leading-none"></i> লগআউট
    </a>
  </nav>
</aside>

<div class="lg:pl-56 pt-[60px] min-h-screen">
<main class="p-4 md:p-6">

<script>
(function(){
  var btn=document.getElementById('sidebarToggle');
  var sb=document.getElementById('adminSidebar');
  var ov=document.getElementById('sidebarOverlay');
  function closeSidebar(){sb.classList.add('-translate-x-full');ov.classList.add('hidden');}
  window.closeSidebar=closeSidebar;
  if(btn)btn.addEventListener('click',function(){sb.classList.toggle('-translate-x-full');ov.classList.toggle('hidden');});
  var ubtn=document.getElementById('userMenuBtn');
  var udrop=document.getElementById('userMenuDrop');
  if(ubtn)ubtn.addEventListener('click',function(e){e.stopPropagation();udrop.classList.toggle('hidden');});
  document.addEventListener('click',function(){if(udrop)udrop.classList.add('hidden');});
})();
</script>

  </main>
</div><!-- /lg:pl-56 -->
</body>
</html>