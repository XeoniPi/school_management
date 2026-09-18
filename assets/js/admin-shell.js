/**
 * KMA Admin — admin-shell.js  |  ES5, vanilla JS
 * Sidebar open/close, user menu, dark mode, and language toggle
 * for the admin dashboard shell (admin_header.php / admin_footer.php).
 */
(function () {
    'use strict';

    /* ── Sidebar toggle ── */
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar   = document.getElementById('adminSidebar');
    var overlay   = document.getElementById('adminSidebarOverlay');
    var isDesktop = function () { return window.innerWidth >= 1024; };

    function openSidebar() {
        if (isDesktop()) {
            sidebar.classList.remove('admin-sidebar-collapsed');
            document.getElementById('adminContentWrap').classList.remove('admin-content-full');
        } else {
            sidebar.classList.add('admin-sidebar-open');
            if (overlay) { overlay.classList.add('show'); }
        }
    }
    function closeSidebar() {
        if (isDesktop()) {
            sidebar.classList.add('admin-sidebar-collapsed');
            document.getElementById('adminContentWrap').classList.add('admin-content-full');
        } else {
            sidebar.classList.remove('admin-sidebar-open');
            if (overlay) { overlay.classList.remove('show'); }
        }
    }
    function isOpen() {
        return isDesktop()
            ? !sidebar.classList.contains('admin-sidebar-collapsed')
            : sidebar.classList.contains('admin-sidebar-open');
    }

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            if (isOpen()) { closeSidebar(); } else { openSidebar(); }
        });
    }
    if (overlay) { overlay.addEventListener('click', closeSidebar); }

    /* Close mobile overlay sidebar automatically after navigating */
    window.addEventListener('resize', function () {
        if (isDesktop() && overlay) { overlay.classList.remove('show'); }
    });

    /* ── User dropdown ── */
    var userBtn  = document.getElementById('userMenuBtn');
    var userDrop = document.getElementById('userMenuDrop');
    if (userBtn && userDrop) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userDrop.classList.toggle('hidden');
        });
        document.addEventListener('click', function () { userDrop.classList.add('hidden'); });
    }

    /* ── Dark mode toggle ── */
    var themeBtns = document.querySelectorAll('[data-admin-theme-toggle]');
    for (var i = 0; i < themeBtns.length; i++) {
        themeBtns[i].addEventListener('click', function () {
            var isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('kma_theme', isDark ? 'dark' : 'light');
        });
    }

    /* ── Language toggle (English default, Bangla optional) ── */
    var langBtns = document.querySelectorAll('[data-lang]');
    function applyLang(lang) {
        if (lang === 'bn') {
            document.documentElement.classList.add('lang-bn');
        } else {
            document.documentElement.classList.remove('lang-bn');
        }
        for (var i = 0; i < langBtns.length; i++) {
            var btn = langBtns[i];
            btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
        }
        localStorage.setItem('kma_admin_lang', lang);
    }
    for (var j = 0; j < langBtns.length; j++) {
        (function (btn) {
            btn.addEventListener('click', function () {
                applyLang(btn.getAttribute('data-lang'));
            });
        })(langBtns[j]);
    }
    /* Apply saved preference (default stays English if nothing saved) */
    var savedLang = localStorage.getItem('kma_admin_lang');
    if (savedLang === 'bn') { applyLang('bn'); } else { applyLang('en'); }

    /* ── Nav dropdown groups ── */
    var navGroupToggles = document.querySelectorAll('[data-nav-group]');
    for (var g = 0; g < navGroupToggles.length; g++) {
        (function (btn) {
            var targetId = btn.getAttribute('data-nav-group');
            var panel = document.getElementById(targetId);
            btn.addEventListener('click', function () {
                var isOpen = panel.style.display !== 'none';
                panel.style.display = isOpen ? 'none' : 'flex';
                btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
        })(navGroupToggles[g]);
    }

    /* ── Toast notifications ── */
    /* Convert any server-rendered .alert banners into auto-dismissing
       toasts, so success/error messages never sit stale on the page. */
    (function () {
        var alerts = document.querySelectorAll('.admin-main > .alert, .admin-main .alert:not(.perm-static)');
        if (!alerts.length) { return; }

        var container = document.createElement('div');
        container.id = 'adminToastContainer';
        document.body.appendChild(container);

        alerts.forEach(function (el) {
            el.classList.add('admin-toast');
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'toast-close';
            closeBtn.innerHTML = '<i class="bi bi-x"></i>';
            var progress = document.createElement('div');
            progress.className = 'toast-progress';

            function dismiss() {
                el.classList.add('toast-leaving');
                setTimeout(function () { if (el.parentNode) { el.parentNode.removeChild(el); } }, 300);
            }
            closeBtn.addEventListener('click', dismiss);
            el.appendChild(closeBtn);
            el.appendChild(progress);
            container.appendChild(el);

            var timer = setTimeout(dismiss, 4500);
            el.addEventListener('mouseenter', function () { clearTimeout(timer); progress.style.animationPlayState = 'paused'; });
            el.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 1500); progress.style.animationPlayState = 'running'; });
        });
    }());
}());
