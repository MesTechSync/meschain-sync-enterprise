/**
 * GittiGidiyor Admin Panel Server
 * Port: 3008
 * GittiGidiyor Integration System
 * Date: 17 Haziran 2025
 */

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3008;

// Middleware
app.use(cors());
app.use(express.json());

// Main GittiGidiyor Admin Panel Route
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GittiGidiyor - Admin Panel</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; }
            .container { max-width: 800px; margin: 0 auto; text-align: center; }
            .logo { font-size: 2.5em; margin-bottom: 20px; }
            .card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0; }
            .btn { background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">🚚 GittiGidiyor</div>
            <h1>GittiGidiyor Online Satış Admin Panel</h1>
            <div class="card">
                <h3>🚀 GittiGidiyor Entegrasyon Sistemi</h3>
                <p>Online satış platformu yönetimi</p>
                <p><strong>Status:</strong> <span style="color: #4ade80;">Aktif</span></p>
                <p><strong>Günlük Sipariş:</strong> 94</p>
                <p><strong>Ürün Sayısı:</strong> 1,234</p>
                <button class="btn" onclick="location.reload()">Yenile</button>
            </div>
        </div>
    </body>
    </html>
    `);
});

app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'GittiGidiyor Integration Panel',
        port: PORT,
        version: '1.0',
        timestamp: new Date().toISOString()
    });
});

app.listen(PORT, () => {
    console.log(`GittiGidiyor Admin Panel running on http://localhost:${PORT}`);
});

module.exports = app;
