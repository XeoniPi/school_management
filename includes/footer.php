<?php
/**
 * KMA — Global Site Footer  |  PHP 7.2 compatible
 */
if (!isset($site) || !is_array($site)) {
    $site = getSiteSettings();
}
$siteName   = isset($site['school_name_bn']) ? $site['school_name_bn'] : 'খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি';
$siteNameEn = isset($site['school_name_en']) ? $site['school_name_en'] : 'Khalilullah Memorial Academy (KMA)';
$phone      = isset($site['school_phone'])   ? $site['school_phone']   : '+880 1866-751015';
$email      = isset($site['school_email'])   ? $site['school_email']   : 'info@kma.edu.bd';
$address    = isset($site['school_address']) ? $site['school_address'] : 'মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী, বাংলাদেশ।';
$officeHrs  = isset($site['office_hours'])   ? $site['office_hours']   : 'শনিবার – বৃহস্পতিবার: সকাল ৮:০০ – দুপুর ১:৩০';
$facebook   = isset($site['school_facebook']) ? $site['school_facebook'] : '#';
$youtube    = isset($site['school_youtube'])  ? $site['school_youtube']  : '#';
$wa         = isset($site['school_whatsapp']) ? ltrim($site['school_whatsapp'], '+') : '8801866751015';

$quickLinks = [
    [BASE_URL . '/index.php',           'হোম'],
    [BASE_URL . '/pages/about.php',     'আমাদের সম্পর্কে'],
    [BASE_URL . '/pages/academics.php', 'একাডেমিক'],
    [BASE_URL . '/pages/admission.php', 'ভর্তি তথ্য'],
    [BASE_URL . '/pages/notices.php',   'নোটিশ বোর্ড'],
    [BASE_URL . '/pages/contact.php',   'যোগাযোগ'],
];
$acadLinks = [
    [BASE_URL . '/academy/class-routine.php', 'ক্লাস রুটিন'],
    [BASE_URL . '/academy/syllabus.php',       'সিলেবাস'],
    [BASE_URL . '/academy/holiday-list.php',   'ছুটির তালিকা'],
    [BASE_URL . '/academy/exam-schedule.php',  'পরীক্ষার সময়সূচি'],
    [BASE_URL . '/academy/dress-code.php',     'ড্রেস কোড'],
];
?>

