# SSL/HTTPS Deployment Status Checker
# MesChain-Sync Enterprise - June 8, 2025

Write-Host "🔍 SSL/HTTPS Deployment Status Check" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Yellow
Write-Host ""

$httpsServices = @(
    @{Port = 4005; Name = "Performance Analytics HTTPS"; Type = "analytics"}
    @{Port = 4006; Name = "Backup Management HTTPS"; Type = "backup"}
    @{Port = 4007; Name = "Legal Compliance HTTPS"; Type = "legal"}
    @{Port = 4012; Name = "Trendyol Seller HTTPS"; Type = "marketplace"}
    @{Port = 4014; Name = "N11 Management HTTPS"; Type = "marketplace"}
)

Write-Host "🔐 Checking HTTPS Service Ports..." -ForegroundColor Cyan
Write-Host ""

$activeServices = 0
$totalServices = $httpsServices.Count

foreach ($service in $httpsServices) {
    $port = $service.Port
    $name = $service.Name
    
    try {
        $connection = Test-NetConnection -ComputerName "localhost" -Port $port -InformationLevel Quiet -ErrorAction SilentlyContinue
        
        if ($connection) {
            Write-Host "✅ Port $port`: $name - ACTIVE" -ForegroundColor Green
            $activeServices++
            
            # Test HTTPS endpoint
            try {
                $response = Invoke-WebRequest -Uri "https://localhost:$port/health" -UseBasicParsing -SkipCertificateCheck -TimeoutSec 5 -ErrorAction SilentlyContinue
                if ($response.StatusCode -eq 200) {
                    Write-Host "   🔗 HTTPS Health Check: PASSED" -ForegroundColor Green
                }
            }
            catch {
                Write-Host "   ⚠️  HTTPS Health Check: Needs attention" -ForegroundColor Yellow
            }
        }
        else {
            Write-Host "❌ Port $port`: $name - NOT ACTIVE" -ForegroundColor Red
        }
    }
    catch {
        Write-Host "❌ Port $port`: $name - ERROR: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "📊 SSL/HTTPS Deployment Summary" -ForegroundColor Yellow
Write-Host "===============================" -ForegroundColor Yellow
Write-Host "✅ Active Services: $activeServices/$totalServices" -ForegroundColor Green
Write-Host "❌ Inactive Services: $($totalServices - $activeServices)/$totalServices" -ForegroundColor Red
Write-Host "📈 Success Rate: $([math]::Round(($activeServices / $totalServices) * 100, 1))%" -ForegroundColor Cyan

if ($activeServices -eq 0) {
    Write-Host ""
    Write-Host "🔄 Starting SSL Deployment..." -ForegroundColor Yellow
    Write-Host ""
    
    # Start the SSL deployment
    Set-Location "c:\Users\musta\Desktop\MUSTI_MESCHAIN_WORKSPACE\meschain-sync-enterprise"
    
    # Start the deployment in background
    Start-Process -FilePath "node" -ArgumentList "simple_ssl_deployment_june8_2025.js" -WindowStyle Minimized
    
    Write-Host "🚀 SSL deployment started in background" -ForegroundColor Green
    Write-Host ""
    Write-Host "⏳ Waiting 10 seconds for services to initialize..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
    
    Write-Host ""
    Write-Host "🔍 Re-checking service status..." -ForegroundColor Cyan
    
    # Re-check services
    $activeAfterDeploy = 0
    foreach ($service in $httpsServices) {
        $port = $service.Port
        $name = $service.Name
        
        $connection = Test-NetConnection -ComputerName "localhost" -Port $port -InformationLevel Quiet -ErrorAction SilentlyContinue
        
        if ($connection) {
            Write-Host "✅ Port $port`: $name - NOW ACTIVE" -ForegroundColor Green
            $activeAfterDeploy++
        }
        else {
            Write-Host "❌ Port $port`: $name - Still not active" -ForegroundColor Red
        }
    }
    
    Write-Host ""
    Write-Host "📊 Post-Deployment Status" -ForegroundColor Yellow
    Write-Host "✅ Services Now Active: $activeAfterDeploy/$totalServices" -ForegroundColor Green
    Write-Host "📈 New Success Rate: $([math]::Round(($activeAfterDeploy / $totalServices) * 100, 1))%" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "🔗 Access URLs (if services are active):" -ForegroundColor Cyan
foreach ($service in $httpsServices) {
    Write-Host "   https://localhost:$($service.Port) - $($service.Name)" -ForegroundColor White
}

Write-Host ""
Write-Host "🎯 Next Steps:" -ForegroundColor Yellow
Write-Host "1. ✅ Review active services above" -ForegroundColor White
Write-Host "2. 🔍 Test HTTPS endpoints in browser" -ForegroundColor White
Write-Host "3. 📊 Monitor performance metrics" -ForegroundColor White
Write-Host "4. 🛡️  Verify security configurations" -ForegroundColor White

Write-Host ""
Write-Host "🌟 SSL/HTTPS Status Check Complete!" -ForegroundColor Green
