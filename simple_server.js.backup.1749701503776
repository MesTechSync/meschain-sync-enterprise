const http = require('http');
const fs = require('fs');
const path = require('path');

// Port 3004'te basit server
const server3004 = http.createServer((req, res) => {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(`
    <html>
        <head>
            <title>Port 3004 - Performance Dashboard</title>
            <style>
                body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin: 0; padding: 20px; color: white; }
                .container { max-width: 800px; margin: 0 auto; }
                h1 { text-align: center; font-size: 2.5em; margin-bottom: 30px; }
                .card { background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; margin: 20px 0; backdrop-filter: blur(10px); }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚀 Port 3004 - Performance Dashboard</h1>
                <div class="card">
                    <h2>Sistem Durumu</h2>
                    <p>✅ Server Aktif</p>
                    <p>📊 Performance monitoring aktif</p>
                    <p>🕐 Başlatma zamanı: ${new Date().toLocaleString()}</p>
                </div>
            </div>
        </body>
    </html>
    `);
});

// Port 3005'te basit server
const server3005 = http.createServer((req, res) => {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(`
    <html>
        <head>
            <title>Port 3005 - Product Management</title>
            <style>
                body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin: 0; padding: 20px; color: white; }
                .container { max-width: 800px; margin: 0 auto; }
                h1 { text-align: center; font-size: 2.5em; margin-bottom: 30px; }
                .card { background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; margin: 20px 0; backdrop-filter: blur(10px); }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>📦 Port 3005 - Product Management</h1>
                <div class="card">
                    <h2>Ürün Yönetimi</h2>
                    <p>✅ Server Aktif</p>
                    <p>🛍️ Ürün yönetim sistemi çalışıyor</p>
                    <p>🕐 Başlatma zamanı: ${new Date().toLocaleString()}</p>
                </div>
            </div>
        </body>
    </html>
    `);
});

// Server'ları başlat
server3004.listen(3004, () => {
    console.log('🚀 Port 3004 - Performance Dashboard Server BAŞLADI!');
    console.log('🌐 http://localhost:3004');
});

server3005.listen(3005, () => {
    console.log('📦 Port 3005 - Product Management Server BAŞLADI!');
    console.log('🌐 http://localhost:3005');
});

console.log('🎯 Servers başlatıldı! Ctrl+C ile durdurun.'); 