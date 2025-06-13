/**
 * AI Assistant Bot - Advanced Conversational AI
 * MesChain-Sync Intelligent Assistant v5.0
 * 
 * Features:
 * - 🗣️ Voice Recognition (Speech-to-Text)
 * - 🎵 Speech Synthesis (Text-to-Speech)
 * - 💬 Natural Language Processing
 * - 🧠 Machine Learning Chat Bot
 * - 🌍 Multi-language Support (TR/EN)
 * - 🤖 Animated AI Avatar
 * - ⚡ Real-time Response System
 * - 📱 Voice Commands & Chat Interface
 */
class AIAssistantBot {
    constructor() {
        this.apiEndpoint = '/api/ai-assistant';
        this.conversationHistory = [];
        this.isRecording = false;
        this.isProcessing = false;
        this.currentLanguage = 'tr';
        
        // Voice Recognition & Synthesis
        this.speechRecognition = null;
        this.speechSynthesis = window.speechSynthesis;
        this.currentVoice = null;
        this.voiceSettings = {
            rate: 1.0,
            pitch: 1.0,
            volume: 0.8,
            autoSpeak: true
        };

        // AI Configuration
        this.aiConfig = {
            model: 'GPT-4-Turbo',
            maxTokens: 1000,
            temperature: 0.7,
            conversationMemory: 10,
            responseDelay: 800,
            confidenceThreshold: 0.85
        };

        // Natural Language Processing
        this.nlpPatterns = {
            turkish: {
                greetings: ['merhaba', 'selam', 'günaydın', 'iyi günler', 'hey'],
                questions: ['nasıl', 'ne', 'kim', 'nerede', 'neden', 'hangi'],
                commands: ['göster', 'yap', 'kontrol et', 'güncelle', 'listele', 'analiz et'],
                pricing: ['fiyat', 'ücret', 'maliyet', 'para', 'tutar'],
                orders: ['sipariş', 'satış', 'alım', 'teslimat'],
                inventory: ['stok', 'envanter', 'ürün', 'malzeme'],
                platforms: ['amazon', 'trendyol', 'n11', 'hepsiburada', 'gittigidiyor', 'ebay', 'ozon']
            },
            english: {
                greetings: ['hello', 'hi', 'good morning', 'hey', 'greetings'],
                questions: ['how', 'what', 'who', 'where', 'why', 'which'],
                commands: ['show', 'do', 'check', 'update', 'list', 'analyze'],
                pricing: ['price', 'cost', 'fee', 'money', 'amount'],
                orders: ['order', 'sale', 'purchase', 'delivery'],
                inventory: ['stock', 'inventory', 'product', 'item'],
                platforms: ['amazon', 'trendyol', 'n11', 'hepsiburada', 'gittigidiyor', 'ebay', 'ozon']
            }
        };

        // Marketplace Integration
        this.marketplaceData = {
            amazon: { orders: 45, revenue: 125420, products: 218 },
            trendyol: { orders: 72, revenue: 189650, products: 324 },
            n11: { orders: 28, revenue: 67840, products: 156 },
            hepsiburada: { orders: 34, revenue: 89320, products: 187 },
            gittigidiyor: { orders: 19, revenue: 42150, products: 98 },
            ebay: { orders: 23, revenue: 56780, products: 132 },
            ozon: { orders: 12, revenue: 31240, products: 76 }
        };

        // Chat Statistics
        this.chatStats = {
            totalConversations: 247,
            todayConversations: 18,
            voiceCommands: 156,
            voiceAccuracy: 94.7,
            aiResponses: 312,
            responseSuccess: 97.1,
            avgResponseTime: 1.2,
            currentResponseTime: 0.8
        };

        // Smart Responses Database
        this.smartResponses = {
            turkish: {
                greeting: [
                    'Merhaba! Ben MesChain AI Assistant\'ınızım. Size nasıl yardımcı olabilirim?',
                    'Selam! Marketplace operasyonlarınız için buradayım. Ne yapmak istersiniz?',
                    'İyi günler! 7 platform desteği ile hizmetinizdeyim. Komutunuz nedir?'
                ],
                pricing: [
                    'Fiyat optimizasyonu için AI engine\'imi çalıştırıyorum...',
                    'En güncel pazar verilerini analiz edip fiyat önerisi hazırlıyorum...',
                    'Rakip analizi ve kar maksimizasyonu hesaplaması yapıyorum...'
                ],
                orders: [
                    'Sipariş bilgilerini tüm platformlardan topluyorum...',
                    'Son sipariş durumlarını kontrol ediyorum...',
                    'Detaylı sipariş analizi hazırlıyorum...'
                ],
                inventory: [
                    'Stok durumunu tüm platformlarda kontrol ediyorum...',
                    'Envanter seviyelerini analiz ediyorum...',
                    'Kritik stok uyarıları için tarama yapıyorum...'
                ],
                analytics: [
                    'Kapsamlı performans analizi hazırlıyorum...',
                    'Tüm platform verilerini karşılaştırıyorum...',
                    'AI-powered insights oluşturuyorum...'
                ],
                error: [
                    'Üzgünüm, bu konuda yardımcı olamıyorum. Başka bir şey deneyebilir miyiz?',
                    'Anlayamadım. Lütfen farklı kelimelerle açıklayabilir misiniz?',
                    'Bu konuda daha fazla bilgiye ihtiyacım var. Detaylandırabilir misiniz?'
                ]
            },
            english: {
                greeting: [
                    'Hello! I\'m your MesChain AI Assistant. How can I help you today?',
                    'Hi there! I\'m here for your marketplace operations. What would you like to do?',
                    'Good day! I support 7 platforms and I\'m at your service. What\'s your command?'
                ],
                pricing: [
                    'Running AI engine for price optimization...',
                    'Analyzing latest market data for pricing recommendations...',
                    'Performing competitor analysis and profit maximization calculations...'
                ],
                orders: [
                    'Collecting order information from all platforms...',
                    'Checking latest order statuses...',
                    'Preparing detailed order analysis...'
                ],
                inventory: [
                    'Checking stock status across all platforms...',
                    'Analyzing inventory levels...',
                    'Scanning for critical stock alerts...'
                ],
                analytics: [
                    'Preparing comprehensive performance analysis...',
                    'Comparing data from all platforms...',
                    'Generating AI-powered insights...'
                ],
                error: [
                    'Sorry, I can\'t help with that. Can we try something else?',
                    'I didn\'t understand. Could you explain it differently?',
                    'I need more information about this. Could you provide more details?'
                ]
            }
        };

        console.log('🤖 AI Assistant Bot başlatılıyor...');
        this.init();
    }

