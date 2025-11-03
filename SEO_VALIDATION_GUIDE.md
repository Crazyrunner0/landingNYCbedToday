# SEO Baseline - Quick Validation Guide

This guide provides quick steps to validate that the SEO baseline configuration is working correctly.

## Pre-requisites

1. WordPress is installed and running: `make up` and access `http://localhost:8080`
2. Complete WordPress installation wizard if not already done
3. RankMath is activated (should happen automatically via mu-plugin)

## Quick Validation Checklist

### 1. ✅ Verify RankMath is Active (30 seconds)

```bash
make wp CMD='plugin list --status=active' | grep rank-math
```

Expected output:
```
rank-math              | 1.x.x | active
```

### 2. ✅ Verify Sitemap Works (30 seconds)

Open in browser or run:
```bash
curl -I http://localhost:8080/sitemap_index.xml
```

Expected response:
```
HTTP/1.1 200 OK
Content-Type: application/xml
```

### 3. ✅ Verify Robots.txt Works (30 seconds)

Open in browser or run:
```bash
curl -I http://localhost:8080/robots.txt
```

Expected response:
```
HTTP/1.1 200 OK
Content-Type: text/plain
```

### 4. ✅ Verify JSON-LD Schemas (1 minute)

Open homepage source code and look for JSON-LD blocks:

```bash
curl -s http://localhost:8080 | grep -A5 '"@context"' | head -20
```

You should see multiple blocks like:
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "NYC Bed Today",
  ...
}
```

### 5. ✅ Test with Google Rich Results (2-3 minutes)

1. Go to: https://search.google.com/test/rich-results
2. Enter URL: `http://localhost:8080`
3. Click "Test URL"
4. Wait for results

Expected: All schemas should be detected with no critical errors.

## Detailed Validation Commands

### View Complete Sitemap

```bash
curl -s http://localhost:8080/sitemap_index.xml | grep -o '<loc>.*</loc>'
```

### View Complete Robots.txt

```bash
curl -s http://localhost:8080/robots.txt
```

### Extract All JSON-LD Schemas

```bash
curl -s http://localhost:8080 | grep -o '<script type="application/ld+json">.*</script>'
```

### Check RankMath Settings

```bash
make wp CMD='option get rank-math-options-general --format=json'
make wp CMD='option get rank-math-modules --format=json'
make wp CMD='option get rank-math-site-meta --format=json'
```

## Manual Inspection (In Browser)

1. **Homepage:**
   - Right-click → View Page Source
   - Search for `"@context": "https://schema.org"`
   - Should find LocalBusiness, BreadcrumbList, and FAQPage schemas

2. **RankMath Admin Dashboard:**
   - Log in to WordPress: `http://localhost:8080/wp-admin`
   - Go to RankMath > General Settings
   - Verify settings are configured

3. **Sitemaps in Browser:**
   - `http://localhost:8080/sitemap_index.xml`
   - `http://localhost:8080/sitemap-posts.xml`
   - `http://localhost:8080/sitemap-pages.xml`

## Troubleshooting

### RankMath Not Active

```bash
# Manually activate
make wp CMD='plugin activate rank-math'

# Verify
make wp CMD='plugin is-active rank-math'
```

### Sitemaps Returning 404

```bash
# Flush rewrite rules
make wp CMD='rewrite flush'

# Regenerate sitemaps
make wp CMD='eval-file scripts/rankmath-import-settings.php'

# Check Docker is running
make up
```

### JSON-LD Not Showing

```bash
# Check mu-plugin is loaded
make wp CMD='mu-plugin list'

# Should show rankmath-setup as active
```

### Port 8080 Not Accessible

```bash
# Check Docker services
make logs

# Restart services
make restart
```

## Full Setup Script (Automated)

To run all setup steps automatically:

```bash
./scripts/setup-rankmath-seo.sh
```

This will:
1. Verify WordPress installation
2. Ensure RankMath is installed
3. Activate RankMath
4. Import configuration
5. Flush rewrite rules
6. Verify sitemaps
7. Verify robots.txt
8. Check JSON-LD schemas

## Next Steps After Validation

✅ **All checks passing?** Proceed to:

1. **Customize Data:**
   - Edit `scripts/rankmath-settings.json`
   - Update address, phone, FAQ items
   - Re-run setup script

2. **Test with Google:**
   - Submit sitemap to Google Search Console
   - Monitor for indexing

3. **Deploy to Production:**
   - Update domain in environment
   - Configure SSL/TLS
   - Test again with live domain

## Documentation References

- **Full Documentation:** [SEO_BASELINE_RANKMATH.md](SEO_BASELINE_RANKMATH.md)
- **RankMath Docs:** https://rankmath.com/kb/
- **Schema.org:** https://schema.org/
- **Google Rich Results:** https://search.google.com/test/rich-results

## Support

For more detailed information, see:
- `SEO_BASELINE_RANKMATH.md` - Complete setup guide
- `README.md` - General project documentation
