/**
 * Hepsiburada Admin Panel Server
 * Port: 3007
 * Hepsiburada Integration System
 * Date: 17 Haziran 2025
 */

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3007;

// Middleware
app.use(cors());
app.use(express.json());

// Main Hepsiburada Admin Panel Route
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hepsiburada - Admin Panel</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #ff6600, #e55a00); color: white; }
            .container { max-width: 800px; margin: 0 auto; text-align: center; }
            .logo { font-size: 2.5em; margin-bottom: 20px; }
            .card { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0; }
            .btn { background: #ff6600; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">🛍️ Hepsiburada</div>
            <h1>Hepsiburada Marketplace Admin Panel</h1>
            <div class="card">
                <h3>🚀 Hepsiburada Entegrasyon Sistemi</h3>
                <p>Marketplace yönetimi ve sipariş takibi</p>
                <p><strong>Status:</strong> <span style="color: #4ade80;">Aktif</span></p>
                <p><strong>Günlük Sipariş:</strong> 189</p>
                <p><strong>Ürün Sayısı:</strong> 1,965</p>
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
        service: 'Hepsiburada Integration Panel',
        port: PORT,
        version: '1.0',
        timestamp: new Date().toISOString()
    });
});

app.listen(PORT, () => {
    console.log(`Hepsiburada Admin Panel running on http://localhost:${PORT}`);
});

module.exports = app;
