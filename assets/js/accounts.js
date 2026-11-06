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

    /* ── Student combobox: class filter → searchable student picker ── */
    var studentsData = [];
    var dataEl = document.getElementById('studentsData');
    if (dataEl) {
        try { studentsData = JSON.parse(dataEl.textContent || dataEl.innerText || '[]'); }
        catch (e) { studentsData = []; }
    }
    var classFilter    = document.getElementById('studentClassFilter');
    var searchInput    = document.getElementById('studentSearchInput');
    var resultsPanel   = document.getElementById('studentSearchResults');
    var studentIdInput = document.getElementById('studentIdInput');

    if (classFilter && searchInput && resultsPanel && studentIdInput) {
        var studentsInClass = function (classId) {
            var list = [];
            if (!classId) { return list; }
            for (var i = 0; i < studentsData.length; i++) {
                if (String(studentsData[i].apply_class_id) === String(classId)) { list.push(studentsData[i]); }
            }
            return list;
        };
        var renderResults = function (matches) {
            if (!matches.length) {
                resultsPanel.innerHTML = '<div class="student-search-empty">কোনো শিক্ষার্থী পাওয়া যায়নি</div>';
                resultsPanel.classList.remove('hidden');
                return;
            }
            var html = '';
            var shown = matches.slice(0, 25);
            for (var i = 0; i < shown.length; i++) {
                var s = shown[i];
                var label = String(s.student_name_bn).replace(/"/g, '&quot;') + ' (' + s.app_no + ')';
                html += '<button type="button" class="student-search-item" data-id="' + s.id + '" data-name="' + label + '">' +
                        s.student_name_bn + ' <span>(' + s.app_no + ')</span></button>';
            }
            if (matches.length > shown.length) {
                html += '<div class="student-search-empty">আরও ' + (matches.length - shown.length) + ' জন — খুঁজে সংকুচিত করুন</div>';
            }
            resultsPanel.innerHTML = html;
            resultsPanel.classList.remove('hidden');
            var items = resultsPanel.querySelectorAll('.student-search-item');
            for (var j = 0; j < items.length; j++) {
                items[j].addEventListener('click', function () {
                    studentIdInput.value = this.getAttribute('data-id');
                    searchInput.value = this.getAttribute('data-name');
                    resultsPanel.classList.add('hidden');
                });
            }
        };
        var doSearch = function () {
            var pool = studentsInClass(classFilter.value);
            var query = searchInput.value.trim().toLowerCase();
            if (!query) { renderResults(pool); return; }
            renderResults(pool.filter(function (s) {
                return s.student_name_bn.toLowerCase().indexOf(query) !== -1 ||
                       String(s.app_no).toLowerCase().indexOf(query) !== -1;
            }));
        };
        classFilter.addEventListener('change', function () {
            studentIdInput.value = '';
            searchInput.value = '';
            searchInput.disabled = !classFilter.value;
            resultsPanel.classList.add('hidden');
        });
        searchInput.addEventListener('focus', doSearch);
        searchInput.addEventListener('input', function () { studentIdInput.value = ''; doSearch(); });
        document.addEventListener('click', function (e) {
            if (e.target !== searchInput && !resultsPanel.contains(e.target)) { resultsPanel.classList.add('hidden'); }
        });
        if (studentIdInput.value) {
            for (var k = 0; k < studentsData.length; k++) {
                if (String(studentsData[k].id) === String(studentIdInput.value)) {
                    searchInput.value = studentsData[k].student_name_bn + ' (' + studentsData[k].app_no + ')';
                    break;
                }
            }
        }
    }
}());
