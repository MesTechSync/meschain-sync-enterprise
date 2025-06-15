const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 6009;

// CORS ve JSON middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname)));

// Ana route - advanced cross marketplace admin panel
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'advanced_cross_marketplace_admin_panel.html'));
});

// API endpoint'leri
app.get('/api/marketplace-stats', (req, res) => {
    res.json({
        success: true,
        data: {
            trendyol: {
                name: 'Trendyol Mağaza',
                status: 'active',
                totalProducts: 1847,
                monthlyOrders: 456,
                monthlySales: '₺67,843',
                averageRating: 4.7,
                growth: '+12%'
            },
            amazon: {
                name: 'Amazon Mağaza',
                status: 'active',
                totalProducts: 2134,
                monthlyOrders: 723,
                monthlySales: '$12,456',
                averageRating: 4.8,
                growth: '+18%'
            },
            hepsiburada: {
                name: 'Hepsiburada Mağaza',
                status: 'active',
                totalProducts: 1523,
                monthlyOrders: 342,
                monthlySales: '₺78,945',
                averageRating: 4.6,
                growth: '+8%'
            },
            n11: {
                name: 'N11 Mağaza',
                status: 'inactive',
                totalProducts: 892,
                monthlyOrders: 156,
                monthlySales: '₺45,321',
                averageRating: 4.3,
                growth: '-2%'
            },
            ebay: {
                name: 'eBay Mağaza',
                status: 'active',
                totalProducts: 1234,
                monthlyOrders: 267,
                monthlySales: '$8,945',
                averageRating: 4.5,
                growth: '+15%'
            },
            ozon: {
                name: 'Ozon Mağaza',
                status: 'active',
                totalProducts: 967,
                monthlyOrders: 189,
                monthlySales: '₽234,567',
                averageRating: 4.4,
                growth: '+22%'
            }
        }
    });
});

app.get('/api/cross-marketplace-overview', (req, res) => {
    res.json({
        success: true,
        data: {
            totalMarketplaces: 6,
            activeMarketplaces: 5,
            totalProducts: 8747,
            totalMonthlyOrders: 2133,
            totalMonthlySales: {
                try: 192109, // TRY
                usd: 21401, // USD
                rub: 234567 // RUB
            },
            averageRating: 4.6,
            growthRate: '+13.2%',
            syncStatus: {
                lastSync: '2025-06-05 14:32:15',
                status: 'completed',
                nextSync: '2025-06-05 15:00:00'
            }
        }
    });
});

app.get('/api/performance-metrics', (req, res) => {
    res.json({
        success: true,
        data: {
            chartData: {
                labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
                datasets: [
                    {
                        label: 'Trendyol',
                        data: [320, 356, 398, 421, 445, 456],
                        borderColor: '#f27a1a',
                        backgroundColor: 'rgba(242, 122, 26, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Amazon',
                        data: [580, 623, 667, 689, 701, 723],
                        borderColor: '#ff9900',
                        backgroundColor: 'rgba(255, 153, 0, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Hepsiburada',
                        data: [234, 267, 289, 312, 328, 342],
                        borderColor: '#ff6000',
                        backgroundColor: 'rgba(255, 96, 0, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'N11',
                        data: [178, 165, 152, 148, 151, 156],
                        borderColor: '#6c5ce7',
                        backgroundColor: 'rgba(108, 92, 231, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'eBay',
                        data: [189, 203, 234, 245, 258, 267],
                        borderColor: '#0064d2',
                        backgroundColor: 'rgba(0, 100, 210, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Ozon',
                        data: [123, 134, 156, 167, 178, 189],
                        borderColor: '#005bff',
                        backgroundColor: 'rgba(0, 91, 255, 0.1)',
                        tension: 0.4
                    }
                ]
            }
        }
    });
});

app.post('/api/sync-all-marketplaces', (req, res) => {
    // Simulate sync process
    setTimeout(() => {
        res.json({
            success: true,
            message: 'Tüm marketplace\'ler başarıyla senkronize edildi',
            data: {
                syncedMarketplaces: 5,
                totalProducts: 8747,
                syncTime: new Date().toISOString(),
                results: {
                    trendyol: { status: 'success', products: 1847, orders: 12 },
                    amazon: { status: 'success', products: 2134, orders: 23 },
                    hepsiburada: { status: 'success', products: 1523, orders: 8 },
                    n11: { status: 'warning', products: 892, orders: 3 },
                    ebay: { status: 'success', products: 1234, orders: 15 },
                    ozon: { status: 'success', products: 967, orders: 7 }
                }
            }
        });
    }, 2000);
});

app.post('/api/bulk-order-import', (req, res) => {
    setTimeout(() => {
        res.json({
            success: true,
            message: 'Toplu sipariş çekme işlemi tamamlandı',
            data: {
                importedOrders: 68,
                marketplaces: {
                    trendyol: 12,
                    amazon: 23,
                    hepsiburada: 8,
                    n11: 3,
                    ebay: 15,
                    ozon: 7
                },
                totalValue: '₺45,678'
            }
        });
    }, 3000);
});

app.get('/api/system-health', (req, res) => {
    res.json({
        success: true,
        data: {
            systemStatus: 'healthy',
            uptime: '15 gün 8 saat 32 dakika',
            cpuUsage: '23%',
            memoryUsage: '67%',
            diskUsage: '45%',
            activeConnections: 156,
            apiResponseTime: '145ms',
            lastBackup: '2025-06-05 03:00:00',
            marketplaceConnections: {
                trendyol: 'connected',
                amazon: 'connected',
                hepsiburada: 'connected',
                n11: 'disconnected',
                ebay: 'connected',
                ozon: 'connected'
            }
        }
    });
});

app.get('/api/notifications', (req, res) => {
    res.json({
        success: true,
        data: [
            {
                id: 1,
                type: 'success',
                title: 'Senkronizasyon Tamamlandı',
                message: 'Trendyol marketplace\'i başarıyla senkronize edildi',
                timestamp: '2025-06-05 14:32:15',
                read: false
            },
            {
                id: 2,
                type: 'warning',
                title: 'Stok Uyarısı',
                message: 'Amazon\'da 15 ürünün stoku kritik seviyede',
                timestamp: '2025-06-05 14:15:23',
                read: false
            },
            {
                id: 3,
                type: 'info',
                title: 'Yeni Sipariş',
                message: 'Hepsiburada\'dan 3 yeni sipariş geldi',
                timestamp: '2025-06-05 14:08:45',
                read: true
            },
            {
                id: 4,
                type: 'error',
                title: 'Bağlantı Hatası',
                message: 'N11 marketplace bağlantısı kesildi',
                timestamp: '2025-06-05 13:45:12',
                read: false
            }
        ]
    });
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({
        success: false,
        message: 'Sunucu hatası oluştu',
        error: process.env.NODE_ENV === 'development' ? err.message : 'Internal server error'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: 'Endpoint bulunamadı'
    });
});

app.listen(PORT, () => {
    // Server started successfully
});

module.exports = app;
