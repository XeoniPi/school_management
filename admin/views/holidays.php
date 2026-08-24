<?php
/**
 * KMA — admin/views/holidays.php  |  PHP 7.2
 * Full CRUD for holiday list.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'holidays';
$pageTitle = 'ছুটির তালিকা | KMA Admin';

$flash = ''; $flashType = 'success'; $errors = [];
$holiday = [
    'title'=>'','description'=>'','type'=>'govt','start_date'=>date('Y-m-d'),
    'end_date'=>'','duration'=>'','year'=>date('Y'),'is_active'=>1
];

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $postAction = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        if ($postAction === 'delete') {
            $did = (int)(isset($_POST['holiday_id']) ? $_POST['holiday_id'] : 0);
            if ($did) { $pdo->prepare('DELETE FROM holidays WHERE id=?')->execute([$did]); $flash = 'ছুটি মুছে ফেলা হয়েছে।'; }
            header('Location: ' . BASE_URL . '/admin/views/holidays.php?flash=' . urlencode($flash)); exit;
        }

        if (in_array($postAction, ['add','edit'])) {
            $holiday = [
                'title'       => sanitize(isset($_POST['title'])       ? $_POST['title']       : ''),
                'description' => sanitize(isset($_POST['description']) ? $_POST['description'] : ''),
                'type'        => sanitize(isset($_POST['type'])        ? $_POST['type']        : 'govt'),
                'start_date'  => sanitize(isset($_POST['start_date'])  ? $_POST['start_date']  : ''),
                'end_date'    => sanitize(isset($_POST['end_date'])    ? $_POST['end_date']    : ''),
                'duration'    => sanitize(isset($_POST['duration'])    ? $_POST['duration']    : ''),
                'year'        => (int)(isset($_POST['year'])           ? $_POST['year']        : date('Y')),
                'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            ];
            $eid = (int)(isset($_POST['holiday_id']) ? $_POST['holiday_id'] : 0);

            if (mb_strlen($holiday['title']) < 2) { $errors[] = 'শিরোনাম লিখুন।'; }
            if (empty($holiday['start_date']))     { $errors[] = 'শুরুর তারিখ দিন।'; }
            $allowedTypes = ['govt','school','exam','event'];
            if (!in_array($holiday['type'], $allowedTypes)) { $holiday['type'] = 'govt'; }

            if (empty($errors)) {
                $cols = 'title,description,type,start_date,end_date,duration,year,is_active';
                $vals = [$holiday['title'],$holiday['description'],$holiday['type'],
                         $holiday['start_date'],$holiday['end_date']?: null,
                         $holiday['duration'],$holiday['year'],$holiday['is_active']];
                if ($postAction === 'add') {
                    $pdo->prepare("INSERT INTO holidays ($cols) VALUES (?,?,?,?,?,?,?,?)")->execute($vals);
                    $flash = 'ছুটি যোগ করা হয়েছে।';
                } else {
                    $vals[] = $eid;
                    $pdo->prepare('UPDATE holidays SET title=?,description=?,type=?,start_date=?,end_date=?,duration=?,year=?,is_active=? WHERE id=?')->execute($vals);
                    $flash = 'ছুটি আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/holidays.php?flash=' . urlencode($flash)); exit;
            }
            $action = $postAction === 'edit' ? 'edit' : 'add';
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

if ($action === 'edit' && $id) {
    $row = $pdo->prepare('SELECT * FROM holidays WHERE id=?');
    $row->execute([$id]);
    $f = $row->fetch();
    if ($f) { $holiday = $f; } else { $action = 'list'; }
}

/* List */
$year   = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$type   = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$where  = 'WHERE year=?'; $params = [$year];
if ($type) { $where .= ' AND type=?'; $params[] = $type; }

$rows = $pdo->prepare("SELECT * FROM holidays $where ORDER BY start_date ASC");
$rows->execute($params);
$holidays = $rows->fetchAll();

$csrf = generateCsrfToken();
$types = ['govt'=>'সরকারি ছুটি','school'=>'বিদ্যালয় ছুটি','exam'=>'পরীক্ষা','event'=>'বিশেষ অনুষ্ঠান'];
$typeBadge = ['govt'=>'bg-red-100 text-red-700','school'=>'bg-green-100 text-green-700','exam'=>'bg-yellow-100 text-yellow-700','event'=>'bg-purple-100 text-purple-700'];
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white">
    <?php echo $action==='list' ? 'ছুটির তালিকা' : ($action==='add' ? 'নতুন ছুটি যোগ' : 'ছুটি সম্পাদনা'); ?>
  </h1>
  <?php if ($action === 'list'): ?>
  <a href="?action=add" class="btn-primary"><i class="bi bi-plus-lg"></i> নতুন ছুটি</a>
  <?php else: ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/holidays.php" class="btn-outline"><i class="bi bi-arrow-left"></i> তালিকায় ফিরুন</a>
  <?php endif; ?>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<?php if ($action === 'list'): ?>

