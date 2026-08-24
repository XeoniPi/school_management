<?php
/**
 * KMA — admin/views/notices.php  |  PHP 7.2
 * Full CRUD: list, add, edit, delete (soft) notices.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentAdminPage = 'notices';
$pageTitle = 'নোটিশ ব্যবস্থাপনা | KMA Admin';

$flash   = '';
$flashType = 'success';
$errors  = [];
$notice  = ['title'=>'','content'=>'','category'=>'notice','notice_date'=>date('Y-m-d'),'is_pinned'=>0,'is_active'=>1,'file_path'=>''];

/* ── Handle POST ─────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {

        /* ── Delete ── */
        if ($postAction === 'delete') {
            $did = (int)(isset($_POST['notice_id']) ? $_POST['notice_id'] : 0);
            if ($did) {
                $pdo->prepare('UPDATE notices SET is_active=0 WHERE id=?')->execute([$did]);
                $flash = 'নোটিশটি মুছে ফেলা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/notices.php?flash=' . urlencode($flash));
            exit;
        }

        /* ── Toggle pin ── */
        if ($postAction === 'toggle_pin') {
            $tid = (int)(isset($_POST['notice_id']) ? $_POST['notice_id'] : 0);
            if ($tid) {
                $pdo->prepare('UPDATE notices SET is_pinned = NOT is_pinned WHERE id=?')->execute([$tid]);
            }
            header('Location: ' . BASE_URL . '/admin/views/notices.php');
            exit;
        }

        /* ── Save (add/edit) ── */
        if (in_array($postAction, ['add', 'edit'])) {
            $notice = [
                'title'       => sanitize(isset($_POST['title'])       ? $_POST['title']       : ''),
                'content'     => sanitize(isset($_POST['content'])     ? $_POST['content']     : ''),
                'category'    => sanitize(isset($_POST['category'])    ? $_POST['category']    : 'notice'),
                'notice_date' => sanitize(isset($_POST['notice_date']) ? $_POST['notice_date'] : date('Y-m-d')),
                'is_pinned'   => isset($_POST['is_pinned'])  ? 1 : 0,
                'is_active'   => isset($_POST['is_active'])  ? 1 : 0,
                'file_path'   => sanitize(isset($_POST['old_file'])    ? $_POST['old_file']    : ''),
            ];
            $eid = (int)(isset($_POST['notice_id']) ? $_POST['notice_id'] : 0);

            if (mb_strlen($notice['title']) < 3)   { $errors[] = 'শিরোনাম কমপক্ষে ৩ অক্ষরের হতে হবে।'; }
            if (mb_strlen($notice['content']) < 10) { $errors[] = 'বিবরণ কমপক্ষে ১০ অক্ষরের হতে হবে।'; }
            if (empty($notice['notice_date']))       { $errors[] = 'তারিখ দিন।'; }

            /* File upload */
            if (!empty($_FILES['notice_file']['name'])) {
                $file = $_FILES['notice_file'];
                $allowed = ['application/pdf','image/jpeg','image/png','image/webp'];
                if ($file['size'] > MAX_PDF_SIZE) {
                    $errors[] = 'ফাইল সর্বোচ্চ ৫ MB হতে পারবে।';
                } elseif (!in_array($file['type'], $allowed)) {
                    $errors[] = 'শুধুমাত্র PDF, JPG, PNG ফাইল আপলোড করুন।';
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'notice_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_NOTICES . $fname);
                    $notice['file_path'] = $fname;
                }
            }

            if (empty($errors)) {
                if ($postAction === 'add') {
                    $pdo->prepare(
                        'INSERT INTO notices (title,content,category,notice_date,is_pinned,is_active,file_path)
                         VALUES (?,?,?,?,?,?,?)'
                    )->execute([
                        $notice['title'], $notice['content'], $notice['category'],
                        $notice['notice_date'], $notice['is_pinned'], $notice['is_active'],
                        $notice['file_path'] ?: null,
                    ]);
                    $flash = 'নোটিশ সফলভাবে যোগ করা হয়েছে।';
                } else {
                    $pdo->prepare(
                        'UPDATE notices SET title=?,content=?,category=?,notice_date=?,
                         is_pinned=?,is_active=?,file_path=? WHERE id=?'
                    )->execute([
                        $notice['title'], $notice['content'], $notice['category'],
                        $notice['notice_date'], $notice['is_pinned'], $notice['is_active'],
                        $notice['file_path'] ?: null, $eid,
                    ]);
                    $flash = 'নোটিশ সফলভাবে আপডেট করা হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/notices.php?flash=' . urlencode($flash));
                exit;
            }
        }
    }
    /* If errors on save, stay on form */
    $action = (isset($_POST['post_action']) && $_POST['post_action'] === 'edit') ? 'edit' : 'add';
}

