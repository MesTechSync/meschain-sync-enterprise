#!/bin/bash

# Get current timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="backups/$TIMESTAMP"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Directories to backup
DIRS_TO_BACKUP=(
    "src"
    "meschain-frontend"
    "CursorDev"
    "modules"
    "*.js"
    "*.json"
    "*.md"
)

# Copy files
echo "Creating backup in $BACKUP_DIR"
for dir in "${DIRS_TO_BACKUP[@]}"; do
    if [ -e "$dir" ]; then
        cp -r "$dir" "$BACKUP_DIR/"
        echo "Backed up $dir"
    fi
done

# Create backup log
echo "Backup created at $TIMESTAMP" > "$BACKUP_DIR/backup.log"
echo "Files backed up:" >> "$BACKUP_DIR/backup.log"
ls -R "$BACKUP_DIR" >> "$BACKUP_DIR/backup.log"

# Create zip archive
zip -r "$BACKUP_DIR.zip" "$BACKUP_DIR"
echo "Created zip archive: $BACKUP_DIR.zip"

# Optional: Remove unzipped backup directory
rm -rf "$BACKUP_DIR"

echo "Backup completed successfully!"
