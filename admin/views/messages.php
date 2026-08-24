<?php
/**
 * KMA — admin/views/messages.php  |  PHP 7.2
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';

requireAdminLogin();

$pdo = getDB();

/* ── Handle hard delete ── */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'delete' &&
    verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')
) {
    $delId = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    if ($delId) {
        $pdo->prepare('DELETE FROM contact_messages WHERE id=?')->execute([$delId]);
    }
    header('Location: ' . BASE_URL . '/admin/views/messages.php?deleted=1');
    exit;
}

/* ── Filters ── */
$filterRead = isset($_GET['read']) ? $_GET['read'] : 'all'; // all | 0 | 1
$search     = isset($_GET['q'])    ? sanitize($_GET['q'])   : '';
$page       = isset($_GET['p'])    ? max(1,(int)$_GET['p']) : 1;
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

/* Build WHERE */
$where  = '1=1';
$params = [];
if ($filterRead === '0') { $where .= ' AND is_read=0'; }
if ($filterRead === '1') { $where .= ' AND is_read=1'; }
if ($search !== '') {
    $where .= ' AND (name LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like,$like,$like,$like]);
}

/* Count */
$total    = (int)$pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where")->execute($params) ?
            $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where")->execute($params) && 0 : 0;
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE $where");
$countStmt->execute($params);
$total    = (int)$countStmt->fetchColumn();
$pages    = $total > 0 ? (int)ceil($total / $perPage) : 1;

/* Fetch page */
$listParams = array_merge($params, [$perPage, $offset]);
$listStmt   = $pdo->prepare("SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$listStmt->execute($listParams);
$messages = $listStmt->fetchAll(PDO::FETCH_ASSOC);

/* Unread count for badge */
$unread = (int)$pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read=0')->fetchColumn();

/* Stats */
$stats = [
    'total'   => (int)$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
    'unread'  => $unread,
    'today'   => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
    'parents' => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE relation='parent'")->fetchColumn(),
];

$csrf = generateCsrfToken();

/* Subject labels */
$subjectLabels = [
    'admission' => 'ভর্তি সংক্রান্ত',
    'fee'       => 'ফি ও পেমেন্ট',
    'academic'  => 'একাডেমিক তথ্য',
    'result'    => 'ফলাফল ও পরীক্ষা',
    'complaint' => 'অভিযোগ / পরামর্শ',
    'job'       => 'চাকরির আবেদন',
    'other'     => 'অন্যান্য',
];
$subjectColors = [
    'admission' => 'bg-blue-100 text-blue-700',
    'fee'       => 'bg-yellow-100 text-yellow-700',
    'academic'  => 'bg-green-100 text-green-700',
    'result'    => 'bg-purple-100 text-purple-700',
    'complaint' => 'bg-red-100 text-red-600',
    'job'       => 'bg-orange-100 text-orange-700',
    'other'     => 'bg-gray-100 text-gray-600',
];

$pageTitle = 'যোগাযোগ বার্তা | KMA Admin';
require_once dirname(__DIR__) . '/includes/admin_header.php';
?>

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <div>
    <h1 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
      <i class="bi bi-envelope-fill text-accent"></i>
      যোগাযোগ বার্তা
      <?php if ($unread > 0): ?>
      <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $unread; ?></span>
      <?php endif; ?>
    </h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">ওয়েবসাইটের যোগাযোগ ফরম থেকে প্রাপ্ত বার্তাসমূহ</p>
  </div>
  <div class="flex gap-2">
    <a href="?read=0" class="btn-outline text-sm flex items-center gap-1.5">
      <i class="bi bi-envelope-fill"></i> অপঠিত (<?php echo $unread; ?>)
    </a>
  </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success mb-5"><i class="bi bi-check-circle-fill"></i> বার্তাটি সফলভাবে মুছে ফেলা হয়েছে।</div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $statCards = [
    ['bi-envelope-fill',      'text-blue-500',   'bg-blue-50 dark:bg-blue-900/20',   $stats['total'],   'মোট বার্তা'],
    ['bi-envelope-exclamation','text-red-500',    'bg-red-50 dark:bg-red-900/20',     $stats['unread'],  'অপঠিত'],
    ['bi-calendar-check',     'text-green-500',  'bg-green-50 dark:bg-green-900/20', $stats['today'],   'আজকের বার্তা'],
    ['bi-people-fill',        'text-purple-500', 'bg-purple-50 dark:bg-purple-900/20',$stats['parents'],'অভিভাবক'],
  ];
  foreach ($statCards as $sc): ?>
  <div class="admin-card p-4 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl <?php echo h($sc[2]); ?> flex items-center justify-center flex-shrink-0">
      <i class="bi <?php echo h($sc[0]); ?> <?php echo h($sc[1]); ?> text-xl"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo h($sc[3]); ?></div>
      <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo h($sc[4]); ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="admin-card p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <!-- Search -->
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">অনুসন্ধান</label>
      <div class="relative">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input type="text" name="q" value="<?php echo h($search); ?>"
               placeholder="নাম, ফোন, বিষয় বা বার্তা…"
               class="form-input pl-9 text-sm py-2" />
      </div>
    </div>
    <!-- Read filter -->
    <div>
      <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">স্ট্যাটাস</label>
      <select name="read" class="form-input text-sm py-2" style="min-width:130px">
        <option value="all" <?php echo $filterRead==='all' ? 'selected' : ''; ?>>সব বার্তা</option>
        <option value="0"   <?php echo $filterRead==='0'   ? 'selected' : ''; ?>>অপঠিত</option>
        <option value="1"   <?php echo $filterRead==='1'   ? 'selected' : ''; ?>>পঠিত</option>
      </select>
    </div>
    <button type="submit" class="btn-primary text-sm py-2">
      <i class="bi bi-funnel-fill"></i> ফিল্টার
    </button>
    <?php if ($search || $filterRead !== 'all'): ?>
    <a href="?" class="btn-outline text-sm py-2"><i class="bi bi-x-circle"></i> রিসেট</a>
    <?php endif; ?>
  </form>
