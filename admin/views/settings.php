<?php
/**
 * KMA — admin/views/settings.php  |  PHP 7.2
 * Site settings editor + password change.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';
requireAdminLogin();

$pdo    = getDB();
$tab    = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'general';
$currentAdminPage = 'settings';
$pageTitle = 'সেটিংস | KMA Admin';

$flash = ''; $flashType = 'success'; $errors = [];

/* Load all settings into a key=>value map */
$allSettings = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
$s = [];
foreach ($allSettings as $row) {
    $s[$row['setting_key']] = $row['setting_value'];
}

/* Helper: upsert a setting */
function saveSetting($pdo, $key, $value) {
    $chk = $pdo->prepare('SELECT id FROM site_settings WHERE setting_key=?');
    $chk->execute([$key]);
    if ($chk->fetch()) {
        $pdo->prepare('UPDATE site_settings SET setting_value=? WHERE setting_key=?')->execute([$value, $key]);
    } else {
        $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?)')->execute([$key, $value]);
    }
}

/* ── POST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $flash = 'নিরাপত্তা যাচাই ব্যর্থ।'; $flashType = 'error';
    } else {
        $pa = sanitize(isset($_POST['post_action']) ? $_POST['post_action'] : '');

        /* ── General / Contact / Social settings ── */
        if ($pa === 'save_general') {
            $fields = [
                'school_name_bn','school_name_en','school_tagline',
                'school_phone','school_phone2','school_email','school_email2',
                'school_address','school_map_url',
                'school_hours','admission_open',
                'footer_text','meta_description',
            ];
            foreach ($fields as $fk) {
                $val = sanitize(isset($_POST[$fk]) ? $_POST[$fk] : '');
                /* Checkboxes */
                if ($fk === 'admission_open') {
                    $val = isset($_POST['admission_open']) ? '1' : '0';
                }
                saveSetting($pdo, $fk, $val);
                $s[$fk] = $val;
            }
            $flash = 'সেটিংস সংরক্ষিত হয়েছে।';
            header('Location: ' . BASE_URL . '/admin/views/settings.php?tab=general&flash=' . urlencode($flash)); exit;
        }

        if ($pa === 'save_social') {
            $socials = ['facebook_url','youtube_url','whatsapp_number','instagram_url'];
            foreach ($socials as $fk) {
                $val = sanitize(isset($_POST[$fk]) ? $_POST[$fk] : '');
                saveSetting($pdo, $fk, $val);
                $s[$fk] = $val;
            }
            $flash = 'সোশ্যাল লিংক সংরক্ষিত হয়েছে।';
            header('Location: ' . BASE_URL . '/admin/views/settings.php?tab=social&flash=' . urlencode($flash)); exit;
        }

        /* ── Password change ── */
        if ($pa === 'change_password') {
            $currentPwd = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $newPwd     = isset($_POST['new_password'])     ? $_POST['new_password']     : '';
            $confirmPwd = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            $adminId = (int)$_SESSION['admin_id'];
            $row = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id=?');
            $row->execute([$adminId]);
            $admin = $row->fetch();

            if (!$admin || !password_verify($currentPwd, $admin['password_hash'])) {
                $errors[] = 'বর্তমান পাসওয়ার্ড সঠিক নয়।'; $flashType = 'error';
            } elseif (mb_strlen($newPwd) < 8) {
                $errors[] = 'নতুন পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।'; $flashType = 'error';
            } elseif ($newPwd !== $confirmPwd) {
                $errors[] = 'নিশ্চিত পাসওয়ার্ড মেলেনি।'; $flashType = 'error';
            } else {
                $hash = password_hash($newPwd, PASSWORD_BCRYPT);
                $pdo->prepare('UPDATE admin_users SET password_hash=? WHERE id=?')->execute([$hash, $adminId]);
                $flash = 'পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে।';
                header('Location: ' . BASE_URL . '/admin/views/settings.php?tab=password&flash=' . urlencode($flash)); exit;
            }
            $tab = 'password';
        }

        /* ── Logo upload ── */
        if ($pa === 'upload_logo') {
            if (!empty($_FILES['logo_file']['name'])) {
                $file = $_FILES['logo_file'];
                if ($file['size'] > MAX_IMG_SIZE) {
                    $errors[] = 'লোগোর আকার সর্বোচ্চ ২ MB।'; $flashType = 'error';
                } elseif (!in_array($file['type'], ALLOWED_IMG_TYPES)) {
                    $errors[] = 'শুধুমাত্র JPG, PNG বা WEBP আপলোড করুন।'; $flashType = 'error';
                } else {
                    $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fname = 'logo.' . $ext;
                    move_uploaded_file($file['tmp_name'], BASE_PATH . '/assets/images/' . $fname);
                    saveSetting($pdo, 'logo_path', 'assets/images/' . $fname);
                    $s['logo_path'] = 'assets/images/' . $fname;
                    $flash = 'লোগো আপলোড হয়েছে।';
                    header('Location: ' . BASE_URL . '/admin/views/settings.php?tab=general&flash=' . urlencode($flash)); exit;
                }
            } else {
                $errors[] = 'একটি ছবি নির্বাচন করুন।'; $flashType = 'error';
            }
            $tab = 'general';
        }
    }
}