<!-- Year + type filter -->
<div class="flex flex-wrap gap-2 mb-4">
  <?php for ($y = (int)date('Y')-1; $y <= (int)date('Y')+1; $y++): ?>
  <a href="?year=<?php echo $y; ?><?php echo $type?'&type='.urlencode($type):''; ?>"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors
            <?php echo $year===$y ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    <?php echo $y; ?>
  </a>
  <?php endfor; ?>
  <span class="border-l border-kma-border mx-1"></span>
  <a href="?year=<?php echo $year; ?>" class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors <?php echo $type==='' ? 'bg-kma-dark text-white border-kma-dark' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">সব</a>
  <?php foreach ($types as $tv => $tl): ?>
  <a href="?year=<?php echo $year; ?>&type=<?php echo urlencode($tv); ?>"
     class="text-xs font-bold px-3 py-1.5 rounded-full border transition-colors
            <?php echo $type===$tv ? 'bg-accent text-white border-accent' : 'bg-white dark:bg-gray-700 border-kma-border text-kma-muted hover:border-accent hover:text-accent'; ?>">
    <?php echo h($tl); ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="admin-card overflow-hidden">
  <?php if (empty($holidays)): ?>
  <div class="py-10 text-center text-kma-muted text-sm"><i class="bi bi-calendar-x text-3xl block mb-2 opacity-30"></i>কোনো ছুটি নেই</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>শিরোনাম</th><th>ধরন</th><th>শুরু</th><th>শেষ</th><th>মেয়াদ</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
        <?php foreach ($holidays as $h2):
          $bc = isset($typeBadge[$h2['type']]) ? $typeBadge[$h2['type']] : 'bg-gray-100 text-gray-600';
          $tl = isset($types[$h2['type']]) ? $types[$h2['type']] : $h2['type'];
        ?>
        <tr>
          <td>
            <div class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($h2['title']); ?></div>
            <?php if ($h2['description']): ?><div class="text-[0.7rem] text-kma-muted truncate max-w-[180px]"><?php echo h($h2['description']); ?></div><?php endif; ?>
          </td>
          <td><span class="badge <?php echo h($bc); ?>"><?php echo h($tl); ?></span></td>
          <td class="text-xs text-kma-muted whitespace-nowrap"><?php echo date('d/m/Y', strtotime($h2['start_date'])); ?></td>
          <td class="text-xs text-kma-muted whitespace-nowrap"><?php echo $h2['end_date'] ? date('d/m/Y', strtotime($h2['end_date'])) : '—'; ?></td>
          <td class="text-xs"><?php echo h($h2['duration'] ?: '—'); ?></td>
          <td><span class="badge <?php echo $h2['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $h2['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?></span></td>
          <td>
            <div class="flex gap-2">
              <a href="?action=edit&id=<?php echo (int)$h2['id']; ?>" class="text-accent text-xs font-semibold hover:underline"><i class="bi bi-pencil-fill"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('মুছে ফেলবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete"/>
                <input type="hidden" name="holiday_id" value="<?php echo (int)$h2['id']; ?>"/>
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

<?php else: /* add / edit form */ ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/holidays.php" class="admin-card p-6 max-w-2xl">
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action==='edit'?'edit':'add'; ?>"/>
  <?php if ($action==='edit'): ?>
  <input type="hidden" name="holiday_id" value="<?php echo (int)($holiday['id'] ?? $id); ?>"/>
  <?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
      <label class="form-label">শিরোনাম <span class="text-red-500">*</span></label>
      <input type="text" name="title" class="form-input" required value="<?php echo h($holiday['title']); ?>" placeholder="ছুটির শিরোনাম"/>
    </div>
    <div>
      <label class="form-label">ধরন</label>
      <select name="type" class="form-input">
        <?php foreach ($types as $tv=>$tl): ?>
        <option value="<?php echo h($tv); ?>" <?php echo ($holiday['type']??'')===$tv?'selected':''; ?>><?php echo h($tl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">বছর</label>
      <select name="year" class="form-input">
        <?php for ($y=(int)date('Y')-1;$y<=(int)date('Y')+1;$y++): ?>
        <option value="<?php echo $y; ?>" <?php echo ((int)($holiday['year']??date('Y')))===$y?'selected':''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div>
      <label class="form-label">শুরুর তারিখ <span class="text-red-500">*</span></label>
      <input type="date" name="start_date" class="form-input" required value="<?php echo h($holiday['start_date']); ?>"/>
    </div>
    <div>
      <label class="form-label">শেষ তারিখ (ঐচ্ছিক)</label>
      <input type="date" name="end_date" class="form-input" value="<?php echo h($holiday['end_date']??''); ?>"/>
    </div>
    <div>
      <label class="form-label">মেয়াদ (যেমন: ৩ দিন)</label>
      <input type="text" name="duration" class="form-input" value="<?php echo h($holiday['duration']??''); ?>" placeholder="যেমন: ১ দিন, ৩ দিন"/>
    </div>
    <div class="flex items-center mt-5">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="accent-accent"
               <?php echo (isset($holiday['is_active'])&&$holiday['is_active'])||$action==='add'?'checked':''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
      </label>
    </div>
    <div class="sm:col-span-2">
      <label class="form-label">বিবরণ</label>
      <textarea name="description" class="form-input" rows="3" placeholder="ঐচ্ছিক বিবরণ..."><?php echo h($holiday['description']??''); ?></textarea>
    </div>
  </div>
  <div class="flex gap-3 mt-5 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo $action==='edit'?'আপডেট করুন':'যোগ করুন'; ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/holidays.php" class="btn-outline">বাতিল</a>
  </div>
</form>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>