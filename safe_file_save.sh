#!/bin/bash
# Safe file save wrapper
# Usage: ./safe_file_save.sh <filename> <content>

FILE="$1"
CONTENT="$2"

if [[ -z "$FILE" ]]; then
    echo "Usage: $0 <filename> [content]"
    exit 1
fi

# Create backup
if [[ -f "$FILE" ]]; then
    cp "$FILE" "${FILE}.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Check if file is locked
if lsof "$FILE" >/dev/null 2>&1; then
    echo "⚠️  Warning: File is open by another process"
    echo "   Close other editors/processes and try again"
    exit 1
fi

# Save content if provided
if [[ -n "$CONTENT" ]]; then
    echo "$CONTENT" > "$FILE"
    echo "✅ File saved successfully: $FILE"
else
    echo "✅ File is ready for editing: $FILE"
fi
