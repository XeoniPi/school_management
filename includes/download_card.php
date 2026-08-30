<?php
/**
 * KMA — includes/download_card.php  |  PHP 7.2
 * Reusable download-file card. Included (not required) from:
 *   pages/downloads.php, academy/class-routine.php,
 *   academy/syllabus.php, academy/holiday-list.php,
 *   academy/exam-schedule.php
 *
 * Expects in scope:
 *   $dl       — one row from the `downloads` table
 *   $catMeta  — ['category_key' => ['label'=>..,'icon'=>..,'color'=>..]]
 */
if (!isset($dl) || !is_array($dl)) { return; }

$dcCat   = isset($dl['category']) ? $dl['category'] : 'other';
$dcMeta  = isset($catMeta[$dcCat]) ? $catMeta[$dcCat] : ['label'=>'ফাইল','icon'=>'bi-file-earmark-fill','color'=>'bg-gray-100 text-gray-600'];
$dcExt   = strtoupper(pathinfo(isset($dl['file_path']) ? $dl['file_path'] : '', PATHINFO_EXTENSION));
$dcExt   = $dcExt !== '' ? $dcExt : 'FILE';
$dcSize  = !empty($dl['file_size']) ? $dl['file_size'] : '';
$dcDate  = !empty($dl['created_at']) ? date('d M Y', strtotime($dl['created_at'])) : '';
?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all">
  <div class="flex items-start gap-3 px-5 py-4">
    <div class="w-11 h-11 rounded-xl <?php echo h($dcMeta['color']); ?> flex items-center justify-center text-lg flex-shrink-0">
      <i class="bi <?php echo h($dcMeta['icon']); ?>"></i>
    </div>
    <div class="min-w-0 flex-1">
      <div class="text-sm font-bold text-kma-dark dark:text-white leading-snug truncate" title="<?php echo h($dl['title']); ?>">
        <?php echo h($dl['title']); ?>
      </div>
      <?php if (!empty($dl['description'])): ?>
      <p class="text-xs text-kma-muted mt-0.5 line-clamp-2"><?php echo h($dl['description']); ?></p>
      <?php endif; ?>
      <div class="flex items-center gap-2 mt-2 flex-wrap">
        <span class="text-[0.65rem] font-bold bg-red-600 text-white px-1.5 py-0.5 rounded"><?php echo h($dcExt); ?></span>
        <?php if ($dcSize): ?>
        <span class="text-[0.7rem] text-kma-muted"><?php echo h($dcSize); ?></span>
        <?php endif; ?>
        <?php if ($dcDate): ?>
        <span class="text-[0.7rem] text-kma-muted">· <?php echo h($dcDate); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <a href="<?php echo UPLOAD_PDFS_URL . h($dl['file_path']); ?>" download
     class="flex items-center justify-center gap-2 bg-accent text-white text-xs font-bold py-2.5 hover:bg-gold hover:text-kma-dark transition-colors">
    <i class="bi bi-download"></i> ডাউনলোড করুন
  </a>
</div>
