jQuery(document).ready(function($) {
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
                nonce: nycbtLogistics.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderZipCodes(response.data.zip_codes);
                }
            }
        });
    }
    
    function renderZipCodes(zipCodes) {
        let html = '<div class="nycbt-zip-add-form">';
        html += '<input type="text" id="nycbt-new-zip" placeholder="Enter ZIP code" maxlength="5" pattern="[0-9]{5}">';
        html += '<button type="button" class="button button-primary" id="nycbt-add-zip">Add ZIP Code</button>';
        html += '</div>';
        
        html += '<div class="nycbt-zip-list">';
        
        if (zipCodes && zipCodes.length > 0) {
            zipCodes.forEach(function(zip) {
                html += '<div class="nycbt-zip-item">';
                html += '<span>' + zip + '</span>';
                html += '<button type="button" class="nycbt-remove-zip" data-zip="' + zip + '">Remove</button>';
                html += '</div>';
            });
        } else {
            html += '<p>No ZIP codes added yet.</p>';
        }
        
        html += '</div>';
        
        $zipList.html(html);
    }
    
    $(document).on('click', '#nycbt-add-zip', function() {
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
                zip_code: zipCode
            },
            success: function(response) {
                if (response.success) {
                    $input.val('');
                    renderZipCodes(response.data.zip_codes);
                    showMessage('ZIP code added successfully', 'success');
                } else {
                    showMessage(response.data.message || 'Error adding ZIP code', 'error');
                }
            },
            error: function() {
                showMessage('Error adding ZIP code', 'error');
            }
        });
    });
    
    $(document).on('click', '.nycbt-remove-zip', function() {
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
                zip_code: zipCode
            },
            success: function(response) {
                if (response.success) {
                    renderZipCodes(response.data.zip_codes);
                    showMessage('ZIP code removed successfully', 'success');
                } else {
                    showMessage(response.data.message || 'Error removing ZIP code', 'error');
                }
            },
            error: function() {
                showMessage('Error removing ZIP code', 'error');
            }
        });
    });
    
    $(document).on('keypress', '#nycbt-new-zip', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#nycbt-add-zip').click();
        }
    });
    
    function showMessage(message, type) {
        const $message = $('<div class="nycbt-message ' + type + '">' + message + '</div>');
        $zipList.prepend($message);
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
