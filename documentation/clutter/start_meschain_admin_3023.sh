#!/bin/bash

# Port 3023 için MesChain Super Admin Panel Server
cd /Users/mezbjen/Desktop/meschain-sync-enterprise-1

echo "🚀 Starting MesChain Super Admin Panel on Port 3023..."

node -e "
const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = 3023;

// Middleware
app.use(cors());
app.use(express.static(__dirname));
app.use(express.json());

// Ana sayfa - meschain_sync_super_admin.html dosyasını serve et
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'meschain_sync_super_admin.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send('Panel dosyası bulunamadı: meschain_sync_super_admin.html');
    }
});

// meschain_sync_super_admin.html'i doğrudan serve et
app.get('/meschain_sync_super_admin.html', (req, res) => {
    const filePath = path.join(__dirname, 'meschain_sync_super_admin.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send('Panel dosyası bulunamadı: meschain_sync_super_admin.html');
    }
});

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        port: PORT, 
        panel: 'MesChain Sync Super Admin Panel',
        version: '4.1.0',
        timestamp: new Date().toISOString() 
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🔗 MesChain Sync Super Admin Panel STARTED!');
    console.log('📡 URL: http://localhost:' + PORT);
    console.log('📄 Panel: meschain_sync_super_admin.html');
    console.log('🎯 Version: 4.1.0 Enterprise');
    console.log('🕐 Started: ' + new Date().toLocaleString('tr-TR'));
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('✅ Super Admin Panel başarıyla çalışıyor!');
    console.log('🌐 Panel URL: http://localhost:' + PORT + '/meschain_sync_super_admin.html');
    console.log('🌐 Ana URL: http://localhost:' + PORT);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 MesChain Super Admin Panel (Port 3023) kapatılıyor...');
    console.log('👋 Güle güle!');
    process.exit(0);
});
"
