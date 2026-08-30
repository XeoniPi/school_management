<?php
/**
 * KMA — admin/views/gallery.php  |  PHP 7.2
 * Full CRUD for photo gallery.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'gallery';
$pageTitle = 'গ্যালারি ব্যবস্থাপনা | KMA Admin';

$flash = ''; $flashType = 'success'; $errors = [];
$item = ['title'=>'','caption'=>'','category'=>'general','image_path'=>'','sort_order'=>0,'is_active'=>1];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        /* Delete */
        if ($pa === 'delete') {
            $did = (int)(isset($_POST['gallery_id']) ? $_POST['gallery_id'] : 0);
            if ($did) {
                $row = $pdo->prepare('SELECT image_path FROM gallery WHERE id=?');
                $row->execute([$did]);
                $r = $row->fetch();
                if ($r && !empty($r['image_path'])) {
                    $fp = UPLOAD_IMAGES . $r['image_path'];
                    if (file_exists($fp)) { @unlink($fp); }
                }
                $pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([$did]);
                $flash = 'ছবিটি মুছে ফেলা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/gallery.php?flash=' . urlencode($flash)); exit;
        }

        /* Toggle active */
        if ($pa === 'toggle') {
            $tid = (int)(isset($_POST['gallery_id']) ? $_POST['gallery_id'] : 0);
            if ($tid) {
                $pdo->prepare('UPDATE gallery SET is_active = NOT is_active WHERE id=?')->execute([$tid]);
            }
            header('Location: ' . BASE_URL . '/admin/views/gallery.php'); exit;
        }

        /* Save */
        if (in_array($pa, ['add','edit'])) {
            $eid = (int)(isset($_POST['gallery_id']) ? $_POST['gallery_id'] : 0);
            $item = [
                'title'       => sanitize(isset($_POST['title'])       ? $_POST['title']       : ''),
                'caption'     => sanitize(isset($_POST['caption'])     ? $_POST['caption']     : ''),
                'category'    => sanitize(isset($_POST['category'])    ? $_POST['category']    : 'general'),
                'image_path'  => sanitize(isset($_POST['old_image'])   ? $_POST['old_image']   : ''),
                'sort_order'  => (int)(isset($_POST['sort_order'])     ? $_POST['sort_order']  : 0),
                'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (mb_strlen($item['title']) < 2) { $errors[] = 'শিরোনাম লিখুন।'; }

            /* Image upload */
            if (!empty($_FILES['gallery_image']['name'])) {
                $file = $_FILES['gallery_image'];
                if ($file['size'] > MAX_IMG_SIZE) {
                    $errors[] = 'ছবির আকার সর্বোচ্চ ২ MB।';
                } elseif (!in_array($file['type'], ALLOWED_IMG_TYPES)) {
                    $errors[] = 'শুধুমাত্র JPG, PNG বা WEBP ছবি আপলোড করুন।';
                } else {
                    $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'gallery_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_IMAGES . $fname);
                    /* Delete old */
                    if (!empty($item['image_path']) && file_exists(UPLOAD_IMAGES . $item['image_path'])) {
                        @unlink(UPLOAD_IMAGES . $item['image_path']);
                    }
                    $item['image_path'] = $fname;
                }
            }

            if ($pa === 'add' && empty($item['image_path'])) { $errors[] = 'ছবি আপলোড করুন।'; }

            if (empty($errors)) {
                if ($pa === 'add') {
                    $pdo->prepare(
                        'INSERT INTO gallery (title,caption,category,image_path,sort_order,is_active,uploaded_by) VALUES (?,?,?,?,?,?,?)'
                    )->execute([$item['title'],$item['caption'],$item['category'],$item['image_path'],$item['sort_order'],$item['is_active'],(int)$_SESSION['admin_id']]);
                    $flash = 'ছবি সফলভাবে যোগ করা হয়েছে।';
                } else {
                    $pdo->prepare(
                        'UPDATE gallery SET title=?,caption=?,category=?,image_path=?,sort_order=?,is_active=? WHERE id=?'
                    )->execute([$item['title'],$item['caption'],$item['category'],$item['image_path'],$item['sort_order'],$item['is_active'],$eid]);
                    $flash = 'ছবি আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/gallery.php?flash=' . urlencode($flash)); exit;
            }
            $action = $pa === 'edit' ? 'edit' : 'add';
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

if ($action === 'edit' && $id) {
    $row = $pdo->prepare('SELECT * FROM gallery WHERE id=?');
    $row->execute([$id]);
    $f = $row->fetch();
    if ($f) { $item = $f; } else { $action = 'list'; }
}

/* List */
$catFilter = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';
$where = '1'; $params = [];
if ($catFilter) { $where .= ' AND category=?'; $params[] = $catFilter; }
$rows = $pdo->prepare("SELECT * FROM gallery WHERE $where ORDER BY sort_order ASC, created_at DESC");
$rows->execute($params);
$gallery = $rows->fetchAll();

$csrf = generateCsrfToken();
$cats = ['general'=>'সাধারণ','event'=>'অনুষ্ঠান','sports'=>'ক্রীড়া','classroom'=>'শ্রেণিকক্ষ','ceremony'=>'অনুষ্ঠান'];

require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white">
      <?php echo $action==='list' ? 'ফটো গ্যালারি' : ($action==='add' ? 'নতুন ছবি যোগ' : 'ছবি সম্পাদনা'); ?>
    </h1>
    <?php if ($action==='list'): ?>
    <p class="text-kma-muted text-xs mt-0.5"><?php echo count($gallery); ?> টি ছবি</p>
    <?php endif; ?>
  </div>
  <?php if ($action === 'list'): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-plus-lg"></i> নতুন ছবি</a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/gallery.php" class="btn-outline"><i class="bi bi-arrow-left"></i> তালিকায় ফিরুন</a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- Category filter -->
<div class="flex flex-wrap gap-2 mb-5">
  <a href="<?php echo BASE_URL; ?>/admin/views/gallery.php"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors <?php echo $catFilter==='' ? 'bg-kma-dark text-white border-kma-dark' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    সব
  </a>
  <?php foreach ($cats as $cv=>$cl): ?>
  <a href="?cat=<?php echo urlencode($cv); ?>"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors
            <?php echo $catFilter===$cv ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    <?php echo h($cl); ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($gallery)): ?>
<div class="admin-card py-14 text-center text-kma-muted text-sm">
  <i class="bi bi-images text-4xl block mb-3 opacity-30"></i>
  কোনো ছবি নেই। <a href="?action=add" class="text-accent font-semibold hover:underline">এখনই যোগ করুন →</a>
</div>
<?php else: ?>

<!-- Photo grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
  <?php foreach ($gallery as $g): ?>
  <div class="admin-card p-0 overflow-hidden group relative hover:shadow-lg transition-shadow">
    <!-- Image -->
    <div class="aspect-square relative overflow-hidden bg-gray-100 dark:bg-gray-700">
      <img src="<?php echo UPLOAD_IMAGES_URL . h($g['image_path']); ?>"
           alt="<?php echo h($g['title']); ?>"
           class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
           loading="lazy" onerror="this.src='https://placehold.co/300x300/e8f4eb/2e6b3e?text=No+Image'" />
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
        <a href="<?php echo UPLOAD_IMAGES_URL . h($g['image_path']); ?>" target="_blank"
           class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white transition-colors" title="দেখুন">
          <i class="bi bi-zoom-in"></i>
        </a>
        <a href="?action=edit&id=<?php echo (int)$g['id']; ?>"
           class="w-9 h-9 rounded-full bg-accent/80 hover:bg-accent flex items-center justify-center text-white transition-colors" title="সম্পাদনা">
          <i class="bi bi-pencil-fill text-sm"></i>
        </a>
        <form method="POST" class="inline" onsubmit="return confirm('ছবিটি মুছে ফেলবেন?')">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
          <input type="hidden" name="post_action" value="delete"/>
          <input type="hidden" name="gallery_id" value="<?php echo (int)$g['id']; ?>"/>
          <button type="submit" class="w-9 h-9 rounded-full bg-red-600/80 hover:bg-red-600 flex items-center justify-center text-white transition-colors" title="মুছুন">
            <i class="bi bi-trash-fill text-sm"></i>
          </button>
        </form>
      </div>
      <!-- Active badge -->
      <?php if (!$g['is_active']): ?>
      <div class="absolute top-2 left-2 bg-gray-800/80 text-white text-[0.6rem] font-bold px-2 py-0.5 rounded">নিষ্ক্রিয়</div>
      <?php endif; ?>
    </div>
    <!-- Info -->
    <div class="p-2.5">
      <div class="text-xs font-semibold text-kma-dark dark:text-gray-200 truncate"><?php echo h($g['title']); ?></div>
      <div class="flex items-center justify-between mt-1.5">
        <span class="text-[0.65rem] text-kma-muted"><?php echo h($cats[$g['category']] ?? $g['category']); ?></span>
        <!-- Toggle active -->
        <form method="POST" class="inline">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
          <input type="hidden" name="post_action" value="toggle"/>
          <input type="hidden" name="gallery_id" value="<?php echo (int)$g['id']; ?>"/>
          <button type="submit"
                  class="text-xs <?php echo $g['is_active'] ? 'text-green-600 hover:text-red-500' : 'text-gray-400 hover:text-green-600'; ?> transition-colors"
                  title="<?php echo $g['is_active'] ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন'; ?>">
            <i class="bi <?php echo $g['is_active'] ? 'bi-eye-fill' : 'bi-eye-slash-fill'; ?>"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php else: /* ── ADD / EDIT FORM ── */ ?>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- Form -->
  <div class="lg:col-span-2">
    <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/gallery.php" enctype="multipart/form-data" class="admin-card p-6">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
      <input type="hidden" name="post_action" value="<?php echo $action==='edit'?'edit':'add'; ?>"/>
      <?php if ($action==='edit'): ?>
      <input type="hidden" name="gallery_id" value="<?php echo (int)($item['id']??$id); ?>"/>
      <input type="hidden" name="old_image" value="<?php echo h($item['image_path']??''); ?>"/>
      <?php endif; ?>

      <div class="grid sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="form-label">শিরোনাম <span class="text-red-500">*</span></label>
          <input type="text" name="title" class="form-input" required
                 value="<?php echo h($item['title']); ?>" placeholder="ছবির শিরোনাম"/>
        </div>
        <div>
          <label class="form-label">ক্যাটাগরি</label>
          <select name="category" class="form-input">
            <?php foreach ($cats as $cv=>$cl): ?>
            <option value="<?php echo h($cv); ?>" <?php echo ($item['category']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">ক্রম (Sort Order)</label>
          <input type="number" name="sort_order" class="form-input" min="0"
                 value="<?php echo (int)($item['sort_order']??0); ?>"/>
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">ক্যাপশন</label>
          <textarea name="caption" class="form-input" rows="2"
                    placeholder="ছবির বিবরণ..."><?php echo h($item['caption']??''); ?></textarea>
        </div>

        <!-- Image upload area -->
        <div class="sm:col-span-2">
          <label class="form-label">ছবি আপলোড <?php echo $action==='add'?'<span class="text-red-500">*</span>':'(নতুন ছবি দিলে পুরানোটি প্রতিস্থাপিত হবে)'; ?></label>
          <label class="block border-2 border-dashed border-kma-border rounded-xl p-8 text-center cursor-pointer hover:border-accent hover:bg-accent/5 transition-colors" id="dropZone">
            <input type="file" name="gallery_image" id="imageInput" class="hidden"
                   accept="image/jpeg,image/png,image/webp"/>
            <div id="uploadPreview">
              <i class="bi bi-cloud-arrow-up text-5xl text-kma-muted block mb-2"></i>
              <p class="text-sm font-semibold text-kma-muted">ক্লিক করুন বা ছবি টেনে আনুন</p>
              <p class="text-xs text-kma-muted mt-1">JPG, PNG, WEBP · সর্বোচ্চ ২ MB</p>
            </div>
            <img id="previewImg" src="" alt="" class="hidden max-h-48 mx-auto rounded-xl mt-2"/>
          </label>
        </div>

        <div>
          <label class="flex items-center gap-2 cursor-pointer mt-1">
            <input type="checkbox" name="is_active" value="1" class="accent-accent"
                   <?php echo (isset($item['is_active'])&&$item['is_active'])||$action==='add'?'checked':''; ?>/>
            <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
          </label>
        </div>
      </div>

      <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
        <button type="submit" class="btn-primary">
          <i class="bi bi-check-lg"></i> <?php echo $action==='edit'?'আপডেট করুন':'ছবি যোগ করুন'; ?>
        </button>
        <a href="<?php echo BASE_URL; ?>/admin/views/gallery.php" class="btn-outline">বাতিল</a>
      </div>
    </form>
  </div>

  <!-- Current image preview (edit) -->
  <?php if ($action === 'edit' && !empty($item['image_path'])): ?>
  <div class="lg:col-span-1">
    <div class="admin-card p-4">
      <h3 class="text-sm font-bold text-kma-dark dark:text-white mb-3">বর্তমান ছবি</h3>
      <img src="<?php echo UPLOAD_IMAGES_URL . h($item['image_path']); ?>"
           alt="<?php echo h($item['title']); ?>"
           class="w-full rounded-xl object-cover aspect-square"
           onerror="this.src='https://placehold.co/300x300/e8f4eb/2e6b3e?text=No+Image'"/>
      <p class="text-xs text-kma-muted mt-2 truncate"><?php echo h($item['image_path']); ?></p>
    </div>
  </div>
  <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/admin-uploader.js"></script>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>