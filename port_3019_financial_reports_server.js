// 💰 MesChain-Sync Mali Raporlar Sunucusu - Port 3019
// Enterprise Grade Financial Reporting Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3019;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 💰 MALİ VERİLER MOCK DATABASE
const financialData = {
    summary: {
        totalRevenue: 1250000,
        totalExpenses: 780000,
        netProfit: 470000,
        profitMargin: 37.6,
        taxAmount: 94000,
        cashFlow: 532000
    },
    monthly: {
        revenue: [98000, 125000, 142000, 156000, 167000, 189000],
        expenses: [65000, 78000, 82000, 89000, 94000, 102000],
        profit: [33000, 47000, 60000, 67000, 73000, 87000]
    },
    categories: {
        revenue: {
            'Trendyol Satışları': 485000,
            'Amazon Satışları': 312000,
            'Hepsiburada Satışları': 267000,
            'N11 Satışları': 186000
        },
        expenses: {
            'Personel Giderleri': 280000,
            'Pazarlama': 165000,
            'Operasyonel': 125000,
            'Teknoloji': 89000,
            'Diğer': 121000
        }
    },
    kpi: {
        roe: 24.8, // Return on Equity
        roa: 18.3, // Return on Assets
        currentRatio: 2.4,
        debtRatio: 0.32,
        grossMargin: 52.1,
        operatingMargin: 41.2
    }
};

// Ana sayfa - Dashboard
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>💰 MesChain-Sync Mali Raporlar - Port 3019</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            body { font-family: 'Inter', sans-serif; }
            .glass-effect {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-currency-circle-dollar mr-3 text-green-600"></i>
                            Mali Raporlar Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Financial Analytics - Port 3019</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="glass-effect px-4 py-2 rounded-lg">
                            <span class="text-sm text-gray-700">📅 ${new Date().toLocaleDateString('tr-TR')}</span>
                        </div>
                        <div class="glass-effect px-4 py-2 rounded-lg">
                            <span class="text-sm text-green-600 font-semibold">🟢 Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Gelir</h3>
                            <p class="text-3xl font-bold text-green-600">₺${financialData.summary.totalRevenue.toLocaleString()}</p>
                            <p class="text-sm text-green-600 mt-1">Bu ay</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-trend-up text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Gider</h3>
                            <p class="text-3xl font-bold text-red-600">₺${financialData.summary.totalExpenses.toLocaleString()}</p>
                            <p class="text-sm text-red-600 mt-1">Bu ay</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="ph ph-trend-down text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Net Kar</h3>
                            <p class="text-3xl font-bold text-blue-600">₺${financialData.summary.netProfit.toLocaleString()}</p>
                            <p class="text-sm text-blue-600 mt-1">%${financialData.summary.profitMargin} margin</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="ph ph-chart-line text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Nakit Akışı</h3>
                            <p class="text-3xl font-bold text-purple-600">₺${financialData.summary.cashFlow.toLocaleString()}</p>
                            <p class="text-sm text-purple-600 mt-1">Pozitif akış</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="ph ph-coins text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-chart-bar mr-2"></i>
                        Aylık Gelir-Gider Analizi
                    </h3>
                    <canvas id="revenueExpenseChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-pie-chart mr-2"></i>
                        Gider Kategorileri
                    </h3>
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <!-- KPI Table -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-calculator mr-2"></i>
                    Finansal Performans Göstergeleri (KPI)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800">Özsermaye Getirisi (ROE)</h4>
                        <p class="text-2xl font-bold text-blue-600">%${financialData.kpi.roe}</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-800">Aktif Getirisi (ROA)</h4>
                        <p class="text-2xl font-bold text-green-600">%${financialData.kpi.roa}</p>
                    </div>
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-purple-800">Cari Oran</h4>
                        <p class="text-2xl font-bold text-purple-600">${financialData.kpi.currentRatio}</p>
                    </div>
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-orange-800">Borç Oranı</h4>
                        <p class="text-2xl font-bold text-orange-600">%${financialData.kpi.debtRatio * 100}</p>
                    </div>
                    <div class="bg-gradient-to-r from-teal-50 to-teal-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-teal-800">Brüt Kar Marjı</h4>
                        <p class="text-2xl font-bold text-teal-600">%${financialData.kpi.grossMargin}</p>
                    </div>
                    <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-indigo-800">Operasyonel Marj</h4>
                        <p class="text-2xl font-bold text-indigo-600">%${financialData.kpi.operatingMargin}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-gear mr-2"></i>
                    Hızlı İşlemler
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="exportFinancialReport()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-download"></i>
                        <span>Mali Rapor İndir</span>
                    </button>
                    <button onclick="generateTaxReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-receipt"></i>
                        <span>Vergi Raporu</span>
                    </button>
                    <button onclick="budgetAnalysis()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-chart-pie"></i>
                        <span>Bütçe Analizi</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Revenue vs Expense Chart
            const ctx1 = document.getElementById('revenueExpenseChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
                    datasets: [
                        {
                            label: 'Gelir',
                            data: ${JSON.stringify(financialData.monthly.revenue)},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Gider',
                            data: ${JSON.stringify(financialData.monthly.expenses)},
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Expense Categories Chart
            const ctx2 = document.getElementById('expenseChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(${JSON.stringify(financialData.categories.expenses)}),
                    datasets: [{
                        data: Object.values(${JSON.stringify(financialData.categories.expenses)}),
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Quick Actions
            function exportFinancialReport() {
                alert('💰 Mali rapor Excel formatında indiriliyor...');
                window.open('/api/export/financial', '_blank');
            }

            function generateTaxReport() {
                alert('📋 Vergi raporu hazırlanıyor...');
                window.open('/api/export/tax', '_blank');
            }

            function budgetAnalysis() {
                alert('📊 Detaylı bütçe analizi açılıyor...');
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Mali veriler güncelleniyor...');
            }, 30000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/financial/summary', (req, res) => {
    res.json(financialData.summary);
});

app.get('/api/financial/monthly', (req, res) => {
    res.json(financialData.monthly);
});

app.get('/api/financial/categories', (req, res) => {
    res.json(financialData.categories);
});

app.get('/api/financial/kpi', (req, res) => {
    res.json(financialData.kpi);
});

app.get('/api/export/financial', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=financial-report.xlsx');
    res.send('Mock Financial Excel file content');
});

app.get('/api/export/tax', (req, res) => {
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader('Content-Disposition', 'attachment; filename=tax-report.pdf');
    res.send('Mock Tax PDF file content');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Financial Reports',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`💰 MesChain-Sync Mali Raporlar Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Financial Dashboard`);
    console.log(`📊 Mali analitikler, KPI takibi, ve finansal raporlama sistemi aktif!`);
});

module.exports = app;
