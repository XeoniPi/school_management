/**
 * KMA — site.js  |  Vanilla JS, ES5 compatible
 * No dependencies. Works on all XAMPP / PHP 7.2 setups.
 */
(function () {
  'use strict';

  /* ─────────────────────────────────────────
     1. DARK / LIGHT MODE TOGGLE
  ───────────────────────────────────────── */
  var themeBtn = document.getElementById('themeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('kma-theme', isDark ? 'dark' : 'light');
    });
  }

  /* ─────────────────────────────────────────
     2. MOBILE MENU TOGGLE
  ───────────────────────────────────────── */
  var menuBtn    = document.getElementById('menuToggle');
  var mobileMenu = document.getElementById('mobileMenu');
  var hb1 = document.getElementById('hb1');
  var hb2 = document.getElementById('hb2');
  var hb3 = document.getElementById('hb3');

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', function () {
      var isOpen = !mobileMenu.classList.contains('hidden');
      if (isOpen) {
        mobileMenu.classList.add('hidden');
        menuBtn.setAttribute('aria-expanded', 'false');
        if (hb1) { hb1.style.transform = ''; hb1.style.opacity = '1'; }
        if (hb2) { hb2.style.opacity = '1'; }
        if (hb3) { hb3.style.transform = ''; hb3.style.opacity = '1'; }
      } else {
        mobileMenu.classList.remove('hidden');
        menuBtn.setAttribute('aria-expanded', 'true');
        if (hb1) { hb1.style.transform = 'translateY(7px) rotate(45deg)'; }
        if (hb2) { hb2.style.opacity = '0'; }
        if (hb3) { hb3.style.transform = 'translateY(-7px) rotate(-45deg)'; }
      }
    });
    document.addEventListener('click', function (e) {
      if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
        mobileMenu.classList.add('hidden');
        menuBtn.setAttribute('aria-expanded', 'false');
        if (hb1) { hb1.style.transform = ''; hb1.style.opacity = '1'; }
        if (hb2) { hb2.style.opacity = '1'; }
        if (hb3) { hb3.style.transform = ''; }
      }
    });
  }

  /* ─────────────────────────────────────────
     3. BACK TO TOP
  ───────────────────────────────────────── */
  var bttBtn = document.getElementById('backToTop');
  if (bttBtn) {
    window.addEventListener('scroll', function () {
      if (window.pageYOffset > 300) {
        bttBtn.style.opacity = '1';
        bttBtn.style.pointerEvents = 'auto';
      } else {
        bttBtn.style.opacity = '0';
        bttBtn.style.pointerEvents = 'none';
      }
    }, { passive: true });
    bttBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ─────────────────────────────────────────
     4. SCROLL REVEAL
  ───────────────────────────────────────── */
  var revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length > 0) {
    if ('IntersectionObserver' in window) {
      var revealObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });
      revealEls.forEach(function (el) { revealObs.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add('visible'); });
    }
  }

  /* ─────────────────────────────────────────
     5. HERO CAROUSEL
  ───────────────────────────────────────── */
  var heroCarousel = document.getElementById('heroCarousel');
  if (heroCarousel) {
    var INTERVAL     = 6000;
    var items        = heroCarousel.querySelectorAll('.carousel-item');
    var dots         = heroCarousel.querySelectorAll('.hero-dot');
    var progressFill = document.getElementById('heroProgressFill');
    var prevBtn      = document.getElementById('heroPrev');
    var nextBtn      = document.getElementById('heroNext');
    var scrollCue    = document.getElementById('heroScrollCue');
    var currentIdx   = 0;
    var autoTimer    = null;
    var TOTAL        = items.length;

    function resetAnimations(item) {
      var animated = item.querySelectorAll('.hero-badge, h1, .hero-desc, .hero-btns');
      animated.forEach(function (el) {
        el.style.animation = 'none';
        void el.offsetWidth;
        el.style.animation = '';
      });
    }
    function startProgress() {
      if (!progressFill) { return; }
      progressFill.style.transition = 'none';
      progressFill.style.width = '0%';
      void progressFill.offsetWidth;
      progressFill.style.transition = 'width ' + INTERVAL + 'ms linear';
      progressFill.style.width = '100%';
    }
    function stopProgress() {
      if (!progressFill) { return; }
      progressFill.style.transition = 'none';
      progressFill.style.width = '0%';
    }
    function syncDots(idx) {
      dots.forEach(function (d, i) {
        if (i === idx) { d.classList.add('active'); }
        else           { d.classList.remove('active'); }
      });
    }
    function goTo(idx) {
      if (idx === currentIdx) { return; }
      items[currentIdx].classList.remove('active');
      currentIdx = (idx + TOTAL) % TOTAL;
      items[currentIdx].classList.add('active');
      syncDots(currentIdx);
      resetAnimations(items[currentIdx]);
      startProgress();
    }
    function startAuto() {
      clearInterval(autoTimer);
      startProgress();
      autoTimer = setInterval(function () { goTo(currentIdx + 1); }, INTERVAL);
    }
    function stopAuto() { clearInterval(autoTimer); stopProgress(); }

    if (prevBtn) { prevBtn.addEventListener('click', function () { stopAuto(); goTo(currentIdx - 1); startAuto(); }); }
    if (nextBtn) { nextBtn.addEventListener('click', function () { stopAuto(); goTo(currentIdx + 1); startAuto(); }); }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        stopAuto();
        goTo(parseInt(dot.getAttribute('data-slide'), 10));
        startAuto();
      });
    });

    heroCarousel.addEventListener('mouseenter', stopAuto);
    heroCarousel.addEventListener('mouseleave', startAuto);

    var touchStartX = 0;
    heroCarousel.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    heroCarousel.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 50) {
        stopAuto();
        if (dx < 0) { goTo(currentIdx + 1); } else { goTo(currentIdx - 1); }
        startAuto();
      }
    }, { passive: true });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft')  { stopAuto(); goTo(currentIdx - 1); startAuto(); }
      if (e.key === 'ArrowRight') { stopAuto(); goTo(currentIdx + 1); startAuto(); }
    });

    if (scrollCue) {
      window.addEventListener('scroll', function () {
        scrollCue.style.opacity = window.pageYOffset > 80 ? '0' : '1';
      }, { passive: true });
      scrollCue.addEventListener('click', function () {
        var sb = document.getElementById('statsBar');
        if (sb) { sb.scrollIntoView({ behavior: 'smooth' }); }
      });
    }
    syncDots(0);
    resetAnimations(items[0]);
    startAuto();
  }

  /* ─────────────────────────────────────────
     6. NOTICE MODAL (AJAX fetch)
  ───────────────────────────────────────── */
  var noticeBackdrop = document.getElementById('noticeModalBackdrop');
  var noticeClose    = document.getElementById('noticeModalClose');

  function openNoticeModal(id) {
    if (!noticeBackdrop) { return; }
    var baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/kma';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', baseUrl + '/ajax/get_notice.php?id=' + encodeURIComponent(id), true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          var data = JSON.parse(xhr.responseText);
          if (data.success) {
            var n = data.notice;
            var t = document.getElementById('modal-notice-title');
            var d = document.getElementById('modal-notice-date');
            var c = document.getElementById('modal-notice-cat');
            var b = document.getElementById('modal-notice-content');
            var fr = document.getElementById('modal-notice-file-row');
            var fl = document.getElementById('modal-notice-file');
            if (t) { t.textContent = n.title || ''; }
            if (d) { d.textContent = n.notice_date || ''; }
            if (c) {
              c.textContent = n.category_label || '';
              c.className   = 'notice-tag ' + (n.category_css || 'tag-general');
            }
            if (b) { b.innerHTML = n.content ? n.content.replace(/\n/g, '<br>') : ''; }
            if (fr && fl) {
              if (n.file_path) {
                fr.style.display = '';
                fl.href = baseUrl + '/uploads/notices/' + n.file_path;
              } else {
                fr.style.display = 'none';
              }
            }
            noticeBackdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
          }
        } catch (err) { console.error('Notice modal error', err); }
      }
    };
    xhr.send();
  }
  function closeNoticeModal() {
    if (noticeBackdrop) {
      noticeBackdrop.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  document.querySelectorAll('.notice-item[data-notice-id]').forEach(function (item) {
    item.setAttribute('tabindex', '0');
    item.setAttribute('role', 'button');
    item.addEventListener('click', function () { openNoticeModal(item.getAttribute('data-notice-id')); });
    item.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openNoticeModal(item.getAttribute('data-notice-id')); }
    });
  });
  if (noticeClose)    { noticeClose.addEventListener('click', closeNoticeModal); }
  if (noticeBackdrop) {
    noticeBackdrop.addEventListener('click', function (e) {
      if (e.target === noticeBackdrop) { closeNoticeModal(); }
    });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeNoticeModal(); } });

  /* ─────────────────────────────────────────
     7. GENERIC TAB SWITCHER
     Works for: class tabs, fee tabs, exam tabs,
     routine tabs, syllabus tabs
  ───────────────────────────────────────── */
  function initTabGroup(tabSelector, panelPrefix, firstActive) {
    var tabs   = document.querySelectorAll(tabSelector);
    if (!tabs.length) { return; }
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.setAttribute('aria-selected', 'false'); });
        tab.setAttribute('aria-selected', 'true');
        var key    = tab.getAttribute('data-key') || tab.getAttribute('data-cls') ||
                     tab.getAttribute('data-fee') || tab.getAttribute('data-exam') ||
                     tab.getAttribute('data-month');
        var panels = document.querySelectorAll('[data-panel]');
        panels.forEach(function (p) {
          if (p.getAttribute('data-panel') === key) {
            p.classList.add('show');
            p.removeAttribute('hidden');
          } else if (p.classList.contains(panelPrefix + '-panel')) {
            p.classList.remove('show');
          }
        });
      });
    });
    if (firstActive && tabs[0]) { tabs[0].setAttribute('aria-selected', 'true'); }
  }

  /* ─────────────────────────────────────────
     8. STICKY SECTION TABS (Academics page)
  ───────────────────────────────────────── */
  var stickyBtns = document.querySelectorAll('.tab-btn[data-target]');
  if (stickyBtns.length) {
    var secIds = [];
    stickyBtns.forEach(function (btn) { secIds.push(btn.getAttribute('data-target')); });

    stickyBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        stickyBtns.forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        var sec = document.getElementById(btn.getAttribute('data-target'));
        if (sec) {
          var navH = (document.getElementById('mainNav') || {}).offsetHeight || 80;
          var top  = sec.getBoundingClientRect().top + window.pageYOffset - navH - 68;
          window.scrollTo({ top: top, behavior: 'smooth' });
        }
      });
    });

    window.addEventListener('scroll', function () {
      var navH = (document.getElementById('mainNav') || {}).offsetHeight || 80;
      var best = 0;
      secIds.forEach(function (id, i) {
        var el = document.getElementById(id);
        if (el && el.getBoundingClientRect().top <= navH + 10) { best = i; }
      });
      stickyBtns.forEach(function (btn, i) {
        if (i === best) { btn.classList.add('active'); btn.setAttribute('aria-selected','true'); }
        else            { btn.classList.remove('active'); btn.setAttribute('aria-selected','false'); }
      });
    }, { passive: true });
  }

  /* ─────────────────────────────────────────
     9. CLS TABS (class-panel pattern)
  ───────────────────────────────────────── */
  function initClsTabs(tabAttr, panelIdPrefix) {
    var tabs   = document.querySelectorAll('.cls-tab[' + tabAttr + ']');
    var panels = document.querySelectorAll('.class-panel, .cls-panel');
    if (!tabs.length) { return; }
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t)   { t.setAttribute('aria-selected','false'); });
        panels.forEach(function (p) { p.classList.remove('show'); });
        tab.setAttribute('aria-selected', 'true');
        var val    = tab.getAttribute(tabAttr);
        var target = document.getElementById(panelIdPrefix + val);
        if (target) {
          target.classList.add('show');
          animateRows(target);
        }
      });
    });
    if (tabs[0]) { tabs[0].setAttribute('aria-selected','true'); }
  }
  initClsTabs('data-cls', 'cls-');
  initClsTabs('data-cls', 'r-');
  initClsTabs('data-cls', 's-');

  /* ─────────────────────────────────────────
     10. FEE TABS
  ───────────────────────────────────────── */
  var feeTabs   = document.querySelectorAll('.fee-tab[data-fee]');
  var feePanels = document.querySelectorAll('.fee-panel');
  if (feeTabs.length) {
    feeTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        feeTabs.forEach(function (t)   { t.setAttribute('aria-selected','false'); });
        feePanels.forEach(function (p) { p.classList.remove('show'); });
        tab.setAttribute('aria-selected','true');
        var panel = document.getElementById('fee-' + tab.getAttribute('data-fee'));
        if (panel) { panel.classList.add('show'); }
      });
    });
    if (feeTabs[0]) { feeTabs[0].setAttribute('aria-selected','true'); }
  }

  /* ─────────────────────────────────────────
     11. EXAM TABS
  ───────────────────────────────────────── */
  var examTabs   = document.querySelectorAll('.exam-tab[data-exam]');
  var examPanels = document.querySelectorAll('.exam-panel');
  if (examTabs.length) {
    examTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        examTabs.forEach(function (t)   { t.setAttribute('aria-selected','false'); });
        examPanels.forEach(function (p) { p.classList.remove('show'); });
        tab.setAttribute('aria-selected','true');
        var panel = document.getElementById('ep-' + tab.getAttribute('data-exam'));
        if (panel) { panel.classList.add('show'); animateRows(panel); }
      });
    });
    if (examTabs[0]) { examTabs[0].setAttribute('aria-selected','true'); }
  }

  /* ─────────────────────────────────────────
     12. HOLIDAY FILTER
  ───────────────────────────────────────── */
  var legBtns     = document.querySelectorAll('.leg-item[data-filter]');
  var monthTabs   = document.querySelectorAll('.m-tab[data-month]');
  var holRows     = document.querySelectorAll('#holidayTable tbody tr[data-month]');
  var noResults   = document.getElementById('noResults');
  if (legBtns.length || monthTabs.length) {
    var activeType  = 'all';
    var activeMonth = 'all';
    function applyFilter() {
      var count = 0;
      holRows.forEach(function (row) {
        var okType  = activeType  === 'all' || row.getAttribute('data-type')  === activeType;
        var okMonth = activeMonth === 'all' || row.getAttribute('data-month') === activeMonth;
        row.style.display = (okType && okMonth) ? '' : 'none';
        if (okType && okMonth) { count++; }
      });
      if (noResults) { noResults.style.display = count === 0 ? 'block' : 'none'; }
    }
    legBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        legBtns.forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
        btn.classList.add('active'); btn.setAttribute('aria-pressed','true');
        activeType = btn.getAttribute('data-filter');
        applyFilter();
      });
    });
    monthTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        monthTabs.forEach(function (t) { t.setAttribute('aria-selected','false'); });
        tab.setAttribute('aria-selected','true');
        activeMonth = tab.getAttribute('data-month');
        applyFilter();
      });
    });
  }

  /* ─────────────────────────────────────────
     13. SEASON TABS (dress code)
  ───────────────────────────────────────── */
  document.querySelectorAll('.dress-card').forEach(function (card) {
    var sTabs   = card.querySelectorAll('.season-tab[data-season]');
    var sPanels = card.querySelectorAll('.season-panel');
    sTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        sTabs.forEach(function (t)   { t.setAttribute('aria-selected','false'); });
        sPanels.forEach(function (p) { p.classList.remove('show'); });
        tab.setAttribute('aria-selected','true');
        var target = document.getElementById(tab.getAttribute('data-season'));
        if (target) { target.classList.add('show'); }
      });
    });
    if (sTabs[0]) { sTabs[0].setAttribute('aria-selected','true'); }
  });

  /* ─────────────────────────────────────────
     14. SEAT BARS
  ───────────────────────────────────────── */
  var seatBars = document.querySelectorAll('.seat-fill[data-width]');
  if (seatBars.length && 'IntersectionObserver' in window) {
    var barObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.style.width = entry.target.getAttribute('data-width') || '0%';
          barObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    seatBars.forEach(function (b) { barObs.observe(b); });
  }

  /* ─────────────────────────────────────────
     15. MAP LAZY LOAD
  ───────────────────────────────────────── */
  var loadMapBtn = document.getElementById('loadMapBtn');
  if (loadMapBtn) {
    loadMapBtn.addEventListener('click', function () {
      var wrap = document.getElementById('mapWrap');
      var ph   = document.getElementById('mapPlaceholder');
      var src  = loadMapBtn.getAttribute('data-src') || '';
      if (!wrap || !src) { return; }
      var iframe             = document.createElement('iframe');
      iframe.title           = 'বিদ্যালয়ের অবস্থান';
      iframe.loading         = 'lazy';
      iframe.referrerPolicy  = 'no-referrer-when-downgrade';
      iframe.allowFullscreen = true;
      iframe.src             = src;
      iframe.style.cssText   = 'position:absolute;top:0;left:0;width:100%;height:100%;border:0';
      if (ph)  { ph.style.display = 'none'; }
      wrap.style.position = 'relative';
      wrap.appendChild(iframe);
    });
  }

  /* ─────────────────────────────────────────
     16. CHAR COUNTER
  ───────────────────────────────────────── */
  var msgBody     = document.getElementById('msgBody');
  var charCounter = document.getElementById('charCount');
  if (msgBody && charCounter) {
    msgBody.addEventListener('input', function () {
      var len = msgBody.value.length;
      var max = parseInt(msgBody.getAttribute('maxlength') || '500', 10);
      charCounter.textContent  = len + ' / ' + max;
      charCounter.style.color  = len > max * 0.9 ? '#dc2626' : '';
    });
  }

  /* ─────────────────────────────────────────
     17. GALLERY LIGHTBOX (open in new tab)
  ───────────────────────────────────────── */
  document.querySelectorAll('.gallery-item').forEach(function (item) {
    item.addEventListener('click', function () {
      var img = item.querySelector('img');
      if (img) { window.open(img.src, '_blank'); }
    });
  });

  /* ─────────────────────────────────────────
     18. FORM VALIDATION HELPER
  ───────────────────────────────────────── */
  function validateForm(form) {
    var inputs  = form.querySelectorAll('[required]');
    var isValid = true;
    inputs.forEach(function (input) {
      var errId = input.getAttribute('data-error');
      var errEl = errId ? document.getElementById(errId) : null;
      var ok;
      if (input.type === 'checkbox') {
        ok = input.checked;
      } else if (input.type === 'email') {
        ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
      } else {
        ok = input.value.trim().length > 0;
      }
      if (!ok) {
        input.classList.add('is-invalid');
        if (errEl) { errEl.style.display = 'block'; }
        isValid = false;
      } else {
        input.classList.remove('is-invalid');
        if (errEl) { errEl.style.display = 'none'; }
      }
      input.addEventListener('input', function () {
        input.classList.remove('is-invalid');
        if (errEl) { errEl.style.display = 'none'; }
      }, { once: false });
    });
    if (!isValid) {
      var first = form.querySelector('.is-invalid');
      if (first) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }
    return isValid;
  }

  /* Attach to contact form */
  var contactForm   = document.getElementById('contactForm');
  var contactSubmit = document.getElementById('contactSubmitBtn');
  if (contactForm && contactSubmit) {
    contactSubmit.addEventListener('click', function (e) {
      e.preventDefault();
      if (validateForm(contactForm)) { contactForm.submit(); }
    });
  }

  /* Attach to admission form */
  var admForm   = document.getElementById('admissionForm');
  var admSubmit = document.getElementById('admissionSubmitBtn');
  if (admForm && admSubmit) {
    admSubmit.addEventListener('click', function (e) {
      e.preventDefault();
      if (validateForm(admForm)) { admForm.submit(); }
    });
  }

  /* ─────────────────────────────────────────
     19. FILE INPUT LABEL UPDATE
  ───────────────────────────────────────── */
  document.querySelectorAll('input[type="file"][data-label]').forEach(function (input) {
    input.addEventListener('change', function () {
      var labelEl = document.getElementById(input.getAttribute('data-label'));
      if (labelEl && input.files && input.files[0]) {
        labelEl.textContent = input.files[0].name;
      }
    });
  });

  /* ─────────────────────────────────────────
     HELPER: animate table rows
  ───────────────────────────────────────── */
  function animateRows(parent) {
    var rows = parent.querySelectorAll('tr, .subj-item');
    rows.forEach(function (row, i) {
      row.style.animation = 'none';
      void row.offsetWidth;
      row.style.animation = 'fadeRowIn .3s ease ' + (i * 0.03) + 's both';
    });
  }

  /* Inject keyframe */
  var styleTag = document.createElement('style');
  styleTag.textContent = '@keyframes fadeRowIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:none}}';
  document.head.appendChild(styleTag);

})();