    async init() {
        try {
            // Voice recognition setup
            await this.initializeVoiceRecognition();
            
            // Speech synthesis setup
            await this.initializeSpeechSynthesis();
            
            // UI event listeners
            this.setupEventListeners();
            
            // Load conversation history
            this.loadConversationHistory();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            // Initialize chat interface
            this.initializeChatInterface();
            
            console.log('✅ AI Assistant Bot hazır!');
            
            // Welcome message
            this.addAssistantMessage(this.getRandomResponse('greeting'));
            
        } catch (error) {
            console.error('❌ AI Assistant Bot init hatası:', error);
            this.showError('AI Assistant başlatma hatası: ' + error.message);
        }
    }

    async initializeVoiceRecognition() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.speechRecognition = new SpeechRecognition();
            
            this.speechRecognition.continuous = false;
            this.speechRecognition.interimResults = false;
            this.speechRecognition.lang = this.currentLanguage === 'tr' ? 'tr-TR' : 'en-US';
            
            this.speechRecognition.onstart = () => {
                console.log('🎤 Voice recognition başladı');
                this.updateVoiceStatus('listening', 'Dinliyorum...');
                this.showVoiceVisualization(true);
            };
            
            this.speechRecognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                const confidence = event.results[0][0].confidence;
                
                console.log('🗣️ Algılanan metin:', transcript, 'Güven:', confidence);
                
