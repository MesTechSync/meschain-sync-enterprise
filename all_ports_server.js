const http = require('http');

// Port tanımları ve renkleri - 60xx serisine taşındı
const ports = {
    6000: { name: 'Dashboard', color: '#667eea', description: '📊 Ana Dashboard Sistemi' },
    6001: { name: 'Frontend Components', color: '#f093fb', description: '🎨 Frontend Bileşenleri' },
    6002: { name: 'Super Admin', color: '#4facfe', description: '👑 Süper Admin Paneli' },
    6003: { name: 'Marketplace Hub', color: '#43e97b', description: '🏪 Marketplace Merkezi' },
    6006: { name: 'Order Management', color: '#fa709a', description: '📋 Sipariş Yönetimi' },
    6007: { name: 'Inventory Management', color: '#ffecd2', description: '📦 Stok Yönetimi' },
    6009: { name: 'Cross Marketplace Admin', color: '#a8edea', description: '🔄 Çapraz Market Yönetimi' },
    6010: { name: 'Hepsiburada Specialist', color: '#fed6e3', description: '🛍️ Hepsiburada Uzmanı' },
    6011: { name: 'Amazon Seller', color: '#ff9a9e', description: '📦 Amazon Satıcı Sistemi' },
    6012: { name: 'Trendyol Seller', color: '#fecfef', description: '🛒 Trendyol Satıcı Sistemi' },
    6013: { name: 'GittiGidiyor Manager', color: '#ffecd2', description: '🎯 GittiGidiyor Yöneticisi' },
    6014: { name: 'N11 Management', color: '#c471f5', description: '🏢 N11 Yönetim Sistemi' },
    6015: { name: 'eBay Integration', color: '#12c2e9', description: '🌐 eBay Entegrasyonu' },
    6016: { name: 'Trendyol Advanced Testing', color: '#f64f59', description: '🧪 Trendyol İleri Testler' }
};

// Her port için server oluştur
Object.entries(ports).forEach(([port, config]) => {
    const server = http.createServer((req, res) => {
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(`
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="utf-8">
            <title>Port ${port} - ${config.name}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Arial', sans-serif; 
                    background: linear-gradient(135deg, ${config.color} 0%, ${config.color}99 100%); 
                    min-height: 100vh; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center;
                    color: white;
                }
                .container { 
                    max-width: 900px; 
                    width: 90%; 
                    text-align: center;
                }
                h1 { 
                    font-size: 3.5em; 
                    margin-bottom: 20px; 
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    animation: glow 2s ease-in-out infinite alternate;
                }
                @keyframes glow {
                    from { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
                    to { text-shadow: 2px 2px 20px rgba(255,255,255,0.3); }
                }
                .card { 
                    background: rgba(255,255,255,0.15); 
                    padding: 40px; 
                    border-radius: 20px; 
                    margin: 30px 0; 
                    backdrop-filter: blur(15px);
                    border: 1px solid rgba(255,255,255,0.2);
                    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                }
                .status { 
                    font-size: 1.5em; 
                    margin: 15px 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                }
                .pulse { 
                    width: 10px; 
                    height: 10px; 
                    background: #4ade80; 
                    border-radius: 50%; 
                    animation: pulse 1s infinite;
                }
                @keyframes pulse {
                    0% { opacity: 1; transform: scale(1); }
                    50% { opacity: 0.5; transform: scale(1.2); }
                    100% { opacity: 1; transform: scale(1); }
                }
                .info { font-size: 1.2em; margin: 10px 0; }
                .port-badge { 
                    display: inline-block; 
                    background: rgba(255,255,255,0.2); 
                    padding: 10px 20px; 
                    border-radius: 25px; 
                    font-weight: bold; 
                    font-size: 1.3em;
                    margin: 10px;
                }
                .links {
                    margin-top: 30px;
                }
                .link-button {
                    display: inline-block;
                    background: rgba(255,255,255,0.2);
                    color: white;
                    text-decoration: none;
                    padding: 15px 30px;
                    border-radius: 25px;
                    margin: 10px;
                    transition: all 0.3s ease;
                    border: 1px solid rgba(255,255,255,0.3);
                }
                .link-button:hover {
                    background: rgba(255,255,255,0.3);
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>${config.description}</h1>
                <div class="card">
                    <div class="port-badge">PORT ${port}</div>
                    <h2>${config.name}</h2>
                    <div class="status">
                        <div class="pulse"></div>
                        Server Aktif & Çalışıyor
                    </div>
                    <div class="info">🕐 Başlatma: ${new Date().toLocaleString('tr-TR')}</div>
                    <div class="info">⚡ Uptime: ${Math.floor(process.uptime())} saniye</div>
                    <div class="info">🌐 URL: http://localhost:${port}</div>
                    
                    <div class="links">
                        <a href="http://localhost:6000" class="link-button">🏠 Dashboard</a>
                        <a href="http://localhost:6001" class="link-button">🎨 Components</a>
                        <a href="http://localhost:6002" class="link-button">👑 Admin</a>
                        <a href="http://localhost:6003" class="link-button">🏪 Hub</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        `);
    });
    
    server.listen(parseInt(port), () => {
        console.log(`🚀 Port ${port} - ${config.name} BAŞLADI! 🌟`);
        console.log(`🌐 http://localhost:${port}`);
        console.log(`📝 ${config.description}`);
        console.log('─'.repeat(50));
    });
});

console.log(`
🎯 ═══════════════════════════════════════════════════════════════
🔥              MESCHAIN-SYNC TÜM PORTLAR AKTİF!               🔥
🎯 ═══════════════════════════════════════════════════════════════

🚀 Aktif 6000 Serisi Sistem Portları:
   • 6000 - Dashboard
   • 6001 - Frontend Components  
   • 6002 - Super Admin
   • 6003 - Marketplace Hub
   • 6006 - Order Management
   • 6007 - Inventory Management
   • 6009 - Cross Marketplace Admin
   • 6010 - Hepsiburada Specialist
   • 6011 - Amazon Seller
   • 6012 - Trendyol Seller
   • 6013 - GittiGidiyor Manager
   • 6014 - N11 Management
   • 6015 - eBay Integration
   • 6016 - Trendyol Advanced Testing

💡 Tüm sistem portlarını görüntülemek için tarayıcınızda http://localhost:6000-6016 adreslerini ziyaret edin!
🛑 Durdurmak için Ctrl+C tuşlayın.

🎯 ═══════════════════════════════════════════════════════════════
`); 