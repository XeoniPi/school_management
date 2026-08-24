<?php
/**
 * KMA — admin/login.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

/* Already logged in */
if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
$old_username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $error = 'নিরাপত্তা যাচাই ব্যর্থ। পুনরায় চেষ্টা করুন।';
    } else {
        $username = sanitize(isset($_POST['username']) ? $_POST['username'] : '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($username) || empty($password)) {
            $error = 'ইউজারনেম ও পাসওয়ার্ড উভয়ই দিন।';
        } else {
            $pdo  = getDB();
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name']     = $admin['full_name'];
                $_SESSION['admin_role']     = $admin['role'];

                $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')
                    ->execute([$admin['id']]);

                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit;
            }
            else {
                $error = 'ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।';
            }
        }
    }
}


$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="bn" class="light">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>অ্যাডমিন লগইন | KMA</title>
<script>
  /* Prevent flash */
  try { if (localStorage.getItem('kma_theme') === 'dark') document.documentElement.classList.replace('light','dark'); } catch(e) {}
</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: { extend: {
    colors: {
      accent: '#2e6b3e', gold: '#c9a227',
      'kma-dark': '#1a1a2e', 'kma-muted': '#6b7280',
      'kma-bg': '#f8faf8', 'kma-border': '#e5e7eb',
    },
    fontFamily: { bn: ['"Hind Siliguri"', 'sans-serif'] }
  }}
};
</script>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
<style>
  body { font-family: 'Hind Siliguri', sans-serif; }
  .form-input {
    width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e5e7eb;
    border-radius: 0.6rem; font-size: 0.9rem; transition: border-color .2s, box-shadow .2s;
    background: white; color: #1a1a2e;
  }
  .form-input:focus { outline: none; border-color: #2e6b3e; box-shadow: 0 0 0 3px rgba(46,107,62,.12); }
  .dark .form-input { background: #1f2937; border-color: #374151; color: #f3f4f6; }
  .dark .form-input:focus { border-color: #2e6b3e; }
</style>
</head>
<body class="min-h-screen bg-kma-bg dark:bg-gray-900 flex items-center justify-center px-4 py-12">

<!-- Dark mode toggle -->
<button onclick="var h=document.documentElement;var d=h.classList.toggle('dark');localStorage.setItem('kma_theme',d?'dark':'light')"
        class="fixed top-4 right-4 w-10 h-10 rounded-full bg-white dark:bg-gray-700 shadow-md flex items-center justify-center text-kma-muted dark:text-gray-300 hover:text-accent transition-colors">
  <i class="bi bi-moon-fill dark:hidden"></i>
  <i class="bi bi-sun-fill hidden dark:block"></i>
</button>

<div class="w-full max-w-md">

  <!-- Card -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">

    <!-- Header -->
    <div class="bg-gradient-to-br from-accent to-[#1a4a2a] px-8 py-8 text-center">
      <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-3">
        <i class="bi bi-shield-lock-fill text-white text-3xl"></i>
      </div>
      <h1 class="text-white font-bold text-xl">অ্যাডমিন প্যানেল</h1>
      <p class="text-white/70 text-sm mt-1">Khalilullah Memorial Academy</p>
    </div>

    <!-- Form -->
    <div class="px-8 py-8">
      <h2 class="text-kma-dark dark:text-white font-bold text-base mb-6 text-center">লগইন করুন</h2>

      <?php if (!empty($error)): ?>
      <div class="flex items-center gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm px-4 py-3 rounded-xl mb-5">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <?php echo h($error); ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo BASE_URL; ?>/admin/login.php">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>" />

        <div class="mb-4">
          <label for="username" class="block text-sm font-semibold text-kma-dark dark:text-gray-200 mb-1.5">
            ইউজারনেম
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-kma-muted">
              <i class="bi bi-person-fill"></i>
            </span>
            <input type="text" id="username" name="username"
                   class="form-input pl-9"
                   placeholder="আপনার ইউজারনেম"
                   value="<?php echo h($old_username); ?>"
                   autocomplete="username" required autofocus />
          </div>
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-semibold text-kma-dark dark:text-gray-200 mb-1.5">
            পাসওয়ার্ড
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-kma-muted">
              <i class="bi bi-lock-fill"></i>
            </span>
            <input type="password" id="password" name="password"
                   class="form-input pl-9 pr-10"
                   placeholder="••••••••"
                   autocomplete="current-password" required />
            <button type="button"
                    onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash')"
                    class="absolute inset-y-0 right-3 flex items-center text-kma-muted hover:text-accent transition-colors">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
        </div>

        <button type="submit"
                class="w-full bg-accent text-white font-bold py-3.5 rounded-xl hover:bg-[#1a4a2a] hover:-translate-y-0.5 hover:shadow-lg transition-all flex items-center justify-center gap-2">
          <i class="bi bi-box-arrow-in-right"></i> লগইন করুন
        </button>
      </form>
    </div>
  </div>

  <!-- Back link -->
  <div class="text-center mt-5">
    <a href="<?php echo BASE_URL; ?>/index.php"
       class="text-kma-muted dark:text-gray-400 text-sm hover:text-accent dark:hover:text-accent transition-colors">
      <i class="bi bi-arrow-left"></i> প্রধান ওয়েবসাইটে ফিরুন
    </a>
  </div>

</div>

</body>
</html>