#!/bin/bash
# RankMath SEO Baseline Setup Script
# Sets up RankMath with configured settings and validates the setup

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "🔍 RankMath SEO Baseline Setup"
echo "=============================="
echo ""

# Check if WordPress is installed
echo "Checking WordPress installation..."
docker compose exec php wp --allow-root core is-installed --quiet 2>/dev/null
if [ $? -ne 0 ]; then
    echo "❌ WordPress is not installed. Please run 'make bootstrap' first."
    exit 1
fi

# Check if RankMath is installed
echo "Checking RankMath installation..."
if ! docker compose exec php wp --allow-root plugin list --status=installed 2>/dev/null | grep -q "rank-math"; then
    echo "⚠️  RankMath is not installed. Installing via Composer..."
    docker compose run --rm php composer require wpackagist-plugin/rank-math:^1.0 --no-interaction
fi

# Activate RankMath
echo ""
echo "Step 1: Activating RankMath..."
docker compose exec php wp --allow-root plugin activate rank-math
echo "✅ RankMath activated"

# Import settings
echo ""
echo "Step 2: Importing RankMath configuration..."
docker compose exec php wp --allow-root eval-file scripts/rankmath-import-settings.php
echo "✅ Settings imported"

# Flush rewrite rules to ensure sitemaps work
echo ""
echo "Step 3: Flushing rewrite rules..."
docker compose exec php wp --allow-root rewrite flush
echo "✅ Rewrite rules flushed"

# Verify sitemap
echo ""
echo "Step 4: Verifying sitemaps..."
SITEMAP_RESPONSE=$(curl -s -I http://localhost:8080/sitemap_index.xml | head -1)
if echo "$SITEMAP_RESPONSE" | grep -q "200"; then
    echo "✅ Sitemaps are working"
else
    echo "⚠️  Sitemap returned: $SITEMAP_RESPONSE"
fi

# Verify robots.txt
echo ""
echo "Step 5: Verifying robots.txt..."
ROBOTS_RESPONSE=$(curl -s -I http://localhost:8080/robots.txt | head -1)
if echo "$ROBOTS_RESPONSE" | grep -q "200"; then
    echo "✅ Robots.txt is working"
else
    echo "⚠️  Robots.txt returned: $ROBOTS_RESPONSE"
fi

# Check for JSON-LD on homepage
echo ""
echo "Step 6: Checking JSON-LD schemas on homepage..."
JSON_LD_COUNT=$(curl -s http://localhost:8080 | grep -o '"@context"' | wc -l)
if [ "$JSON_LD_COUNT" -gt 0 ]; then
    echo "✅ Found $JSON_LD_COUNT JSON-LD schema(s) on homepage"
else
    echo "⚠️  No JSON-LD schemas found on homepage"
fi

echo ""
echo "=============================="
echo "✅ RankMath SEO Setup Complete!"
echo ""
echo "Next steps:"
echo "1. Test structured data with Google Rich Results Test:"
echo "   https://search.google.com/test/rich-results?url=http://localhost:8080"
echo ""
echo "2. View detailed documentation:"
echo "   cat $PROJECT_ROOT/SEO_BASELINE_RANKMATH.md"
echo ""
echo "3. Customize placeholder data:"
echo "   - Edit rankmath-settings.json"
echo "   - Update address, phone, FAQ items, etc."
echo "   - Re-run this script to import changes"
echo ""
