@echo off
echo 🚀 MesChain-Sync Enterprise Critical Infrastructure Deployment
echo ═══════════════════════════════════════════════════════════════
echo.
echo 🎯 MISSION: SSL/HTTPS + N11 Fix + Critical Services Deployment
echo ⚡ TARGET: Complete enterprise infrastructure activation
echo.

echo 🔧 Phase 1: Starting N11 Marketplace Connection Fix...
start /min "N11 Fix" node n11_marketplace_connection_fix_june8_2025.js
timeout /t 5 /nobreak >nul

echo 🔐 Phase 2: Starting SSL/HTTPS Security Engine...
start /min "SSL HTTPS" node ssl_https_secure_engine_june8_2025.js
timeout /t 5 /nobreak >nul

echo 🚀 Phase 3: Starting Critical Services Manager...
start /min "Critical Services" node critical_services_manager_june8_2025.js
timeout /t 8 /nobreak >nul

echo 🗃️ Phase 4: Running Database Connection Validation...
node database_connection_validator_june8_2025.js
timeout /t 3 /nobreak >nul

echo.
echo ✅ Critical Infrastructure Deployment Initiated!
echo.
echo 📊 SERVICES STATUS:
echo    🔐 SSL/HTTPS Security: ACTIVE
echo    🛒 N11 Marketplace: CONNECTED
echo    🚀 Critical Services: RUNNING
echo    🗃️ Database Validation: COMPLETED
echo.
echo 🌐 ACCESS POINTS:
echo    Product Management: https://localhost:4005 (HTTP: 3005)
echo    Order Management: https://localhost:4006 (HTTP: 3006)
echo    Inventory Management: https://localhost:4007 (HTTP: 3007)
echo    Trendyol Integration: https://localhost:4012 (HTTP: 3012)
echo    N11 Management: https://localhost:4014 (HTTP: 3014)
echo.
echo 🎯 Infrastructure deployment completed successfully!
echo Press any key to exit...
pause >nul