if (!empty($_GET['flash'])) { $flash = sanitize($_GET['flash']); }

$csrf = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/admin_header.php';

/* Helper for setting value */
function sv($s, $key, $default = '') { return isset($s[$key]) ? $s[$key] : $default; }
?>

<div class="mb-5">
  <h1 class="text-lg font-bold text-kma-dark dark:text-white">সেটিংস</h1>
  <p class="text-kma-muted text-xs mt-0.5">সাইটের সাধারণ কনফিগারেশন পরিবর্তন করুন</p>
</div>

<?php if ($flash): ?><div class="alert <?php echo $flashType==='error'?'alert-error':'alert-success'; ?>"><i class="bi bi-check-circle-fill"></i><?php echo h($flash); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error flex-col items-start"><?php foreach($errors as $e): ?><div><i class="bi bi-exclamation-circle-fill mr-1"></i><?php echo h($e); ?></div><?php endforeach; ?></div><?php endif; ?>

<!-- Tabs -->
<div class="flex gap-0 mb-6 bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-kma-border dark:border-gray-700">
  <?php
  $settingsTabs = [
    ['general',  'bi-gear-fill',       'সাধারণ'],
    ['social',   'bi-share-fill',      'সোশ্যাল মিডিয়া'],
    ['password', 'bi-lock-fill',       'পাসওয়ার্ড'],
    ['admins',   'bi-people-fill',     'অ্যাডমিন ব্যবহারকারী'],
  ];
  foreach ($settingsTabs as $st): ?>
  <a href="?tab=<?php echo h($st[0]); ?>"
     class="flex-1 flex items-center justify-center gap-1.5 py-3 text-xs font-semibold transition-colors border-b-[3px]
            <?php echo $tab===$st[0] ? 'border-accent text-accent bg-accent-light dark:bg-accent/10' : 'border-transparent text-kma-muted hover:text-accent'; ?>">
    <i class="bi <?php echo h($st[1]); ?>"></i>
    <span class="hidden sm:inline"><?php echo h($st[2]); ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- ══ GENERAL ══ -->