</div>

<!-- Messages table -->
<div class="admin-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm" aria-label="যোগাযোগ বার্তার তালিকা">
      <thead>
        <tr class="border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8"></th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">প্রেরক</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">বিষয়</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">বার্তা (সংক্ষেপ)</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">তারিখ</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">অ্যাকশন</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        <?php if (empty($messages)): ?>
        <tr>
          <td colspan="6" class="text-center py-16 text-gray-400 dark:text-gray-500">
            <i class="bi bi-inbox text-4xl block mb-3"></i>
            কোনো বার্তা পাওয়া যায়নি।
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($messages as $msg):
          $isUnread  = !(int)$msg['is_read'];
          $rowBg     = $isUnread ? 'bg-blue-50/40 dark:bg-blue-900/10' : '';
          $subjLabel = isset($subjectLabels[$msg['subject']]) ? $subjectLabels[$msg['subject']] : h($msg['subject']);
          $subjColor = isset($subjectColors[$msg['subject']]) ? $subjectColors[$msg['subject']] : 'bg-gray-100 text-gray-600';
          $excerpt   = mb_strlen($msg['message']) > 60 ? mb_substr($msg['message'],0,60).'…' : $msg['message'];
        ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors <?php echo $rowBg; ?>"
            id="msg-row-<?php echo $msg['id']; ?>">

          <!-- Unread dot -->
          <td class="px-4 py-3 text-center">
            <?php if ($isUnread): ?>
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block" title="অপঠিত"></span>
            <?php else: ?>
            <span class="w-2.5 h-2.5 rounded-full bg-gray-200 dark:bg-gray-600 inline-block" title="পঠিত"></span>
            <?php endif; ?>
          </td>

          <!-- Sender -->
          <td class="px-4 py-3">
            <div class="font-semibold text-gray-800 dark:text-white <?php echo $isUnread ? 'font-bold' : ''; ?>">
              <?php echo h($msg['name']); ?>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
              <a href="tel:<?php echo h($msg['phone']); ?>" class="hover:text-accent transition-colors flex items-center gap-1">
                <i class="bi bi-telephone-fill"></i><?php echo h($msg['phone']); ?>
              </a>
              <?php if (!empty($msg['email'])): ?>
              <a href="mailto:<?php echo h($msg['email']); ?>" class="hover:text-accent transition-colors flex items-center gap-1">
                <i class="bi bi-envelope-fill"></i><?php echo h($msg['email']); ?>
              </a>
              <?php endif; ?>
            </div>
            <?php if (!empty($msg['relation'])): ?>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
              <?php
              $rels = ['parent'=>'অভিভাবক','student'=>'শিক্ষার্থী','guardian'=>'অন্য অভিভাবক','teacher'=>'শিক্ষক','other'=>'অন্যান্য'];
              echo isset($rels[$msg['relation']]) ? h($rels[$msg['relation']]) : h($msg['relation']);
              ?>
            </div>
            <?php endif; ?>
          </td>

          <!-- Subject -->
          <td class="px-4 py-3">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?php echo h($subjColor); ?>">
              <?php echo h($subjLabel); ?>
            </span>
            <?php if (!empty($msg['contact_method'])): ?>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
              <?php
              $cmIcons = ['phone'=>'bi-telephone','email'=>'bi-envelope','whatsapp'=>'bi-whatsapp'];
              $cmLabels= ['phone'=>'ফোনে','email'=>'ইমেইলে','whatsapp'=>'WhatsApp'];
              $cm = $msg['contact_method'];
              ?>
              <i class="bi <?php echo isset($cmIcons[$cm]) ? h($cmIcons[$cm]) : 'bi-chat'; ?>"></i>
              <?php echo isset($cmLabels[$cm]) ? h($cmLabels[$cm]) : h($cm); ?> উত্তর চান
            </div>
            <?php endif; ?>
          </td>

          <!-- Excerpt -->
          <td class="px-4 py-3 hidden md:table-cell">
            <p class="text-gray-600 dark:text-gray-400 text-xs max-w-xs"><?php echo h($excerpt); ?></p>
          </td>

          <!-- Date -->
          <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
            <div><?php echo date('d M Y', strtotime($msg['created_at'])); ?></div>
            <div><?php echo date('h:i A', strtotime($msg['created_at'])); ?></div>
          </td>

          <!-- Actions -->
          <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-2">
              <!-- View detail -->
              <button type="button"
                      onclick="viewMessage(<?php echo $msg['id']; ?>)"
                      class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-white transition-colors flex items-center justify-center"
                      title="বার্তা দেখুন">
                <i class="bi bi-eye-fill text-sm"></i>
              </button>

              <!-- Reply via phone -->
              <a href="tel:<?php echo h($msg['phone']); ?>"
                 class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-500 hover:text-white transition-colors flex items-center justify-center"
                 title="ফোন করুন">
                <i class="bi bi-telephone-fill text-sm"></i>
              </a>

              <?php if (!empty($msg['email'])): ?>
              <a href="mailto:<?php echo h($msg['email']); ?>"
                 class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-500 hover:text-white transition-colors flex items-center justify-center"
                 title="ইমেইল করুন">
                <i class="bi bi-envelope-fill text-sm"></i>
              </a>
              <?php endif; ?>

              <!-- Delete -->
              <form method="POST" class="inline" onsubmit="return confirm('এই বার্তাটি মুছে ফেলবেন?');">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>" />
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>" />
                <button type="submit"
                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center"
                        title="মুছুন">
                  <i class="bi bi-trash-fill text-sm"></i>
                </button>
              </form>
            </div>
          </td>

        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between flex-wrap gap-3">
    <p class="text-sm text-gray-500 dark:text-gray-400">
      মোট <strong><?php echo $total; ?></strong> বার্তা, পেজ <?php echo $page; ?> / <?php echo $pages; ?>
    </p>
    <div class="flex gap-1 flex-wrap">
      <?php if ($page > 1): ?>
      <a href="?p=<?php echo $page-1; ?>&read=<?php echo h($filterRead); ?>&q=<?php echo h($search); ?>"
         class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-accent hover:text-white transition-colors">
        <i class="bi bi-chevron-left"></i>
      </a>
      <?php endif; ?>
      <?php for ($pg = max(1,$page-2); $pg <= min($pages,$page+2); $pg++): ?>
      <a href="?p=<?php echo $pg; ?>&read=<?php echo h($filterRead); ?>&q=<?php echo h($search); ?>"
         class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                <?php echo $pg===$page ? 'bg-accent text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-accent hover:text-white'; ?>">
        <?php echo $pg; ?>
      </a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
      <a href="?p=<?php echo $page+1; ?>&read=<?php echo h($filterRead); ?>&q=<?php echo h($search); ?>"
         class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-accent hover:text-white transition-colors">
        <i class="bi bi-chevron-right"></i>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ── Message Detail Modal ── -->
