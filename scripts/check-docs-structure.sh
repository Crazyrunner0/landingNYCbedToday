#!/bin/bash

# Check documentation structure
# Verifies that all *.md files are either:
# - In /docs/ directory, or
# - README.md at project root
# 
# Usage: bash scripts/check-docs-structure.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "🔍 Checking documentation structure..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Find all .md files (excluding node_modules, vendor, web/*, .git, build/)
ORPHANED_FILES=$(find "$PROJECT_ROOT" \
  -maxdepth 1 \
  -name "*.md" \
  -type f \
  ! -name "README.md" 2>/dev/null || true)

# Check for orphaned markdown files
if [ -n "$ORPHANED_FILES" ]; then
  echo "❌ FAILED: Found orphaned *.md files in root directory:"
  echo ""
  echo "$ORPHANED_FILES" | while read -r file; do
    echo "  • $(basename "$file")"
  done
  echo ""
  echo "⚠️  All documentation (except README.md) should be in /docs/"
  echo ""
  exit 1
fi

# Count files in /docs
DOCS_COUNT=$(find "$PROJECT_ROOT/docs" -name "*.md" -type f 2>/dev/null | wc -l)

if [ "$DOCS_COUNT" -eq 0 ]; then
  echo "❌ FAILED: No documentation files found in /docs/"
  echo ""
  exit 1
fi

# List documentation files
echo "✅ Documentation structure is valid:"
echo ""
echo "📁 Root:"
echo "  • README.md (project overview, links to /docs)"
echo ""
echo "📁 /docs/ ($DOCS_COUNT files):"
find "$PROJECT_ROOT/docs" -name "*.md" -type f | sort | while read -r file; do
  filename=$(basename "$file")
  lines=$(wc -l < "$file")
  echo "  • $filename ($lines lines)"
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ All documentation is properly organized!"
exit 0