<?php if ($tab === 'general'): ?>
<div class="grid lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2">
    <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/settings.php?tab=general" class="admin-card p-6">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
      <input type="hidden" name="post_action" value="save_general"/>

      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4 pb-2 border-b border-kma-border dark:border-gray-700">
        <i class="bi bi-building text-accent mr-1"></i> বিদ্যালয়ের তথ্য
      </h2>
      <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <div class="sm:col-span-2">
          <label class="form-label">বিদ্যালয়ের নাম (বাংলা)</label>
          <input type="text" name="school_name_bn" class="form-input" value="<?php echo h(sv($s,'school_name_bn')); ?>" placeholder="যেমন: খলিলুল্লাহ মেমোরিয়াল একাডেমি"/>
        </div>
        <div>
          <label class="form-label">বিদ্যালয়ের নাম (ইংরেজি)</label>
          <input type="text" name="school_name_en" class="form-input" value="<?php echo h(sv($s,'school_name_en')); ?>" placeholder="Khalilullah Memorial Academy"/>
        </div>
        <div>
          <label class="form-label">ট্যাগলাইন</label>
          <input type="text" name="school_tagline" class="form-input" value="<?php echo h(sv($s,'school_tagline')); ?>" placeholder="Modern Education, Timeless Values"/>
        </div>
        <div>
          <label class="form-label">মূল ফোন নম্বর</label>
          <input type="text" name="school_phone" class="form-input" value="<?php echo h(sv($s,'school_phone')); ?>" placeholder="+880 1866-751015"/>
        </div>
        <div>
          <label class="form-label">দ্বিতীয় ফোন নম্বর</label>
          <input type="text" name="school_phone2" class="form-input" value="<?php echo h(sv($s,'school_phone2')); ?>" placeholder="+880 1800-000000"/>
        </div>
        <div>
          <label class="form-label">প্রধান ইমেইল</label>
          <input type="email" name="school_email" class="form-input" value="<?php echo h(sv($s,'school_email')); ?>" placeholder="info@kma.edu.bd"/>
        </div>
        <div>
          <label class="form-label">ভর্তি ইমেইল</label>
          <input type="email" name="school_email2" class="form-input" value="<?php echo h(sv($s,'school_email2')); ?>" placeholder="admission@kma.edu.bd"/>
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">ঠিকানা</label>
          <textarea name="school_address" class="form-input" rows="2" placeholder="মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী"><?php echo h(sv($s,'school_address')); ?></textarea>
        </div>
        <div>
          <label class="form-label">অফিস সময়</label>
          <input type="text" name="school_hours" class="form-input" value="<?php echo h(sv($s,'school_hours')); ?>" placeholder="শনি–বৃহস্পতি: সকাল ৮:০০ – দুপুর ১:৩০"/>
        </div>
        <div>
          <label class="form-label">Google Maps Embed URL</label>
          <input type="url" name="school_map_url" class="form-input" value="<?php echo h(sv($s,'school_map_url')); ?>" placeholder="https://www.google.com/maps/embed?..."/>
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">SEO বিবরণ (meta description)</label>
          <textarea name="meta_description" class="form-input" rows="2" placeholder="মানসম্পন্ন প্রাথমিক শিক্ষার আলোকিত প্রতিষ্ঠান..."><?php echo h(sv($s,'meta_description')); ?></textarea>
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">ফুটার টেক্সট</label>
          <input type="text" name="footer_text" class="form-input" value="<?php echo h(sv($s,'footer_text')); ?>" placeholder="© ২০২৬ খলিলুল্লাহ মেমোরিয়াল একাডেমি"/>
        </div>
        <div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="admission_open" value="1" class="accent-accent"
                   <?php echo sv($s,'admission_open')==='1'?'checked':''; ?>/>
            <span class="text-sm font-semibold text-kma-dark dark:text-gray-200">ভর্তি চলছে (urgency bar দেখাবে)</span>
          </label>
        </div>
      </div>

      <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> সেটিংস সংরক্ষণ করুন</button>
    </form>
  </div>

  <!-- Logo upload -->
  <div>
    <div class="admin-card p-5">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-4">
        <i class="bi bi-image text-accent mr-1"></i> বিদ্যালয়ের লোগো
      </h2>
      <!-- Current logo -->
      <?php
      $logoPath = sv($s,'logo_path');
      $logoSrc  = !empty($logoPath) && file_exists(BASE_PATH . '/' . $logoPath)
                  ? BASE_URL . '/' . $logoPath
                  : 'https://placehold.co/120x120/2e6b3e/ffffff?text=KMA';
      ?>
      <div class="text-center mb-5">
        <img src="<?php echo h($logoSrc); ?>" alt="বিদ্যালয়ের লোগো"
             class="w-28 h-28 rounded-2xl object-cover mx-auto border-4 border-accent shadow-lg"
             onerror="this.src='https://placehold.co/120x120/2e6b3e/ffffff?text=KMA'"/>
        <p class="text-xs text-kma-muted mt-2">বর্তমান লোগো</p>
      </div>
      <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/settings.php?tab=general" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
        <input type="hidden" name="post_action" value="upload_logo"/>
        <label class="form-label">নতুন লোগো আপলোড</label>
        <label class="block border-2 border-dashed border-kma-border rounded-xl p-4 text-center cursor-pointer hover:border-accent hover:bg-accent/5 transition-colors mb-3">
          <input type="file" name="logo_file" class="hidden" accept="image/jpeg,image/png,image/webp"
                 onchange="var p=document.getElementById('logoPreview');p.src=URL.createObjectURL(this.files[0]);p.classList.remove('hidden')"/>
          <i class="bi bi-cloud-arrow-up text-2xl text-kma-muted block mb-1"></i>
          <p class="text-xs text-kma-muted">JPG, PNG, WEBP · সর্বোচ্চ ২ MB</p>
          <img id="logoPreview" src="" class="hidden w-20 h-20 rounded-xl mx-auto mt-2 object-cover"/>
        </label>
        <button type="submit" class="btn-primary w-full justify-center text-sm">
          <i class="bi bi-upload"></i> লোগো আপলোড করুন
        </button>
      </form>
    </div>
  </div>