<div id="msgModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="msgModalTitle">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeMsgModal()"></div>
  <!-- Panel -->
  <div class="absolute inset-y-0 right-0 w-full max-w-lg bg-white dark:bg-gray-800 shadow-2xl flex flex-col">
    <!-- Header -->
    <div class="bg-accent px-6 py-4 flex items-center justify-between flex-shrink-0">
      <h2 id="msgModalTitle" class="text-white font-bold flex items-center gap-2">
        <i class="bi bi-envelope-open-fill text-gold"></i>
        বার্তার বিস্তারিত
      </h2>
      <button onclick="closeMsgModal()" class="text-white/70 hover:text-white transition-colors">
        <i class="bi bi-x-lg text-xl"></i>
      </button>
    </div>
    <!-- Loading -->
    <div id="msgLoading" class="flex-1 flex items-center justify-center">
      <div class="text-center text-gray-400">
        <i class="bi bi-hourglass-split text-3xl block mb-2 animate-spin"></i>
        <p class="text-sm">লোড হচ্ছে…</p>
      </div>
    </div>
    <!-- Content -->
    <div id="msgContent" class="flex-1 overflow-y-auto hidden p-6 space-y-5">

      <!-- Sender info -->
      <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-12 h-12 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xl font-bold flex-shrink-0" id="md-avatar"></div>
          <div>
            <div class="font-bold text-gray-800 dark:text-white" id="md-name"></div>
            <div class="text-xs text-gray-500 dark:text-gray-400" id="md-relation"></div>
          </div>
          <div class="ml-auto" id="md-subject-badge"></div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <div class="text-xs text-gray-400 mb-0.5">ফোন</div>
            <a id="md-phone" href="#" class="font-semibold text-accent hover:underline"></a>
          </div>
          <div>
            <div class="text-xs text-gray-400 mb-0.5">ইমেইল</div>
            <a id="md-email" href="#" class="font-semibold text-accent hover:underline text-xs break-all"></a>
          </div>
          <div>
            <div class="text-xs text-gray-400 mb-0.5">পছন্দের যোগাযোগ</div>
            <div id="md-method" class="font-semibold text-gray-700 dark:text-gray-300 text-xs"></div>
          </div>
          <div>
            <div class="text-xs text-gray-400 mb-0.5">তারিখ</div>
            <div id="md-date" class="font-semibold text-gray-700 dark:text-gray-300 text-xs"></div>
          </div>
        </div>
      </div>

      <!-- Message body -->
      <div>
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
          <i class="bi bi-chat-left-text-fill text-accent mr-1"></i> বার্তা
        </div>
        <div id="md-message"
             class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
        </div>
      </div>

      <!-- IP info -->
      <div id="md-ip-wrap" class="text-xs text-gray-400 flex items-center gap-1">
        <i class="bi bi-globe"></i>
        <span id="md-ip"></span>
      </div>
    </div>

    <!-- Footer actions -->
    <div id="msgFooter" class="hidden border-t border-gray-200 dark:border-gray-600 px-6 py-4 flex gap-3 flex-shrink-0 flex-wrap">
      <a id="md-call-btn" href="#"
         class="flex-1 flex items-center justify-center gap-2 bg-green-500 text-white font-bold py-2.5 rounded-xl hover:bg-green-600 transition-colors text-sm">
        <i class="bi bi-telephone-fill"></i> ফোন করুন
      </a>
      <a id="md-email-btn" href="#"
         class="flex items-center justify-center gap-2 bg-blue-500 text-white font-bold px-4 py-2.5 rounded-xl hover:bg-blue-600 transition-colors text-sm">
        <i class="bi bi-envelope-fill"></i>
      </a>
      <a id="md-wa-btn" href="#" target="_blank" rel="noopener"
         class="flex items-center justify-center gap-2 bg-green-600 text-white font-bold px-4 py-2.5 rounded-xl hover:bg-green-700 transition-colors text-sm">
        <i class="bi bi-whatsapp"></i>
      </a>
      <form method="POST" class="inline" onsubmit="return confirm('এই বার্তাটি মুছে ফেলবেন?');" id="md-delete-form">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>" />
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="" id="md-delete-id" />
        <button type="submit"
                class="flex items-center justify-center gap-2 bg-red-500 text-white font-bold px-4 py-2.5 rounded-xl hover:bg-red-600 transition-colors text-sm">
          <i class="bi bi-trash-fill"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<script>
