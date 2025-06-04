@echo off
echo ================================
echo Trendyol Advanced Deployment Validation
echo ================================
echo.

echo 📁 Checking file deployment status...
if exist "upload\admin\controller\extension\module\trendyol_advanced.php" (
    echo ✅ Controller deployed: upload\admin\controller\extension\module\trendyol_advanced.php
    for %%A in ("upload\admin\controller\extension\module\trendyol_advanced.php") do echo    Size: %%~zA bytes
) else (
    echo ❌ Controller NOT found
)

if exist "upload\admin\model\extension\module\trendyol_advanced.php" (
    echo ✅ Model deployed: upload\admin\model\extension\module\trendyol_advanced.php
    for %%A in ("upload\admin\model\extension\module\trendyol_advanced.php") do echo    Size: %%~zA bytes
) else (
    echo ❌ Model NOT found
)

if exist "upload\admin\view\template\extension\module\trendyol_advanced.twig" (
    echo ✅ Template deployed: upload\admin\view\template\extension\module\trendyol_advanced.twig
    for %%A in ("upload\admin\view\template\extension\module\trendyol_advanced.twig") do echo    Size: %%~zA bytes
) else (
    echo ❌ Template NOT found
)

if exist "upload\admin\view\javascript\meschain\trendyol_advanced.js" (
    echo ✅ JavaScript deployed: upload\admin\view\javascript\meschain\trendyol_advanced.js
    for %%A in ("upload\admin\view\javascript\meschain\trendyol_advanced.js") do echo    Size: %%~zA bytes
) else (
    echo ❌ JavaScript NOT found
)

if exist "upload\admin\language\en-gb\extension\module\trendyol_advanced.php" (
    echo ✅ English language file deployed
) else (
    echo ❌ English language file NOT found
)

if exist "upload\admin\language\tr-tr\extension\module\trendyol_advanced.php" (
    echo ✅ Turkish language file deployed
) else (
    echo ❌ Turkish language file NOT found
)

echo.
echo 📋 Installation Scripts:
if exist "trendyol_advanced_install.php" (
    echo ✅ PHP Installation script available
)
if exist "trendyol_advanced_web_installer.html" (
    echo ✅ Web installer available
)
if exist "test_trendyol_advanced.php" (
    echo ✅ Test suite available
)

echo.
echo 🎯 Next Steps:
echo 1. Run database installation script
echo 2. Test AJAX endpoints
echo 3. Verify dashboard functionality
echo 4. Connect to Trendyol API

echo.
echo ================================
echo Validation Complete
echo ================================
pause