<!-- ══ FOOTER ══ -->
<footer class="bg-kma-dark dark:bg-gray-950 text-white/75 pt-14" aria-label="সাইট ফুটার">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

      <!-- Brand col -->
      <div>
        <img src="https://placehold.co/60x60/2e6b3e/ffffff?text=KMA&font=open-sans"
             alt="KMA লোগো" width="60" height="60" loading="lazy"
             class="rounded-full border-2 border-gold mb-4" />
        <div class="mb-3">
          <div class="text-lg font-bold text-white font-bn"><?php echo h($siteName); ?></div>
          <div class="text-[0.75rem] text-gold tracking-wide"><?php echo h($siteNameEn); ?></div>
        </div>
        <p class="text-sm leading-7">
          জ্ঞান, মেধা ও মানবিকতার সমন্বয়ে একটি আলোকিত প্রজন্ম গড়ে তোলার অঙ্গীকার নিয়ে আমরা এগিয়ে চলেছি ২০২৬ সাল থেকে।
        </p>
        <div class="flex gap-2 mt-4" aria-label="সামাজিক মাধ্যম">
          <?php
          $socials = [
            ['href' => h($facebook),                  'label' => 'ফেসবুক',      'icon' => 'bi-facebook'],
            ['href' => h($youtube),                   'label' => 'ইউটিউব',      'icon' => 'bi-youtube'],
            ['href' => 'https://wa.me/' . h($wa),     'label' => 'হোয়াটসঅ্যাপ', 'icon' => 'bi-whatsapp'],
            ['href' => 'mailto:' . h($email),         'label' => 'ইমেইল',       'icon' => 'bi-envelope-fill'],
          ];
          foreach ($socials as $s): ?>
          <a href="<?php echo $s['href']; ?>"
             <?php if (strpos($s['href'], 'http') === 0): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
             aria-label="<?php echo h($s['label']); ?>"
             class="w-9 h-9 flex items-center justify-center rounded-full
                    bg-white/10 hover:bg-accent text-white transition-colors">
            <i class="bi <?php echo h($s['icon']); ?>"></i>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h3 class="text-white font-bold text-base mb-4 pb-2 relative after:absolute
                   after:bottom-0 after:left-0 after:w-9 after:h-0.5 after:bg-gold">
          দ্রুত লিংক
        </h3>
        <ul class="space-y-2" role="list">
          <?php foreach ($quickLinks as $lnk): ?>
          <li>
            <a href="<?php echo h($lnk[0]); ?>"
               class="flex items-center gap-1.5 text-white/68 text-sm hover:text-gold transition-colors">
              <i class="bi bi-chevron-right text-xs"></i> <?php echo h($lnk[1]); ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Academic Links -->
      <div>
        <h3 class="text-white font-bold text-base mb-4 pb-2 relative after:absolute
                   after:bottom-0 after:left-0 after:w-9 after:h-0.5 after:bg-gold">
          একাডেমিক
        </h3>
        <ul class="space-y-2" role="list">
          <?php foreach ($acadLinks as $lnk): ?>
          <li>
            <a href="<?php echo h($lnk[0]); ?>"
               class="flex items-center gap-1.5 text-white/68 text-sm hover:text-gold transition-colors">
              <i class="bi bi-chevron-right text-xs"></i> <?php echo h($lnk[1]); ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h3 class="text-white font-bold text-base mb-4 pb-2 relative after:absolute
                   after:bottom-0 after:left-0 after:w-9 after:h-0.5 after:bg-gold">
          যোগাযোগ
        </h3>
        <ul class="space-y-2.5" role="list">
          <li class="flex gap-2.5 text-sm items-start">
            <i class="bi bi-geo-alt-fill text-gold mt-0.5 flex-shrink-0"></i>
            <span><?php echo h($address); ?></span>
          </li>
          <li class="flex gap-2.5 text-sm items-center">
            <i class="bi bi-telephone-fill text-gold flex-shrink-0"></i>
            <a href="tel:<?php echo h($phone); ?>" class="hover:text-gold transition-colors">
              <?php echo h($phone); ?>
            </a>
          </li>
          <li class="flex gap-2.5 text-sm items-center">
            <i class="bi bi-envelope-fill text-gold flex-shrink-0"></i>
            <a href="mailto:<?php echo h($email); ?>" class="hover:text-gold transition-colors">
              <?php echo h($email); ?>
            </a>
          </li>
          <li class="flex gap-2.5 text-sm items-start">
            <i class="bi bi-clock-fill text-gold mt-0.5 flex-shrink-0"></i>
            <span><?php echo h($officeHrs); ?></span>
          </li>
        </ul>
      </div>

    </div><!-- /grid -->
  </div><!-- /container -->

  <!-- Bottom bar -->
  <div class="border-t border-white/10 mt-10 py-4 text-center text-xs text-white/40">
    <div class="max-w-7xl mx-auto px-4">
      &copy; ২০২৬
      <strong class="text-white/60"><?php echo h($siteName); ?></strong> ।
      সমস্ত অধিকার সংরক্ষিত ।
      ডিজাইন ও ডেভেলপমেন্ট:
      <a href="https://github.com/XeoniPi" target="_blank" rel="noopener"
         class="text-gold hover:underline">XeoniFi</a>
    </div>
  </div>
</footer>

<!-- Back-to-top -->
<button id="backToTop" aria-label="উপরে যান"
        class="fixed bottom-7 right-7 z-[999] w-11 h-11 rounded-full
               bg-accent text-white flex items-center justify-center shadow-lg
               opacity-0 pointer-events-none
               hover:bg-gold hover:text-kma-dark hover:-translate-y-1
               transition-all duration-300">
  <i class="bi bi-arrow-up text-lg"></i>
</button>

<!-- Global JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/site.js"></script>
</body>
</html>