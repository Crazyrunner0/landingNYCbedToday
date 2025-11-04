jQuery(document).ready(function ($) {
  const $zipList = $('#nycbt-zip-list');

  if ($zipList.length) {
    loadZipCodes();
  }

  function loadZipCodes() {
    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_get_zip_codes',
        nonce: nycbtLogistics.nonce,
      },
      success: function (response) {
        if (response.success) {
          renderZipCodes(response.data.zip_codes);
        }
      },
    });
  }

  function renderZipCodes(zipCodes) {
    let html = '<div class="nycbt-zip-manager-controls">';
    
    html += '<div class="nycbt-zip-add-form">';
    html += '<div class="nycbt-zip-single-add">';
    html +=
      '<input type="text" id="nycbt-new-zip" placeholder="Enter ZIP code" maxlength="5" pattern="[0-9]{5}">';
    html +=
      '<button type="button" class="button button-primary" id="nycbt-add-zip">Add ZIP Code</button>';
    html += '</div>';
    html += '<div class="nycbt-zip-bulk-actions">';
    html += '<button type="button" class="button" id="nycbt-export-zips-json" title="Export as JSON">Export JSON</button>';
    html += '<button type="button" class="button" id="nycbt-export-zips-csv" title="Export as CSV">Export CSV</button>';
    html += '<button type="button" class="button" id="nycbt-reseed-default" title="Reset to default NYC ZIP codes">Reseed Default</button>';
    html += '<button type="button" class="button" id="nycbt-import-zips" title="Bulk import ZIP codes">Bulk Import</button>';
    html += '</div>';
    html += '</div>';

    html += '<div class="nycbt-zip-import-form" id="nycbt-import-form" style="display:none;">';
    html += '<h3>Bulk Import ZIP Codes</h3>';
    html += '<p>Enter ZIP codes one per line or comma-separated:</p>';
    html += '<textarea id="nycbt-import-text" placeholder="10001&#10;10002&#10;10003..." rows="10"></textarea>';
    html += '<label><input type="checkbox" id="nycbt-clear-existing"> Clear existing ZIP codes first</label>';
    html += '<div class="nycbt-import-buttons">';
    html += '<button type="button" class="button button-primary" id="nycbt-confirm-import">Import</button>';
    html += '<button type="button" class="button" id="nycbt-cancel-import">Cancel</button>';
    html += '</div>';
    html += '</div>';

    html += '<div class="nycbt-zip-list">';

    if (zipCodes && zipCodes.length > 0) {
      zipCodes.forEach(function (zip) {
        html += '<div class="nycbt-zip-item">';
        html += '<span>' + zip + '</span>';
        html +=
          '<button type="button" class="nycbt-remove-zip" data-zip="' + zip + '">Remove</button>';
        html += '</div>';
      });
    } else {
      html += '<p>No ZIP codes added yet.</p>';
    }

    html += '</div>';
    html += '</div>';

    $zipList.html(html);
  }

  $(document).on('click', '#nycbt-add-zip', function () {
    const $input = $('#nycbt-new-zip');
    const zipCode = $input.val().trim();

    if (!zipCode || zipCode.length !== 5) {
      alert('Please enter a valid 5-digit ZIP code');
      return;
    }

    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_add_zip_code',
        nonce: nycbtLogistics.nonce,
        zip_code: zipCode,
      },
      success: function (response) {
        if (response.success) {
          $input.val('');
          renderZipCodes(response.data.zip_codes);
          showMessage('ZIP code added successfully', 'success');
        } else {
          showMessage(response.data.message || 'Error adding ZIP code', 'error');
        }
      },
      error: function () {
        showMessage('Error adding ZIP code', 'error');
      },
    });
  });

  $(document).on('click', '.nycbt-remove-zip', function () {
    const zipCode = $(this).data('zip');

    if (!confirm('Are you sure you want to remove ZIP code ' + zipCode + '?')) {
      return;
    }

    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_remove_zip_code',
        nonce: nycbtLogistics.nonce,
        zip_code: zipCode,
      },
      success: function (response) {
        if (response.success) {
          renderZipCodes(response.data.zip_codes);
          showMessage('ZIP code removed successfully', 'success');
        } else {
          showMessage(response.data.message || 'Error removing ZIP code', 'error');
        }
      },
      error: function () {
        showMessage('Error removing ZIP code', 'error');
      },
    });
  });

  $(document).on('click', '#nycbt-export-zips-json', function () {
    exportZips('json');
  });

  $(document).on('click', '#nycbt-export-zips-csv', function () {
    exportZips('csv');
  });

  $(document).on('click', '#nycbt-import-zips', function () {
    $('#nycbt-import-form').toggle();
  });

  $(document).on('click', '#nycbt-cancel-import', function () {
    $('#nycbt-import-form').hide();
    $('#nycbt-import-text').val('');
  });

  $(document).on('click', '#nycbt-confirm-import', function () {
    const zipsText = $('#nycbt-import-text').val().trim();
    const clearExisting = $('#nycbt-clear-existing').is(':checked');

    if (!zipsText) {
      showMessage('Please enter ZIP codes to import', 'error');
      return;
    }

    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_bulk_import_zips',
        nonce: nycbtLogistics.nonce,
        zips_text: zipsText,
        clear_existing: clearExisting ? 'true' : 'false',
      },
      success: function (response) {
        if (response.success) {
          $('#nycbt-import-form').hide();
          $('#nycbt-import-text').val('');
          renderZipCodes(response.data.zip_codes);
          showMessage(response.data.message, 'success');
        } else {
          showMessage(response.data.message || 'Error importing ZIP codes', 'error');
        }
      },
      error: function () {
        showMessage('Error importing ZIP codes', 'error');
      },
    });
  });

  $(document).on('click', '#nycbt-reseed-default', function () {
    if (!confirm('This will replace your current ZIP codes with default NYC ZIP codes. Continue?')) {
      return;
    }

    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_reseed_zips',
        nonce: nycbtLogistics.nonce,
      },
      success: function (response) {
        if (response.success) {
          renderZipCodes(response.data.zip_codes);
          showMessage(response.data.message, 'success');
        } else {
          showMessage(response.data.message || 'Error reseeding ZIP codes', 'error');
        }
      },
      error: function () {
        showMessage('Error reseeding ZIP codes', 'error');
      },
    });
  });

  $(document).on('keypress', '#nycbt-new-zip', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#nycbt-add-zip').click();
    }
  });

  function exportZips(format) {
    $.ajax({
      url: nycbtLogistics.ajaxurl,
      type: 'POST',
      data: {
        action: 'nycbt_export_zips',
        nonce: nycbtLogistics.nonce,
        format: format,
      },
      success: function (response) {
        if (response.success) {
          downloadFile(response.data.content, response.data.filename, format === 'json' ? 'application/json' : 'text/csv');
          showMessage('ZIP codes exported successfully', 'success');
        } else {
          showMessage('Error exporting ZIP codes', 'error');
        }
      },
      error: function () {
        showMessage('Error exporting ZIP codes', 'error');
      },
    });
  }

  function downloadFile(content, filename, contentType) {
    const blob = new Blob([content], { type: contentType });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  }

  function showMessage(message, type) {
    const $message = $('<div class="nycbt-message ' + type + '">' + message + '</div>');
    $zipList.prepend($message);
    setTimeout(function () {
      $message.fadeOut(function () {
        $(this).remove();
      });
    }, 3000);
  }
});
