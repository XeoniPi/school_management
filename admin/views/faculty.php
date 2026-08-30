<?php
/**
 * KMA — admin/views/faculty.php  |  PHP 7.2
 * Full CRUD for faculty / staff directory (shown on pages/about.php).
 * Categories are FIXED (administration / teacher / staff) — only
 * members are added/edited/deleted here, not the categories themselves.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'faculty';
$pageTitle = 'শিক্ষক ও প্রশাসন | KMA Admin';

$categories = [
    'administration' => 'প্রশাসন',
    'teacher'        => 'শিক্ষকমণ্ডলী',
    'staff'          => 'সহায়ক স্টাফ',
];
$catColor = [
    'administration' => 'bg-purple-100 text-purple-700',
    'teacher'        => 'bg-blue-100 text-blue-700',
    'staff'          => 'bg-green-100 text-green-700',
];

$flash = ''; $flashType = 'success'; $errors = [];
$member = [
    'name_bn'=>'', 'name_en'=>'', 'designation'=>'', 'category'=>'teacher',
    'education'=>'', 'experience'=>'', 'photo_path'=>'', 'email'=>'',
    'phone'=>'', 'portfolio_url'=>'', 'bio'=>'', 'sort_order'=>0, 'is_active'=>1,
];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        /* Delete */
        if ($pa === 'delete') {
            $did = (int)(isset($_POST['faculty_id']) ? $_POST['faculty_id'] : 0);
            if ($did) {
                $row = $pdo->prepare('SELECT photo_path FROM faculty WHERE id=?');
                $row->execute([$did]);
                $r = $row->fetch();
                if ($r && !empty($r['photo_path'])) {
                    $fp = UPLOAD_IMAGES . $r['photo_path'];
                    if (file_exists($fp)) { @unlink($fp); }
                }
                $pdo->prepare('DELETE FROM faculty WHERE id=?')->execute([$did]);
                $flash = 'সদস্যকে মুছে ফেলা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/faculty.php?flash=' . urlencode($flash)); exit;
        }

        /* Toggle active */
        if ($pa === 'toggle') {
            $tid = (int)(isset($_POST['faculty_id']) ? $_POST['faculty_id'] : 0);
            if ($tid) {
                $pdo->prepare('UPDATE faculty SET is_active = NOT is_active WHERE id=?')->execute([$tid]);
            }
            header('Location: ' . BASE_URL . '/admin/views/faculty.php'); exit;
        }

        /* Save (add / edit) */
        if (in_array($pa, ['add', 'edit'])) {
            $eid = (int)(isset($_POST['faculty_id']) ? $_POST['faculty_id'] : 0);
            $member = [
                'name_bn'       => sanitize(isset($_POST['name_bn'])       ? $_POST['name_bn']       : ''),
                'name_en'       => sanitize(isset($_POST['name_en'])       ? $_POST['name_en']       : ''),
                'designation'   => sanitize(isset($_POST['designation'])   ? $_POST['designation']   : ''),
                'category'      => sanitize(isset($_POST['category'])      ? $_POST['category']      : 'teacher'),
                'education'     => sanitize(isset($_POST['education'])     ? $_POST['education']     : ''),
                'experience'    => sanitize(isset($_POST['experience'])    ? $_POST['experience']    : ''),
                'photo_path'    => sanitize(isset($_POST['old_photo'])     ? $_POST['old_photo']     : ''),
                'email'         => sanitize(isset($_POST['email'])         ? $_POST['email']         : ''),
                'phone'         => sanitize(isset($_POST['phone'])         ? $_POST['phone']         : ''),
                'portfolio_url' => sanitize(isset($_POST['portfolio_url']) ? $_POST['portfolio_url'] : ''),
                'bio'           => sanitize(isset($_POST['bio'])           ? $_POST['bio']           : ''),
                'sort_order'    => (int)(isset($_POST['sort_order'])       ? $_POST['sort_order']    : 0),
                'is_active'     => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (mb_strlen($member['name_bn'])     < 2) { $errors[] = 'নাম (বাংলা) লিখুন।'; }
            if (mb_strlen($member['designation'])  < 2) { $errors[] = 'পদবি লিখুন।'; }
            if (!array_key_exists($member['category'], $categories)) { $member['category'] = 'teacher'; }
            if (!empty($member['email']) && !filter_var($member['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'সঠিক ইমেইল দিন।'; }

            /* Photo upload */
            if (!empty($_FILES['photo']['name'])) {
                $file = $_FILES['photo'];
                if ($file['size'] > MAX_IMG_SIZE) {
                    $errors[] = 'ছবির আকার সর্বোচ্চ ২ MB।';
                } elseif (!in_array($file['type'], ALLOWED_IMG_TYPES)) {
                    $errors[] = 'শুধুমাত্র JPG, PNG বা WEBP ছবি আপলোড করুন।';
                } else {
                    $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'faculty_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], UPLOAD_IMAGES . $fname);
                    if (!empty($member['photo_path']) && file_exists(UPLOAD_IMAGES . $member['photo_path'])) {
                        @unlink(UPLOAD_IMAGES . $member['photo_path']);
                    }
                    $member['photo_path'] = $fname;
                }
            }

            if (empty($errors)) {
                if ($pa === 'add') {
                    $pdo->prepare(
                        'INSERT INTO faculty
                         (name_bn,name_en,designation,category,education,experience,photo_path,email,phone,portfolio_url,bio,sort_order,is_active,uploaded_by)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                    )->execute([
                        $member['name_bn'], $member['name_en'], $member['designation'], $member['category'],
                        $member['education'], $member['experience'], $member['photo_path'], $member['email'],
                        $member['phone'], $member['portfolio_url'], $member['bio'], $member['sort_order'],
                        $member['is_active'], (int)$_SESSION['admin_id'],
                    ]);
                    $flash = 'সদস্য যোগ করা হয়েছে।';
                } else {
                    $pdo->prepare(
                        'UPDATE faculty SET name_bn=?,name_en=?,designation=?,category=?,education=?,experience=?,
                         photo_path=?,email=?,phone=?,portfolio_url=?,bio=?,sort_order=?,is_active=? WHERE id=?'
                    )->execute([
                        $member['name_bn'], $member['name_en'], $member['designation'], $member['category'],
                        $member['education'], $member['experience'], $member['photo_path'], $member['email'],
                        $member['phone'], $member['portfolio_url'], $member['bio'], $member['sort_order'],
                        $member['is_active'], $eid,
                    ]);
                    $flash = 'তথ্য আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/faculty.php?flash=' . urlencode($flash)); exit;
            }
            $action = $pa === 'edit' ? 'edit' : 'add';
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

if ($action === 'edit' && $id) {
    $row = $pdo->prepare('SELECT * FROM faculty WHERE id=?');
    $row->execute([$id]);
    $f = $row->fetch();
    if ($f) { $member = $f; } else { $action = 'list'; }
}

/* List */
$catFilter = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';
$where = '1'; $params = [];
if ($catFilter && array_key_exists($catFilter, $categories)) { $where .= ' AND category=?'; $params[] = $catFilter; }
$rows = $pdo->prepare("SELECT * FROM faculty WHERE $where ORDER BY category, sort_order ASC, name_bn ASC");
$rows->execute($params);
$facultyList = $rows->fetchAll();

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white">
      <?php echo $action==='list' ? 'শিক্ষক ও প্রশাসন' : ($action==='add' ? 'নতুন সদস্য যোগ' : 'তথ্য সম্পাদনা'); ?>
    </h1>
    <?php if ($action==='list'): ?>
    <p class="text-kma-muted text-xs mt-0.5"><?php echo count($facultyList); ?> জন সদস্য</p>
    <?php endif; ?>
  </div>
  <?php if ($action === 'list'): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-person-plus-fill"></i> নতুন সদস্য</a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/faculty.php" class="btn-outline"><i class="bi bi-arrow-left"></i> তালিকায় ফিরুন</a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- Category filter -->
<div class="flex flex-wrap gap-2 mb-5">
  <a href="<?php echo BASE_URL; ?>/admin/views/faculty.php"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors <?php echo $catFilter==='' ? 'bg-kma-dark text-white border-kma-dark' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    সবাই
  </a>
  <?php foreach ($categories as $cv=>$cl): ?>
  <a href="?cat=<?php echo urlencode($cv); ?>"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors
            <?php echo $catFilter===$cv ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    <?php echo h($cl); ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($facultyList)): ?>
<div class="admin-card py-14 text-center text-kma-muted text-sm">
  <i class="bi bi-people text-4xl block mb-3 opacity-30"></i>
  কোনো সদস্য নেই। <a href="?action=add" class="text-accent font-semibold hover:underline">এখনই যোগ করুন →</a>
</div>
<?php else: ?>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
  <?php foreach ($facultyList as $m): ?>
  <div class="admin-card p-0 overflow-hidden group relative hover:shadow-lg transition-shadow">
    <div class="aspect-square relative overflow-hidden bg-gray-100 dark:bg-gray-700">
      <?php if (!empty($m['photo_path'])): ?>
      <img src="<?php echo UPLOAD_IMAGES_URL . h($m['photo_path']); ?>"
           alt="<?php echo h($m['name_bn']); ?>"
           class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
           loading="lazy" onerror="this.src='https://placehold.co/300x300/e8f4eb/2e6b3e?text=No+Photo'" />
      <?php else: ?>
      <div class="w-full h-full flex items-center justify-center text-5xl text-kma-border"><i class="bi bi-person-fill"></i></div>
      <?php endif; ?>
      <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
        <a href="?action=edit&id=<?php echo (int)$m['id']; ?>"
           class="w-9 h-9 rounded-full bg-accent/80 hover:bg-accent flex items-center justify-center text-white transition-colors" title="সম্পাদনা">
          <i class="bi bi-pencil-fill text-sm"></i>
        </a>
        <form method="POST" class="inline" onsubmit="return confirm('এই সদস্যকে মুছে ফেলবেন?')">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
          <input type="hidden" name="post_action" value="delete"/>
          <input type="hidden" name="faculty_id" value="<?php echo (int)$m['id']; ?>"/>
          <button type="submit" class="w-9 h-9 rounded-full bg-red-600/80 hover:bg-red-600 flex items-center justify-center text-white transition-colors" title="মুছুন">
            <i class="bi bi-trash-fill text-sm"></i>
          </button>
        </form>
      </div>
      <?php if (!$m['is_active']): ?>
      <div class="absolute top-2 left-2 bg-gray-800/80 text-white text-[0.6rem] font-bold px-2 py-0.5 rounded">নিষ্ক্রিয়</div>
      <?php endif; ?>
    </div>
    <div class="p-2.5">
      <div class="text-xs font-semibold text-kma-dark dark:text-gray-200 truncate"><?php echo h($m['name_bn']); ?></div>
      <div class="text-[0.65rem] text-kma-muted truncate"><?php echo h($m['designation']); ?></div>
      <div class="flex items-center justify-between mt-1.5">
        <span class="text-[0.6rem] font-bold px-1.5 py-0.5 rounded <?php echo h($catColor[$m['category']] ?? 'bg-gray-100 text-gray-600'); ?>">
          <?php echo h($categories[$m['category']] ?? $m['category']); ?>
        </span>
        <form method="POST" class="inline">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
          <input type="hidden" name="post_action" value="toggle"/>
          <input type="hidden" name="faculty_id" value="<?php echo (int)$m['id']; ?>"/>
          <button type="submit"
                  class="text-xs <?php echo $m['is_active'] ? 'text-green-600 hover:text-red-500' : 'text-gray-400 hover:text-green-600'; ?> transition-colors"
                  title="<?php echo $m['is_active'] ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন'; ?>">
            <i class="bi <?php echo $m['is_active'] ? 'bi-eye-fill' : 'bi-eye-slash-fill'; ?>"></i>
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
  <div class="lg:col-span-2">
    <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/faculty.php" enctype="multipart/form-data" class="admin-card p-6">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
      <input type="hidden" name="post_action" value="<?php echo $action==='edit'?'edit':'add'; ?>"/>
      <?php if ($action==='edit'): ?>
      <input type="hidden" name="faculty_id" value="<?php echo (int)($member['id']??$id); ?>"/>
      <input type="hidden" name="old_photo" value="<?php echo h($member['photo_path']??''); ?>"/>
      <?php endif; ?>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="form-label">নাম (বাংলা) <span class="text-red-500">*</span></label>
          <input type="text" name="name_bn" class="form-input" required value="<?php echo h($member['name_bn']); ?>" placeholder="যেমন: মো. মহিন উদ্দিন"/>
        </div>
        <div>
          <label class="form-label">নাম (English)</label>
          <input type="text" name="name_en" class="form-input" value="<?php echo h($member['name_en']??''); ?>" placeholder="e.g. Md. Mohin Uddin"/>
        </div>
        <div>
          <label class="form-label">পদবি <span class="text-red-500">*</span></label>
          <input type="text" name="designation" class="form-input" required value="<?php echo h($member['designation']); ?>" placeholder="যেমন: প্রধান শিক্ষক"/>
        </div>
        <div>
          <label class="form-label">ক্যাটাগরি</label>
          <select name="category" class="form-input">
            <?php foreach ($categories as $cv=>$cl): ?>
            <option value="<?php echo h($cv); ?>" <?php echo ($member['category']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">শিক্ষাগত যোগ্যতা</label>
          <input type="text" name="education" class="form-input" value="<?php echo h($member['education']??''); ?>" placeholder="যেমন: এম.এ, বি.এড"/>
        </div>
        <div>
          <label class="form-label">অভিজ্ঞতা</label>
          <input type="text" name="experience" class="form-input" value="<?php echo h($member['experience']??''); ?>" placeholder="যেমন: ১০ বছর"/>
        </div>
        <div>
          <label class="form-label">ইমেইল</label>
          <input type="email" name="email" class="form-input" value="<?php echo h($member['email']??''); ?>" placeholder="example@kma.edu.bd"/>
        </div>
        <div>
          <label class="form-label">ফোন নম্বর</label>
          <input type="text" name="phone" class="form-input" value="<?php echo h($member['phone']??''); ?>" placeholder="01XXXXXXXXX"/>
        </div>
        <div>
          <label class="form-label">পার্সোনাল পোর্টফোলিও (ঐচ্ছিক)</label>
          <input type="url" name="portfolio_url" class="form-input" value="<?php echo h($member['portfolio_url']??''); ?>" placeholder="https://..."/>
        </div>
        <div>
          <label class="form-label">ক্রম (Sort Order)</label>
          <input type="number" name="sort_order" class="form-input" min="0" value="<?php echo (int)($member['sort_order']??0); ?>"/>
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">সংক্ষিপ্ত বিবরণ (Bio)</label>
          <textarea name="bio" class="form-input" rows="3" placeholder="সদস্য সম্পর্কে সংক্ষিপ্ত বিবরণ..."><?php echo h($member['bio']??''); ?></textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="form-label">প্রোফাইল ছবি <?php echo $action==='add'?'':'(নতুন ছবি দিলে পুরানোটি প্রতিস্থাপিত হবে)'; ?></label>
          <label class="block border-2 border-dashed border-kma-border rounded-xl p-8 text-center cursor-pointer hover:border-accent hover:bg-accent/5 transition-colors" id="dropZone">
            <input type="file" name="photo" id="imageInput" class="hidden" accept="image/jpeg,image/png,image/webp"/>
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
                   <?php echo (isset($member['is_active'])&&$member['is_active'])||$action==='add'?'checked':''; ?>/>
            <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয় (ওয়েবসাইটে দেখাবে)</span>
          </label>
        </div>
      </div>

      <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
        <button type="submit" class="btn-primary">
          <i class="bi bi-check-lg"></i> <?php echo $action==='edit'?'আপডেট করুন':'সদস্য যোগ করুন'; ?>
        </button>
        <a href="<?php echo BASE_URL; ?>/admin/views/faculty.php" class="btn-outline">বাতিল</a>
      </div>
    </form>
  </div>

  <?php if ($action === 'edit' && !empty($member['photo_path'])): ?>
  <div class="lg:col-span-1">
    <div class="admin-card p-4">
      <h3 class="text-sm font-bold text-kma-dark dark:text-white mb-3">বর্তমান ছবি</h3>
      <img src="<?php echo UPLOAD_IMAGES_URL . h($member['photo_path']); ?>"
           alt="<?php echo h($member['name_bn']); ?>"
           class="w-full rounded-xl object-cover aspect-square"
           onerror="this.src='https://placehold.co/300x300/e8f4eb/2e6b3e?text=No+Photo'"/>
    </div>
  </div>
  <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/admin-uploader.js"></script>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>
