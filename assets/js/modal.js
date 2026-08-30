/**
 * KMA — modal.js  |  ES5, vanilla JS
 * Generic open/close + scroll-lock for the shared .kma-modal system.
 * Handles: close buttons ([data-close]), backdrop click, and Escape key.
 * Page-specific logic (populating a notice, a gallery image, a faculty
 * profile, etc.) lives in that page's own script and calls
 * KmaModal.open(id) / KmaModal.close(id).
 */
(function (window, document) {
  'use strict';

  var bodyEl = document.body;
  var openCount = 0;

  function lockScroll() {
    openCount++;
    bodyEl.classList.add('kma-modal-lock');
  }
  function unlockScroll() {
    openCount = openCount > 0 ? openCount - 1 : 0;
    if (openCount === 0) { bodyEl.classList.remove('kma-modal-lock'); }
  }

  function open(idOrEl) {
    var modal = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
    if (!modal) { return; }
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    lockScroll();
  }

  function close(idOrEl) {
    var modal = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
    if (!modal || !modal.classList.contains('show')) { return; }
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    unlockScroll();
  }

  function closeAll() {
    var open = document.querySelectorAll('.kma-modal.show');
    for (var i = 0; i < open.length; i++) { close(open[i]); }
  }

  /* Close via [data-close] buttons or backdrop click */
  document.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target.closest('[data-close]') : null;
    if (!t) { return; }
    /* Backdrop wrapper doubles as the click-outside target — only close
       when the click landed directly on it, not on the panel inside it */
    if (t.classList.contains('kma-modal-dialog') && e.target !== t) { return; }
    var modal = t.closest ? t.closest('.kma-modal') : null;
    if (modal) { close(modal); }
  });

  /* Close on Escape */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') { closeAll(); }
  });

  window.KmaModal = { open: open, close: close, closeAll: closeAll };
})(window, document);