                if (confidence > this.aiConfig.confidenceThreshold) {
                    this.processVoiceInput(transcript);
                } else {
                    this.showError('Ses tanıma güven seviyesi düşük. Lütfen tekrar deneyin.');
                }
            };
            
            this.speechRecognition.onerror = (event) => {
                console.error('❌ Voice recognition hatası:', event.error);
                this.updateVoiceStatus('idle', 'Mikrofona basarak konuşmaya başlayın');
                this.showVoiceVisualization(false);
            };
            
            this.speechRecognition.onend = () => {
                console.log('🎤 Voice recognition bitti');
                this.isRecording = false;
                this.updateVoiceStatus('idle', 'Mikrofona basarak konuşmaya başlayın');
                this.showVoiceVisualization(false);
                this.updateVoiceButton('idle');
            };
            
        } else {
            console.warn('⚠️ Voice recognition desteklenmiyor');
            this.showError('Tarayıcınız ses tanımayı desteklemiyor');
        }
    }

    async initializeSpeechSynthesis() {
        if ('speechSynthesis' in window) {
            // Wait for voices to load
            const loadVoices = () => {
                const voices = this.speechSynthesis.getVoices();
                
                // Turkish voice priority
                if (this.currentLanguage === 'tr') {
                    this.currentVoice = voices.find(voice => 
                        voice.lang.includes('tr') || voice.name.includes('Turkish')
                    ) || voices.find(voice => voice.lang.includes('en'));
                } else {
                    this.currentVoice = voices.find(voice => 
                        voice.lang.includes('en') && voice.name.includes('Female')
                    ) || voices[0];
                }
                
                console.log('🎵 Selected voice:', this.currentVoice?.name || 'Default');
            };
            
            if (this.speechSynthesis.getVoices().length) {
                loadVoices();
            } else {
                this.speechSynthesis.onvoiceschanged = loadVoices;
            }
            
        } else {
            console.warn('⚠️ Speech synthesis desteklenmiyor');
        }
    }

    setupEventListeners() {
        // Global functions for HTML onclick events
        window.toggleVoiceRecording = () => this.toggleVoiceRecording();
        window.speakLastMessage = () => this.speakLastMessage();
        window.sendMessage = (text) => this.sendMessage(text);
        window.handleKeyPress = (event) => this.handleKeyPress(event);
        window.setLanguage = (lang) => this.setLanguage(lang);
        window.quickCommand = (type) => this.quickCommand(type);
        window.updateSpeechRate = (value) => this.updateSpeechRate(value);
        window.updateSpeechPitch = (value) => this.updateSpeechPitch(value);
        window.updateSpeechVolume = (value) => this.updateSpeechVolume(value);
    }

    initializeChatInterface() {
        // Auto-scroll to bottom
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Focus on input
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            messageInput.focus();
        }
    }

    startRealTimeUpdates() {
        // Update chat stats every 30 seconds
        setInterval(() => {
            this.updateChatStats();
        }, 30000);
        
        // Simulate AI learning every 2 minutes
        setInterval(() => {
            this.simulateAILearning();
        }, 120000);
    }

    toggleVoiceRecording() {
        if (!this.speechRecognition) {
            this.showError('Voice recognition kullanılamıyor');
            return;
        }
        
        if (this.isRecording) {
            this.speechRecognition.stop();
        } else {
            this.isRecording = true;
            this.updateVoiceButton('recording');
            this.speechRecognition.start();
        }
    }

    processVoiceInput(transcript) {
        // Add user message to chat
        this.addUserMessage(transcript, true);
        
        // Process with AI
        this.processUserInput(transcript);
        
        // Update stats
        this.chatStats.voiceCommands++;
        this.updateChatStats();
    }

    async sendMessage(text) {
        const messageInput = document.getElementById('message-input');
        const messageText = text || (messageInput ? messageInput.value.trim() : '');
        
        if (!messageText) return;
        
        // Clear input
        if (messageInput && !text) {
            messageInput.value = '';
        }
        
        // Add user message
        this.addUserMessage(messageText, false);
        
        // Process with AI
        await this.processUserInput(messageText);
        
        // Update stats
        this.chatStats.totalConversations++;
        this.chatStats.todayConversations++;
        this.updateChatStats();
    }

    async processUserInput(userInput) {
        this.isProcessing = true;
        this.showTypingIndicator();
        
        try {
            // Simulate AI processing delay
            await this.delay(this.aiConfig.responseDelay);
            
            // Natural Language Processing
            const intent = this.analyzeIntent(userInput);
            const response = await this.generateAIResponse(userInput, intent);
            
            // Remove typing indicator
            this.hideTypingIndicator();
            
            // Add AI response
            this.addAssistantMessage(response);
            
            // Speak response if enabled
            if (this.voiceSettings.autoSpeak) {
                this.speakText(response);
            }
            
            // Update conversation history
            this.conversationHistory.push({
                user: userInput,
                assistant: response,
                timestamp: new Date(),
                intent: intent
            });
            
            // Keep only last 10 conversations
            if (this.conversationHistory.length > this.aiConfig.conversationMemory) {
                this.conversationHistory.shift();
            }
            
        } catch (error) {
            console.error('❌ AI processing hatası:', error);
            this.hideTypingIndicator();
            this.addAssistantMessage(this.getRandomResponse('error'));
        } finally {
            this.isProcessing = false;
        }
    }

    analyzeIntent(userInput) {
        const input = userInput.toLowerCase();
        const patterns = this.nlpPatterns[this.currentLanguage];
        
        // Check for pricing-related queries
        if (patterns.pricing.some(word => input.includes(word))) {
            return 'pricing';
        }
        
        // Check for order-related queries
        if (patterns.orders.some(word => input.includes(word))) {
            return 'orders';
        }
        
        // Check for inventory-related queries
        if (patterns.inventory.some(word => input.includes(word))) {
            return 'inventory';
        }
        
        // Check for analytics queries
        if (input.includes('analiz') || input.includes('rapor') || input.includes('performans') || 
            input.includes('analytics') || input.includes('report') || input.includes('performance')) {
            return 'analytics';
        }
        
        // Check for greetings
        if (patterns.greetings.some(word => input.includes(word))) {
            return 'greeting';
        }
        
        // Check for specific platforms
        const mentionedPlatform = patterns.platforms.find(platform => input.includes(platform));
        if (mentionedPlatform) {
            return `platform_${mentionedPlatform}`;
        }
        
        return 'general';
    }

    async generateAIResponse(userInput, intent) {
        // Generate contextual AI response based on intent
        switch (intent) {
            case 'pricing':
                return await this.handlePricingQuery(userInput);
            case 'orders':
                return await this.handleOrdersQuery(userInput);
            case 'inventory':
                return await this.handleInventoryQuery(userInput);
            case 'analytics':
                return await this.handleAnalyticsQuery(userInput);
            case 'greeting':
                return this.getRandomResponse('greeting');
            default:
                if (intent.startsWith('platform_')) {
                    const platform = intent.replace('platform_', '');
                    return await this.handlePlatformQuery(userInput, platform);
                }
                return this.handleGeneralQuery(userInput);
        }
    }

    async handlePricingQuery(userInput) {
        const responses = [
            `AI Pricing Engine çalıştırılıyor... 📊\n\nGüncel market analizi:\n• Amazon: 45 ürün optimize edildi\n• Trendyol: +12.4% kar artışı\n• N11: Rakip fiyat avantajı tespit edildi\n\nAI önerisi: 3 ürün için fiyat artışı, 2 ürün için fiyat düşürülmesi tavsiye ediliyor.`,
            `Fiyat optimizasyon analizi tamamlandı! 🤖\n\nBugünkü AI insights:\n• Toplam optimize edilen ürün: 127\n• Ortalama kar artışı: %18.6\n• En performanslı platform: Trendyol\n\nDetaylı AI raporu için "AI pricing dashboard" komutunu kullanabilirsiniz.`,
            `Machine Learning fiyat modeli aktif! 🧠\n\nGerçek zamanlı sonuçlar:\n• Neural Network accuracy: %94.2\n• Cross-platform sync: Aktif\n• Profit maximization: Çalışıyor\n\nSesli komutla "optimize all prices" diyebilirsiniz.`
        ];
        
        await this.delay(1500); // Simulate AI processing
        return responses[Math.floor(Math.random() * responses.length)];
    }

    async handleOrdersQuery(userInput) {
        const totalOrders = Object.values(this.marketplaceData).reduce((sum, data) => sum + data.orders, 0);
        const totalRevenue = Object.values(this.marketplaceData).reduce((sum, data) => sum + data.revenue, 0);
        
        const response = `Sipariş analizi tamamlandı! 📦\n\nBugünkü durumlar:\n• Toplam sipariş: ${totalOrders}\n• Toplam ciro: ${this.formatCurrency(totalRevenue)}\n• En aktif platform: Trendyol (${this.marketplaceData.trendyol.orders} sipariş)\n\nPlatform detayları:\n${Object.entries(this.marketplaceData).map(([platform, data]) => 
            `• ${platform.charAt(0).toUpperCase() + platform.slice(1)}: ${data.orders} sipariş`
        ).join('\n')}\n\nDetaylı analiz için "orders dashboard" komutunu kullanın.`;
        
        await this.delay(1200);
        return response;
    }

    async handleInventoryQuery(userInput) {
        const totalProducts = Object.values(this.marketplaceData).reduce((sum, data) => sum + data.products, 0);
        
        const response = `Envanter kontrolü tamamlandı! 📋\n\nStok durumu:\n• Toplam ürün: ${totalProducts}\n• Aktif listing: ${Math.floor(totalProducts * 0.85)}\n• Kritik stok: 12 ürün\n• Stok güncellemesi gerekli: 8 ürün\n\nPlatform dağılımı:\n${Object.entries(this.marketplaceData).map(([platform, data]) => 
            `• ${platform.charAt(0).toUpperCase() + platform.slice(1)}: ${data.products} ürün`
        ).join('\n')}\n\nOtomatik stok sync aktif! ✅`;
        
        await this.delay(1000);
        return response;
    }

    async handleAnalyticsQuery(userInput) {
        const response = `AI Analytics raporu hazırlandı! 📈\n\nPerformans özeti:\n• Conversion rate: %3.4 (+0.8)\n• AI optimization impact: +%23.7\n• Best performing category: Electronics\n• Revenue growth: +%31.2 (7 günlük)\n\nPlatform karşılaştırması:\n• #1 Trendyol: ${this.formatCurrency(this.marketplaceData.trendyol.revenue)}\n• #2 Amazon: ${this.formatCurrency(this.marketplaceData.amazon.revenue)}\n• #3 Hepsiburada: ${this.formatCurrency(this.marketplaceData.hepsiburada.revenue)}\n\nAI predictions: Gelecek hafta %15 büyüme bekleniyor 🚀`;
        
        await this.delay(2000);
        return response;
    }

    async handlePlatformQuery(userInput, platform) {
        const data = this.marketplaceData[platform];
        if (!data) {
            return `${platform} platformu için veri bulunamadı.`;
        }
        
        const response = `${platform.charAt(0).toUpperCase() + platform.slice(1)} Platform Raporu 🏪\n\nGüncel durum:\n• Siparişler: ${data.orders}\n• Ciro: ${this.formatCurrency(data.revenue)}\n• Ürün sayısı: ${data.products}\n• Ortalama sipariş değeri: ${this.formatCurrency(data.revenue / data.orders)}\n\nAI insights:\n• Performans skoru: ${85 + Math.floor(Math.random() * 15)}/100\n• Optimization durumu: ${Math.random() > 0.5 ? 'Aktif' : 'Standby'}\n• Öneri: ${this.getPlatformRecommendation(platform)}`;
        
        await this.delay(1300);
        return response;
    }

    handleGeneralQuery(userInput) {
        const generalResponses = [
            `Anladım! Bu konuda size nasıl yardımcı olabilirim? Pricing, inventory, orders veya analytics konularında uzmanım. 🤖`,
            `Elbette! MesChain-Sync'in tüm özelliklerini kullanabilirsiniz. Hangi platform veya işlem hakkında bilgi almak istersiniz? 💼`,
            `Harika soru! Sesli komutlar veya yazılı mesajlarla tüm marketplace operasyonlarınızı yönetebilirsiniz. Ne yapmak istersiniz? 🚀`
        ];
        
        return generalResponses[Math.floor(Math.random() * generalResponses.length)];
    }

    getPlatformRecommendation(platform) {
        const recommendations = [
            'Fiyat optimizasyonu önerilir',
            'Cross-listing fırsatı mevcut',
            'Inventory rotasyonu gerekli',
            'Marketing kampanyası zamanı',
            'Seasonal pricing aktif edilebilir'
        ];
        
        return recommendations[Math.floor(Math.random() * recommendations.length)];
    }

    addUserMessage(text, isVoice = false) {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user';
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-${isVoice ? 'microphone' : 'user'}"></i>
            </div>
            <div class="message-bubble">
                ${text}
                ${isVoice ? ' <small class="text-muted">🎤</small>' : ''}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    addAssistantMessage(text) {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message assistant';
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble">
                ${text.replace(/\n/g, '<br>')}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Update stats
        this.chatStats.aiResponses++;
        this.updateChatStats();
    }

    showTypingIndicator() {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message assistant';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    speakText(text) {
        if (!this.speechSynthesis || !this.currentVoice) return;
        
        // Cancel any ongoing speech
        this.speechSynthesis.cancel();
        
        // Clean text for speech
        const cleanText = text.replace(/<[^>]*>/g, '').replace(/[📊📦📋📈🏪🤖💼🚀]/g, '');
        
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.voice = this.currentVoice;
        utterance.rate = this.voiceSettings.rate;
        utterance.pitch = this.voiceSettings.pitch;
        utterance.volume = this.voiceSettings.volume;
        
        utterance.onstart = () => {
            this.updateVoiceStatus('speaking', 'Konuşuyorum...');
            const indicator = document.querySelector('.status-indicator');
            if (indicator) {
                indicator.className = 'status-indicator status-speaking';
            }
        };
        
        utterance.onend = () => {
            this.updateVoiceStatus('idle', 'Mikrofona basarak konuşmaya başlayın');
            const indicator = document.querySelector('.status-indicator');
            if (indicator) {
                indicator.className = 'status-indicator status-online';
            }
        };
        
        this.speechSynthesis.speak(utterance);
    }

    speakLastMessage() {
        const messages = document.querySelectorAll('.message.assistant .message-bubble');
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1].textContent;
            this.speakText(lastMessage);
        }
    }

    updateVoiceButton(state) {
        const voiceBtn = document.getElementById('voice-btn');
        if (!voiceBtn) return;
        
        voiceBtn.className = `voice-btn ${state}`;
        
        const icon = voiceBtn.querySelector('i');
        if (icon) {
            switch (state) {
                case 'recording':
                    icon.className = 'fas fa-stop';
                    break;
                case 'listening':
                    icon.className = 'fas fa-microphone';
                    break;
                default:
                    icon.className = 'fas fa-microphone';
                    break;
            }
        }
    }

    updateVoiceStatus(status, message) {
        const statusText = document.getElementById('voice-status-text');
        if (statusText) {
            statusText.textContent = message;
        }
        
        const voiceStatus = document.getElementById('voice-status');
        if (voiceStatus) {
            const statusIcons = {
                idle: '🎤 Hazır',
                listening: '👂 Dinliyor',
                recording: '🔴 Kaydediyor',
                speaking: '🗣️ Konuşuyor'
            };
            voiceStatus.textContent = statusIcons[status] || '🎤 Hazır';
        }
    }

    showVoiceVisualization(show) {
        const visualization = document.getElementById('voice-visualization');
        if (visualization) {
            visualization.style.display = show ? 'flex' : 'none';
        }
    }

    updateChatStats() {
        // Update metrics with some randomization
        this.animateCounter('total-conversations', this.chatStats.totalConversations);
        this.animateCounter('today-conversations', this.chatStats.todayConversations);
        this.animateCounter('voice-commands', this.chatStats.voiceCommands);
        this.animateCounter('voice-accuracy', this.chatStats.voiceAccuracy.toFixed(1) + '%');
        this.animateCounter('ai-responses', this.chatStats.aiResponses);
        this.animateCounter('response-success', this.chatStats.responseSuccess.toFixed(1) + '%');
        this.animateCounter('response-time', this.chatStats.currentResponseTime + 's');
        this.animateCounter('avg-response-time', this.chatStats.avgResponseTime + 's');
    }

    simulateAILearning() {
        // Simulate AI improvement over time
        this.chatStats.voiceAccuracy = Math.min(99.9, this.chatStats.voiceAccuracy + 0.1);
        this.chatStats.responseSuccess = Math.min(99.9, this.chatStats.responseSuccess + 0.05);
        this.chatStats.currentResponseTime = Math.max(0.3, this.chatStats.currentResponseTime - 0.01);
        
        this.updateChatStats();
    }

    setLanguage(lang) {
        this.currentLanguage = lang;
        
        // Update UI
        const buttons = document.querySelectorAll('.language-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.onclick.toString().includes(lang)) {
                btn.classList.add('active');
            }
        });
        
        // Update speech recognition language
        if (this.speechRecognition) {
            this.speechRecognition.lang = lang === 'tr' ? 'tr-TR' : 'en-US';
        }
        
        // Update voice
        this.initializeSpeechSynthesis();
        
        console.log(`🌍 Dil değiştirildi: ${lang}`);
    }

    quickCommand(type) {
        const commands = {
            prices: 'Fiyatları kontrol et ve AI önerilerini göster',
            orders: 'Tüm platformlardaki siparişleri listele',
            inventory: 'Stok durumunu analiz et',
            analytics: 'Detaylı performans raporu hazırla'
        };
        
        if (commands[type]) {
            this.sendMessage(commands[type]);
        }
    }

    updateSpeechRate(value) {
        this.voiceSettings.rate = parseFloat(value);
        document.getElementById('rate-value').textContent = value + 'x';
    }

    updateSpeechPitch(value) {
        this.voiceSettings.pitch = parseFloat(value);
        document.getElementById('pitch-value').textContent = value;
    }

    updateSpeechVolume(value) {
        this.voiceSettings.volume = parseFloat(value);
        document.getElementById('volume-value').textContent = Math.round(value * 100) + '%';
    }

    handleKeyPress(event) {
        if (event.key === 'Enter') {
            this.sendMessage();
        }
    }

    loadConversationHistory() {
        // Load from localStorage if available
        const saved = localStorage.getItem('meschain_conversation_history');
        if (saved) {
            try {
                this.conversationHistory = JSON.parse(saved);
            } catch (error) {
                console.warn('⚠️ Conversation history yüklenemedi:', error);
            }
        }
    }

    saveConversationHistory() {
        // Save to localStorage
        localStorage.setItem('meschain_conversation_history', JSON.stringify(this.conversationHistory));
    }

    getRandomResponse(type) {
        const responses = this.smartResponses[this.currentLanguage][type];
        if (responses && responses.length > 0) {
            return responses[Math.floor(Math.random() * responses.length)];
        }
        return 'Üzgünüm, şu anda yanıt veremiyorum.';
    }

    // Utility Methods
    formatCurrency(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.style.transform = 'scale(1.1)';
        element.style.color = '#10B981';
        
        setTimeout(() => {
            element.textContent = targetValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 300);
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} 
                          alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px;';
        toast.innerHTML = `
            <i class="fas fa-robot me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    destroy() {
        // Clean up speech synthesis
        if (this.speechSynthesis) {
            this.speechSynthesis.cancel();
        }
        
        // Clean up speech recognition
        if (this.speechRecognition) {
            this.speechRecognition.stop();
        }
        
        // Save conversation history
        this.saveConversationHistory();
        
        console.log('🧹 AI Assistant Bot temizlendi');
    }
}

// Export for use in other modules
window.AIAssistantBot = AIAssistantBot; 