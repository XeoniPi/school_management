<?php
/**
 * KMA — admin/views/classes.php  |  PHP 7.2
 * Manage classes and their subjects.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$tab    = isset($_GET['tab'])    ? sanitize($_GET['tab'])    : 'classes';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'classes';
$pageTitle = 'শ্রেণি ও বিষয় | KMA Admin';

$flash = ''; $flashType = 'success'; $errors = [];

$emptyClass = ['class_key'=>'','class_name'=>'','class_name_en'=>'','age_range'=>'','description'=>'','sort_order'=>0,'is_active'=>1];
$emptySubj  = ['name_bn'=>'','name_en'=>'','type'=>'core','color_class'=>'s-bn','sort_order'=>0,'is_active'=>1];
$cls  = $emptyClass;
$subj = $emptySubj;

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $pa  = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');
        $eid = (int)(isset($_POST['record_id'])    ? $_POST['record_id']    : 0);

        /* ── CLASS CRUD ── */
        if ($pa === 'delete_class') {
            $pdo->prepare('UPDATE classes SET is_active=0 WHERE id=?')->execute([$eid]);
            $flash = 'শ্রেণি নিষ্ক্রিয় করা হয়েছে।';
            header('Location: ' . BASE_URL . '/admin/views/classes.php?flash=' . urlencode($flash)); exit;
        }

        if (in_array($pa, ['add_class','edit_class'])) {
            $cls = [
                'class_key'    => sanitize(isset($_POST['class_key'])    ? $_POST['class_key']    : ''),
                'class_name'   => sanitize(isset($_POST['class_name'])   ? $_POST['class_name']   : ''),
                'class_name_en'=> sanitize(isset($_POST['class_name_en'])? $_POST['class_name_en']: ''),
                'age_range'    => sanitize(isset($_POST['age_range'])    ? $_POST['age_range']    : ''),
                'description'  => sanitize(isset($_POST['description'])  ? $_POST['description']  : ''),
                'sort_order'   => (int)(isset($_POST['sort_order'])      ? $_POST['sort_order']   : 0),
                'is_active'    => isset($_POST['is_active']) ? 1 : 0,
            ];
            if (mb_strlen($cls['class_key'])  < 1) { $errors[] = 'ক্লাস কী লিখুন (যেমন: c1, pk)।'; }
            if (mb_strlen($cls['class_name']) < 2) { $errors[] = 'শ্রেণির নাম লিখুন।'; }

            if (empty($errors)) {
                if ($pa === 'add_class') {
                    $pdo->prepare('INSERT INTO classes (class_key,class_name,class_name_en,age_range,description,sort_order,is_active) VALUES (?,?,?,?,?,?,?)')->execute([$cls['class_key'],$cls['class_name'],$cls['class_name_en'],$cls['age_range'],$cls['description'],$cls['sort_order'],$cls['is_active']]);
                    $flash = 'শ্রেণি যোগ করা হয়েছে।';
                } else {
                    $pdo->prepare('UPDATE classes SET class_key=?,class_name=?,class_name_en=?,age_range=?,description=?,sort_order=?,is_active=? WHERE id=?')->execute([$cls['class_key'],$cls['class_name'],$cls['class_name_en'],$cls['age_range'],$cls['description'],$cls['sort_order'],$cls['is_active'],$eid]);
                    $flash = 'শ্রেণি আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/classes.php?tab=classes&flash=' . urlencode($flash)); exit;
            }
            $tab = 'classes'; $action = $pa === 'edit_class' ? 'edit_class' : 'add_class';
        }

        /* ── SUBJECT CRUD ── */
        if ($pa === 'delete_subject') {
            $pdo->prepare('DELETE FROM subjects WHERE id=?')->execute([$eid]);
            $flash = 'বিষয় মুছে ফেলা হয়েছে।';
            header('Location: ' . BASE_URL . '/admin/views/classes.php?tab=subjects&flash=' . urlencode($flash)); exit;
        }

        if (in_array($pa, ['add_subject','edit_subject'])) {
            $subj = [
                'name_bn'     => sanitize(isset($_POST['name_bn'])     ? $_POST['name_bn']     : ''),
                'name_en'     => sanitize(isset($_POST['name_en'])     ? $_POST['name_en']     : ''),
                'type'        => sanitize(isset($_POST['type'])        ? $_POST['type']        : 'core'),
                'color_class' => sanitize(isset($_POST['color_class']) ? $_POST['color_class'] : 's-bn'),
                'sort_order'  => (int)(isset($_POST['sort_order'])     ? $_POST['sort_order']  : 0),
                'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            ];
            if (mb_strlen($subj['name_bn']) < 2) { $errors[] = 'বাংলা নাম লিখুন।'; }
            if (empty($errors)) {
                if ($pa === 'add_subject') {
                    $pdo->prepare('INSERT INTO subjects (name_bn,name_en,type,color_class,sort_order,is_active) VALUES (?,?,?,?,?,?)')->execute([$subj['name_bn'],$subj['name_en'],$subj['type'],$subj['color_class'],$subj['sort_order'],$subj['is_active']]);
                    $flash = 'বিষয় যোগ করা হয়েছে।';
                } else {
                    $pdo->prepare('UPDATE subjects SET name_bn=?,name_en=?,type=?,color_class=?,sort_order=?,is_active=? WHERE id=?')->execute([$subj['name_bn'],$subj['name_en'],$subj['type'],$subj['color_class'],$subj['sort_order'],$subj['is_active'],$eid]);
                    $flash = 'বিষয় আপডেট হয়েছে।';
                }
                header('Location: ' . BASE_URL . '/admin/views/classes.php?tab=subjects&flash=' . urlencode($flash)); exit;
            }
            $tab = 'subjects'; $action = $pa === 'edit_subject' ? 'edit_subject' : 'add_subject';
        }

        /* ── CLASS-SUBJECT ASSIGNMENT ── */
        if ($pa === 'assign_subject') {
            $classId   = (int)(isset($_POST['class_id'])   ? $_POST['class_id']   : 0);
            $subjectId = (int)(isset($_POST['subject_id']) ? $_POST['subject_id'] : 0);
            $teacher   = sanitize(isset($_POST['teacher_name']) ? $_POST['teacher_name'] : '');
            $sortOrd   = (int)(isset($_POST['sort_order']) ? $_POST['sort_order'] : 0);
            if ($classId && $subjectId) {
                /* Upsert */
                $chk = $pdo->prepare('SELECT id FROM class_subjects WHERE class_id=? AND subject_id=?');
                $chk->execute([$classId,$subjectId]);
                if ($chk->fetch()) {
                    $pdo->prepare('UPDATE class_subjects SET teacher_name=?,sort_order=? WHERE class_id=? AND subject_id=?')->execute([$teacher,$sortOrd,$classId,$subjectId]);
                } else {
                    $pdo->prepare('INSERT INTO class_subjects (class_id,subject_id,teacher_name,sort_order) VALUES (?,?,?,?)')->execute([$classId,$subjectId,$teacher,$sortOrd]);
                }
                $flash = 'বিষয় নির্ধারণ করা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/classes.php?tab=assign&flash=' . urlencode($flash)); exit;
        }

        if ($pa === 'remove_assignment') {
            $classId   = (int)(isset($_POST['class_id'])   ? $_POST['class_id']   : 0);
            $subjectId = (int)(isset($_POST['subject_id']) ? $_POST['subject_id'] : 0);
            if ($classId && $subjectId) {
                $pdo->prepare('DELETE FROM class_subjects WHERE class_id=? AND subject_id=?')->execute([$classId,$subjectId]);
                $flash = 'বিষয় সরানো হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/classes.php?tab=assign&class_id=' . $classId . '&flash=' . urlencode($flash)); exit;
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

/* Load for edit */
if ($action === 'edit_class' && $id) {
    $r = $pdo->prepare('SELECT * FROM classes WHERE id=?'); $r->execute([$id]); $f = $r->fetch();
    if ($f) { $cls = $f; } else { $action = 'list'; }
}
if ($action === 'edit_subject' && $id) {
    $r = $pdo->prepare('SELECT * FROM subjects WHERE id=?'); $r->execute([$id]); $f = $r->fetch();
    if ($f) { $subj = $f; } else { $action = 'list'; }
}

/* Data for lists */
$classes  = $pdo->query('SELECT * FROM classes  ORDER BY sort_order')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY sort_order,name_bn')->fetchAll();

/* Assignment view */
$assignClassId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : (isset($classes[0]) ? (int)$classes[0]['id'] : 0);
$assignedSubjs = [];
if ($tab === 'assign' && $assignClassId) {
    $aStmt = $pdo->prepare(
        'SELECT cs.*, s.name_bn, s.type FROM class_subjects cs
         JOIN subjects s ON s.id=cs.subject_id
         WHERE cs.class_id=? ORDER BY cs.sort_order'
    );
    $aStmt->execute([$assignClassId]);
    $assignedSubjs = $aStmt->fetchAll();
}

$csrf = generateCsrfToken();
$subjectTypes  = ['core'=>'মূল বিষয়','religion'=>'ধর্ম','extra'=>'সহশিক্ষা','optional'=>'ঐচ্ছিক'];
$colorClasses  = ['s-bn'=>'বাংলা (নীল)','s-en'=>'ইংরেজি (সবুজ)','s-math'=>'গণিত (হলুদ)','s-sci'=>'বিজ্ঞান (বেগুনি)','s-soc'=>'সমাজ (কমলা)','s-rel'=>'ধর্ম (গোলাপি)','s-ict'=>'ICT (নীল)','s-art'=>'শিল্পকলা','s-pe'=>'শারীরিক শিক্ষা'];
$typeBadge = ['core'=>'bg-blue-100 text-blue-700','religion'=>'bg-yellow-100 text-yellow-700','extra'=>'bg-green-100 text-green-700','optional'=>'bg-gray-100 text-gray-600'];

require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white">শ্রেণি ও বিষয় ব্যবস্থাপনা</h1>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<!-- Tabs -->
<div class="flex gap-0 mb-5 bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-kma-border dark:border-gray-700">
  <?php
  $mainTabs = [
    ['classes',  'bi-grid-3x3-gap-fill', 'শ্রেণি'],
    ['subjects', 'bi-journal-bookmark-fill', 'বিষয়'],
    ['assign',   'bi-link-45deg', 'শ্রেণি-বিষয় নির্ধারণ'],
  ];
  foreach ($mainTabs as $mt): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=<?php echo h($mt[0]); ?>"
     class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-semibold transition-colors border-b-[3px]
            <?php echo $tab===$mt[0] ? 'border-accent text-accent bg-accent-light dark:bg-accent/10' : 'border-transparent text-kma-muted hover:text-accent'; ?>">
    <i class="bi <?php echo h($mt[1]); ?>"></i>
    <span class="hidden sm:inline"><?php echo h($mt[2]); ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- ══ CLASSES TAB ══ -->
<?php if ($tab === 'classes'): ?>

<?php if (in_array($action, ['add_class','edit_class'])): ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=classes" class="admin-card p-6 max-w-2xl mb-5">
  <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4"><?php echo $action==='edit_class'?'শ্রেণি সম্পাদনা':'নতুন শ্রেণি যোগ'; ?></h2>
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action==='edit_class'?'edit_class':'add_class'; ?>"/>
  <?php if ($action==='edit_class'): ?><input type="hidden" name="record_id" value="<?php echo (int)($cls['id']??$id); ?>"/><?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="form-label">ক্লাস কী <span class="text-red-500">*</span></label>
      <input type="text" name="class_key" class="form-input" required value="<?php echo h($cls['class_key']); ?>" placeholder="যেমন: pk, c1, c2"/>
      <p class="text-xs text-kma-muted mt-1">URL-safe কী, ছোট হাতে, ড্যাশ ব্যবহার করুন</p>
    </div>
    <div>
      <label class="form-label">শ্রেণির নাম (বাংলা) <span class="text-red-500">*</span></label>
      <input type="text" name="class_name" class="form-input" required value="<?php echo h($cls['class_name']); ?>" placeholder="যেমন: প্রথম শ্রেণি"/>
    </div>
    <div>
      <label class="form-label">শ্রেণির নাম (ইংরেজি)</label>
      <input type="text" name="class_name_en" class="form-input" value="<?php echo h($cls['class_name_en']??''); ?>" placeholder="e.g. Class One"/>
    </div>
    <div>
      <label class="form-label">বয়সসীমা</label>
      <input type="text" name="age_range" class="form-input" value="<?php echo h($cls['age_range']??''); ?>" placeholder="যেমন: ৬–৭ বছর"/>
    </div>
    <div>
      <label class="form-label">সাজানোর ক্রম</label>
      <input type="number" name="sort_order" class="form-input" value="<?php echo (int)($cls['sort_order']??0); ?>" min="0"/>
    </div>
    <div class="flex items-end pb-1">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="accent-accent" <?php echo (isset($cls['is_active'])&&$cls['is_active'])||$action==='add_class'?'checked':''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
      </label>
    </div>
    <div class="sm:col-span-2">
      <label class="form-label">বিবরণ</label>
      <textarea name="description" class="form-input" rows="2" placeholder="শ্রেণির সংক্ষিপ্ত বিবরণ..."><?php echo h($cls['description']??''); ?></textarea>
    </div>
  </div>
  <div class="flex gap-3 mt-4 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo $action==='edit_class'?'আপডেট':'যোগ করুন'; ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=classes" class="btn-outline">বাতিল</a>
  </div>
</form>

<?php else: ?>

<div class="flex justify-end mb-3">
  <a href="?tab=classes&action=add_class" class="btn-primary"><i class="bi bi-plus-lg"></i> নতুন শ্রেণি</a>
</div>
<div class="admin-card overflow-hidden">
  <?php if (empty($classes)): ?>
  <div class="py-10 text-center text-kma-muted text-sm"><i class="bi bi-grid-3x3-gap text-3xl block mb-2 opacity-30"></i>কোনো শ্রেণি নেই</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>ক্লাস কী</th><th>নাম (বাংলা)</th><th>ইংরেজি নাম</th><th>বয়সসীমা</th><th>ক্রম</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
        <?php foreach ($classes as $c): ?>
        <tr>
          <td><code class="text-xs bg-kma-bg dark:bg-gray-700 px-2 py-0.5 rounded font-mono"><?php echo h($c['class_key']); ?></code></td>
          <td class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($c['class_name']); ?></td>
          <td class="text-xs text-kma-muted"><?php echo h($c['class_name_en']??'—'); ?></td>
          <td class="text-xs text-kma-muted"><?php echo h($c['age_range']??'—'); ?></td>
          <td class="text-xs text-kma-muted"><?php echo (int)$c['sort_order']; ?></td>
          <td><span class="badge <?php echo $c['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $c['is_active']?'সক্রিয়':'নিষ্ক্রিয়'; ?></span></td>
          <td>
            <div class="flex gap-2">
              <a href="?tab=classes&action=edit_class&id=<?php echo (int)$c['id']; ?>" class="text-accent text-xs font-semibold hover:underline"><i class="bi bi-pencil-fill"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('শ্রেণিটি নিষ্ক্রিয় করবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete_class"/>
                <input type="hidden" name="record_id" value="<?php echo (int)$c['id']; ?>"/>
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
<?php endif; ?>

<!-- ══ SUBJECTS TAB ══ -->
<?php elseif ($tab === 'subjects'): ?>

<?php if (in_array($action, ['add_subject','edit_subject'])): ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=subjects" class="admin-card p-6 max-w-2xl mb-5">
  <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4"><?php echo $action==='edit_subject'?'বিষয় সম্পাদনা':'নতুন বিষয় যোগ'; ?></h2>
  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
  <input type="hidden" name="post_action" value="<?php echo $action==='edit_subject'?'edit_subject':'add_subject'; ?>"/>
  <?php if ($action==='edit_subject'): ?><input type="hidden" name="record_id" value="<?php echo (int)($subj['id']??$id); ?>"/><?php endif; ?>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="form-label">বিষয়ের নাম (বাংলা) <span class="text-red-500">*</span></label>
      <input type="text" name="name_bn" class="form-input" required value="<?php echo h($subj['name_bn']); ?>" placeholder="যেমন: বাংলা"/>
    </div>
    <div>
      <label class="form-label">বিষয়ের নাম (ইংরেজি)</label>
      <input type="text" name="name_en" class="form-input" value="<?php echo h($subj['name_en']??''); ?>" placeholder="e.g. Bangla"/>
    </div>
    <div>
      <label class="form-label">ধরন</label>
      <select name="type" class="form-input">
        <?php foreach ($subjectTypes as $tv=>$tl): ?>
        <option value="<?php echo h($tv); ?>" <?php echo ($subj['type']??'')===$tv?'selected':''; ?>><?php echo h($tl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">রঙ ক্লাস</label>
      <select name="color_class" class="form-input">
        <?php foreach ($colorClasses as $cv=>$cl): ?>
        <option value="<?php echo h($cv); ?>" <?php echo ($subj['color_class']??'')===$cv?'selected':''; ?>><?php echo h($cl); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">সাজানোর ক্রম</label>
      <input type="number" name="sort_order" class="form-input" value="<?php echo (int)($subj['sort_order']??0); ?>" min="0"/>
    </div>
    <div class="flex items-end pb-1">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="accent-accent" <?php echo (isset($subj['is_active'])&&$subj['is_active'])||$action==='add_subject'?'checked':''; ?>/>
        <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">সক্রিয়</span>
      </label>
    </div>
  </div>
  <div class="flex gap-3 mt-4 pt-4 border-t border-kma-border dark:border-gray-700">
    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> <?php echo $action==='edit_subject'?'আপডেট':'যোগ করুন'; ?></button>
    <a href="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=subjects" class="btn-outline">বাতিল</a>
  </div>
</form>

<?php else: ?>

<div class="flex justify-end mb-3">
  <a href="?tab=subjects&action=add_subject" class="btn-primary"><i class="bi bi-plus-lg"></i> নতুন বিষয়</a>
</div>
<div class="admin-card overflow-hidden">
  <?php if (empty($subjects)): ?>
  <div class="py-10 text-center text-kma-muted text-sm"><i class="bi bi-journal-x text-3xl block mb-2 opacity-30"></i>কোনো বিষয় নেই</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr><th>বাংলা নাম</th><th>ইংরেজি নাম</th><th>ধরন</th><th>রঙ ক্লাস</th><th>ক্রম</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr></thead>
      <tbody>
        <?php foreach ($subjects as $s):
          $tb = isset($typeBadge[$s['type']]) ? $typeBadge[$s['type']] : 'bg-gray-100 text-gray-600';
          $tl = isset($subjectTypes[$s['type']]) ? $subjectTypes[$s['type']] : $s['type'];
        ?>
        <tr>
          <td class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($s['name_bn']); ?></td>
          <td class="text-xs text-kma-muted"><?php echo h($s['name_en']??'—'); ?></td>
          <td><span class="badge <?php echo h($tb); ?>"><?php echo h($tl); ?></span></td>
          <td><code class="text-xs bg-kma-bg dark:bg-gray-700 px-1.5 py-0.5 rounded font-mono"><?php echo h($s['color_class']??'—'); ?></code></td>
          <td class="text-xs text-kma-muted"><?php echo (int)$s['sort_order']; ?></td>
          <td><span class="badge <?php echo $s['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>"><?php echo $s['is_active']?'সক্রিয়':'নিষ্ক্রিয়'; ?></span></td>
          <td>
            <div class="flex gap-2">
              <a href="?tab=subjects&action=edit_subject&id=<?php echo (int)$s['id']; ?>" class="text-accent text-xs font-semibold hover:underline"><i class="bi bi-pencil-fill"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('বিষয়টি মুছে ফেলবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete_subject"/>
                <input type="hidden" name="record_id" value="<?php echo (int)$s['id']; ?>"/>
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
<?php endif; ?>

<!-- ══ ASSIGN TAB ══ -->
<?php elseif ($tab === 'assign'): ?>

<div class="grid lg:grid-cols-3 gap-5">

  <!-- Class picker -->
  <div class="lg:col-span-1">
    <div class="admin-card p-4">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-3">শ্রেণি নির্বাচন</h2>
      <div class="space-y-1">
        <?php foreach ($classes as $c): ?>
        <a href="?tab=assign&class_id=<?php echo (int)$c['id']; ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors
                  <?php echo $assignClassId===(int)$c['id'] ? 'bg-accent text-white' : 'text-kma-muted hover:bg-kma-bg dark:hover:bg-gray-700 hover:text-accent'; ?>">
          <i class="bi bi-grid-3x3-gap-fill text-sm"></i>
          <?php echo h($c['class_name']); ?>
          <?php
          $cnt = $pdo->prepare('SELECT COUNT(*) FROM class_subjects WHERE class_id=?');
          $cnt->execute([(int)$c['id']]);
          $cntVal = (int)$cnt->fetchColumn();
          ?>
          <span class="ml-auto text-xs font-bold px-1.5 py-0.5 rounded-full <?php echo $assignClassId===(int)$c['id'] ? 'bg-white/20 text-white' : 'bg-kma-border text-kma-muted'; ?>"><?php echo $cntVal; ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Assignment panel -->
  <div class="lg:col-span-2 space-y-4">

    <?php if ($assignClassId): ?>

    <!-- Add subject form -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4">বিষয় যোগ করুন</h2>
      <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/classes.php?tab=assign&class_id=<?php echo $assignClassId; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
        <input type="hidden" name="post_action" value="assign_subject"/>
        <input type="hidden" name="class_id" value="<?php echo $assignClassId; ?>"/>
        <div class="grid sm:grid-cols-3 gap-3">
          <div class="sm:col-span-1">
            <label class="form-label">বিষয় <span class="text-red-500">*</span></label>
            <select name="subject_id" class="form-input" required>
              <option value="">বিষয় নির্বাচন করুন</option>
              <?php
              /* Already assigned IDs */
              $assignedIds = array_map(function($a){ return (int)$a['subject_id']; }, $assignedSubjs);
              foreach ($subjects as $s):
              ?>
              <option value="<?php echo (int)$s['id']; ?>" <?php echo in_array((int)$s['id'],$assignedIds)?'disabled':''; ?>>
                <?php echo h($s['name_bn']); ?> <?php echo in_array((int)$s['id'],$assignedIds)?'(যোগ আছে)':''; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">শিক্ষকের নাম</label>
            <input type="text" name="teacher_name" class="form-input" placeholder="শিক্ষকের নাম"/>
          </div>
          <div>
            <label class="form-label">ক্রম</label>
            <input type="number" name="sort_order" class="form-input" value="<?php echo count($assignedSubjs)+1; ?>" min="1"/>
          </div>
        </div>
        <button type="submit" class="btn-primary mt-3 text-sm"><i class="bi bi-plus-lg"></i> বিষয় যোগ করুন</button>
      </form>
    </div>

    <!-- Assigned subjects -->
    <div class="admin-card overflow-hidden">
      <div class="px-5 py-3.5 border-b border-kma-border dark:border-gray-700 font-bold text-sm text-kma-dark dark:text-white">
        <i class="bi bi-journal-bookmark-fill text-accent mr-1"></i>
        নির্ধারিত বিষয়সমূহ
        <?php
        $selClass = null;
        foreach ($classes as $c) { if ((int)$c['id']==$assignClassId) { $selClass=$c; break; } }
        if ($selClass): ?> — <?php echo h($selClass['class_name']); ?><?php endif; ?>
      </div>
      <?php if (empty($assignedSubjs)): ?>
      <div class="py-8 text-center text-kma-muted text-sm"><i class="bi bi-journal-x text-2xl block mb-2 opacity-30"></i>এই শ্রেণিতে কোনো বিষয় নির্ধারণ করা হয়নি</div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table>
          <thead><tr><th>ক্রম</th><th>বিষয়</th><th>ধরন</th><th>শিক্ষক</th><th>অ্যাকশন</th></tr></thead>
          <tbody>
            <?php foreach ($assignedSubjs as $as):
              $tb = isset($typeBadge[$as['type']]) ? $typeBadge[$as['type']] : 'bg-gray-100 text-gray-600';
              $tl = isset($subjectTypes[$as['type']]) ? $subjectTypes[$as['type']] : $as['type'];
            ?>
            <tr>
              <td class="text-xs font-mono text-kma-muted"><?php echo (int)$as['sort_order']; ?></td>
              <td class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($as['name_bn']); ?></td>
              <td><span class="badge <?php echo h($tb); ?>"><?php echo h($tl); ?></span></td>
              <td class="text-xs text-kma-muted"><?php echo h($as['teacher_name'] ?: '—'); ?></td>
              <td>
                <form method="POST" class="inline" onsubmit="return confirm('এই বিষয়টি সরিয়ে দেবেন?')">
                  <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                  <input type="hidden" name="post_action" value="remove_assignment"/>
                  <input type="hidden" name="class_id" value="<?php echo $assignClassId; ?>"/>
                  <input type="hidden" name="subject_id" value="<?php echo (int)$as['subject_id']; ?>"/>
                  <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-semibold">
                    <i class="bi bi-x-circle-fill"></i> সরান
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <div class="admin-card py-10 text-center text-kma-muted text-sm">বাম পাশ থেকে একটি শ্রেণি নির্বাচন করুন।</div>
    <?php endif; ?>
  </div>

</div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>