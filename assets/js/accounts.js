/**
 * KMA Admin — accounts.js  |  ES5, vanilla JS
 * Powers admin/views/accounts.php's add/edit transaction form:
 *  - Income/Expense toggle → filters the category <select>
 *  - Party toggle (Other/Student/Faculty) → shows the right picker
 */
(function () {
    'use strict';

    var form = document.getElementById('txForm');
    if (!form) { return; }

    /* ── Type toggle ── */
    var typeBtns   = document.querySelectorAll('.type-btn');
    var typeInput  = document.getElementById('typeInput');
    var catSelect  = document.getElementById('categorySelect');
    var incomeOpts = catSelect ? catSelect.querySelectorAll('.income-group option') : [];
    var expenseOpts= catSelect ? catSelect.querySelectorAll('.expense-group option') : [];

    function applyType(type) {
        typeInput.value = type;
        for (var i = 0; i < typeBtns.length; i++) {
            var btn = typeBtns[i];
            var active = btn.getAttribute('data-type') === type;
            btn.classList.toggle('bg-green-50', active && type === 'income');
            btn.classList.toggle('border-green-500', active && type === 'income');
            btn.classList.toggle('text-green-700', active && type === 'income');
            btn.classList.toggle('bg-red-50', active && type === 'expense');
            btn.classList.toggle('border-red-500', active && type === 'expense');
            btn.classList.toggle('text-red-700', active && type === 'expense');
            if (!active) {
                btn.classList.remove('bg-green-50','border-green-500','text-green-700','bg-red-50','border-red-500','text-red-700');
                btn.classList.add('border-kma-border','text-kma-muted');
            } else {
                btn.classList.remove('border-kma-border','text-kma-muted');
            }
        }
        var showIncome = type === 'income';
        for (var j = 0; j < incomeOpts.length; j++) { incomeOpts[j].parentNode.hidden = !showIncome; }
        for (var k = 0; k < expenseOpts.length; k++) { expenseOpts[k].parentNode.hidden = showIncome; }
        if (catSelect) {
            var visibleGroup = showIncome ? incomeOpts : expenseOpts;
            var currentOptVisible = false;
            for (var m = 0; m < visibleGroup.length; m++) {
                if (visibleGroup[m].value === catSelect.value) { currentOptVisible = true; }
            }
            if (!currentOptVisible && visibleGroup.length) { catSelect.value = visibleGroup[0].value; }
        }
    }
    for (var t = 0; t < typeBtns.length; t++) {
        (function (btn) {
            btn.addEventListener('click', function () { applyType(btn.getAttribute('data-type')); });
        })(typeBtns[t]);
    }
    if (typeInput) { applyType(typeInput.value || 'income'); }

    /* ── Party toggle ── */
    var partyBtns  = document.querySelectorAll('.party-btn');
    var partyInput = document.getElementById('partyTypeInput');
    var partyWraps = {
        other:   document.getElementById('partyOtherWrap'),
        student: document.getElementById('partyStudentWrap'),
        faculty: document.getElementById('partyFacultyWrap'),
    };

    function applyParty(party) {
        partyInput.value = party;
        for (var i = 0; i < partyBtns.length; i++) {
            var btn = partyBtns[i];
            var active = btn.getAttribute('data-party') === party;
            btn.classList.toggle('bg-accent', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('border-accent', active);
            btn.classList.toggle('border-kma-border', !active);
            btn.classList.toggle('text-kma-muted', !active);
        }
        for (var key in partyWraps) {
            if (partyWraps.hasOwnProperty(key) && partyWraps[key]) {
                partyWraps[key].classList.toggle('hidden', key !== party);
            }
        }
    }
    for (var p = 0; p < partyBtns.length; p++) {
        (function (btn) {
            btn.addEventListener('click', function () { applyParty(btn.getAttribute('data-party')); });
        })(partyBtns[p]);
    }
    if (partyInput) { applyParty(partyInput.value || 'other'); }
}());
