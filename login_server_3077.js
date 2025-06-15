const express = require('express');
const path = require('path');

const app = express();
const PORT = 3077;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname));

// Ana login sayfası
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MesChain-Sync Enterprise v4.5 - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                background: rgba(255, 255, 255, 0.95);
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(10px);
                width: 100%;
                max-width: 400px;
            }
            .logo {
                text-align: center;
                margin-bottom: 30px;
            }
            .logo h1 {
                color: #333;
                font-size: 24px;
                margin-bottom: 10px;
            }
            .logo p {
                color: #666;
                font-size: 14px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 500;
            }
            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 12px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input[type="text"]:focus, input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            .login-btn {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }
            .info {
                margin-top: 20px;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <h1>🔐 MesChain-Sync</h1>
                <p>Enterprise v4.5 - Güvenli Giriş</p>
            </div>
            
            <form id="loginForm" action="/login" method="POST">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">
                    🚀 Giriş Yap
                </button>
            </form>
            
            <div class="info">
                <p>Port: 3077 | Status: Active</p>
                <p>Demo: admin / 123456</p>
            </div>
        </div>
        
        <script>
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                // Demo login
                if (username === 'admin' && password === '123456') {
                    alert('✅ Giriş Başarılı! Ana dashboard\'a yönlendiriliyorsunuz...');
                    window.location.href = 'http://localhost:3000/';
                } else {
                    alert('❌ Hatalı kullanıcı adı veya şifre!');
                }
            });
        </script>
    </body>
    </html>
    `);
});

// Login işlemi
app.post('/login', (req, res) => {
    const { username, password } = req.body;
    
    // Demo login kontrolü
    if (username === 'admin' && password === '123456') {
        res.json({ success: true, message: 'Giriş başarılı!', redirect: 'http://localhost:3000/' });
    } else {
        res.json({ success: false, message: 'Hatalı kullanıcı adı veya şifre!' });
    }
});

// Status endpoint
app.get('/status', (req, res) => {
    res.json({
        service: 'MesChain-Sync Login System',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: '🔐 Güvenli Giriş Sistemi'
    });
});

app.listen(PORT, () => {
    console.log(`
🔐 ═══════════════════════════════════════════════════════════════
🚀           MESCHAIN-SYNC LOGIN SİSTEMİ AKTİF!              🚀
🔐 ═══════════════════════════════════════════════════════════════

📊 Login URL: http://localhost:${PORT}/
🔑 Demo Giriş: admin / 123456
📈 Status API: http://localhost:${PORT}/status
✨ Başarılı girişte ana dashboard'a yönlendirme

🔐 ═══════════════════════════════════════════════════════════════
    `);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Login Server kapatılıyor...');
    process.exit(0);
});
