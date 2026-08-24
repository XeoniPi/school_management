<?php
/**
 * KMA — admin/views/admissions.php  |  PHP 7.2
 * List + status update + view detail for admissions.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$id     = isset($_GET['id'])     ? (int)$_GET['id']         : 0;
$currentAdminPage = 'admissions';
$pageTitle = 'ভর্তি আবেদন | KMA Admin';

$flash = '';
$flashType = 'success';

/* ── POST handlers ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $postAction = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        /* Update status */
        if ($postAction === 'update_status') {
            $aid    = (int)(isset($_POST['admission_id']) ? $_POST['admission_id'] : 0);
            $status = sanitize(isset($_POST['status'])      ? $_POST['status']      : '');
            $note   = sanitize(isset($_POST['admin_note'])  ? $_POST['admin_note']  : '');
            $allowed = ['pending','approved','rejected','enrolled'];
            if ($aid && in_array($status, $allowed)) {
                $pdo->prepare('UPDATE admissions SET status=?, admin_note=?, updated_at=NOW() WHERE id=?')
                    ->execute([$status, $note, $aid]);
                $flash = 'আবেদনের স্ট্যাটাস আপডেট হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/admissions.php?flash=' . urlencode($flash));
            exit;
        }

        /* Delete */
        if ($postAction === 'delete') {
            $aid = (int)(isset($_POST['admission_id']) ? $_POST['admission_id'] : 0);
            if ($aid) {
                $pdo->prepare('DELETE FROM admissions WHERE id=?')->execute([$aid]);
                $flash = 'আবেদনটি মুছে ফেলা হয়েছে।';
            }
            header('Location: ' . BASE_URL . '/admin/views/admissions.php?flash=' . urlencode($flash));
            exit;
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

/* ── Load single for detail view ─────────────────────────────────────────── */
$admission = null;
if ($action === 'view' && $id) {
    $stmt = $pdo->prepare(
        'SELECT a.*, c.class_name FROM admissions a
         LEFT JOIN classes c ON c.id = a.apply_class_id
         WHERE a.id = ?'
    );
    $stmt->execute([$id]);
    $admission = $stmt->fetch();
    if (!$admission) { $action = 'list'; }
}

/* ── List (paginated + filtered) ─────────────────────────────────────────── */
$page    = max(1, (int)(isset($_GET['page'])   ? $_GET['page']   : 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$filter  = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search  = isset($_GET['q'])      ? sanitize($_GET['q'])      : '';

$where  = '1';
$params = [];
if ($filter !== '') { $where .= ' AND a.status=?';     $params[] = $filter; }
if ($search !== '') {
    $where .= ' AND (a.student_name_bn LIKE ? OR a.app_no LIKE ? OR a.guardian_phone LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like]);
}

$countSql = "SELECT COUNT(*) FROM admissions a WHERE $where";
$cStmt = $pdo->prepare($countSql);
$cStmt->execute($params);
$totalRows  = (int)$cStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$listSql = "SELECT a.*, c.class_name FROM admissions a
            LEFT JOIN classes c ON c.id = a.apply_class_id
            WHERE $where ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset";
$lStmt = $pdo->prepare($listSql);
$lStmt->execute($params);
$admissions = $lStmt->fetchAll();

/* Status summary counts */
$sumRows = $pdo->query(
    "SELECT status, COUNT(*) AS cnt FROM admissions GROUP BY status"
)->fetchAll();
$sumMap = ['pending'=>0,'approved'=>0,'rejected'=>0,'enrolled'=>0];
foreach ($sumRows as $sr) {
    if (isset($sumMap[$sr['status']])) { $sumMap[$sr['status']] = (int)$sr['cnt']; }
}
$sumMap['total'] = array_sum($sumMap);

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';

/* Helpers */
$stBadge = [
    'pending'  => 'bg-amber-100 text-amber-700',
    'approved' => 'bg-green-100 text-green-700',
    'rejected' => 'bg-red-100 text-red-700',
    'enrolled' => 'bg-blue-100 text-blue-700',
];
$stLabel = ['pending'=>'অপেক্ষমাণ','approved'=>'অনুমোদিত','rejected'=>'বাতিল','enrolled'=>'ভর্তি'];
?>

<div class="flex items-center justify-between mb-5">
  <div>
    <h1 class="text-lg font-bold text-kma-dark dark:text-white">
      <?php echo $action === 'view' ? 'আবেদনের বিবরণ' : 'ভর্তি আবেদন'; ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <p class="text-kma-muted text-xs mt-0.5">মোট <?php echo h($totalRows); ?> টি আবেদন</p>
    <?php endif; ?>
  </div>
  <?php if ($action === 'view'): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php" class="btn-outline">
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

<?php if ($action === 'list'): ?>

<!-- Summary cards -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
  <?php
  $sumCards = [
    ['total','bi-people-fill','bg-gray-500','মোট'],
    ['pending','bi-hourglass-split','bg-amber-500','অপেক্ষমাণ'],
    ['approved','bi-check-circle-fill','bg-green-600','অনুমোদিত'],
    ['enrolled','bi-mortarboard-fill','bg-blue-600','ভর্তি'],
    ['rejected','bi-x-circle-fill','bg-red-600','বাতিল'],
  ];
  foreach ($sumCards as $sc): ?>
  <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php<?php echo $sc[0] !== 'total' ? '?status='.$sc[0] : ''; ?>"
     class="admin-card p-4 flex items-center gap-3 hover:shadow-md transition-shadow <?php echo $filter === $sc[0] ? 'ring-2 ring-accent' : ''; ?>">
    <div class="w-9 h-9 rounded-lg <?php echo h($sc[2]); ?> flex items-center justify-center text-white text-sm flex-shrink-0">
      <i class="bi <?php echo h($sc[1]); ?>"></i>
    </div>
    <div>
      <div class="font-bold text-lg text-kma-dark dark:text-white leading-none"><?php echo h($sumMap[$sc[0]] ?? 0); ?></div>
      <div class="text-[0.7rem] text-kma-muted font-semibold"><?php echo h($sc[3]); ?></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Filters + search -->
<div class="flex flex-wrap gap-2 mb-4">
  <form method="GET" class="flex items-center gap-2 flex-1 min-w-[200px]">
    <?php if ($filter): ?><input type="hidden" name="status" value="<?php echo h($filter); ?>"/><?php endif; ?>
    <input type="text" name="q" class="form-input py-2 text-sm max-w-xs"
           placeholder="নাম, আবেদন নং বা ফোনে খুঁজুন..." value="<?php echo h($search); ?>"/>
    <button type="submit" class="btn-primary py-2 px-4 text-xs"><i class="bi bi-search"></i> খুঁজুন</button>
    <?php if ($search || $filter): ?>
    <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php" class="btn-outline py-2 px-3 text-xs">
      <i class="bi bi-x-lg"></i> রিসেট
    </a>
    <?php endif; ?>
  </form>
</div>

<!-- Table -->
<div class="admin-card overflow-hidden">
  <?php if (empty($admissions)): ?>
  <div class="py-12 text-center text-kma-muted text-sm"><i class="bi bi-inbox text-3xl block mb-2 opacity-30"></i>কোনো আবেদন পাওয়া যায়নি</div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table>
      <thead><tr>
        <th>আবেদন নং</th><th>শিক্ষার্থী</th><th>শ্রেণি</th><th>অভিভাবক</th><th>তারিখ</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th>
      </tr></thead>
      <tbody>
        <?php foreach ($admissions as $adm):
          $bc = isset($stBadge[$adm['status']]) ? $stBadge[$adm['status']] : 'bg-gray-100 text-gray-600';
          $bl = isset($stLabel[$adm['status']]) ? $stLabel[$adm['status']] : $adm['status'];
        ?>
        <tr>
          <td class="font-mono text-xs text-kma-muted"><?php echo h($adm['app_no']); ?></td>
          <td>
            <div class="font-semibold text-xs text-kma-dark dark:text-gray-200"><?php echo h($adm['student_name_bn']); ?></div>
            <div class="text-[0.7rem] text-kma-muted"><?php echo h($adm['student_name_en']); ?></div>
          </td>
          <td class="text-xs"><?php echo h($adm['class_name'] ?? '—'); ?></td>
          <td>
            <div class="text-xs text-kma-dark dark:text-gray-300"><?php echo h($adm['father_name']); ?></div>
            <div class="text-[0.7rem] text-kma-muted"><?php echo h($adm['guardian_phone']); ?></div>
          </td>
          <td class="text-xs text-kma-muted whitespace-nowrap"><?php echo date('d/m/Y', strtotime($adm['created_at'])); ?></td>
          <td><span class="badge <?php echo h($bc); ?>"><?php echo h($bl); ?></span></td>
          <td>
            <div class="flex items-center gap-2">
              <a href="<?php echo BASE_URL; ?>/admin/views/admissions.php?action=view&id=<?php echo (int)$adm['id']; ?>"
                 class="text-accent hover:underline text-xs font-semibold"><i class="bi bi-eye-fill"></i> দেখুন</a>
              <form method="POST" class="inline" onsubmit="return confirm('এই আবেদনটি স্থায়ীভাবে মুছবেন?')">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
                <input type="hidden" name="post_action" value="delete"/>
                <input type="hidden" name="admission_id" value="<?php echo (int)$adm['id']; ?>"/>
                <button type="submit" class="text-red-400 hover:text-red-600 text-xs"><i class="bi bi-trash-fill"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="flex items-center justify-between px-5 py-3 border-t border-kma-border dark:border-gray-700">
    <span class="text-xs text-kma-muted">পৃষ্ঠা <?php echo $page; ?> / <?php echo $totalPages; ?></span>
    <div class="flex gap-1">
      <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
      <a href="?page=<?php echo $p; ?><?php echo $filter ? '&status='.urlencode($filter) : ''; ?><?php echo $search ? '&q='.urlencode($search) : ''; ?>"
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

<?php else: /* ── DETAIL VIEW ── */ ?>

<?php if ($admission): ?>
<div class="grid lg:grid-cols-3 gap-5">

  <!-- Left: main info -->
  <div class="lg:col-span-2 space-y-5">

    <!-- Student info card -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4 pb-2 border-b border-kma-border dark:border-gray-700">
        <i class="bi bi-person-fill text-accent mr-1"></i> শিক্ষার্থীর তথ্য
      </h2>
      <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <?php
        $fields = [
          ['বাংলা নাম',       $admission['student_name_bn']],
          ['ইংরেজি নাম',      $admission['student_name_en']],
          ['জন্ম তারিখ',      $admission['dob'] ? date('d/m/Y', strtotime($admission['dob'])) : '—'],
          ['লিঙ্গ',           $admission['gender'] === 'male' ? 'ছেলে' : 'মেয়ে'],
          ['ধর্ম',            $admission['religion']],
          ['রক্তের গ্রুপ',   $admission['blood_group'] ?: '—'],
          ['আবেদনকৃত শ্রেণি', $admission['class_name'] ?? '—'],
          ['জন্ম নিবন্ধন নং', $admission['birth_cert_no']],
          ['পূর্ববর্তী বিদ্যালয়', $admission['prev_school'] ?: '—'],
        ];
        foreach ($fields as $f): ?>
        <div>
          <span class="text-kma-muted text-xs"><?php echo h($f[0]); ?></span>
          <div class="font-semibold text-kma-dark dark:text-gray-200 text-xs mt-0.5"><?php echo h($f[1]); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Guardian info -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4 pb-2 border-b border-kma-border dark:border-gray-700">
        <i class="bi bi-people-fill text-accent mr-1"></i> অভিভাবকের তথ্য
      </h2>
      <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <?php
        $gFields = [
          ['পিতার নাম',     $admission['father_name']],
          ['মাতার নাম',     $admission['mother_name']],
          ['পিতার পেশা',    $admission['father_occupation'] ?: '—'],
          ['মাতার পেশা',    $admission['mother_occupation'] ?: '—'],
          ['মোবাইল নম্বর', $admission['guardian_phone']],
          ['ইমেইল',         $admission['guardian_email'] ?: '—'],
          ['পিতার NID',     $admission['father_nid']],
          ['বার্ষিক আয়',   $admission['annual_income'] ?: '—'],
        ];
        foreach ($gFields as $f): ?>
        <div>
          <span class="text-kma-muted text-xs"><?php echo h($f[0]); ?></span>
          <div class="font-semibold text-kma-dark dark:text-gray-200 text-xs mt-0.5"><?php echo h($f[1]); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Address + extras -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4 pb-2 border-b border-kma-border dark:border-gray-700">
        <i class="bi bi-geo-alt-fill text-accent mr-1"></i> ঠিকানা ও অন্যান্য
      </h2>
      <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <?php
        $aFields = [
          ['ঠিকানা',          $admission['address']],
          ['জেলা',             $admission['district']],
          ['উপজেলা',           $admission['upazila'] ?: '—'],
          ['পোস্ট কোড',       $admission['post_code'] ?: '—'],
          ['বৃত্তির আবেদন',  $admission['scholarship_apply'] ? 'হ্যাঁ' : 'না'],
          ['কীভাবে জানলেন',  $admission['hear_about'] ?: '—'],
        ];
        foreach ($aFields as $f): ?>
        <div>
          <span class="text-kma-muted text-xs"><?php echo h($f[0]); ?></span>
          <div class="font-semibold text-kma-dark dark:text-gray-200 text-xs mt-0.5"><?php echo h($f[1]); ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($admission['remarks'])): ?>
        <div class="sm:col-span-2">
          <span class="text-kma-muted text-xs">মন্তব্য</span>
          <div class="text-xs text-kma-dark dark:text-gray-200 mt-0.5"><?php echo h($admission['remarks']); ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Attachments -->
    <?php if (!empty($admission['photo_path']) || !empty($admission['birth_cert_path'])): ?>
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-3 pb-2 border-b border-kma-border dark:border-gray-700">
        <i class="bi bi-paperclip text-accent mr-1"></i> সংযুক্তি
      </h2>
      <div class="flex gap-4 flex-wrap">
        <?php if (!empty($admission['photo_path'])): ?>
        <div>
          <p class="text-xs text-kma-muted mb-1">শিক্ষার্থীর ছবি</p>
          <img src="<?php echo UPLOAD_IMAGES_URL . h($admission['photo_path']); ?>"
               alt="ছবি" class="w-20 h-20 object-cover rounded-xl border border-kma-border shadow" />
        </div>
        <?php endif; ?>
        <?php if (!empty($admission['birth_cert_path'])): ?>
        <div>
          <p class="text-xs text-kma-muted mb-1">জন্ম নিবন্ধন সনদ</p>
          <a href="<?php echo UPLOAD_PDFS_URL . h($admission['birth_cert_path']); ?>" target="_blank"
             class="flex items-center gap-2 text-xs text-accent hover:underline font-semibold">
            <i class="bi bi-file-earmark-pdf text-red-500 text-2xl"></i> ফাইল দেখুন
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Right: status + meta -->
  <div class="space-y-5">

    <!-- App info -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-3">আবেদন তথ্য</h2>
      <div class="space-y-2 text-sm">
        <div><span class="text-kma-muted text-xs">আবেদন নং</span>
             <div class="font-mono font-bold text-accent"><?php echo h($admission['app_no']); ?></div></div>
        <div><span class="text-kma-muted text-xs">আইপি ঠিকানা</span>
             <div class="text-xs font-semibold dark:text-gray-300"><?php echo h($admission['ip_address'] ?? '—'); ?></div></div>
        <div><span class="text-kma-muted text-xs">জমার তারিখ</span>
             <div class="text-xs font-semibold dark:text-gray-300"><?php echo date('d/m/Y h:i A', strtotime($admission['created_at'])); ?></div></div>
        <div><span class="text-kma-muted text-xs">বর্তমান স্ট্যাটাস</span>
             <div><span class="badge <?php echo h($stBadge[$admission['status']] ?? 'bg-gray-100 text-gray-600'); ?> text-xs mt-0.5">
               <?php echo h($stLabel[$admission['status']] ?? $admission['status']); ?>
             </span></div></div>
        <?php if (!empty($admission['admin_note'])): ?>
        <div><span class="text-kma-muted text-xs">অ্যাডমিন নোট</span>
             <div class="text-xs text-kma-dark dark:text-gray-300 mt-0.5"><?php echo h($admission['admin_note']); ?></div></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Update status form -->
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4">স্ট্যাটাস পরিবর্তন করুন</h2>
      <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/admissions.php">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
        <input type="hidden" name="post_action" value="update_status"/>
        <input type="hidden" name="admission_id" value="<?php echo (int)$admission['id']; ?>"/>
        <div class="mb-3">
          <label class="form-label">নতুন স্ট্যাটাস</label>
          <select name="status" class="form-input">
            <?php foreach ($stLabel as $sv => $sl): ?>
            <option value="<?php echo h($sv); ?>" <?php echo $admission['status'] === $sv ? 'selected' : ''; ?>>
              <?php echo h($sl); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label">অ্যাডমিন নোট (ঐচ্ছিক)</label>
          <textarea name="admin_note" class="form-input" rows="3"
                    placeholder="অতিরিক্ত মন্তব্য..."><?php echo h($admission['admin_note'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn-primary w-full justify-center">
          <i class="bi bi-check-lg"></i> আপডেট করুন
        </button>
      </form>
    </div>

    <!-- Print / delete -->
    <div class="flex gap-2">
      <button onclick="window.print()"
              class="btn-outline flex-1 justify-center text-xs py-2.5">
        <i class="bi bi-printer-fill"></i> প্রিন্ট
      </button>
      <form method="POST" class="flex-1" onsubmit="return confirm('এই আবেদনটি স্থায়ীভাবে মুছে ফেলবেন?')">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
        <input type="hidden" name="post_action" value="delete"/>
        <input type="hidden" name="admission_id" value="<?php echo (int)$admission['id']; ?>"/>
        <button type="submit" class="btn-danger w-full justify-center text-xs py-2.5">
          <i class="bi bi-trash-fill"></i> মুছুন
        </button>
      </form>
    </div>

  </div>
</div>
<?php endif; ?>

<?php endif; /* end view */ ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>