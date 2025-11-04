# ZIP Whitelist Management Guide

## Overview

The NYC Bed Today Same-Day Logistics plugin includes comprehensive ZIP code whitelist management. This guide covers all features for managing your service area ZIP codes through the admin panel and WP-CLI.

## Admin Panel Management

### Accessing the ZIP Codes Tab

1. Navigate to **Settings → Same-day Logistics** in the WordPress admin
2. Click the **ZIP Codes** tab
3. You'll see the ZIP code management interface

### Features

#### Add Single ZIP Code

1. Enter a 5-digit ZIP code in the input field
2. Click **Add ZIP Code** button
3. The ZIP will be added to the whitelist (duplicates are prevented)
4. You can press Enter as a keyboard shortcut

#### View ZIP Codes

- All whitelisted ZIP codes are displayed in a grid
- ZIP codes are sorted numerically for easy reference
- The total count is shown in the interface

#### Remove ZIP Code

1. Locate the ZIP code in the list
2. Click the **Remove** button next to it
3. Confirm the action when prompted

#### Bulk Import ZIP Codes

1. Click the **Bulk Import** button
2. Enter ZIP codes in the textarea:
   - One ZIP code per line, OR
   - Comma-separated values (e.g., `10001,10002,10003`)
   - Mix of both formats is supported
3. Check "Clear existing ZIP codes first" if you want to replace the entire whitelist
4. Click **Import**
5. The interface updates with the imported codes

**Import Format Examples:**

```
10001
10002
10003
```

OR

```
10001,10002,10003,10004,10005
```

OR mix both:

```
10001
10002,10003,10004
10005
```

#### Export ZIP Codes

##### Export as JSON

1. Click **Export JSON**
2. A file named `zip-codes-YYYY-MM-DD-HHMMSS.json` is downloaded
3. The file includes metadata and all ZIP codes in a structured format

**Example JSON export:**

```json
{
  "version": "1.0.0",
  "description": "NYC Bed Today ZIP Codes Export",
  "exported_at": "2024-01-15T10:30:00+00:00",
  "total_zips": 25,
  "zips": [
    "10001",
    "10002",
    "10003"
  ]
}
```

##### Export as CSV

1. Click **Export CSV**
2. A file named `zip-codes-YYYY-MM-DD-HHMMSS.csv` is downloaded
3. The file contains one ZIP code per line

**Example CSV export:**

```
10001
10002
10003
```

#### Reseed Default ZIP Codes

This restores the default NYC ZIP codes (100+ codes):

1. Click **Reseed Default**
2. Confirm the action (existing codes will be replaced)
3. The default NYC ZIP codes are loaded
4. This includes ZIP codes for:
   - Manhattan (10001-10040, etc.)
   - Brooklyn (11201-11239, etc.)
   - Queens (11004-11109, etc.)
   - Bronx (10451-10475, etc.)
   - Staten Island (10301-10314, etc.)

## WP-CLI Commands

### Reseed ZIP Codes

Reset to default NYC ZIP codes:

```bash
wp nycbt zip reseed
```

**Output:**
```
Reseeding default NYC ZIP codes...
Success: Reseeded 100 NYC ZIP codes successfully.
```

### List ZIP Codes

Display all whitelisted ZIP codes:

```bash
wp nycbt zip list
```

Display with specific format:

```bash
wp nycbt zip list --format=json
wp nycbt zip list --format=csv
```

**Formats:**
- `list` (default): One ZIP per line
- `json`: JSON formatted array
- `csv`: Comma-separated values

**Example outputs:**

```
# list format (default)
10001
10002
10003

# json format
["10001","10002","10003"]

# csv format
10001,10002,10003
```

### Import ZIP Codes from File

Import ZIP codes from a JSON or CSV file:

```bash
wp nycbt zip import /path/to/zips.json
```

With clear option (removes existing ZIP codes first):

```bash
wp nycbt zip import /path/to/zips.json --clear
```

**Supported file formats:**

JSON format (`zips.json`):
```json
{
  "zips": ["10001", "10002", "10003"]
}
```

OR

```json
["10001", "10002", "10003"]
```

CSV format (`zips.csv`):
```
10001
10002
10003
```

OR comma-separated:
```
10001,10002,10003
```

**Examples:**

Import and append:
```bash
wp nycbt zip import ~/zips.json
# Success: Imported 50 ZIP codes from zips.json
```

Import and replace:
```bash
wp nycbt zip import ~/zips.csv --clear
# Success: Imported 50 ZIP codes from zips.csv
```

## Default NYC ZIP Codes

The plugin includes 100+ NYC ZIP codes by default:

### Manhattan (10000 range)
10001, 10002, 10003, 10004, 10005, 10006, 10007, 10009, 10010, 10011, 10012, 10013, 10014, 10016, 10017, 10018, 10019, 10020, 10021, 10022, 10023, 10024, 10025, 10026, 10027, 10028, 10029, 10030, 10031, 10032, 10033, 10034, 10035, 10036, 10037, 10038, 10039, 10040, 10044, 10065, 10069, 10075, 10128, 10280, 10282

