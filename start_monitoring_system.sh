#!/bin/bash

echo "🚀 Starting MesChain OpenCart Enterprise Monitoring System..."

# Kill existing monitoring process
pkill -f "comprehensive_monitoring_system" 2>/dev/null || echo "No existing process found"

# Wait a moment
sleep 2

# Start new monitoring system
cd /Users/mezbjen/Desktop/meschain-sync-enterprise-1
node comprehensive_monitoring_system.js &

echo "✅ Monitoring system started on port 4500"
echo "📊 Dashboard: http://localhost:4500/dashboard"
echo "🔗 Health: http://localhost:4500/health"
