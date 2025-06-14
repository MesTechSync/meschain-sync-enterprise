#!/bin/bash

# MesChain Sync Enterprise System Status Report
echo "🚀 MESCHAIN SYNC ENTERPRISE SİSTEM DURUMU"
echo "========================================"
echo "📅 Tarih: $(date)"
echo ""

# Check running services
echo "✅ ÇALIŞAN SERVİSLER:"
echo "===================="

# Port checks
ports=(3000 3006 3007 3035 3036 3037 3038 3040 7071)
active_services=0

for port in "${ports[@]}"; do
    if lsof -i :$port >/dev/null 2>&1; then
        echo "✅ Port $port: AKTİF"
        active_services=$((active_services + 1))
    else
        echo "❌ Port $port: PASİF"
    fi
done

echo ""
echo "📊 SERVİS DURUM ÖZETİ:"
echo "====================="
echo "Toplam port sayısı: ${#ports[@]}"
echo "Aktif servis sayısı: $active_services"
echo "Sistem durumu: $(if [ $active_services -gt 3 ]; then echo "🟢 İYİ"; elif [ $active_services -gt 1 ]; then echo "🟡 ORTA"; else echo "🔴 DİKKAT"; fi)"

# Create JSON status
cat > system_status.json << EOF
{
  "timestamp": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "system_name": "MesChain Sync Enterprise",
  "total_ports": ${#ports[@]},
  "active_services": $active_services,
  "status": "$(if [ $active_services -gt 3 ]; then echo "healthy"; elif [ $active_services -gt 1 ]; then echo "warning"; else echo "critical"; fi)",
  "services": {
    "react_frontend": "$(if lsof -i :3000 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "opencart_module": "$(if lsof -i :3006 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "enhanced_opencart": "$(if lsof -i :3007 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "dropshipping_backend": "$(if lsof -i :3035 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "user_management": "$(if lsof -i :3036 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "realtime_features": "$(if lsof -i :3037 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "marketplace_engine": "$(if lsof -i :3038 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "advanced_marketplace": "$(if lsof -i :3040 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)",
    "azure_functions": "$(if lsof -i :7071 >/dev/null 2>&1; then echo "active"; else echo "inactive"; fi)"
  }
}
EOF

echo ""
echo "📁 JSON raporu system_status.json dosyasına kaydedildi"
echo ""
echo "🌐 ERİŞİM LİNKLERİ:"
echo "=================="
echo "Frontend: http://localhost:3000"
echo "OpenCart Module: http://localhost:3006"
echo "Enhanced OpenCart: http://localhost:3007"
echo ""
echo "✅ Sistem kontrolü tamamlandı!"
