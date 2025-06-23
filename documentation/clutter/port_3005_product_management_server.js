const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const cors = require('cors');
const path = require('path');

const app = express();
const server = http.createServer(app);
const PORT = 3005;

// Create WebSocket server
const wss = new WebSocket.Server({ 
    server,
    path: '/dashboard'
});

// WebSocket connection handling
wss.on('connection', (ws, req) => {
    console.log('🔌 Dashboard WebSocket connected from:', req.socket.remoteAddress);
    
    // Send welcome message
    ws.send(JSON.stringify({
        type: 'connection',
        status: 'connected',
        message: 'Connected to Product Management Suite Dashboard',
        timestamp: new Date().toISOString()
    }));
    
    // Handle incoming messages
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            console.log('📨 Dashboard message:', data);
            
            // Echo back or process dashboard commands
            ws.send(JSON.stringify({
                type: 'response',
                data: data,
                timestamp: new Date().toISOString()
            }));
        } catch (error) {
            console.error('❌ WebSocket message error:', error);
        }
    });
    
    // Handle connection close
    ws.on('close', () => {
        console.log('🔌 Dashboard WebSocket disconnected');
    });
    
    // Send periodic updates
    const updateInterval = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({
                type: 'dashboard_update',
                data: {
                    products: Math.floor(Math.random() * 1000) + 500,
                    orders: Math.floor(Math.random() * 100) + 50,
                    inventory: Math.floor(Math.random() * 5000) + 1000,
                    performance: (Math.random() * 10 + 90).toFixed(1)
                },
                timestamp: new Date().toISOString()
            }));
        } else {
            clearInterval(updateInterval);
        }
    }, 5000);
});

console.log('🚀 WebSocket server initialized on /dashboard endpoint');

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Advanced Rate Limiting System
const AdvancedRateLimitingSystem = require('./api_rate_limiting_system');
const rateLimiting = new AdvancedRateLimitingSystem();

