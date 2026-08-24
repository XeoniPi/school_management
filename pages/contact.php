<?php
/**
 * KMA — pages/contact.php  |  PHP 7.2
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'যোগাযোগ | খলিলুল্লাহ মেমোরিয়াল একাডেমি';
$pageDesc  = 'বিদ্যালয়ের ঠিকানা, ফোন ও ইমেইলে যোগাযোগ করুন।';

$pdo  = getDB();
$site = getSiteSettings();

$success = false;
$errors  = [];
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'নিরাপত্তা যাচাই ব্যর্থ।';
    } else {
        $old = [
            'name'           => sanitize(isset($_POST['name'])           ? $_POST['name']           : ''),
            'phone'          => sanitize(isset($_POST['phone'])          ? $_POST['phone']          : ''),
            'email'          => sanitize(isset($_POST['email'])          ? $_POST['email']          : ''),
            'relation'       => sanitize(isset($_POST['relation'])       ? $_POST['relation']       : ''),
            'subject'        => sanitize(isset($_POST['subject'])        ? $_POST['subject']        : ''),
            'message'        => sanitize(isset($_POST['message'])        ? $_POST['message']        : ''),
            'contact_method' => sanitize(isset($_POST['contact_method']) ? $_POST['contact_method'] : 'phone'),
        ];

        if (mb_strlen($old['name']) < 2)                                      { $errors[] = 'আপনার নাম লিখুন।'; }
        if (!preg_match('/^01[3-9]\d{8}$/', $old['phone']))                   { $errors[] = 'সঠিক মোবাইল নম্বর দিন।'; }
        if (!empty($old['email']) && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'সঠিক ইমেইল দিন।'; }
        if (empty($old['subject']))                                            { $errors[] = 'বিষয় নির্বাচন করুন।'; }
        if (mb_strlen($old['message']) < 10)                                  { $errors[] = 'বার্তা কমপক্ষে ১০ অক্ষরের হতে হবে।'; }
        if (!isset($_POST['privacy']))                                         { $errors[] = 'গোপনীয়তা নীতিতে সম্মতি দিন।'; }

        if (empty($errors)) {
            $pdo->prepare(
                'INSERT INTO contact_messages (name, phone, email, relation, subject, message, contact_method, ip_address)
                 VALUES (?,?,?,?,?,?,?,?)'
            )->execute([
                $old['name'], $old['phone'], $old['email'], $old['relation'],
                $old['subject'], $old['message'], $old['contact_method'],
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            ]);
            $success = true;
            $old = [];
            unset($_SESSION['csrf_token']);
        }
    }
}

$csrf = generateCsrfToken();
$phone  = isset($site['school_phone'])  ? $site['school_phone']  : '+880 1866-751015';
$phone2 = isset($site['school_phone2']) ? $site['school_phone2'] : '';
$email  = isset($site['school_email'])  ? $site['school_email']  : 'info@kma.edu.bd';
$addr   = isset($site['school_address'])? $site['school_address']: 'মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী';
$hours  = isset($site['school_hours'])  ? $site['school_hours']  : 'শনি–বৃহস্পতি: সকাল ৮:০০ – দুপুর ১:৩০';
$mapUrl = isset($site['school_map_url'])? $site['school_map_url']: '';
$fb     = isset($site['facebook_url'])  ? $site['facebook_url']  : 'https://www.facebook.com/KhalilullahMemorialAcademy';
$wa     = isset($site['whatsapp_number'])? $site['whatsapp_number']: '8801866751015';

require_once dirname(__DIR__) . '/includes/header.php';
?>
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>

<!-- Hero -->
<header class="page-hero" style="min-height:260px">
  <div class="page-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1600&q=80')"></div>
  <div class="max-w-7xl mx-auto px-4 w-full">
    <div class="page-hero-body">
      <div class="ph-pill"><i class="bi bi-telephone-fill"></i> যোগাযোগ করুন</div>
      <h1 class="font-bn font-bold text-4xl mb-3">আমাদের সাথে <em style="font-style:normal;color:#c9a227">যোগাযোগ</em></h1>
      <nav class="flex items-center gap-2 text-sm" aria-label="ব্রেডক্রাম্ব">
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-white/70 hover:text-gold transition-colors"><i class="bi bi-house-fill"></i> হোম</a>
        <i class="bi bi-chevron-right text-white/40 text-xs"></i>
        <span class="text-gold font-semibold" aria-current="page">যোগাযোগ</span>
      </nav>
    </div>
  </div>
</header>

<!-- Quick strip -->
<div class="bg-kma-dark py-0">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-white/10">
      <?php
      $strips = [
        ['bi-telephone-fill','bg-accent-light text-accent','ফোন (অফিস)', h($phone), 'tel:'.preg_replace('/\s+/','',$phone)],
        ['bi-headset','bg-gold/15 text-gold','ভর্তি হেল্পলাইন', h($phone2 ?: $phone), 'tel:'.preg_replace('/\s+/','',$phone2?:$phone)],
        ['bi-envelope-fill','bg-blue-100/10 text-blue-300','ইমেইল', h($email), 'mailto:'.$email],
        ['bi-clock-fill','bg-red-100/10 text-red-300','অফিস সময়', h($hours), '#'],
      ];
      foreach ($strips as $st): ?>
      <a href="<?php echo h($st[4]); ?>"
         class="flex items-center gap-3 px-4 py-4 hover:bg-white/5 transition-colors group">
        <div class="w-10 h-10 rounded-full <?php echo h($st[1]); ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
          <i class="bi <?php echo h($st[0]); ?> text-sm"></i>
        </div>
        <div class="min-w-0">
          <div class="text-white/50 text-[0.65rem] uppercase tracking-wider"><?php echo h($st[2]); ?></div>
          <div class="text-white text-xs font-bold truncate"><?php echo $st[3]; ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<main id="main-content">

<section class="py-16 bg-kma-bg dark:bg-gray-900">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-10 reveal">
      <div class="ornament"><span></span><i class="bi bi-chat-dots-fill"></i><span></span></div>
      <h2 class="section-title">বার্তা পাঠান</h2>
      <p class="text-kma-muted text-sm mt-1">ফরম পূরণ করুন — ২৪ ঘণ্টার মধ্যে উত্তর দেওয়া হবে</p>
    </div>

    <div class="grid lg:grid-cols-5 gap-6 items-start">

      <!-- ── Contact Form ── -->
      <div class="lg:col-span-3 reveal reveal-d1">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
          <div class="bg-gradient-to-r from-accent to-[#1a4a2a] px-7 py-6">
            <h2 class="text-white font-bold text-base"><i class="bi bi-envelope-paper-fill me-2"></i>যোগাযোগ ফরম</h2>
            <p class="text-white/75 text-xs mt-1">আপনার প্রশ্ন, মতামত বা ভর্তি সংক্রান্ত জিজ্ঞাসা পাঠান।</p>
          </div>
          <div class="p-7">
            <?php if ($success): ?>
            <div class="text-center py-10">
              <div class="w-16 h-16 bg-accent-light rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-check-lg text-accent text-3xl"></i>
              </div>
              <h3 class="font-bold text-accent text-lg mb-2">বার্তা পাঠানো সফল হয়েছে!</h3>
              <p class="text-kma-muted text-sm">২৪ ঘণ্টার মধ্যে আপনার পছন্দের মাধ্যমে উত্তর দেওয়া হবে।</p>
              <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="mt-5 inline-flex btn-primary" style="border-radius:8px">আরেকটি বার্তা পাঠান</a>
            </div>
            <?php else: ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 text-sm px-4 py-3 rounded-xl mb-5">
              <?php foreach ($errors as $err): ?>
              <div class="flex items-center gap-2 mb-1 last:mb-0"><i class="bi bi-exclamation-circle-fill flex-shrink-0"></i><?php echo h($err); ?></div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/pages/contact.php" novalidate>
              <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>" />

              <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="form-label" for="cname">আপনার নাম <span class="text-red-500">*</span></label>
                  <input type="text" id="cname" name="name" class="form-input" required
                         placeholder="আপনার পূর্ণ নাম"
                         value="<?php echo h(isset($old['name']) ? $old['name'] : ''); ?>" />
                </div>
                <div>
                  <label class="form-label" for="cphone">মোবাইল নম্বর <span class="text-red-500">*</span></label>
                  <input type="tel" id="cphone" name="phone" class="form-input" required
                         placeholder="01XXXXXXXXX" maxlength="11"
                         value="<?php echo h(isset($old['phone']) ? $old['phone'] : ''); ?>" />
                </div>
                <div>
                  <label class="form-label" for="cemail">ইমেইল ঠিকানা</label>
                  <input type="email" id="cemail" name="email" class="form-input"
                         placeholder="example@email.com"
                         value="<?php echo h(isset($old['email']) ? $old['email'] : ''); ?>" />
                </div>
                <div>
                  <label class="form-label" for="crelation">আপনি কে?</label>
                  <select id="crelation" name="relation" class="form-input">
                    <option value="">নির্বাচন করুন</option>
                    <?php
                    $rels = ['parent'=>'অভিভাবক','student'=>'শিক্ষার্থী','guardian'=>'অন্য অভিভাবক','teacher'=>'শিক্ষক / প্রার্থী','other'=>'অন্যান্য'];
                    foreach ($rels as $rv=>$rl): ?>
                    <option value="<?php echo h($rv); ?>" <?php echo (isset($old['relation'])&&$old['relation']===$rv)?'selected':''; ?>><?php echo h($rl); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="sm:col-span-2">
                  <label class="form-label" for="csubject">বিষয় <span class="text-red-500">*</span></label>
                  <select id="csubject" name="subject" class="form-input" required>
                    <option value="">বিষয় নির্বাচন করুন</option>
                    <?php
                    $subjs = ['admission'=>'ভর্তি সংক্রান্ত জিজ্ঞাসা','fee'=>'ফি ও পেমেন্ট','academic'=>'একাডেমিক তথ্য','result'=>'ফলাফল ও পরীক্ষা','complaint'=>'অভিযোগ / পরামর্শ','job'=>'চাকরির আবেদন','other'=>'অন্যান্য'];
                    foreach ($subjs as $sv=>$sl): ?>
                    <option value="<?php echo h($sv); ?>" <?php echo (isset($old['subject'])&&$old['subject']===$sv)?'selected':''; ?>><?php echo h($sl); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="sm:col-span-2">
                  <label class="form-label" for="cmessage">আপনার বার্তা <span class="text-red-500">*</span></label>
                  <textarea id="cmessage" name="message" class="form-input" rows="5" required maxlength="500"
                            placeholder="আপনার প্রশ্ন বা বার্তা বিস্তারিত লিখুন…"
                            oninput="document.getElementById('charCount').textContent=this.value.length+' / ৫০০'"><?php echo h(isset($old['message']) ? $old['message'] : ''); ?></textarea>
                  <div class="text-right text-xs text-kma-muted mt-1" id="charCount">0 / ৫০০</div>
                </div>
                <div class="sm:col-span-2">
                  <label class="form-label mb-2">পছন্দের যোগাযোগ মাধ্যম</label>
                  <div class="flex gap-5 flex-wrap">
                    <?php
                    $methods = ['phone'=>'ফোনে','email'=>'ইমেইলে','whatsapp'=>'WhatsApp-এ'];
                    foreach ($methods as $mv=>$ml): ?>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-kma-muted">
                      <input type="radio" name="contact_method" value="<?php echo h($mv); ?>" class="accent-accent"
                             <?php echo (isset($old['contact_method'])&&$old['contact_method']===$mv)||(!isset($old['contact_method'])&&$mv==='phone') ?'checked':''; ?>/>
                      <?php echo h($ml); ?>
                    </label>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="sm:col-span-2">
                  <label class="flex items-start gap-2 cursor-pointer text-sm text-kma-muted">
                    <input type="checkbox" name="privacy" class="accent-accent mt-1 flex-shrink-0" required />
                    <span>আমি সম্মত যে আমার প্রদত্ত তথ্য শুধুমাত্র যোগাযোগের উদ্দেশ্যে ব্যবহৃত হবে। <span class="text-red-500">*</span></span>
                  </label>
                </div>
              </div>

              <button type="submit"
                      class="w-full bg-accent text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2
                             hover:bg-[#1a4a2a] hover:-translate-y-0.5 hover:shadow-lg transition-all text-sm">
                <i class="bi bi-send-fill"></i> বার্তা পাঠান
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ── Info sidebar ── -->
      <div class="lg:col-span-2 space-y-4 reveal reveal-d2">

        <!-- Contact info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
          <div class="bg-accent text-white px-5 py-3.5 font-bold text-sm flex items-center gap-2">
            <i class="bi bi-geo-alt-fill text-gold"></i> যোগাযোগের তথ্য
          </div>
          <?php
          $infoRows = [
            ['bi-geo-alt-fill','bg-accent-light text-accent','ঠিকানা', h($addr),''],
            ['bi-telephone-fill','bg-accent-light text-accent','ফোন', h($phone), 'tel:'.preg_replace('/\s+/','',$phone)],
            ['bi-envelope-fill','bg-blue-100 text-blue-600','ইমেইল', h($email), 'mailto:'.$email],
            ['bi-whatsapp','bg-green-100 text-green-600','WhatsApp', h('+'.ltrim($wa,'+')), 'https://wa.me/'.ltrim($wa,'+')],
            ['bi-facebook','bg-blue-50 text-blue-700','ফেসবুক', 'Facebook Page', h($fb)],
            ['bi-clock-fill','bg-gold/15 text-gold','অফিস সময়', h($hours),''],
          ];
          foreach ($infoRows as $ir): ?>
          <div class="flex items-start gap-3 px-5 py-3.5 border-b border-kma-border dark:border-gray-700 last:border-0 hover:bg-kma-bg dark:hover:bg-gray-700 transition-colors">
            <div class="w-9 h-9 rounded-lg <?php echo h($ir[1]); ?> flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="bi <?php echo h($ir[0]); ?> text-sm"></i>
            </div>
            <div class="min-w-0">
              <div class="text-xs text-kma-muted"><?php echo h($ir[2]); ?></div>
              <?php if (!empty($ir[4])): ?>
              <a href="<?php echo h($ir[4]); ?>" target="_blank" rel="noopener"
                 class="text-xs font-semibold text-accent hover:underline break-all"><?php echo $ir[3]; ?></a>
              <?php else: ?>
              <div class="text-xs font-semibold text-kma-dark dark:text-gray-200"><?php echo $ir[3]; ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Office hours -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
          <div class="bg-kma-dark text-white px-5 py-3.5 font-bold text-sm flex items-center gap-2">
            <i class="bi bi-clock-fill text-gold"></i> অফিস সময়সূচি
          </div>
          <div class="p-4 text-sm space-y-2">
            <?php
            $dayNow = (int)date('N'); // 1=Mon .. 7=Sun
            $schedRows = [
              ['শনিবার – বৃহস্পতিবার', 'সকাল ৮:০০ – দুপুর ১:৩০', true],
              ['শুক্রবার', '—', false],
              ['সরকারি ছুটির দিন', '—', false],
            ];
            foreach ($schedRows as $sr): ?>
            <div class="flex items-center justify-between py-1.5 border-b border-kma-border dark:border-gray-700 last:border-0">
              <span class="text-kma-muted text-xs"><?php echo h($sr[0]); ?></span>
              <?php if ($sr[1] !== '—'): ?>
              <span class="text-xs font-bold text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-full"><?php echo h($sr[1]); ?></span>
              <?php else: ?>
              <span class="text-xs font-bold text-red-500 bg-red-100 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-full">বন্ধ</span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Social links -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
          <h3 class="text-sm font-bold text-kma-dark dark:text-white mb-3"><i class="bi bi-share-fill text-accent mr-1"></i> সামাজিক মাধ্যম</h3>
          <div class="flex flex-wrap gap-2">
            <a href="<?php echo h($fb); ?>" target="_blank" rel="noopener"
               class="flex items-center gap-1.5 bg-blue-600 text-white text-xs font-bold px-3 py-2 rounded-lg hover:bg-blue-700 hover:-translate-y-0.5 transition-all">
              <i class="bi bi-facebook"></i> ফেসবুক
            </a>
            <a href="https://wa.me/<?php echo h(ltrim($wa,'+')); ?>" target="_blank" rel="noopener"
               class="flex items-center gap-1.5 bg-green-500 text-white text-xs font-bold px-3 py-2 rounded-lg hover:bg-green-600 hover:-translate-y-0.5 transition-all">
              <i class="bi bi-whatsapp"></i> WhatsApp
            </a>
            <a href="mailto:<?php echo h($email); ?>"
               class="flex items-center gap-1.5 bg-kma-dark text-white text-xs font-bold px-3 py-2 rounded-lg hover:bg-accent hover:-translate-y-0.5 transition-all">
              <i class="bi bi-envelope-fill"></i> ইমেইল
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ── Google Map ── -->
<section class="py-14 bg-white dark:bg-gray-800">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center title-center mb-8 reveal">
      <div class="ornament"><span></span><i class="bi bi-map-fill"></i><span></span></div>
      <h2 class="section-title">আমাদের অবস্থান</h2>
      <p class="text-kma-muted text-sm mt-1">গুগল ম্যাপে বিদ্যালয়ের অবস্থান দেখুন</p>
    </div>
    <div class="rounded-2xl overflow-hidden shadow-xl reveal">
      <!-- Lazy-load map on user click -->
      <div id="mapWrap" class="relative" style="padding-bottom:42%;min-height:320px">
        <div id="mapPlaceholder"
             class="absolute inset-0 bg-gradient-to-br from-accent-light to-green-100 dark:from-gray-700 dark:to-gray-600
                    flex flex-col items-center justify-center gap-4">
          <div class="w-16 h-16 rounded-full bg-accent text-white flex items-center justify-center text-3xl shadow-lg animate-pulse">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div class="text-center">
            <h3 class="font-bold text-kma-dark dark:text-white text-sm mb-1">গুগল ম্যাপ লোড করুন</h3>
            <p class="text-kma-muted text-xs"><?php echo h($addr); ?></p>
          </div>
          <button onclick="loadMap()"
                  class="flex items-center gap-2 bg-accent text-white text-sm font-bold px-5 py-2.5 rounded-full hover:bg-[#1a4a2a] transition-colors shadow-md">
            <i class="bi bi-map"></i> ম্যাপ দেখুন
          </button>
          <a href="https://www.google.com/maps/search/<?php echo urlencode($addr); ?>" target="_blank" rel="noopener"
             class="text-xs text-accent font-semibold hover:underline">গুগল ম্যাপে সরাসরি দেখুন →</a>
        </div>
      </div>
      <div class="flex items-center justify-between px-5 py-3 bg-kma-bg dark:bg-gray-700 border-t border-kma-border dark:border-gray-600 flex-wrap gap-3">
        <div class="text-xs text-kma-muted"><i class="bi bi-geo-alt mr-1"></i><?php echo h($addr); ?></div>
        <a href="https://www.google.com/maps/search/<?php echo urlencode($addr); ?>" target="_blank" rel="noopener"
           class="flex items-center gap-1.5 bg-accent text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#1a4a2a] transition-colors">
          <i class="bi bi-box-arrow-up-right"></i> গুগল ম্যাপে দেখুন
        </a>
      </div>
    </div>
  </div>
</section>

</main>

<!-- CTA -->
<section class="cta-section">
  <div class="max-w-7xl mx-auto px-4 reveal text-center">
    <div class="ornament mb-3"><span style="background:rgba(255,255,255,.3)"></span><i class="bi bi-mortarboard-fill"></i><span style="background:rgba(255,255,255,.3)"></span></div>
    <h2 class="font-bold mb-3" style="font-size:clamp(1.5rem,3vw,2rem)">আজই ভর্তির আবেদন করুন</h2>
    <p class="text-white/80 mb-6 text-sm">সীমিত আসনে ভর্তি চলছে।</p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?php echo BASE_URL; ?>/pages/admission.php" class="btn-gold"><i class="bi bi-pencil-square"></i> ভর্তির আবেদন করুন</a>
      <a href="tel:<?php echo h(preg_replace('/\s+/','',$phone)); ?>" class="btn-outline"><i class="bi bi-telephone"></i> এখনই কল করুন</a>
    </div>
  </div>
</section>

<script>
function loadMap() {
  var wrap = document.getElementById('mapWrap');
  var ph   = document.getElementById('mapPlaceholder');
  var src  = <?php echo json_encode(!empty($mapUrl) ? $mapUrl : 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d233668.36730614474!2d90.27923950512697!3d23.780573073604537!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8b087026b81%3A0x8fa563bbdd5904c2!2sDhaka%2C%20Bangladesh!5e0!3m2!1sen!2sbd!4v1700000000000'); ?>;
  var iframe = document.createElement('iframe');
  iframe.title = 'বিদ্যালয়ের অবস্থান';
  iframe.loading = 'lazy';
  iframe.allowFullscreen = true;
  iframe.src = src;
  iframe.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;border:0';
  wrap.appendChild(iframe);
  if (ph) { ph.style.opacity = '0'; ph.style.pointerEvents = 'none'; }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>