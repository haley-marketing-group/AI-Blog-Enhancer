#!/bin/bash

# HMG AI Blog Enhancer - Development Log Helper
# Quick script to add work entries to the development log

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

LOG_FILE="docs/DEVELOPMENT_LOG.md"

echo -e "${BLUE}📋 HMG AI Blog Enhancer - Development Log Helper${NC}"
echo "=================================================="

# Check if log file exists
if [ ! -f "$LOG_FILE" ]; then
    echo -e "${YELLOW}⚠️  Development log not found at $LOG_FILE${NC}"
    exit 1
fi

# Get current date
CURRENT_DATE=$(date +"%B %d, %Y")

echo -e "\n${GREEN}Adding work entry for: $CURRENT_DATE${NC}"
echo ""

# Prompt for session information
echo "Enter session title (e.g., 'Session 5: Main Plugin Development'):"
read -r SESSION_TITLE

echo ""
echo "Enter session focus (e.g., 'Plugin Foundation & Core Structure'):"
read -r SESSION_FOCUS

echo ""
echo "Enter completed work summary (brief description):"
read -r WORK_SUMMARY

echo ""
echo "Enter key decisions made (optional, press enter to skip):"
read -r KEY_DECISIONS

echo ""
echo "Enter any issues encountered (optional, press enter to skip):"
read -r ISSUES

# Create the log entry
LOG_ENTRY="
#### ✅ **$SESSION_TITLE**
**Time**: $(date +"%H:%M")  
**Focus**: $SESSION_FOCUS

**Completed Work:**
$WORK_SUMMARY"

if [ -n "$KEY_DECISIONS" ]; then
    LOG_ENTRY="$LOG_ENTRY

**Key Decisions Made:**
$KEY_DECISIONS"
fi

if [ -n "$ISSUES" ]; then
    LOG_ENTRY="$LOG_ENTRY

**Issues Encountered:**
$ISSUES"
fi

LOG_ENTRY="$LOG_ENTRY

---
"

# Find the insertion point (after the current date header)
if grep -q "### \*\*$CURRENT_DATE\*\*" "$LOG_FILE"; then
    # Date already exists, add after it
    echo -e "${GREEN}✅ Adding to existing date section${NC}"
    
    # Create a temporary file with the new entry
    awk -v entry="$LOG_ENTRY" '
        /^### \*\*'"$CURRENT_DATE"'\*\*/ {
            print
            getline
            print
            print entry
            next
        }
        { print }
    ' "$LOG_FILE" > "${LOG_FILE}.tmp" && mv "${LOG_FILE}.tmp" "$LOG_FILE"
else
    # New date, add new section
    echo -e "${GREEN}✅ Creating new date section${NC}"
    
    # Find the insertion point (after "## 📅 Development Timeline")
    awk -v date="$CURRENT_DATE" -v entry="$LOG_ENTRY" '
        /^## 📅 Development Timeline/ {
            print
            print ""
            print "### **" date "**"
            print ""
            print entry
            next
        }
        { print }
    ' "$LOG_FILE" > "${LOG_FILE}.tmp" && mv "${LOG_FILE}.tmp" "$LOG_FILE"
fi

echo -e "${GREEN}✅ Development log updated successfully!${NC}"
echo ""
echo -e "${BLUE}View the updated log:${NC}"
echo "  cat docs/DEVELOPMENT_LOG.md"
echo ""
echo -e "${BLUE}Check current status:${NC}"
echo "  ./check-status.sh" 