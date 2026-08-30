/**
 * KMA — faculty.js  |  ES5, vanilla JS
 * Category filter + detail modal for the faculty directory on
 * pages/about.php. Uses assets/js/modal.js for open/close/scroll-lock.
 */
(function () {
    'use strict';

    var facultyData = {};
    var dataEl = document.getElementById('facultyData');
    if (dataEl) {
        try { facultyData = JSON.parse(dataEl.textContent || dataEl.innerText || '{}'); }
        catch (e) { facultyData = {}; }
    }

    /* ── Category filter ── */
    var filterBtns = document.querySelectorAll('.faculty-filter-btn');
    var cards      = document.querySelectorAll('.faculty-card');
    var noResults  = document.getElementById('facultyNoResults');

    function applyFilter(key) {
        var visibleCount = 0;
        for (var i = 0; i < cards.length; i++) {
            var match = (key === 'all') || (cards[i].getAttribute('data-category') === key);
            cards[i].classList.toggle('is-hidden', !match);
            if (match) { visibleCount++; }
        }
        if (noResults) { noResults.classList.toggle('hidden', visibleCount !== 0); }
    }

    for (var b = 0; b < filterBtns.length; b++) {
        (function (btn) {
            btn.addEventListener('click', function () {
                for (var j = 0; j < filterBtns.length; j++) {
                    filterBtns[j].classList.remove('active');
                    filterBtns[j].setAttribute('aria-selected', 'false');
                }
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
                applyFilter(btn.getAttribute('data-filter'));
            });
        })(filterBtns[b]);
    }

    /* ── Detail modal ── */
    var modal = document.getElementById('facultyModal');
    if (!modal) { return; }

    var elPhoto        = document.getElementById('facultyModalPhoto');
    var elName         = document.getElementById('facultyModalName');
    var elDesignation  = document.getElementById('facultyModalDesignation');
    var elCategory     = document.getElementById('facultyModalCategory');
    var elEducationRow = document.getElementById('facultyModalEducationRow');
    var elEducation    = document.getElementById('facultyModalEducation');
    var elExperienceRow= document.getElementById('facultyModalExperienceRow');
    var elExperience   = document.getElementById('facultyModalExperience');
    var elPhoneRow     = document.getElementById('facultyModalPhoneRow');
    var elPhone        = document.getElementById('facultyModalPhone');
    var elEmailRow     = document.getElementById('facultyModalEmailRow');
    var elEmail        = document.getElementById('facultyModalEmail');
    var elBioWrap      = document.getElementById('facultyModalBioWrap');
    var elBio          = document.getElementById('facultyModalBio');
    var elPortfolioRow = document.getElementById('facultyModalPortfolioRow');
    var elPortfolio    = document.getElementById('facultyModalPortfolio');

    function toggleRow(rowEl, valueEl, value) {
        if (!rowEl) { return; }
        if (value) {
            if (valueEl) { valueEl.textContent = value; }
            rowEl.classList.remove('hidden');
        } else {
            rowEl.classList.add('hidden');
        }
    }

    function openFacultyModal(id) {
        var m = facultyData[id];
        if (!m) { return; }

        elPhoto.src = m.photo || 'https://placehold.co/200x200/e8f4eb/2e6b3e?text=' + encodeURIComponent((m.name_bn || '?').charAt(0));
        elPhoto.alt = m.name_bn || '';
        elName.textContent = m.name_bn + (m.name_en ? ' (' + m.name_en + ')' : '');
        elDesignation.textContent = m.designation || '';
        elCategory.textContent = m.category || '';

        toggleRow(elEducationRow, elEducation, m.education);
        toggleRow(elExperienceRow, elExperience, m.experience);

        if (m.phone) {
            elPhone.textContent = m.phone;
            elPhone.href = 'tel:' + m.phone;
            elPhoneRow.classList.remove('hidden');
        } else { elPhoneRow.classList.add('hidden'); }

        if (m.email) {
            elEmail.textContent = m.email;
            elEmail.href = 'mailto:' + m.email;
            elEmailRow.classList.remove('hidden');
        } else { elEmailRow.classList.add('hidden'); }

        if (m.bio) {
            elBio.innerHTML = m.bio;
            elBioWrap.classList.remove('hidden');
        } else { elBioWrap.classList.add('hidden'); }

        if (m.portfolio) {
            elPortfolio.href = m.portfolio;
            elPortfolioRow.classList.remove('hidden');
        } else { elPortfolioRow.classList.add('hidden'); }

        var bodyBox = modal.querySelector('.kma-modal-body');
        if (bodyBox) { bodyBox.scrollTop = 0; }

        window.KmaModal.open(modal);
    }

    for (var c = 0; c < cards.length; c++) {
        (function (card) {
            card.addEventListener('click', function () {
                openFacultyModal(card.getAttribute('data-faculty-id'));
            });
        })(cards[c]);
    }
}());
