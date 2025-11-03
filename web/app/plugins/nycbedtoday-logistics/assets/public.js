jQuery(document).ready(function ($) {
  $('.nycbt-zip-check-btn').on('click', function () {
    const $btn = $(this);
    const $input = $btn.siblings('.nycbt-zip-input');
    const $result = $btn.closest('.nycbt-zip-checker').find('.nycbt-zip-result');
    const zip = $input.val().trim();

    if (!zip || zip.length !== 5) {
      $result
        .removeClass('valid invalid loading')
        .addClass('invalid')
        .text('Please enter a valid 5-digit ZIP code')
        .show();
      return;
    }

    $btn.prop('disabled', true);
    $result.removeClass('valid invalid').addClass('loading').text('Checking...').show();

    $.ajax({
      url: nycbtLogistics.apiUrl + '/check-zip',
      type: 'POST',
      data: JSON.stringify({ zip: zip }),
      contentType: 'application/json',
      headers: {
        'X-WP-Nonce': nycbtLogistics.nonce,
      },
      success: function (response) {
        $btn.prop('disabled', false);

        if (response.valid) {
          let message = 'Great! We deliver to ZIP code ' + response.zip + '.';
          if (response.next_available_date) {
            const date = new Date(response.next_available_date);
            const formatted = date.toLocaleDateString('en-US', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric',
            });
            message += ' Next available delivery: ' + formatted;
          }

          $result.removeClass('loading invalid').addClass('valid').text(message);
        } else {
          $result.removeClass('loading valid').addClass('invalid').text(response.message);
        }
      },
      error: function () {
        $btn.prop('disabled', false);
        $result
          .removeClass('loading valid')
          .addClass('invalid')
          .text('Error checking ZIP code. Please try again.');
      },
    });
  });

  $('.nycbt-zip-input').on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $(this).siblings('.nycbt-zip-check-btn').click();
    }
  });

  $('.nycbt-zip-input').on('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
  });

  $('#nycbt-slot-date').on('change', function () {
    const date = $(this).val();
    const $display = $(this).closest('.nycbt-slots-display');

    loadSlotsForDate(date, $display);
  });

  function loadSlotsForDate(date, $display) {
    const $slotsList = $display.find('.nycbt-slots-list');

    $slotsList.html('<p>Loading slots...</p>');

    $.ajax({
      url: nycbtLogistics.apiUrl + '/available-slots',
      type: 'GET',
      data: { date: date },
      headers: {
        'X-WP-Nonce': nycbtLogistics.nonce,
      },
      success: function (response) {
        if (response.slots && response.slots.length > 0) {
          renderSlots(response.slots, response.date, $slotsList);
        } else {
          $slotsList.html(
            '<p>' + (response.message || 'No delivery slots available for this date.') + '</p>'
          );
        }
      },
      error: function () {
        $slotsList.html('<p>Error loading slots. Please try again.</p>');
      },
    });
  }

  function renderSlots(slots, date, $container) {
    const dateObj = new Date(date);
    const dateFormatted = dateObj.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });

    let html = '<h3>Available Slots for ' + dateFormatted + '</h3>';
    html += '<ul>';

    slots.forEach(function (slot) {
      html += '<li class="nycbt-slot-item">';
      html += '<span class="nycbt-slot-time">' + slot.label + '</span>';
      html += '<span class="nycbt-slot-capacity">' + slot.available + ' spots available</span>';
      html += '</li>';
    });

    html += '</ul>';

    $container.html(html);
  }
});
