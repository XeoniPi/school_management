/**
 * KMA — accordion.js  |  ES5, vanilla JS
 * Generic accordion toggle used by academy/holiday-list.php and
 * academy/syllabus.php. Buttons carry data-acc-toggle="<bodyId>"
 * instead of an inline onclick attribute.
 */
(function () {
    'use strict';

    function toggleAcc(id) {
        var body  = document.getElementById(id);
        var btn   = body ? body.previousElementSibling : null;
        var arrow = btn ? btn.querySelector('.acc-arrow') : null;
        if (!body) { return; }
        var open = body.style.display !== 'none';
        body.style.display = open ? 'none' : 'block';
        if (btn)   { btn.setAttribute('aria-expanded', open ? 'false' : 'true'); }
        if (arrow) { arrow.style.transform = open ? '' : 'rotate(180deg)'; }
    }

    var triggers = document.querySelectorAll('[data-acc-toggle]');
    for (var i = 0; i < triggers.length; i++) {
        (function (btn) {
            btn.addEventListener('click', function () {
                toggleAcc(btn.getAttribute('data-acc-toggle'));
            });
        })(triggers[i]);
    }

    /* Open the first accordion's arrow on load (holiday-list.php starts
       with the first month expanded) */
    document.addEventListener('DOMContentLoaded', function () {
        var first = document.querySelector('.acc-arrow');
        var firstBody = triggers.length ? document.getElementById(triggers[0].getAttribute('data-acc-toggle')) : null;
        if (first && firstBody && firstBody.style.display !== 'none') {
            first.style.transform = 'rotate(180deg)';
        }
    });
}());
