# ZIP Whitelist Testing Guide

## Manual Testing Checklist

### Admin Panel Tests

#### Test 1: Access ZIP Codes Tab
- [ ] Navigate to Settings → Same-day Logistics
- [ ] Click the "ZIP Codes" tab
- [ ] Page loads without errors
- [ ] ZIP codes list displays

#### Test 2: Add Single ZIP Code
- [ ] In admin, enter "10050" in the ZIP input field
- [ ] Click "Add ZIP Code"
- [ ] Success message appears
- [ ] ZIP code appears in the list
- [ ] Try adding the same ZIP code again - should show "already exists" message

#### Test 3: Remove ZIP Code
- [ ] Find a ZIP code in the list (e.g., "10050")
- [ ] Click its "Remove" button
- [ ] Confirm the dialog
- [ ] Success message appears
- [ ] ZIP code is removed from list

#### Test 4: Bulk Import
- [ ] Click "Bulk Import" button
- [ ] A form appears with textarea
- [ ] Enter multiple ZIP codes:
  ```
  10051
  10052
  10053
  ```
- [ ] Click "Import"
- [ ] Success message shows count of imported ZIPs
- [ ] New ZIPs appear in list

#### Test 5: Bulk Import with Clear
- [ ] Click "Bulk Import" button
- [ ] Enter some ZIP codes
- [ ] Check "Clear existing ZIP codes first"
- [ ] Click "Import"
- [ ] List should only contain the imported ZIPs

#### Test 6: Export as JSON
- [ ] Click "Export JSON" button
- [ ] File `zip-codes-YYYY-MM-DD-HHMMSS.json` downloads
- [ ] Open file in text editor
- [ ] Verify format:
  ```json
  {
    "version": "1.0.0",
    "description": "NYC Bed Today ZIP Codes Export",
    "exported_at": "...",
    "total_zips": N,
    "zips": [...]
  }
  ```

#### Test 7: Export as CSV
- [ ] Click "Export CSV" button
- [ ] File `zip-codes-YYYY-MM-DD-HHMMSS.csv` downloads
- [ ] Open file in text editor
- [ ] Verify one ZIP code per line

#### Test 8: Reseed Default
- [ ] Click "Reseed Default" button
- [ ] Confirm the dialog
- [ ] Success message shows 100 ZIPs
- [ ] List populates with NYC ZIP codes (10001, 10002, etc.)

### Frontend Tests

#### Test 9: ZIP Checker Shortcode
- [ ] Create a test page with shortcode:
  ```
  [nycbt_check_zip]
  ```
- [ ] Visit the page
- [ ] Form displays
- [ ] Enter valid ZIP (10001):
  - [ ] "Great! We deliver to ZIP code 10001" message shows
  - [ ] No errors in console
- [ ] Enter invalid ZIP (90210):
  - [ ] "We do not deliver to this ZIP code" message shows
- [ ] Enter non-5-digit value (123):
  - [ ] Validation error shows

#### Test 10: ZIP Checker Block
- [ ] In page editor, search for "ZIP Code Checker" block
- [ ] Block inserts
- [ ] Customize button text and placeholder
- [ ] Publish page
- [ ] Visit page
- [ ] Block displays with custom text
- [ ] Validation works same as shortcode

#### Test 11: REST API - Valid ZIP
- [ ] Via terminal or API client:
  ```bash
  curl -X POST https://example.com/wp-json/nycbedtoday-logistics/v1/check-zip \
    -H "Content-Type: application/json" \
    -d '{"zip":"10001"}'
  ```
- [ ] Response shows:
  - [ ] `"valid": true`
  - [ ] `"zip": "10001"`
  - [ ] `"next_available_date"` present

#### Test 12: REST API - Invalid ZIP
- [ ] Via terminal:
  ```bash
  curl -X POST https://example.com/wp-json/nycbedtoday-logistics/v1/check-zip \
    -H "Content-Type: application/json" \
    -d '{"zip":"90210"}'
  ```
