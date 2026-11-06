/**
 * KMA Admin — admin-holidays.js  |  ES5, vanilla JS
 * admin/views/holidays.php:
 *  - Year dropdown auto-submits on change
 *  - মেয়াদ (duration) is calculated from শুরুর/শেষ তারিখ automatically
 */
(function () {
    'use strict';

    var autos = document.querySelectorAll('[data-auto-submit]');
    for (var a = 0; a < autos.length; a++) {
        autos[a].addEventListener('change', function () { if (this.form) { this.form.submit(); } });
    }

    var startEl = document.getElementById('holStart');
    var endEl   = document.getElementById('holEnd');
    var durEl   = document.getElementById('holDuration');
    if (!startEl || !endEl || !durEl) { return; }

    var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    function toBn(n) {
        return String(n).replace(/[0-9]/g, function (d) { return BN[parseInt(d, 10)]; });
    }
    function parse(v) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v || '');
        return m ? Date.UTC(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10)) : null;
    }

    function recalc() {
        var s = parse(startEl.value);
        endEl.min = startEl.value || '';
        if (s === null) { durEl.value = ''; return; }
        var e = parse(endEl.value);
        if (e !== null && e < s) { endEl.value = ''; e = null; }
        var days = (e === null) ? 1 : Math.round((e - s) / 86400000) + 1;
        durEl.value = toBn(days) + ' দিন';
    }

    startEl.addEventListener('change', recalc);
    startEl.addEventListener('input', recalc);
    endEl.addEventListener('change', recalc);
    endEl.addEventListener('input', recalc);
    recalc();
}());