### Brooklyn & Staten Island (11000/10300 range)
10301, 10302, 10303, 10304, 10305, 10306, 10307, 10308, 10309, 10310, 10311, 10312, 10314

11004, 11005, 11101, 11102, 11103, 11104, 11105, 11106, 11109, 11201, 11203, 11204, 11205, 11206, 11207, 11208, 11209, 11210, 11211, 11212, 11213, 11214, 11215, 11216, 11217, 11218, 11219, 11220, 11221, 11222, 11223, 11224, 11225, 11226, 11228, 11229, 11230, 11231, 11232, 11233, 11234, 11235, 11236, 11237, 11238, 11239

## REST API

### Check ZIP Code

**Endpoint**: `POST /wp-json/nycbedtoday-logistics/v1/check-zip`

Check if a ZIP code is whitelisted:

```bash
curl -X POST https://example.com/wp-json/nycbedtoday-logistics/v1/check-zip \
  -H "Content-Type: application/json" \
  -d '{"zip":"10001"}'
```

**Response (valid ZIP):**
```json
{
  "valid": true,
  "zip": "10001",
  "next_available_date": "2024-01-16"
}
```

**Response (invalid ZIP):**
```json
{
  "valid": false,
  "zip": "90210",
  "message": "Sorry, we do not deliver to this ZIP code."
}
```

## Frontend ZIP Validation

### Shortcode Usage

Display a ZIP validation form:

```
[nycbt_check_zip]
```

With custom text:

```
[nycbt_check_zip button_text="Check My ZIP" placeholder="Enter your ZIP"]
```

### Gutenberg Block

Search for "ZIP Code Checker" block in the Gutenberg editor and insert it with custom:
- Button text
- Placeholder text

## Best Practices

### 1. Regular Backups

Before bulk operations, consider exporting your ZIP codes:

```bash
wp nycbt zip list --format=json > zip-codes-backup-$(date +%Y-%m-%d).json
```

### 2. Validation

After importing, verify the ZIP codes were added correctly:

```bash
wp nycbt zip list | head -20
```

### 3. Gradual Updates

When making large changes, use the export/import feature to maintain a backup version.

### 4. Testing

After modifying ZIP codes, test the frontend validation with a test ZIP:
1. Go to a page with the ZIP checker shortcode/block
2. Test with an existing ZIP code (should show valid)
3. Test with a removed ZIP code (should show invalid)

## Troubleshooting

### ZIP Codes Not Saving

**Problem**: Changes don't persist after clicking Save

**Solution**: 
- Check that you're logged in with admin privileges
- Check browser console for JavaScript errors
- Try clearing browser cache

### Import Failed

**Problem**: Bulk import returns an error

**Solution**:
- Verify file format is correct (valid JSON or CSV)
- Check file is readable and accessible
- Ensure ZIP codes are in correct format (5 digits)

### ZIP Validation Not Working

**Problem**: Frontend ZIP checker always shows "invalid"

**Solution**:
- Verify ZIP codes are in the whitelist: `wp nycbt zip list`
- Clear WordPress object cache: `wp cache flush`
- Check that plugin is activated: `wp plugin status nycbedtoday-logistics`

### Reseed Command Failed

**Problem**: `wp nycbt zip reseed` returns an error

**Solution**:
- Ensure plugin is installed and activated
- Check WP-CLI version compatibility
- Try running with verbose flag: `wp nycbt zip reseed --debug`

## Data Storage

ZIP codes are stored in the WordPress options table:

- **Option name**: `nycbedtoday_logistics_zip_whitelist`
- **Format**: PHP serialized array
- **Max size**: Limited by database (typically thousands of codes)

To view the raw data:

```bash
wp option get nycbedtoday_logistics_zip_whitelist
```

To update directly:

```bash
wp option set nycbedtoday_logistics_zip_whitelist '["10001","10002"]'
```

## Security Considerations

- ZIP whitelist modifications are restricted to admin users only
- All AJAX requests use WordPress nonces for CSRF protection
- ZIP codes are sanitized before storage
- File imports are validated and processed server-side

## Advanced: Data File Format

The plugin includes a default data file at:

```
plugins/nycbedtoday-logistics/data/nyc-zip-codes.json
```

This file is used by the "Reseed Default" function and contains the complete list of NYC ZIP codes.

**File structure:**

```json
{
  "version": "1.0.0",
  "description": "NYC Bed Today Service Area ZIP Codes",
  "generated_at": "2024-01-01T00:00:00Z",
  "total_zips": 100,
  "zips": [
    "10001",
    "10002"
  ]
}
```

## Next Steps

After setting up your ZIP whitelist:

1. **Add ZIP-restricted content**: Use the `[nycbt_check_zip]` shortcode on your service area page
2. **Configure delivery slots**: Set up time slot preferences in the Delivery Slots tab
3. **Test checkout**: Place a test order to verify ZIP validation in checkout
4. **Monitor orders**: Use the Reservations tab to track customer orders by ZIP code
