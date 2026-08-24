# খলিলুল্লাহ মেমোরিয়াল একাডেমি — KMA School Website

A full-stack school website built with **PHP 7.2**, **MySQL**, **Tailwind CSS v3 CDN**, and **Vanilla JS (ES5)**.

---

## Requirements

| Software | Version |
|----------|---------|
| XAMPP    | 8.x (PHP 7.2+, Apache, MySQL) |
| PHP      | 7.2 or higher |
| MySQL    | 5.7 or higher |
| Browser  | Chrome / Firefox / Edge (modern) |

---

## Installation

### 1. Copy project files

```
C:\xampp\htdocs\kma\
```

Paste the entire `kma/` folder into `htdocs/`.

### 2. Import the database

1. Start **XAMPP** → start **Apache** and **MySQL**
2. Open `http://localhost/phpmyadmin`
3. Create a new database named **`kma_db`**
4. Click **Import** → choose `kma/database/kma_schema.sql`
5. Click **Go**

### 3. Configure database connection

Open `kma/config/db.php` and update if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kma_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP default is blank
```

### 4. Verify BASE_URL

In `kma/config/app.php`:

```php
define('BASE_URL', '/kma');   // matches the folder name in htdocs
```

If you rename the folder, update this value.

### 5. Set folder permissions (Linux/Mac only)

```bash
chmod -R 775 kma/uploads/
chmod -R 775 kma/files/
chmod -R 775 kma/logs/
```

Windows users: no action needed.

### 6. Open the website

| URL | Description |
|-----|-------------|
| `http://localhost/kma/` | Public homepage |
| `http://localhost/kma/admin/login.php` | Admin panel |

---

## Default Admin Credentials

| Field    | Value         |
|----------|---------------|
| Username | `admin`       |
| Password | `Admin@1234`  |

**Change the password immediately** after first login via Admin → Settings → Password.

---

## Project Structure

```
kma/
├── .htaccess                  # Apache security & rewrite rules
├── index.php                  # Homepage
├── config/
│   ├── db.php                 # PDO connection singleton
│   └── app.php                # Global helpers, constants, CSRF
├── database/
│   └── kma_schema.sql         # Full DB schema + seed data
├── includes/
│   ├── header.php             # Public site header + nav
│   ├── footer.php             # Public site footer
│   └── download_card.php      # Reusable download card partial
├── assets/
│   ├── css/site.css           # Component styles
│   └── js/site.js             # Dark mode, carousel, tabs, XHR
├── pages/
│   ├── notices.php            # Notice board
│   ├── about.php              # About us
│   ├── academics.php          # Academics hub
│   ├── admission.php          # Admission + application form
│   ├── contact.php            # Contact form
│   └── downloads.php          # Public downloads
├── academy/
│   ├── class-routine.php      # Weekly timetable
│   ├── syllabus.php           # Chapter-wise syllabus
│   ├── holiday-list.php       # Holiday calendar
│   ├── exam-schedule.php      # Exam timetable
│   └── dress-code.php         # Dress code & conduct
├── ajax/
│   └── get_notice.php         # Public AJAX: notice detail
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── includes/
│   │   ├── admin_header.php   # Sidebar layout + shared CSS
│   │   └── admin_footer.php
│   ├── ajax/
│   │   └── handler.php        # Admin AJAX: toggles, stats, etc.
│   └── views/
│       ├── notices.php
│       ├── admissions.php
│       ├── holidays.php
│       ├── downloads.php
│       ├── gallery.php
│       ├── classes.php
│       ├── settings.php
│       └── messages.php
├── uploads/
│   ├── .htaccess              # Blocks PHP execution in uploads
│   ├── notices/               # Notice attachments
│   ├── images/                # Gallery & logo uploads
│   ├── pdfs/                  # Download PDFs (syllabus, routine…)
│   └── gallery/               # Gallery photos
├── files/
│   └── *.pdf                  # Static/placeholder PDF files
└── logs/
    └── php_errors.log         # PHP error log (auto-created)
```

---

## Key Design Decisions

- **BASE_URL = `/kma`** — all internal links use `BASE_URL . '/path'`
- **PDO prepared statements** everywhere — no raw SQL with user input
- **`h()` helper** — wraps `htmlspecialchars()`, used on all output
- **CSRF tokens** — generated per session, verified on every POST
- **Dark mode** — `class` strategy on `<html>`, persisted via `localStorage`
- **File uploads** — renamed with `random_bytes(3)` hex prefix, stored in `uploads/`
- **PHP 7.2 strict** — no arrow functions, no `match`, no typed properties, no `??=`
- **Tailwind CDN** — config injected per page via `<script>` block (Play CDN mode)

---

## Admin Panel Features

| Module | Features |
|--------|----------|
| Dashboard | Live stats, recent admissions, recent messages |
| Notices | CRUD, pin toggle, category filter, file attachments |
| Admissions | Status update (pending/approved/rejected/enrolled), photo & cert view, print |
| Holidays | CRUD, year/type filter |
| Downloads | CRUD, category filter, drag-drop upload |
| Gallery | Photo grid, drag-sort, visibility toggle |
| Classes | Class CRUD, subject CRUD, assign subjects per class |
| Settings | School info, social links, logo upload, password change |
| Messages | List, read/unread, detail modal, reply via phone/email/WhatsApp, delete |

---

## Customisation

### Change school name / contact
Admin Panel → Settings → General

### Add new notices
Admin Panel → Notices → নতুন নোটিশ

### Upload actual PDFs
Admin Panel → Downloads → নতুন ফাইল যোগ করুন

### Replace placeholder logo
Admin Panel → Settings → General → লোগো আপলোড

### Update Google Maps embed
Admin Panel → Settings → General → Google Maps URL

---

## Troubleshooting

**Blank page / 500 error**
- Enable PHP errors temporarily: set `display_errors = On` in `php.ini`
- Check `kma/logs/php_errors.log`

**Database connection failed**
- Ensure MySQL is running in XAMPP
- Verify credentials in `config/db.php`

**Uploads not working**
- Check `uploads/` directory exists and is writable
- Verify `upload_max_filesize` in `php.ini` (default 2M — increase to 10M)

**CSS/JS not loading**
- Confirm `BASE_URL` in `config/app.php` matches your folder name
- Hard-refresh browser (`Ctrl+Shift+R`)

---

## Credits

- Design & Development: **XeoniFi**
- School: Khalilullah Memorial Academy (KMA)
- Location: মধ্যম বাগ্যা, চর-জুবলী, সুবর্ণচর, নোয়াখালী, বাংলাদেশ