</div>

<!-- ══ SOCIAL ══ -->
<?php elseif ($tab === 'social'): ?>
<div class="max-w-2xl">
  <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/settings.php?tab=social" class="admin-card p-6">
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
    <input type="hidden" name="post_action" value="save_social"/>
    <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-5 pb-2 border-b border-kma-border dark:border-gray-700">
      <i class="bi bi-share-fill text-accent mr-1"></i> সোশ্যাল মিডিয়া লিংক
    </h2>
    <div class="space-y-4">
      <?php
      $socials = [
        ['facebook_url',     'bi-facebook text-blue-600',  'ফেসবুক পেজ URL',    'https://www.facebook.com/...'],
        ['youtube_url',      'bi-youtube text-red-600',    'ইউটিউব চ্যানেল URL', 'https://www.youtube.com/...'],
        ['whatsapp_number',  'bi-whatsapp text-green-600', 'WhatsApp নম্বর',      '+8801866751015'],
        ['instagram_url',    'bi-instagram text-pink-600', 'ইনস্টাগ্রাম URL',    'https://www.instagram.com/...'],
      ];
      foreach ($socials as $soc): ?>
      <div>
        <label class="form-label flex items-center gap-2">
          <i class="bi <?php echo h($soc[1]); ?>"></i> <?php echo h($soc[2]); ?>
        </label>
        <input type="text" name="<?php echo h($soc[0]); ?>" class="form-input"
               value="<?php echo h(sv($s,$soc[0])); ?>" placeholder="<?php echo h($soc[3]); ?>"/>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn-primary mt-5"><i class="bi bi-check-lg"></i> সংরক্ষণ করুন</button>
  </form>
</div>

