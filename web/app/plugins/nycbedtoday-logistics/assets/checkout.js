jQuery(document).ready(function($) {
    let currentZip = '';
    let selectedSlot = null;
    
    function getZipCode() {
        let zip = $('#billing_postcode').val();
        if ($('#ship-to-different-address-checkbox').is(':checked')) {
            zip = $('#shipping_postcode').val() || zip;
        }
        return zip ? zip.trim() : '';
    }
    
    function checkZipAndLoadSlots() {
        const zip = getZipCode();
        
        if (!zip || zip.length < 5) {
            return;
        }
        
        if (zip === currentZip) {
            return;
        }
        
        currentZip = zip;
        
        $('.nycbt-slot-loading').show();
        $('.nycbt-slot-error').hide();
        $('.nycbt-slots-container').empty();
        
        $.ajax({
            url: nycbtLogistics.apiUrl + '/check-zip',
            type: 'POST',
            data: JSON.stringify({ zip: zip }),
            contentType: 'application/json',
            headers: {
                'X-WP-Nonce': nycbtLogistics.nonce
            },
            success: function(response) {
                if (response.valid) {
                    loadAvailableSlots(response.next_available_date);
                } else {
                    showError(response.message || nycbtLogistics.messages.invalidZip);
                    $('.nycbt-slot-loading').hide();
                }
            },
            error: function() {
                showError('Error checking ZIP code');
                $('.nycbt-slot-loading').hide();
            }
        });
    }
    
    function loadAvailableSlots(date) {
        $.ajax({
            url: nycbtLogistics.apiUrl + '/available-slots',
            type: 'GET',
            data: { date: date },
            headers: {
                'X-WP-Nonce': nycbtLogistics.nonce
            },
            success: function(response) {
                $('.nycbt-slot-loading').hide();
                
                if (response.slots && response.slots.length > 0) {
                    renderSlots(response.slots, response.date);
                } else {
                    showError(response.message || nycbtLogistics.messages.noSlots);
                }
            },
            error: function() {
                $('.nycbt-slot-loading').hide();
                showError('Error loading time slots');
            }
        });
    }
    
    function renderSlots(slots, date) {
        const $container = $('.nycbt-slots-container');
        $container.empty();
        
        slots.forEach(function(slot) {
            const $slotEl = $('<div class="nycbt-slot">')
                .data('date', date)
                .data('start', slot.start)
                .data('end', slot.end)
                .data('label', slot.label);
            
            $slotEl.append('<span class="nycbt-slot-time">' + slot.label + '</span>');
            $slotEl.append('<span class="nycbt-slot-capacity">' + slot.available + ' spots available</span>');
            
            $container.append($slotEl);
        });
    }
    
    function showError(message) {
        $('.nycbt-slot-error').text(message).show();
        clearSelection();
    }
    
    function clearSelection() {
        selectedSlot = null;
        $('#nycbt_delivery_date').val('');
        $('#nycbt_delivery_slot_start').val('');
        $('#nycbt_delivery_slot_end').val('');
        $('#nycbt_reservation_id').val('');
        $('.nycbt-slot').removeClass('selected');
    }
    
    $(document).on('click', '.nycbt-slot', function() {
        if ($(this).hasClass('disabled')) {
            return;
        }
        
        $('.nycbt-slot').removeClass('selected');
        $(this).addClass('selected');
        
        const date = $(this).data('date');
        const start = $(this).data('start');
        const end = $(this).data('end');
        
        $('#nycbt_delivery_date').val(date);
        $('#nycbt_delivery_slot_start').val(start);
        $('#nycbt_delivery_slot_end').val(end);
        
        selectedSlot = {
            date: date,
            start: start,
            end: end
        };
        
        reserveSlot();
    });
    
    function reserveSlot() {
        if (!selectedSlot) {
            return;
        }
        
        const zip = getZipCode();
        
        $.ajax({
            url: nycbtLogistics.apiUrl + '/reserve-slot',
            type: 'POST',
            data: JSON.stringify({
                date: selectedSlot.date,
                start: selectedSlot.start,
                end: selectedSlot.end,
                zip: zip
            }),
            contentType: 'application/json',
            headers: {
                'X-WP-Nonce': nycbtLogistics.nonce
            },
            success: function(response) {
                if (response.success && response.reservation_id) {
                    $('#nycbt_reservation_id').val(response.reservation_id);
                }
            }
        });
    }
    
    $('#billing_postcode, #shipping_postcode').on('blur', function() {
        setTimeout(checkZipAndLoadSlots, 300);
    });
    
    $('#ship-to-different-address-checkbox').on('change', function() {
        setTimeout(checkZipAndLoadSlots, 300);
    });
    
    $(document.body).on('updated_checkout', function() {
        const zip = getZipCode();
        if (zip && zip.length === 5 && zip !== currentZip) {
            checkZipAndLoadSlots();
        }
    });
    
    if (getZipCode()) {
        checkZipAndLoadSlots();
    }
});
