<?php
/**
 * KMA – Full Photo Gallery Page  |  PHP 7.2 compatible
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

$pageTitle     = 'ফটো গ্যালারি | খলিল উল্ল্যাহ মেমোরিয়াল একাডেমি';
$pageDesc      = 'খলিল উল্ল্যাহ মেমোরিয়াল একাডেমির বিভিন্ন কার্যক্রম, অনুষ্ঠান ও স্মরণীয় মুহূর্তের সম্পূর্ণ ছবির সংগ্রহ।';
$canonicalPath = '/pages/gallery.php';

/* ── DB query: ALL active gallery images ── */
$gallery = [];
try {
    $stmt = getDB()->prepare(
        'SELECT * FROM gallery WHERE is_active = 1
         ORDER BY sort_order ASC, id DESC'
    );
    $stmt->execute();
    $gallery = $stmt->fetchAll();
} catch (Exception $e) {}

$totalImages = count($gallery);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ══════════════════════════════════════════════════
     PAGE HERO
══════════════════════════════════════════════════ -->
<section class="relative overflow-hidden" aria-label="গ্যালারি পেজ হেডার" style="min-height:300px;">
  <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1600&q=80'); transform:scale(1.04);"></div>
  <div class="absolute inset-0" style="background:linear-gradient(120deg, rgba(20,40,25,.87) 0%, rgba(39,39,39,.62) 100%);"></div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 sm:py-20">
    <div class="inline-flex items-center gap-2 bg-gold/20 border border-gold/50 text-gold text-xs font-semibold uppercase tracking-widest px-4 py-1.5 rounded-full mb-4">
      <i class="bi bi-images"></i> মুহূর্তের সংগ্রহ
    </div>
    <h1 class="text-white font-bold text-3xl sm:text-4xl leading-tight mb-3">ফটো <span class="text-gold">গ্যালারি</span></h1>
    <p class="text-white/80 text-sm sm:text-base max-w-xl mb-5 leading-7">
      বিদ্যালয়ের শ্রেণিকক্ষ কার্যক্রম, সাংস্কৃতিক অনুষ্ঠান, ক্রীড়া প্রতিযোগিতা ও বিভিন্ন স্মরণীয় মুহূর্তের সম্পূর্ণ ছবির সংগ্রহ।
    </p>
    <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
      <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors">
        <i class="bi bi-house-fill me-1"></i>হোম
      </a>
      <span class="text-white/40"><i class="bi bi-chevron-right"></i></span>
      <span class="text-gold font-semibold">ফটো গ্যালারি</span>
    </nav>
    <div class="mt-5 inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white/90 text-xs font-semibold px-4 py-2 rounded-full backdrop-blur-sm">
      <i class="bi bi-collection"></i> মোট <?php echo (int) $totalImages; ?>টি ছবি
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     GALLERY GRID
══════════════════════════════════════════════════ -->
<section class="bg-kma-bg dark:bg-gray-900 py-14 sm:py-20">
  <div class="max-w-7xl mx-auto px-4">

    <?php if (!empty($gallery)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
      <?php foreach ($gallery as $gi => $g):
          $imgSrc = UPLOAD_GALLERY_URL . h($g['filename']);
          $imgAlt = !empty($g['caption']) ? h($g['caption']) : 'গ্যালারি ছবি';
          $imgCap = !empty($g['description']) ? h($g['description']) : $imgAlt;
      ?>
      <div class="gallery-thumb group relative aspect-square rounded-xl overflow-hidden cursor-pointer shadow-sm"
           role="button" tabindex="0"
           data-index="<?php echo (int) $gi; ?>"
           data-img="<?php echo $imgSrc; ?>"
           data-caption="<?php echo $imgCap; ?>"
           aria-haspopup="dialog"
           aria-label="ছবি বড় করে দেখুন — <?php echo $imgAlt; ?>">
        <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgAlt; ?>" loading="lazy"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
        <div class="absolute inset-0 bg-accent/0 group-hover:bg-accent/60 transition-colors duration-300 flex items-center justify-center">
          <i class="bi bi-zoom-in text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
        </div>
        <?php if (!empty($g['caption'])): ?>
        <div class="absolute inset-x-0 bottom-0 hidden sm:block px-2.5 pt-6 pb-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
             style="background:linear-gradient(to top, rgba(0,0,0,.75), transparent);">
          <p class="text-white text-xs font-medium truncate"><?php echo h($g['caption']); ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-20 text-kma-muted dark:text-gray-400">
      <i class="bi bi-images text-5xl block mb-4 opacity-40"></i>
      <p class="text-sm">এখনো কোনো ছবি যোগ করা হয়নি। শীঘ্রই নতুন ছবি যুক্ত করা হবে।</p>
    </div>
    <?php endif; ?>

  </div>
</section>


<!-- ══════════════════════════════════════════════════
     CTA
══════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20 text-center" style="background:linear-gradient(135deg,#2e6b3e 0%,#1a4a2a 100%);">
  <div class="max-w-3xl mx-auto px-4">
    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">আজই ভর্তির আবেদন করুন</h2>
    <p class="text-white/80 mb-7 text-sm sm:text-base">২০২৫-২৬ শিক্ষাবর্ষে সীমিত আসনে ভর্তি চলছে। দ্রুত আবেদন করুন এবং আপনার সন্তানের উজ্জ্বল ভবিষ্যৎ নিশ্চিত করুন।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/pages/admission.php"
         class="inline-flex items-center gap-2 bg-gold text-kma-dark font-bold px-7 py-3 rounded-full hover:bg-white hover:text-accent transition-colors">
        <i class="bi bi-pencil-square"></i> ভর্তির আবেদন করুন
      </a>
      <a href="<?php echo BASE_URL; ?>/pages/contact.php"
         class="inline-flex items-center gap-2 border-2 border-white/60 text-white font-semibold px-7 py-3 rounded-full hover:bg-white/15 transition-colors">
        <i class="bi bi-telephone"></i> যোগাযোগ করুন
      </a>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     IMAGE MODAL — image + short description + close.
     No new-tab / no window.open anywhere; the image is
     only ever shown inside this modal.
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
        <span id="galleryModalIndex" class="kma-modal-index"></span>
        <p id="galleryModalCaption" class="text-sm sm:text-base text-white/95 leading-6"></p>
      </div>
    </div>
  </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>


<style>
/* Page-local style only — the shared .kma-modal system now lives in
   assets/css/site.css (used by index.php, this page, and about.php). */
.gallery-thumb:focus-visible { outline: 2px solid #2e6b3e; outline-offset: -2px; }
</style>

<script src="<?php echo BASE_URL; ?>/assets/js/modal.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/gallery-page.js"></script>