<?php
/**
 * KMA Admin — includes/i18n.php  |  PHP 7.2
 * Minimal bilingual helper for the admin dashboard ONLY.
 * Default language is English (professional look for the dashboard);
 * a toggle in the top bar switches to Bangla instantly via CSS/JS
 * (no page reload) — see assets/js/admin-shell.js.
 *
 * Usage in a view:
 *   <?php echo t('save'); ?>                  → renders both spans
 *   <?php echo t('dashboard_title'); ?>
 *
 * Add new strings by adding a new key to $GLOBALS['KMA_I18N'] below.
 * Falls back to the key itself (readable) if a translation is missing.
 */

$GLOBALS['KMA_I18N'] = [
    // Sidebar / nav
    'nav_dashboard'   => ['en' => 'Dashboard',        'bn' => 'ড্যাশবোর্ড'],
    'nav_notices'     => ['en' => 'Notices',          'bn' => 'নোটিশ'],
    'nav_admissions'  => ['en' => 'Admissions',       'bn' => 'ভর্তি আবেদন'],
    'nav_classes'     => ['en' => 'Classes & Subjects','bn' => 'শ্রেণি ও বিষয়'],
    'nav_faculty'     => ['en' => 'Faculty & Staff',  'bn' => 'শিক্ষক ও প্রশাসন'],
    'nav_holidays'    => ['en' => 'Holidays',         'bn' => 'ছুটির তালিকা'],
    'nav_downloads'   => ['en' => 'Downloads',        'bn' => 'ডাউনলোড'],
    'nav_gallery'     => ['en' => 'Gallery',          'bn' => 'গ্যালারি'],
    'nav_accounts'    => ['en' => 'Accounts',         'bn' => 'হিসাব'],
    'nav_users'       => ['en' => 'Staff Users',      'bn' => 'স্টাফ ইউজার'],
    'nav_error_log'   => ['en' => 'Error Logs',        'bn' => 'এরর লগ'],
    'nav_backup'      => ['en' => 'Database Backup',    'bn' => 'ডাটাবেজ ব্যাকআপ'],
    'nav_settings'    => ['en' => 'Settings',         'bn' => 'সেটিংস'],
    'nav_logout'      => ['en' => 'Logout',           'bn' => 'লগআউট'],
    'nav_messages'    => ['en' => 'Messages',         'bn' => 'বার্তা'],

    // Top bar / common actions
    'view_site'       => ['en' => 'View site',        'bn' => 'সাইট দেখুন'],
    'save'            => ['en' => 'Save',              'bn' => 'সংরক্ষণ করুন'],
    'cancel'          => ['en' => 'Cancel',             'bn' => 'বাতিল'],
    'add_new'         => ['en' => 'Add New',            'bn' => 'নতুন যোগ'],
    'edit'            => ['en' => 'Edit',               'bn' => 'সম্পাদনা'],
    'delete'          => ['en' => 'Delete',             'bn' => 'মুছুন'],
    'active'          => ['en' => 'Active',             'bn' => 'সক্রিয়'],
    'inactive'        => ['en' => 'Inactive',           'bn' => 'নিষ্ক্রিয়'],
    'back_to_list'    => ['en' => 'Back to list',       'bn' => 'তালিকায় ফিরুন'],
    'search'          => ['en' => 'Search',             'bn' => 'খুঁজুন'],
    'welcome'         => ['en' => 'Welcome',            'bn' => 'স্বাগতম'],

    // Accounts module
    'income'          => ['en' => 'Income',             'bn' => 'আয়'],
    'expense'         => ['en' => 'Expense',             'bn' => 'ব্যয়'],
    'balance'         => ['en' => 'Net Balance',          'bn' => 'নিট ব্যালেন্স'],
    'this_month'      => ['en' => 'This Month',           'bn' => 'এই মাস'],
    'total_income'    => ['en' => 'Total Income',         'bn' => 'মোট আয়'],
    'total_expense'   => ['en' => 'Total Expense',        'bn' => 'মোট ব্যয়'],
    'add_transaction' => ['en' => 'Add Transaction',      'bn' => 'নতুন লেনদেন'],
    'transaction_date'=> ['en' => 'Date',                 'bn' => 'তারিখ'],
    'category'        => ['en' => 'Category',             'bn' => 'ক্যাটাগরি'],
    'amount'          => ['en' => 'Amount',               'bn' => 'পরিমাণ'],
    'remark'          => ['en' => 'Remark',               'bn' => 'মন্তব্য'],
    'payment_method'  => ['en' => 'Payment Method',       'bn' => 'পেমেন্ট মাধ্যম'],
    'invoice'         => ['en' => 'Invoice',              'bn' => 'ইনভয়েস'],
    'generate_invoice'=> ['en' => 'Generate Invoice',     'bn' => 'ইনভয়েস তৈরি করুন'],
    'student'         => ['en' => 'Student',              'bn' => 'শিক্ষার্থী'],
    'faculty'         => ['en' => 'Faculty / Staff',      'bn' => 'শিক্ষক/স্টাফ'],
    'other'           => ['en' => 'Other',                'bn' => 'অন্যান্য'],
    'party'           => ['en' => 'Related To',           'bn' => 'সম্পর্কিত'],
    'view_profile'    => ['en' => 'View Profile',         'bn' => 'প্রোফাইল দেখুন'],
    'transactions'    => ['en' => 'Transactions',         'bn' => 'লেনদেনসমূহ'],
    'all_time'        => ['en' => 'All Time',             'bn' => 'সর্বমোট'],

    // Users / permissions
    'permissions'     => ['en' => 'Permissions',          'bn' => 'অনুমতি'],
    'role'            => ['en' => 'Role',                 'bn' => 'ভূমিকা'],
    'block'           => ['en' => 'Block',                'bn' => 'ব্লক করুন'],
    'activate'        => ['en' => 'Activate',             'bn' => 'সক্রিয় করুন'],
    'blocked'         => ['en' => 'Blocked',               'bn' => 'ব্লক করা'],
    'my_profile'      => ['en' => 'My Profile',            'bn' => 'আমার প্রোফাইল'],
    'change_password' => ['en' => 'Change Password',       'bn' => 'পাসওয়ার্ড পরিবর্তন'],
    'add_user'        => ['en' => 'Add Staff User',        'bn' => 'নতুন ইউজার যোগ'],
    'read'            => ['en' => 'View',                  'bn' => 'দেখা'],
    'insert'          => ['en' => 'Add',                   'bn' => 'যোগ করা'],
    'module'          => ['en' => 'Module',                'bn' => 'মডিউল'],
];

/**
 * Render a translatable string as two spans (English shown by default,
 * Bangla hidden until the user toggles). Escapes both variants.
 */
function t($key)
{
    $entry = isset($GLOBALS['KMA_I18N'][$key]) ? $GLOBALS['KMA_I18N'][$key] : null;
    $en = $entry ? $entry['en'] : $key;
    $bn = $entry ? $entry['bn'] : $key;
    return '<span data-i18n-en>' . h($en) . '</span><span data-i18n-bn>' . h($bn) . '</span>';
}

/** Plain-string getter (e.g. for use inside an attribute) for one language. */
function t_plain($key, $lang = 'en')
{
    $entry = isset($GLOBALS['KMA_I18N'][$key]) ? $GLOBALS['KMA_I18N'][$key] : null;
    if (!$entry) { return $key; }
    return isset($entry[$lang]) ? $entry[$lang] : $key;
}