/* ── Flash from redirect ─────────────────────────────────────────────────── */
if (!empty($_GET['flash'])) {
    $flash = sanitize($_GET['flash']);
}

/* ── Load notice for edit ────────────────────────────────────────────────── */
if ($action === 'edit' && $id) {
    $row = $pdo->prepare('SELECT * FROM notices WHERE id=?');
    $row->execute([$id]);
    $fetched = $row->fetch();
    if ($fetched) { $notice = $fetched; } else { $action = 'list'; }
}

/* ── List (paginated) ────────────────────────────────────────────────────── */
$page    = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;
$cat     = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';

$where = ''; $params = [];
if ($cat !== '') { $where = 'WHERE category=?'; $params[] = $cat; }

$total = $pdo->prepare("SELECT COUNT(*) FROM notices $where");
$total->execute($params);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$listStmt = $pdo->prepare("SELECT * FROM notices $where ORDER BY is_pinned DESC, notice_date DESC LIMIT $perPage OFFSET $offset");
$listStmt->execute($params);
$notices = $listStmt->fetchAll();

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white">
      <?php echo $action === 'list' ? 'নোটিশ ব্যবস্থাপনা' : ($action === 'add' ? 'নতুন নোটিশ' : 'নোটিশ সম্পাদনা'); ?>
    </h1>
    <p class="text-kma-muted text-xs mt-0.5"><?php echo h($totalRows); ?> টি নোটিশ</p>
  </div>
  <?php if ($action === 'list'): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/notices.php?action=add" class="btn-primary">
    <i class="bi bi-plus-lg"></i> নতুন নোটিশ
  </a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/notices.php" class="btn-outline">
    <i class="bi bi-arrow-left"></i> তালিকায় ফিরুন
  </a>
  <?php endif; ?>
</div>

<?php if (!empty($flash)): ?>
<div class="alert <?php echo $flashType === 'error' ? 'alert-error' : 'alert-success'; ?>">
  <i class="bi <?php echo $flashType === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill'; ?>"></i>
  <?php echo h($flash); ?>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error flex-col items-start">
  <?php foreach ($errors as $e): ?><div class="flex items-center gap-2"><i class="bi bi-exclamation-circle-fill"></i><?php echo h($e); ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── LIST ── -->
<?php if ($action === 'list'): ?>