var ADMIN_AJAX = '<?php echo BASE_URL; ?>/admin/ajax/handler.php';
var CSRF_TOKEN = '<?php echo h($csrf); ?>';

var subjectLabels = <?php echo json_encode($subjectLabels); ?>;
var subjectColors = {
  admission:'bg-blue-100 text-blue-700',fee:'bg-yellow-100 text-yellow-700',
  academic:'bg-green-100 text-green-700',result:'bg-purple-100 text-purple-700',
  complaint:'bg-red-100 text-red-600',job:'bg-orange-100 text-orange-700',other:'bg-gray-100 text-gray-600'
};
var relationLabels = {parent:'অভিভাবক',student:'শিক্ষার্থী',guardian:'অন্য অভিভাবক',teacher:'শিক্ষক',other:'অন্যান্য'};
var methodLabels   = {phone:'ফোনে',email:'ইমেইলে',whatsapp:'WhatsApp-এ'};

function viewMessage(id) {
  /* Show modal */
  var modal = document.getElementById('msgModal');
  var loading = document.getElementById('msgLoading');
  var content = document.getElementById('msgContent');
  var footer  = document.getElementById('msgFooter');
  modal.classList.remove('hidden');
  loading.classList.remove('hidden');
  content.classList.add('hidden');
  footer.classList.add('hidden');
  document.body.style.overflow = 'hidden';

  /* AJAX */
  var fd = new FormData();
  fd.append('action', 'get_message');
  fd.append('id', id);
  fd.append('csrf_token', CSRF_TOKEN);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', ADMIN_AJAX, true);
  xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
  xhr.onload = function() {
    if (xhr.status === 200) {
      var data = JSON.parse(xhr.responseText);
      if (data.success) {
        populateModal(data.message);
        /* Mark row as read */
        var dot = document.querySelector('#msg-row-' + id + ' td:first-child span');
        if (dot) { dot.style.background = '#d1d5db'; }
        var row = document.getElementById('msg-row-' + id);
        if (row) { row.classList.remove('bg-blue-50/40','dark:bg-blue-900/10'); }
      }
    }
    loading.classList.add('hidden');
    content.classList.remove('hidden');
    footer.classList.remove('hidden');
  };
  xhr.send(fd);
}