// Apply rate limiting middleware (before authentication)
rateLimiting.setupMiddleware(app);

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'Product Management Suite Server',
    serviceType: 'product_management',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'product_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['products', 'inventory', 'pricing'], 'product_manager': ['products', 'inventory']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    res.send(`<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant Bot - MesChain-Sync</title>
    
    <!-- PWA Integration -->
    <link rel="manifest" href="/CursorDev/PWA/manifest.json">
    <meta name="theme-color" content="#10B981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MesChain AI Assistant">
    <link rel="apple-touch-icon" href="/assets/images/ai-assistant-icon-152.png">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .assistant-theme { 
            background: linear-gradient(135deg, #10B981 0%, #059669 50%, #047857 100%);
            color: white;
        }
        .assistant-card { 
            border-left: 5px solid #10B981;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .assistant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3);
        }
        .assistant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10B981, #059669, #047857, #065F46, #10B981);
            animation: assistantGradient 4s ease-in-out infinite;
        }
        @keyframes assistantGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .voice-icon {
            font-size: 2.5rem;
            color: #10B981;
            margin-bottom: 15px;
            animation: voicePulse 2s ease-in-out infinite;
        }
        @keyframes voicePulse {
            0%, 100% { transform: scale(1); color: #10B981; }
            50% { transform: scale(1.1); color: #059669; }
        }
        .ai-avatar {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #10B981, #3B82F6);
            animation: avatarFloat 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        @keyframes avatarFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(180deg); }
        }
        .chat-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: linear-gradient(45deg, #10B981, #059669);
            color: white;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: chatAvatarPulse 2s ease-in-out infinite;
        }
        @keyframes chatAvatarPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .message.user {
            flex-direction: row-reverse;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 20px;
            position: relative;
            animation: messageSlide 0.3s ease-out;
        }
        @keyframes messageSlide {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.user .message-bubble {
            background: linear-gradient(45deg, #10B981, #059669);
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message.assistant .message-bubble {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .message.user .message-avatar {
            background: #10B981;
            color: white;
        }
        .message.assistant .message-avatar {
            background: linear-gradient(45deg, #10B981, #3B82F6);
            color: white;
            animation: assistantAvatar 2s ease-in-out infinite;
        }
        @keyframes assistantAvatar {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(360deg); }
        }
        .assistant-badge {
            background: linear-gradient(45deg, #10B981, #059669);
            color: white;
            padding: 6px 14px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        .metric-display {
            font-weight: bold;
            color: #10B981;
            font-size: 1.2rem;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-online {
            background: #10B981;
            animation: statusPulse 2s ease-in-out infinite;
        }
        .status-speaking {
            background: #3B82F6;
            animation: statusPulse 1s ease-in-out infinite;
        }
        .status-listening {
            background: #F59E0B;
            animation: statusPulse 1.5s ease-in-out infinite;
        }
        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.2); }
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .quick-action {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .quick-action:hover {
            border-color: #10B981;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .quick-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
            transition: left 0.5s ease;
        }
        .quick-action:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg assistant-theme shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-robot me-2"></i>
                AI Assistant Bot
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <span class="status-indicator status-online"></span>
                    AI Status: <span id="assistant-status-text">Aktif ve dinliyor...</span>
                    <span class="badge bg-light text-dark ms-2" id="voice-status">🎤 Hazır</span>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Assistant Status -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card assistant-card h-100">
                    <div class="ai-avatar">🤖</div>
                    <div class="card-body text-center">
                        <i class="fas fa-comments voice-icon"></i>
                        <h5 class="card-title">Konuşmalar</h5>
                        <h2 id="total-conversations" class="display-6">247</h2>
                        <div class="mt-2">
                            <small>Bugün: <span id="today-conversations" class="metric-display">18</span></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card assistant-card h-100">
                    <div class="ai-avatar">🎤</div>
                    <div class="card-body text-center">
                        <i class="fas fa-microphone voice-icon"></i>
                        <h5 class="card-title">Sesli Komutlar</h5>
                        <h2 id="voice-commands" class="display-6">156</h2>
                        <div class="mt-2">
                            <small>Accuracy: <span id="voice-accuracy" class="metric-display">94.7%</span></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card assistant-card h-100">
                    <div class="ai-avatar">🧠</div>
                    <div class="card-body text-center">
                        <i class="fas fa-brain voice-icon"></i>
                        <h5 class="card-title">AI Yanıtları</h5>
                        <h2 id="ai-responses" class="display-6">312</h2>
                        <div class="mt-2">
                            <small>Başarı: <span id="response-success" class="metric-display">97.1%</span></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card assistant-card h-100">
                    <div class="ai-avatar">⚡</div>
                    <div class="card-body text-center">
                        <i class="fas fa-bolt voice-icon"></i>
                        <h5 class="card-title">Yanıt Süresi</h5>
                        <h2 id="response-time" class="display-6">0.8s</h2>
                        <div class="mt-2">
                            <small>Ortalama: <span id="avg-response-time" class="metric-display">1.2s</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Interface -->
        <div class="row">
            <div class="col-lg-8">
                <div class="chat-container">
                    <div class="chat-header">
                        <div class="chat-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">MesChain AI Assistant</h5>
                            <small>Marketplace uzmanınız - 7 platform desteği</small>
                        </div>
                        <div class="ms-auto">
                            <span class="assistant-badge">🧠 AI Aktif</span>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <!-- Mesajlar buraya dinamik olarak eklenecek -->
                        <div class="message assistant">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-bubble">
                                Merhaba! Ben MesChain AI Assistant'ınızım. Size nasıl yardımcı olabilirim? 
                                Sesli komutlar veya yazarak sorularınızı sorabilirsiniz. 🤖
                                <br><br>
                                Kullanıcı: <strong>${req.user.username}</strong> (${req.user.role})
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card assistant-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Hızlı Komutlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <div class="quick-action">
                                <i class="fas fa-tags text-primary mb-2"></i>
                                <div class="fw-bold">Fiyatları Kontrol Et</div>
                                <small class="text-muted">AI fiyat önerilerini göster</small>
                            </div>
                            <div class="quick-action">
                                <i class="fas fa-shopping-cart text-success mb-2"></i>
                                <div class="fw-bold">Siparişleri Görüntüle</div>
                                <small class="text-muted">Son siparişleri listele</small>
                            </div>
                            <div class="quick-action">
                                <i class="fas fa-boxes text-warning mb-2"></i>
                                <div class="fw-bold">Stok Durumu</div>
                                <small class="text-muted">Envanter bilgilerini getir</small>
                            </div>
                            <div class="quick-action">
                                <i class="fas fa-chart-bar text-info mb-2"></i>
                                <div class="fw-bold">Analytics Raporu</div>
                                <small class="text-muted">Performans verilerini analiz et</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Status -->
                <div class="card assistant-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-robot me-2"></i>
                            AI Durumu
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small>NLP Engine:</small>
                            <strong class="float-end text-success">Aktif</strong>
                        </div>
                        <div class="mb-2">
                            <small>Voice Recognition:</small>
                            <strong class="float-end text-success">Çalışıyor</strong>
                        </div>
                        <div class="mb-2">
                            <small>Speech Synthesis:</small>
                            <strong class="float-end text-success">Hazır</strong>
                        </div>
                        <div class="mb-2">
                            <small>AI Confidence:</small>
                            <strong class="float-end metric-display">95.8%</strong>
                        </div>
                        <div class="mb-2">
                            <small>Response Time:</small>
                            <strong class="float-end">0.8s</strong>
                        </div>
                        <div class="mb-2">
                            <small>Active Sessions:</small>
                            <strong class="float-end">1</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <div class="status-indicator status-online"></div>
                            <span class="fw-bold text-success">AI Assistant Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        console.log('🤖 AI Assistant Bot başlatıldı');
        
        // Live data simulation
        setInterval(() => {
            const conversations = document.getElementById('total-conversations');
            const voiceCommands = document.getElementById('voice-commands');
            const aiResponses = document.getElementById('ai-responses');
            const responseTime = document.getElementById('response-time');
            
            if (conversations) conversations.textContent = (Math.floor(Math.random() * 50) + 200);
            if (voiceCommands) voiceCommands.textContent = (Math.floor(Math.random() * 30) + 140);
            if (aiResponses) aiResponses.textContent = (Math.floor(Math.random() * 40) + 290);
            if (responseTime) responseTime.textContent = (Math.random() * 0.5 + 0.5).toFixed(1) + 's';
        }, 5000);
    </script>
</body>
</html>`);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Product Management Suite Server',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        user: req.user
    });
});

// Health check endpoint (no auth required)
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'Product Management Suite Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server with WebSocket support
server.listen(PORT, () => {
    console.log(`🚀 Product Management Suite Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Multi-marketplace Product Management`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔌 WebSocket: ws://localhost:${PORT}/dashboard`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Product Management Suite Server shutting down gracefully...');
    process.exit(0);
});