<!-- ══ PASSWORD ══ -->
<?php elseif ($tab === 'password'): ?>
<div class="max-w-md">
  <form method="POST" action="<?php echo BASE_URL; ?>/admin/views/settings.php?tab=password" class="admin-card p-6">
    <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>"/>
    <input type="hidden" name="post_action" value="change_password"/>
    <h2 class="text-sm font-bold text-kma-dark dark:text-white mb-5 pb-2 border-b border-kma-border dark:border-gray-700">
      <i class="bi bi-lock-fill text-accent mr-1"></i> পাসওয়ার্ড পরিবর্তন
    </h2>
    <div class="space-y-4">
      <?php
      $pwFields = [
        ['current_password','বর্তমান পাসওয়ার্ড','বর্তমান পাসওয়ার্ড লিখুন'],
        ['new_password',    'নতুন পাসওয়ার্ড',    'কমপক্ষে ৮ অক্ষর'],
        ['confirm_password','পাসওয়ার্ড নিশ্চিত করুন','আবার নতুন পাসওয়ার্ড লিখুন'],
      ];
      foreach ($pwFields as $pwf): ?>
      <div>
        <label class="form-label"><?php echo h($pwf[1]); ?> <span class="text-red-500">*</span></label>
        <div class="relative">
          <input type="password" name="<?php echo h($pwf[0]); ?>" id="<?php echo h($pwf[0]); ?>"
                 class="form-input pr-10" placeholder="<?php echo h($pwf[2]); ?>" required autocomplete="new-password"/>
          <button type="button"
                  onclick="var i=document.getElementById('<?php echo h($pwf[0]); ?>');i.type=i.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash')"
                  class="absolute inset-y-0 right-3 flex items-center text-kma-muted hover:text-accent transition-colors">
            <i class="bi bi-eye-slash text-sm"></i>
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-xs text-amber-700 dark:text-amber-400">
      <i class="bi bi-info-circle-fill mr-1"></i>
      পাসওয়ার্ড পরিবর্তনের পর আবার লগইন করতে হবে না। তবে শক্তিশালী পাসওয়ার্ড ব্যবহার করুন।
    </div>
    <button type="submit" class="btn-primary mt-5 w-full justify-center">
      <i class="bi bi-lock-fill"></i> পাসওয়ার্ড পরিবর্তন করুন
    </button>
  </form>
</div>

<!-- ══ ADMINS ══ -->
<?php elseif ($tab === 'admins'): ?>
<?php
$admins = $pdo->query('SELECT id, username, full_name, email, role, is_active, last_login, created_at FROM admin_users ORDER BY id')->fetchAll();
?>
<div class="max-w-3xl">
  <div class="admin-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-kma-border dark:border-gray-700">
      <h2 class="text-sm font-bold text-kma-dark dark:text-white"><i class="bi bi-people-fill text-accent mr-1"></i> অ্যাডমিন ব্যবহারকারী</h2>
    </div>
    <?php if (empty($admins)): ?>
    <div class="py-8 text-center text-kma-muted text-sm">কোনো ব্যবহারকারী নেই</div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table>
        <thead><tr><th>নাম</th><th>ইউজারনেম</th><th>ইমেইল</th><th>ভূমিকা</th><th>শেষ লগইন</th><th>স্ট্যাটাস</th></tr></thead>
        <tbody>
          <?php foreach ($admins as $adm): ?>
          <tr class="<?php echo (int)$adm['id']===(int)$_SESSION['admin_id'] ? 'bg-accent-light dark:bg-accent/10' : ''; ?>">
            <td>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                  <?php echo strtoupper(mb_substr($adm['full_name'],0,1)); ?>
                </div>
                <div>
                  <div class="text-xs font-semibold text-kma-dark dark:text-gray-200"><?php echo h($adm['full_name']); ?></div>
                  <?php if ((int)$adm['id']===(int)$_SESSION['admin_id']): ?>
                  <span class="text-[0.6rem] text-accent font-bold">(আপনি)</span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td class="font-mono text-xs text-kma-muted"><?php echo h($adm['username']); ?></td>
            <td class="text-xs text-kma-muted"><?php echo h($adm['email'] ?: '—'); ?></td>
            <td>
              <span class="badge <?php echo $adm['role']==='superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                <?php echo h($adm['role']); ?>
              </span>
            </td>
            <td class="text-xs text-kma-muted">
              <?php echo $adm['last_login'] ? date('d/m/Y h:i A', strtotime($adm['last_login'])) : '—'; ?>
            </td>
            <td>
              <span class="badge <?php echo $adm['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>">
                <?php echo $adm['is_active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
  <p class="text-xs text-kma-muted mt-3 flex items-center gap-1.5">
    <i class="bi bi-info-circle-fill text-gold"></i>
    নতুন অ্যাডমিন যোগ করতে বা বিদ্যমান অ্যাডমিনের তথ্য পরিবর্তন করতে সরাসরি ডেটাবেসে পরিবর্তন করুন।
  </p>
</div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/admin_footer.php'; ?>