- [ ] Response shows:
  - [ ] `"valid": false`
  - [ ] `"zip": "90210"`
  - [ ] Error message present

### WP-CLI Tests

#### Test 13: List ZIP Codes
- [ ] Via terminal:
  ```bash
  wp nycbt zip list
  ```
- [ ] Shows ZIP codes (one per line)
- [ ] Test JSON format:
  ```bash
  wp nycbt zip list --format=json
  ```
- [ ] Output is valid JSON array
- [ ] Test CSV format:
  ```bash
  wp nycbt zip list --format=csv
  ```
- [ ] Output is comma-separated

#### Test 14: Reseed via CLI
- [ ] Via terminal:
  ```bash
  wp nycbt zip reseed
  ```
- [ ] Success message shows
- [ ] 100 NYC ZIP codes imported
- [ ] Verify via admin or:
  ```bash
  wp nycbt zip list | wc -l
  ```

#### Test 15: Import from JSON File
- [ ] Create test file `/tmp/zips.json`:
  ```json
  {
    "zips": ["10101", "10102", "10103"]
  }
  ```
- [ ] Via terminal:
  ```bash
  wp nycbt zip import /tmp/zips.json
  ```
- [ ] Success message
- [ ] ZIP codes added

#### Test 16: Import from CSV File
- [ ] Create test file `/tmp/zips.csv`:
  ```
  10201
  10202
  10203
  ```
- [ ] Via terminal:
  ```bash
  wp nycbt zip import /tmp/zips.csv
  ```
- [ ] Success message
- [ ] ZIP codes added

#### Test 17: Import with Clear Option
- [ ] Via terminal:
  ```bash
  wp nycbt zip import /tmp/zips.csv --clear
  ```
- [ ] Success message
- [ ] Only imported ZIPs in list
- [ ] Verify:
  ```bash
  wp nycbt zip list
  ```

### Error Handling Tests

#### Test 18: Invalid JSON Format
- [ ] Create invalid JSON file
- [ ] Try to import via CLI
- [ ] Error message shown
- [ ] No data changed

#### Test 19: File Not Found
- [ ] Via CLI:
  ```bash
  wp nycbt zip import /nonexistent/file.json
  ```
- [ ] Error message: "File not found"

#### Test 20: Unauthorized Access
- [ ] Modify admin.js to remove nonce
- [ ] Try to add ZIP via AJAX
- [ ] Should fail with "Unauthorized" error
- [ ] Restore nonce

### Data Persistence Tests

#### Test 21: Data Survives Page Reload
- [ ] Add ZIP codes in admin
- [ ] Reload page
- [ ] ZIP codes still present

#### Test 22: Data Survives Plugin Deactivation
- [ ] Add ZIP codes
- [ ] Deactivate plugin
- [ ] Reactivate plugin
- [ ] ZIP codes still present

#### Test 23: Default Seed on First Activation
- [ ] Delete option:
  ```bash
  wp option delete nycbedtoday_logistics_zip_whitelist
  ```
- [ ] Deactivate and reactivate plugin
- [ ] 100 NYC ZIP codes should be auto-loaded

## Performance Tests

### Test 24: Large Import Performance
- [ ] Generate large ZIP list (1000+ codes)
- [ ] Import via admin UI
- [ ] Should complete within reasonable time (< 5 seconds)
- [ ] No timeout errors

### Test 25: List Display Performance
- [ ] With 100+ ZIP codes in whitelist
- [ ] Admin page loads quickly
- [ ] Grid renders smoothly
- [ ] No lag when scrolling

## Security Tests

### Test 26: CSRF Token Validation
- [ ] Inspect network tab
- [ ] All AJAX requests have nonce
- [ ] Try to submit form without nonce:
  ```javascript
  jQuery.ajax({
    url: '/wp-admin/admin-ajax.php',
    data: {action: 'nycbt_add_zip_code', zip_code: '10001'}
  });
  ```
