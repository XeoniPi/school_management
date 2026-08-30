/**
 * KMA Admin — shared image upload dropzone preview.
 * ES5, vanilla JS. Expects this exact markup pattern:
 *
 *   <label id="dropZone">
 *     <input type="file" id="imageInput" />
 *     <div id="uploadPreview">...placeholder...</div>
 *     <img id="previewImg" class="hidden" />
 *   </label>
 *
 * Used by: admin/views/faculty.php, admin/views/gallery.php
 */
(function () {
  'use strict';

  var input       = document.getElementById('imageInput');
  var preview     = document.getElementById('previewImg');
  var placeholder = document.getElementById('uploadPreview');
  var zone        = document.getElementById('dropZone');
  if (!input || !preview || !placeholder) { return; }

  function showPreview(file) {
    if (!file) { return; }
    var reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.classList.remove('hidden');
      placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }

  input.addEventListener('change', function () {
    if (this.files && this.files[0]) { showPreview(this.files[0]); }
  });

  if (zone) {
    zone.addEventListener('dragover', function (e) {
      e.preventDefault();
      zone.classList.add('border-accent');
    });
    zone.addEventListener('dragleave', function () {
      zone.classList.remove('border-accent');
    });
    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      zone.classList.remove('border-accent');
      var file = e.dataTransfer.files[0];
      if (file) {
        input.files = e.dataTransfer.files;
        showPreview(file);
      }
    });
  }
})();
