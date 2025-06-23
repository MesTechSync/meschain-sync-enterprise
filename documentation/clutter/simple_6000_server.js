const express = require('express');
const app = express();
const PORT = 6000;

// Basit ana dashboard
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>Port 6000 - Ana Dashboard</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white; 
                text-align: center; 
                padding: 50px; 
            }
            .container { 
                background: rgba(255,255,255,0.1); 
                padding: 40px; 
                border-radius: 15px; 
                max-width: 600px; 
                margin: 0 auto; 
            }
            h1 { font-size: 2.5em; margin-bottom: 20px; }
            .status { font-size: 1.5em; color: #4ade80; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📊 MesChain-Sync Ana Dashboard</h1>
            <div class="status">✅ Port 6000 Aktif</div>
            <p>Ana sistem dashboard'u çalışıyor</p>
            <p>Sistem saati: ${new Date().toLocaleString('tr-TR')}</p>
        </div>
    </body>
    </html>
    `);
});

app.listen(PORT, () => {
    console.log(`✅ Port ${PORT} Ana Dashboard başlatıldı`);
    console.log(`🌐 http://localhost:${PORT}`);
});