- [ ] Should fail (no nonce)

### Test 27: Permission Checks
- [ ] Log out or use non-admin account
- [ ] Try to access admin API:
  ```bash
  curl -X POST http://example.com/wp-admin/admin-ajax.php \
    -d "action=nycbt_add_zip_code&zip_code=10001"
  ```
- [ ] Should fail (not authenticated/authorized)

### Test 28: Input Sanitization
- [ ] Try to add ZIP with SQL injection attempt: `10001'; DROP TABLE zips; --`
- [ ] Should be sanitized to: `10001`
- [ ] Database unaffected

## Browser Compatibility Tests

### Test 29: Chrome/Chromium
- [ ] All features work
- [ ] No console errors
- [ ] Export downloads work

### Test 30: Firefox
- [ ] All features work
- [ ] No console errors
- [ ] Export downloads work

### Test 31: Safari
- [ ] All features work
- [ ] No console errors
- [ ] Export downloads work

## Responsive Design Tests

### Test 32: Mobile Layout
- [ ] Admin page responsive
- [ ] Controls stack appropriately on narrow screens
- [ ] Import form usable on mobile
- [ ] ZIP list readable

## Integration Tests

### Test 33: ZIP Validation in Checkout
- [ ] Ensure WooCommerce is active
- [ ] Create test order with valid ZIP (10001)
- [ ] Checkout succeeds
- [ ] Order saved with ZIP metadata

### Test 34: ZIP Validation Fails in Checkout
- [ ] Create test order with invalid ZIP (90210)
- [ ] Checkout should fail with error
- [ ] ZIP not in whitelist message

## Documentation Tests

### Test 35: Guide is Accurate
- [ ] Follow each step in ZIP_WHITELIST_GUIDE.md
- [ ] All commands work as documented
- [ ] All UI features match descriptions

### Test 36: README is Complete
- [ ] All CLI commands documented
- [ ] All admin features documented
- [ ] Examples are accurate

## Test Results Summary

| Test # | Description | Status | Notes |
|--------|-------------|--------|-------|
| 1 | Access ZIP tab | [ ] | |
| 2 | Add single ZIP | [ ] | |
| 3 | Remove ZIP | [ ] | |
| 4 | Bulk import | [ ] | |
| 5 | Import with clear | [ ] | |
| 6 | Export JSON | [ ] | |
| 7 | Export CSV | [ ] | |
| 8 | Reseed default | [ ] | |
| 9 | Shortcode check | [ ] | |
| 10 | Block check | [ ] | |
| 11 | REST API valid | [ ] | |
| 12 | REST API invalid | [ ] | |
| 13 | CLI list | [ ] | |
| 14 | CLI reseed | [ ] | |
| 15 | CLI import JSON | [ ] | |
| 16 | CLI import CSV | [ ] | |
| 17 | CLI import clear | [ ] | |
| 18 | Invalid JSON | [ ] | |
| 19 | File not found | [ ] | |
| 20 | Unauthorized | [ ] | |
| 21 | Data persistence | [ ] | |
| 22 | After deactivation | [ ] | |
| 23 | Auto seed | [ ] | |
| 24 | Large import | [ ] | |
| 25 | Performance | [ ] | |
| 26 | CSRF validation | [ ] | |
| 27 | Permission checks | [ ] | |
| 28 | Input sanitization | [ ] | |
| 29 | Chrome compat | [ ] | |
| 30 | Firefox compat | [ ] | |
| 31 | Safari compat | [ ] | |
| 32 | Mobile layout | [ ] | |
| 33 | WC checkout valid | [ ] | |
| 34 | WC checkout invalid | [ ] | |
| 35 | Guide accurate | [ ] | |
| 36 | README complete | [ ] | |

## Notes

- [ ] All tests passed
- [ ] Issues found:
  - [ ] 
- [ ] Date tested: _____________
- [ ] Tested by: _____________
