#!/bin/bash

# MesChain Sync v3.1.0 - OCMOD Package Validation Script
# This script validates the OCMOD package structure and creates the final ZIP file

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="/Users/mezbjen/Desktop/MesTech/MesChain-Sync"
OCMOD_DIR="$PROJECT_DIR/NEW_OCMOD"
PACKAGE_NAME="meschain_sync_v3.1.0_ocmod.zip"

echo "==================================================="
echo "MesChain Sync v3.1.0 - OCMOD Package Validation"
echo "==================================================="

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    case $color in
        "green") echo -e "\033[32m✓ $message\033[0m" ;;
        "red") echo -e "\033[31m✗ $message\033[0m" ;;
        "yellow") echo -e "\033[33m⚠ $message\033[0m" ;;
        "blue") echo -e "\033[34mℹ $message\033[0m" ;;
    esac
}

# Check if OCMOD directory exists
if [ ! -d "$OCMOD_DIR" ]; then
    print_status "red" "OCMOD directory not found: $OCMOD_DIR"
    exit 1
fi

print_status "green" "OCMOD directory found: $OCMOD_DIR"

# Validate required files
echo
echo "Validating OCMOD structure..."

required_files=(
    "install.xml"
    "upload/install/install.php"
    "upload/install/meschain_sync_installer.php"
    "upload/system/library/meschain/helper/trendyol.php"
    "upload/admin/controller/extension/module/meschain_sync.php"
    "upload/admin/controller/extension/module/trendyol_advanced.php"
)

missing_files=0
for file in "${required_files[@]}"; do
    if [ -f "$OCMOD_DIR/$file" ]; then
        print_status "green" "Found: $file"
    else
        print_status "red" "Missing: $file"
        missing_files=$((missing_files + 1))
    fi
done

if [ $missing_files -gt 0 ]; then
    print_status "red" "$missing_files required files are missing!"
    exit 1
fi

# Count total files
echo
echo "Counting files..."
total_files=$(find "$OCMOD_DIR" -type f | wc -l | xargs)
upload_files=$(find "$OCMOD_DIR/upload" -type f | wc -l | xargs)
print_status "blue" "Total files in OCMOD: $total_files"
print_status "blue" "Files in upload directory: $upload_files"

# Validate install.xml
echo
echo "Validating install.xml..."
if [ -f "$OCMOD_DIR/install.xml" ]; then
    # Check if XML is valid
    xmllint --noout "$OCMOD_DIR/install.xml" 2>/dev/null
    if [ $? -eq 0 ]; then
        print_status "green" "install.xml is valid XML"
    else
        print_status "red" "install.xml has XML syntax errors"
        exit 1
    fi
    
    # Check for required elements
    if grep -q "meschain_sync_v3_1_0" "$OCMOD_DIR/install.xml"; then
        print_status "green" "OCMOD code found in install.xml"
    else
        print_status "red" "OCMOD code not found in install.xml"
        exit 1
    fi
else
    print_status "red" "install.xml not found"
    exit 1
fi

# Validate PHP syntax for key files (skip if PHP not available)
echo
echo "Validating PHP syntax..."

if command -v php > /dev/null 2>&1; then
    php_files=(
        "upload/install/install.php"
        "upload/install/meschain_sync_installer.php"
        "upload/system/library/meschain/helper/trendyol.php"
    )

    for php_file in "${php_files[@]}"; do
        if [ -f "$OCMOD_DIR/$php_file" ]; then
            php -l "$OCMOD_DIR/$php_file" > /dev/null 2>&1
            if [ $? -eq 0 ]; then
                print_status "green" "PHP syntax OK: $php_file"
            else
                print_status "red" "PHP syntax error in: $php_file"
                exit 1
            fi
        fi
    done
else
    print_status "yellow" "PHP not available for syntax checking - skipping"
fi

# Check file permissions
echo
echo "Checking file permissions..."
find "$OCMOD_DIR" -type f -not -readable | while read file; do
    print_status "yellow" "File not readable: $file"
done

# Validate critical directories
echo
echo "Validating directory structure..."

required_dirs=(
    "upload/admin/controller/extension/module"
    "upload/admin/view/template/extension/module"
    "upload/admin/language/en-gb/extension/module"
    "upload/admin/language/tr-tr/extension/module"
    "upload/system/library/meschain"
    "upload/system/library/entegrator"
    "upload/install"
)

for dir in "${required_dirs[@]}"; do
    if [ -d "$OCMOD_DIR/$dir" ]; then
        file_count=$(find "$OCMOD_DIR/$dir" -type f | wc -l | xargs)
        print_status "green" "Directory OK: $dir ($file_count files)"
    else
        print_status "red" "Missing directory: $dir"
        exit 1
    fi
done

# Create the OCMOD ZIP package
echo
echo "Creating OCMOD package..."

cd "$OCMOD_DIR" || exit 1

# Remove any existing package
if [ -f "$PROJECT_DIR/$PACKAGE_NAME" ]; then
    rm "$PROJECT_DIR/$PACKAGE_NAME"
    print_status "yellow" "Removed existing package"
fi

# Create the ZIP file
zip -r "$PROJECT_DIR/$PACKAGE_NAME" . -x "*.DS_Store" "*.git*" "*.svn*" "Thumbs.db"

if [ $? -eq 0 ]; then
    print_status "green" "OCMOD package created successfully!"
    
    # Get package size
    package_size=$(du -h "$PROJECT_DIR/$PACKAGE_NAME" | cut -f1)
    print_status "blue" "Package location: $PROJECT_DIR/$PACKAGE_NAME"
    print_status "blue" "Package size: $package_size"
    
    # Test ZIP integrity
    echo
    echo "Testing package integrity..."
    zip -T "$PROJECT_DIR/$PACKAGE_NAME" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        print_status "green" "ZIP package integrity OK"
    else
        print_status "red" "ZIP package integrity check failed"
        exit 1
    fi
    
    # List package contents
    echo
    echo "Package contents summary:"
    zip_files=$(unzip -l "$PROJECT_DIR/$PACKAGE_NAME" | grep -c "\.php\|\.xml\|\.tpl\|\.js\|\.css\|\.sql")
    print_status "blue" "Total files in package: $zip_files"
    
else
    print_status "red" "Failed to create OCMOD package"
    exit 1
fi

# Final validation summary
echo
echo "==================================================="
echo "OCMOD Package Validation Summary"
echo "==================================================="
print_status "green" "All validations passed successfully!"
print_status "blue" "Package: $PACKAGE_NAME"
print_status "blue" "Ready for installation on OpenCart 3.0.4.0+"
echo
echo "Installation Instructions:"
echo "1. Login to OpenCart Admin Panel"
echo "2. Go to Extensions > Installer"
echo "3. Upload the $PACKAGE_NAME file"
echo "4. Go to Extensions > Modifications"
echo "5. Click 'Refresh' to activate the modification"
echo "6. Go to Extensions > Extensions > Modules"
echo "7. Find 'MesChain Sync' and install/configure"
echo "==================================================="
