/**
 * KMA — academics-tabs.js  |  ES5, vanilla JS
 * pages/academics.php only:
 *  1) Month accordion open/close (replaces the old inline onclick)
 *  2) Holiday legend → filters visible holiday rows by type, hides
 *     empty months, auto-expands the first month with a match.
 */
(function () {
    'use strict';

    /* ── 1. Accordion toggle ── */
    var monthToggles = document.querySelectorAll('.holiday-month-toggle');
    for (var i = 0; i < monthToggles.length; i++) {
        monthToggles[i].addEventListener('click', function () {
            var body = this.nextElementSibling;
            var icon = this.querySelector('.acc-icon');
            if (body) { body.classList.toggle('hidden'); }
            if (icon) { icon.classList.toggle('rotate-180'); }
        });
    }

    /* ── 2. Legend filter ── */
    var legendBtns = document.querySelectorAll('#holidayLegend .leg-item');
    var months     = document.querySelectorAll('.holiday-month');
    var noResults  = document.getElementById('holidayNoResults');
    if (!legendBtns.length || !months.length) { return; }

    function applyHolidayFilter(type) {
        var totalVisible = 0;
        var firstMatchBody = null;

        for (var m = 0; m < months.length; m++) {
            var month = months[m];
            var rows  = month.querySelectorAll('.holiday-row');
            var body  = month.querySelector('.holiday-month-body');
            var countBadge = month.querySelector('.holiday-month-count');
            var visibleInMonth = 0;

            for (var r = 0; r < rows.length; r++) {
                var match = (type === 'all') || (rows[r].getAttribute('data-type') === type);
                rows[r].classList.toggle('is-hidden', !match);
                if (match) { visibleInMonth++; }
            }

            month.classList.toggle('is-empty', visibleInMonth === 0);
            if (countBadge) { countBadge.textContent = visibleInMonth; }
            totalVisible += visibleInMonth;

            if (visibleInMonth > 0 && !firstMatchBody && type !== 'all') {
                firstMatchBody = body;
            }
        }

        /* When a specific type is chosen, auto-expand the first month
           that has a match so the result is immediately visible. */
        if (firstMatchBody && firstMatchBody.classList.contains('hidden')) {
            firstMatchBody.classList.remove('hidden');
            var toggleBtn = firstMatchBody.previousElementSibling;
            var icon = toggleBtn ? toggleBtn.querySelector('.acc-icon') : null;
            if (icon) { icon.classList.add('rotate-180'); }
        }

        if (noResults) { noResults.classList.toggle('hidden', totalVisible !== 0); }
    }

    for (var b = 0; b < legendBtns.length; b++) {
        (function (btn) {
            btn.addEventListener('click', function () {
                for (var j = 0; j < legendBtns.length; j++) {
                    legendBtns[j].classList.remove('active');
                    legendBtns[j].setAttribute('aria-pressed', 'false');
                }
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                applyHolidayFilter(btn.getAttribute('data-filter'));
            });
        })(legendBtns[b]);
    }
}());