<!-- Filter bar -->
<div class="flex flex-wrap gap-2 mb-4">
  <?php
  $cats = [''=> 'সব','notice'=>'নোটিশ','exam'=>'পরীক্ষা','holiday'=>'ছুটি','event'=>'অনুষ্ঠান','general'=>'সাধারণ'];
  foreach ($cats as $cv => $cl): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/notices.php<?php echo $cv !== '' ? '?cat=' . urlencode($cv) : ''; ?>"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors
            <?php echo $cat === $cv ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted dark:text-gray-400 hover:border-accent hover:text-accent'; ?>">
    <?php echo h($cl); ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="admin-card overflow-hidden">
  <?php if (empty($notices)): ?>
  <div class="py-12 text-center text-kma-muted text-sm"><i class="bi bi-bell-slash text-3xl block mb-2 opacity-30"></i>কোনো নোটিশ নেই</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>শিরোনাম</th><th>ক্যাটাগরি</th><th>তারিখ</th><th>পিন</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
        <?php foreach ($notices as $nt): ?>
        <tr>
          <td class="max-w-[220px]">
            <div class="font-semibold text-kma-dark dark:text-gray-200 truncate text-xs"><?php echo h($nt['title']); ?></div>
            <?php if (!empty($nt['file_path'])): ?>
            <span class="text-[0.65rem] text-kma-muted"><i class="bi bi-paperclip"></i> সংযুক্তি আছে</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?php echo h(noticeCategoryClass($nt['category'])); ?>"><?php echo h(noticeCategoryLabel($nt['category'])); ?></span></td>
          <td class="text-xs text-kma-muted"><?php echo date('d/m/Y', strtotime($nt['notice_date'])); ?></td>
          <td>
            <form method="POST" class="inline">
              <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
              <input type="hidden" name="post_action" value="toggle_pin"/>
              <input type="hidden" name="notice_id" value="<?php echo (int)$nt['id']; ?>"/>
              <button type="submit" class="text-lg <?php echo $nt['is_pinned'] ? 'text-gold' : 'text-kma-border hover:text-gold'; ?> transition-colors" title="পিন টগল করুন">
                <i class="bi bi-pin-fill"></i>
              </button>
            </form>
          </td>
          <td>
            <span class="badge <?php echo $nt['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>">
              <?php echo $nt['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
            </span>
          </td>
          <td>
            <div class="flex items-center gap-2">
              <a href="<?php echo BASE_URL; ?>/admin/views/notices.php?action=edit&id=<?php echo (int)$nt['id']; ?>"
                 class="text-accent hover:underline text-xs font-semibold"><i class="bi bi-pencil-fill"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('এই নোটিশটি মুছে ফেলবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete"/>
                <input type="hidden" name="notice_id" value="<?php echo (int)$nt['id']; ?>"/>
                <button type="submit" class="text-red-500 hover:text-red-700 text-xs"><i class="bi bi-trash-fill"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="flex items-center justify-between px-5 py-3 border-t border-kma-border dark:border-gray-700">
    <span class="text-xs text-kma-muted">পৃষ্ঠা <?php echo $page; ?> / <?php echo $totalPages; ?></span>
    <div class="flex gap-1">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?php echo $p; ?><?php echo $cat ? '&cat='.urlencode($cat) : ''; ?>"
         class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors
                <?php echo $p === $page ? 'bg-accent text-white' : 'bg-kma-bg dark:bg-gray-700 text-kma-muted hover:bg-accent hover:text-white'; ?>">
        <?php echo $p; ?>
      </a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- ── ADD / EDIT FORM ── -->
<?php else: ?>
<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/notices.php" enctype="multipart/form-data" class="admin-card p-6 max-w-3xl">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>"/>
  <?php if ($action === 'edit'): ?>
  <input type="hidden" name="notice_id" value="<?php echo (int)($notice['id'] ?? $id); ?>"/>
  <input type="hidden" name="old_file" value="<?php echo h($notice['file_path'] ?? ''); ?>"/>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4 mb-4">
    <div class="sm:col-span-2">
      <label class="form-label">শিরোনাম <span class="text-red-500">*</span></label>
      <input type="text" name="title" class="form-input" required
             value="<?php echo h($notice['title']); ?>" placeholder="নোটিশের শিরোনাম লিখুন"/>
    </div>
    <div>
      <label class="form-label">ক্যাটাগরি</label>
      <select name="category" class="form-input">
        <?php
        $ncats = ['notice'=>'নোটিশ','exam'=>'পরীক্ষা','holiday'=>'ছুটি','event'=>'অনুষ্ঠান','general'=>'সাধারণ'];
        foreach ($ncats as $nv => $nl): ?>
        <option value="<?php echo h($nv); ?>" <?php echo ($notice['category'] ?? '') === $nv ? 'selected' : ''; ?>><?php echo h($nl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">তারিখ <span class="text-red-500">*</span></label>
      <input type="date" name="notice_date" class="form-input" required
             value="<?php echo h($notice['notice_date'] ?? date('Y-m-d')); ?>"/>
    </div>
    <div class="sm:col-span-2">
      <label class="form-label">বিবরণ <span class="text-red-500">*</span></label>
      <textarea name="content" class="form-input" rows="6" required
                placeholder="নোটিশের বিস্তারিত বিবরণ..."><?php echo h($notice['content']); ?></textarea>
    </div>
    <div>
      <label class="form-label">ফাইল সংযুক্তি (ঐচ্ছিক)</label>
      <input type="file" name="notice_file" class="form-input py-2" accept=".pdf,.jpg,.jpeg,.png,.webp"/>
      <?php if (!empty($notice['file_path'])): ?>
      <p class="text-xs text-kma-muted mt-1"><i class="bi bi-paperclip"></i> বর্তমান: <?php echo h($notice['file_path']); ?></p>
      <?php endif; ?>
    </div>
    <div class="flex flex-col gap-3 justify-center">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_pinned" value="1" class="accent-accent"
               <?php echo !empty($notice['is_pinned']) ? 'checked' : ''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">পিন করুন</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="accent-accent"
               <?php echo (isset($notice['is_active']) && $notice['is_active']) || $action === 'add' ? 'checked' : ''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
      </label>
    </div>
  </div>

  <div class="flex gap-3 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary">
      <i class="bi bi-check-lg"></i> <?php echo $action === 'edit' ? 'আপডেট করুন' : 'নোটিশ যোগ করুন'; ?>
    </button>
    <a href="<?php echo BASE_URL; ?>/admin/views/notices.php" class="btn-outline">বাতিল</a>
  </div>
</form>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>