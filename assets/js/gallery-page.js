/**
 * KMA — gallery-page.js  |  ES5, vanilla JS
 * Full gallery page (pages/gallery.php) lightbox.
 * Image is ALWAYS shown inside the shared .kma-modal; there is no
 * window.open() / target="_blank" anywhere, so a click never opens
 * a new tab. Uses assets/js/modal.js for open/close/scroll-lock.
 */
(function () {
    'use strict';

    var modal   = document.getElementById('galleryModal');
    var imgEl   = document.getElementById('galleryModalImg');
    var capEl   = document.getElementById('galleryModalCaption');
    var idxEl   = document.getElementById('galleryModalIndex');
    var prevBtn = document.getElementById('galleryPrevBtn');
    var nextBtn = document.getElementById('galleryNextBtn');
    if (!modal) { return; }

    var thumbs = document.querySelectorAll('.gallery-thumb');
    var list   = [];
    for (var t = 0; t < thumbs.length; t++) {
        list.push({
            img: thumbs[t].getAttribute('data-img'),
            caption: thumbs[t].getAttribute('data-caption')
        });
    }
    var current = 0;

    function render(idx) {
        if (!list.length) { return; }
        if (idx < 0) { idx = list.length - 1; }
        if (idx >= list.length) { idx = 0; }
        current = idx;
        var item = list[current];
        imgEl.src = item.img;
        imgEl.alt = item.caption;
        capEl.textContent = item.caption;
        if (idxEl) { idxEl.textContent = (current + 1) + ' / ' + list.length; }
        var multi = list.length > 1;
        if (prevBtn) { prevBtn.style.display = multi ? 'flex' : 'none'; }
        if (nextBtn) { nextBtn.style.display = multi ? 'flex' : 'none'; }
    }

    for (var i = 0; i < thumbs.length; i++) {
        (function (el, idx) {
            el.addEventListener('click', function () { render(idx); window.KmaModal.open(modal); });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                    render(idx); window.KmaModal.open(modal);
                }
            });
        })(thumbs[i], i);
    }

    if (prevBtn) { prevBtn.addEventListener('click', function () { render(current - 1); }); }
    if (nextBtn) { nextBtn.addEventListener('click', function () { render(current + 1); }); }

    /* Arrow-key navigation while this modal is open.
       Close-on-click / close-on-Escape is handled centrally by modal.js */
    document.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('show')) { return; }
        if (e.key === 'ArrowLeft')  { render(current - 1); }
        if (e.key === 'ArrowRight') { render(current + 1); }
    });
}());