function populateModal(m) {
  /* Avatar initial */
  var name = m.name || '?';
  document.getElementById('md-avatar').textContent = name.charAt(0).toUpperCase();

  document.getElementById('md-name').textContent = name;
  document.getElementById('md-relation').textContent = relationLabels[m.relation] || m.relation || '';

  /* Subject badge */
  var sb = document.getElementById('md-subject-badge');
  var slabel = subjectLabels[m.subject] || m.subject || '';
  var scolor = subjectColors[m.subject] || 'bg-gray-100 text-gray-600';
  sb.innerHTML = '<span class="text-xs font-bold px-2.5 py-1 rounded-full '+scolor+'">'+slabel+'</span>';

  /* Contact */
  var phone = m.phone || '';
  var email = m.email || '';
  var phoneEl = document.getElementById('md-phone');
  phoneEl.textContent = phone;
  phoneEl.href = 'tel:'+phone;

  var emailEl = document.getElementById('md-email');
  if (email) { emailEl.textContent = email; emailEl.href = 'mailto:'+email; }
  else { emailEl.textContent = '—'; emailEl.href = '#'; }

  document.getElementById('md-method').textContent = methodLabels[m.contact_method] || m.contact_method || '—';

  /* Date */
  var d = new Date(m.created_at);
  document.getElementById('md-date').textContent = d.toLocaleDateString('bn-BD') + ' ' + d.toLocaleTimeString('bn-BD');

  /* Message */
  document.getElementById('md-message').textContent = m.message || '';

  /* IP */
  var ipWrap = document.getElementById('md-ip-wrap');
  var ipEl   = document.getElementById('md-ip');
  if (m.ip_address) { ipEl.textContent = 'IP: ' + m.ip_address; ipWrap.style.display = ''; }
  else { ipWrap.style.display = 'none'; }

  /* Footer buttons */
  document.getElementById('md-call-btn').href  = 'tel:'+phone;
  document.getElementById('md-email-btn').href = email ? 'mailto:'+email : '#';
  document.getElementById('md-wa-btn').href    = 'https://wa.me/880'+phone.replace(/^0/,'');
  document.getElementById('md-delete-id').value = m.id;
}

function closeMsgModal() {
  document.getElementById('msgModal').classList.add('hidden');
  document.body.style.overflow = '';
}

/* Close on Escape */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeMsgModal();
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>