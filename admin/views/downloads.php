<?php
/**
 * KMA — admin/views/downloads.php  |  PHP 7.2
 * Full CRUD for downloadable files.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'downloads';
$pageTitle = 'ডাউনলোড ব্যবস্থাপনা | KMA Admin';

$flash = ''; $flashType = 'success'; $errors = [];
$dl = ['title'=>'','description'=>'','category'=>'routine','class_id'=>'','file_path'=>'','file_size'=>'','is_active'=>1];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        if ($pa === 'delete') {
            $did = (int)(isset($_POST['dl_id']) ? $_POST['dl_id'] : 0);
            if ($did) {
                $row = $pdo->prepare('SELECT file_path FROM downloads WHERE id=?');
                $row->execute([$did]);
                $row = $row->fetch();
                if ($row && !empty($row['file_path'])) {
                    $fp = UPLOAD_PDFS . $row['file_path'];
                    if (file_exists($fp)) { @unlink($fp); }
                }
                $pdo->prepare('DELETE FROM downloads WHERE id=?')->execute([$did]);
                $flash = 'ফাইলটি মুছে ফেলা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/downloads.php?flash=' . urlencode($flash)); exit;
        }

        if (in_array($pa, ['add','edit'])) {
            $eid = (int)(isset($_POST['dl_id']) ? $_POST['dl_id'] : 0);
            $dl = [
                'title'       => sanitize(isset($_POST['title'])       ? $_POST['title']       : ''),
                'description' => sanitize(isset($_POST['description']) ? $_POST['description'] : ''),
                'category'    => sanitize(isset($_POST['category'])    ? $_POST['category']    : 'routine'),
                'class_id'    => (int)(isset($_POST['class_id'])       ? $_POST['class_id']    : 0) ?: null,
                'file_path'   => sanitize(isset($_POST['old_file'])    ? $_POST['old_file']    : ''),
                'file_size'   => sanitize(isset($_POST['old_size'])    ? $_POST['old_size']    : ''),
                'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (mb_strlen($dl['title']) < 2) { $errors[] = 'শিরোনাম লিখুন।'; }

            /* File upload */
            if (!empty($_FILES['dl_file']['name'])) {
                $file    = $_FILES['dl_file'];
                $allowed = ['application/pdf','image/jpeg','image/png','application/zip',
                            'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if ($file['size'] > MAX_PDF_SIZE) {
                    $errors[] = 'ফাইল সর্বোচ্চ ৫ MB।';
                } elseif (!in_array($file['type'], $allowed) && $file['size'] > 0) {
                    /* Accept by extension if mime unreliable */
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['pdf','jpg','jpeg','png','zip','doc','docx'])) {
                        $errors[] = 'অনুমোদিত ফাইল টাইপ: PDF, JPG, PNG, ZIP, DOC।';
                    }
                }
                if (empty($errors)) {
                    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $fname = 'dl_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_PDFS . $fname);
                    /* Delete old file if replacing */
                    if (!empty($dl['file_path']) && file_exists(UPLOAD_PDFS . $dl['file_path'])) {
                        @unlink(UPLOAD_PDFS . $dl['file_path']);
                    }
                    $dl['file_path'] = $fname;
                    $dl['file_name'] = sanitize($file['name']);
                    $dl['file_type'] = $ext !== '' ? $ext : 'pdf';
                    $dl['file_size'] = round($file['size'] / 1024) . ' KB';
                }
            }

            /* Add requires file */
            if ($pa === 'add' && empty($dl['file_path'])) { $errors[] = 'ফাইল আপলোড করুন।'; }

            if (empty($errors)) {
                if ($pa === 'add') {
                    $cols = 'title,description,category,class_id,file_path,file_name,file_type,file_size,is_active,uploaded_by';
                    $vals = [$dl['title'],$dl['description'],$dl['category'],$dl['class_id'],
                             $dl['file_path'],
                             isset($dl['file_name']) ? $dl['file_name'] : $dl['file_path'],
                             isset($dl['file_type']) ? $dl['file_type'] : 'pdf',
                             $dl['file_size'],$dl['is_active'], (int)$_SESSION['admin_id']];
                    $pdo->prepare("INSERT INTO downloads ($cols) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute($vals);
                    $flash = 'ফাইল সফলভাবে যোগ করা হয়েছে।';
                } else {
                    $vals = [$dl['title'],$dl['description'],$dl['category'],
                             $dl['class_id'],$dl['file_path'],$dl['file_size'],$dl['is_active']];
                    $vals[] = $eid;
                    $pdo->prepare('UPDATE downloads SET title=?,description=?,category=?,class_id=?,file_path=?,file_size=?,is_active=? WHERE id=?')->execute($vals);
                    $flash = 'ফাইল আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/downloads.php?flash=' . urlencode($flash)); exit;
            }
            $action = $pa === 'edit' ? 'edit' : 'add';
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

if ($action === 'edit' && $id) {
    $row = $pdo->prepare('SELECT * FROM downloads WHERE id=?');
    $row->execute([$id]);
    $f = $row->fetch();
    if ($f) { $dl = $f; } else { $action = 'list'; }
}

/* List */
$catFilter = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';
$where = '1'; $params = [];
if ($catFilter) { $where .= ' AND d.category=?'; $params[] = $catFilter; }

$rows = $pdo->prepare("SELECT d.*, c.class_name FROM downloads d LEFT JOIN classes c ON c.id=d.class_id WHERE $where ORDER BY d.category, d.created_at DESC");
$rows->execute($params);
$downloads = $rows->fetchAll();

$classes = $pdo->query('SELECT id,class_name FROM classes WHERE is_active=1 ORDER BY sort_order')->fetchAll();

$csrf = generateCsrfToken();
$cats = ['routine'=>'ক্লাস রুটিন','syllabus'=>'সিলেবাস','exam_schedule'=>'পরীক্ষার সময়সূচি','holiday'=>'ছুটির তালিকা','other'=>'অন্যান্য'];
$catBadge = ['routine'=>'bg-blue-100 text-blue-700','syllabus'=>'bg-gold/20 text-yellow-700','exam_schedule'=>'bg-purple-100 text-purple-700','holiday'=>'bg-green-100 text-green-700','other'=>'bg-gray-100 text-gray-600'];

require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white">
    <?php echo $action==='list' ? 'ডাউনলোড ফাইল' : ($action==='add' ? 'নতুন ফাইল আপলোড' : 'ফাইল সম্পাদনা'); ?>
  </h1>
  <?php if ($action === 'list'): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-upload"></i> নতুন ফাইল</a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/downloads.php" class="btn-outline"><i class="bi bi-arrow-left"></i> তালিকায় ফিরুন</a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- Category filter -->
<div class="flex flex-wrap gap-2 mb-4">
  <a href="<?php echo BASE_URL; ?>/admin/views/downloads.php" class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors <?php echo $catFilter==='' ? 'bg-kma-dark text-white border-kma-dark' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">সব</a>
  <?php foreach ($cats as $cv=>$cl): ?>
  <a href="?cat=<?php echo urlencode($cv); ?>" class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors <?php echo $catFilter===$cv ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>"><?php echo h($cl); ?></a>
  <?php endforeach; ?>
</div>

<div class="admin-card overflow-hidden">
  <?php if (empty($downloads)): ?>
  <div class="py-10 text-center text-kma-muted text-sm"><i class="bi bi-file-earmark-x text-3xl block mb-2 opacity-30"></i>কোনো ফাইল নেই</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>শিরোনাম</th><th>ক্যাটাগরি</th><th>শ্রেণি</th><th>আকার</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
        <?php foreach ($downloads as $d):
          $bc = isset($catBadge[$d['category']]) ? $catBadge[$d['category']] : 'bg-gray-100 text-gray-600';
          $cl = isset($cats[$d['category']]) ? $cats[$d['category']] : $d['category'];
          $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION));
          $icon = $ext === 'pdf' ? 'bi-file-earmark-pdf text-red-500' : ($ext === 'zip' ? 'bi-file-earmark-zip text-yellow-500' : 'bi-file-earmark-text text-blue-500');
        ?>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <i class="bi <?php echo h($icon); ?> text-lg flex-shrink-0"></i>
              <div>
                <div class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($d['title']); ?></div>
                <?php if ($d['description']): ?><div class="text-[0.7rem] text-kma-muted truncate max-w-[160px]"><?php echo h($d['description']); ?></div><?php endif; ?>
              </div>
            </div>
          </td>
          <td><span class="badge <?php echo h($bc); ?>"><?php echo h($cl); ?></span></td>
          <td class="text-xs text-kma-muted"><?php echo h($d['class_name'] ?? '—'); ?></td>
          <td class="text-xs text-kma-muted"><?php echo h($d['file_size'] ?: '—'); ?></td>
          <td><span class="badge <?php echo $d['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $d['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?></span></td>
          <td>
            <div class="flex items-center gap-2">
              <a href="<?php echo UPLOAD_PDFS_URL . h($d['file_path']); ?>" target="_blank" class="text-blue-500 hover:text-blue-700 text-xs" title="দেখুন"><i class="bi bi-eye-fill"></i></a>
              <a href="?action=edit&id=<?php echo (int)$d['id']; ?>" class="text-accent hover:underline text-xs font-semibold"><i class="bi bi-pencil-fill"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('ফাইলটি মুছে ফেলবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete"/>
                <input type="hidden" name="dl_id" value="<?php echo (int)$d['id']; ?>"/>
                <button type="submit" class="text-red-400 hover:text-red-600 text-xs"><i class="bi bi-trash-fill"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php else: /* add / edit */ ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/downloads.php" enctype="multipart/form-data" class="admin-card p-6 max-w-2xl">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action==='edit'?'edit':'add'; ?>"/>
  <?php if ($action==='edit'): ?>
  <input type="hidden" name="dl_id" value="<?php echo (int)($dl['id']??$id); ?>"/>
  <input type="hidden" name="old_file" value="<?php echo h($dl['file_path']??''); ?>"/>
  <input type="hidden" name="old_size" value="<?php echo h($dl['file_size']??''); ?>"/>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
      <label class="form-label">শিরোনাম <span class="text-red-500">*</span></label>
      <input type="text" name="title" class="form-input" required value="<?php echo h($dl['title']); ?>" placeholder="ফাইলের শিরোনাম"/>
    </div>
    <div>
      <label class="form-label">ক্যাটাগরি</label>
      <select name="category" class="form-input">
        <?php foreach ($cats as $cv=>$cl): ?>
        <option value="<?php echo h($cv); ?>" <?php echo ($dl['category']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">শ্রেণি (ঐচ্ছিক)</label>
      <select name="class_id" class="form-input">
        <option value="">— সব শ্রেণির জন্য —</option>
        <?php foreach ($classes as $cls): ?>
        <option value="<?php echo (int)$cls['id']; ?>" <?php echo ((int)($dl['class_id']??0))===(int)$cls['id']?'selected':''; ?>>
          <?php echo h($cls['class_name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="sm:col-span-2">
      <label class="form-label">ফাইল আপলোড <?php echo $action==='add'?'<span class="text-red-500">*</span>':'(নতুন ফাইল দিলে পুরানোটি প্রতিস্থাপিত হবে)'; ?></label>
      <label class="block border-2 border-dashed border-kma-border rounded-xl p-6 text-center cursor-pointer hover:border-accent hover:bg-accent/5 transition-colors">
        <input type="file" name="dl_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.zip,.doc,.docx"
               onchange="document.getElementById('file-label').textContent=this.files[0]?this.files[0].name:'ক্লিক করে ফাইল বেছে নিন'"/>
        <i class="bi bi-cloud-arrow-up text-4xl text-kma-muted block mb-2"></i>
        <p id="file-label" class="text-sm text-kma-muted font-semibold">ক্লিক করে ফাইল বেছে নিন</p>
        <p class="text-xs text-kma-muted mt-1">PDF, JPG, PNG, ZIP, DOC · সর্বোচ্চ ৫ MB</p>
      </label>
      <?php if (!empty($dl['file_path'])): ?>
      <p class="text-xs text-kma-muted mt-1.5"><i class="bi bi-paperclip"></i> বর্তমান: <?php echo h($dl['file_path']); ?> <?php if($dl['file_size']): ?>(<?php echo h($dl['file_size']); ?>)<?php endif; ?></p>
      <?php endif; ?>
    </div>
    <div class="sm:col-span-2">
      <label class="form-label">বিবরণ</label>
      <textarea name="description" class="form-input" rows="2" placeholder="ঐচ্ছিক বিবরণ..."><?php echo h($dl['description']??''); ?></textarea>
    </div>
    <div>
      <label class="flex items-center gap-2 cursor-pointer mt-2">
        <input type="checkbox" name="is_active" value="1" class="accent-accent"
               <?php echo (isset($dl['is_active'])&&$dl['is_active'])||$action==='add'?'checked':''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
      </label>
    </div>
  </div>

  <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo $action==='edit'?'আপডেট করুন':'আপলোড করুন'; ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/downloads.php" class="btn-outline">বাতিল</a>
  </div>
</form>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>