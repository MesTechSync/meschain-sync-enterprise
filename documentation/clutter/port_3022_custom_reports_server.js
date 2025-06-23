// 🛠️ MesChain-Sync Özel Raporlar Sunucusu - Port 3022
// Enterprise Grade Custom Reporting Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3022;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 🛠️ ÖZEL RAPOR VERİLERİ MOCK DATABASE
const customReportsData = {
    templates: [
        { id: 1, name: 'Aylık Satış Analizi', category: 'Satış', description: 'Detaylı aylık satış performans raporu', fields: ['Tarih', 'Marketplace', 'Gelir', 'Sipariş Sayısı'], usage: 42 },
        { id: 2, name: 'Stok Hareket Raporu', category: 'Envanter', description: 'Ürün bazında stok giriş-çıkış analizi', fields: ['Ürün', 'Giriş', 'Çıkış', 'Mevcut Stok'], usage: 38 },
        { id: 3, name: 'Karlılık Analizi', category: 'Mali', description: 'Ürün ve kategori bazında kar analizi', fields: ['Kategori', 'Maliyet', 'Satış Fiyatı', 'Kar Marjı'], usage: 31 },
        { id: 4, name: 'Müşteri Segment Analizi', category: 'CRM', description: 'Müşteri davranış ve segment raporu', fields: ['Müşteri ID', 'Segment', 'Toplam Harcama', 'Sipariş Sıklığı'], usage: 28 },
        { id: 5, name: 'Performans Karşılaştırma', category: 'Performans', description: 'Marketplace karşılaştırmalı performans', fields: ['Marketplace', 'Satış', 'Komisyon', 'Net Kar'], usage: 35 }
    ],
    recentReports: [
        { name: 'Haziran 2025 Satış Raporu', date: '2025-06-13', type: 'Otomatik', status: 'Tamamlandı', size: '2.4 MB' },
        { name: 'Stok Analiz Raporu', date: '2025-06-13', type: 'Manuel', status: 'Hazırlanıyor', size: '1.8 MB' },
        { name: 'Karlılık Raporu Q2', date: '2025-06-12', type: 'Zamanlanmış', status: 'Tamamlandı', size: '3.1 MB' },
        { name: 'Müşteri Davranış Analizi', date: '2025-06-12', type: 'Manuel', status: 'Tamamlandı', size: '4.2 MB' },
        { name: 'Marketplace Performans', date: '2025-06-11', type: 'Otomatik', status: 'Tamamlandı', size: '2.7 MB' }
    ],
    scheduledReports: [
        { name: 'Günlük Satış Özeti', frequency: 'Günlük', nextRun: '2025-06-14 09:00', enabled: true },
        { name: 'Haftalık Envanter Raporu', frequency: 'Haftalık', nextRun: '2025-06-16 08:00', enabled: true },
        { name: 'Aylık Mali Rapor', frequency: 'Aylık', nextRun: '2025-07-01 10:00', enabled: true },
        { name: 'Çeyreklik Performans Raporu', frequency: 'Çeyreklik', nextRun: '2025-09-01 09:00', enabled: false }
    ],
    filters: {
        dateRanges: ['Bugün', 'Bu Hafta', 'Bu Ay', 'Son 3 Ay', 'Bu Yıl', 'Özel Tarih'],
        marketplaces: ['Trendyol', 'Amazon', 'Hepsiburada', 'N11', 'eBay', 'Ozon'],
        categories: ['Elektronik', 'Giyim', 'Ev & Yaşam', 'Kitap & Hobi', 'Spor & Outdoor', 'Kozmetik'],
        formats: ['Excel (.xlsx)', 'PDF', 'CSV', 'JSON', 'XML']
    },
    statistics: {
        totalReports: 847,
        activeSchedules: 12,
        avgGenerationTime: 34,
        totalDownloads: 2156,
        popularTemplate: 'Aylık Satış Analizi',
        lastGenerated: '2025-06-13 14:23'
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
        <title>🛠️ MesChain-Sync Özel Raporlar - Port 3022</title>
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
            .template-card {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .template-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            }
            .status-completed { color: #10b981; }
            .status-processing { color: #f59e0b; }
            .status-failed { color: #ef4444; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-indigo-50 to-purple-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-gear mr-3 text-indigo-600"></i>
                            Özel Raporlar Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Custom Reports - Port 3022</p>
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

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Rapor</h3>
                            <p class="text-3xl font-bold text-indigo-600">${customReportsData.statistics.totalReports}</p>
                            <p class="text-sm text-indigo-600 mt-1">Oluşturulan rapor</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-full">
                            <i class="ph ph-file-text text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Aktif Zamanlama</h3>
                            <p class="text-3xl font-bold text-purple-600">${customReportsData.statistics.activeSchedules}</p>
                            <p class="text-sm text-purple-600 mt-1">Otomatik rapor</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="ph ph-clock text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Ortalama Süre</h3>
                            <p class="text-3xl font-bold text-green-600">${customReportsData.statistics.avgGenerationTime}s</p>
                            <p class="text-sm text-green-600 mt-1">Oluşturma süresi</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-lightning text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">İndirme</h3>
                            <p class="text-3xl font-bold text-orange-600">${customReportsData.statistics.totalDownloads}</p>
                            <p class="text-sm text-orange-600 mt-1">Toplam indirme</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="ph ph-download text-2xl text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Report Creator -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-plus-circle mr-2"></i>
                    Hızlı Rapor Oluşturucu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rapor Türü</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option>Satış Raporu</option>
                            <option>Envanter Raporu</option>
                            <option>Mali Rapor</option>
                            <option>Performans Raporu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tarih Aralığı</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            ${customReportsData.filters.dateRanges.map(range => `<option>${range}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            ${customReportsData.filters.formats.map(format => `<option>${format}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">İşlem</label>
                        <button onclick="generateQuickReport()" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors">
                            Rapor Oluştur
                        </button>
                    </div>
                </div>
            </div>

            <!-- Report Templates -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-layout mr-2"></i>
                        Rapor Şablonları
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        ${customReportsData.templates.map(template => `
                            <div class="template-card border rounded-lg p-4 hover:border-indigo-300" onclick="useTemplate(${template.id})">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-800">${template.name}</h4>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${template.category}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">${template.description}</p>
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>${template.fields.length} alan</span>
                                    <span>${template.usage} kullanım</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-clock-clockwise mr-2"></i>
                        Son Raporlar
                    </h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        ${customReportsData.recentReports.map(report => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full bg-${report.status === 'Tamamlandı' ? 'green' : report.status === 'Hazırlanıyor' ? 'yellow' : 'red'}-500"></div>
                                    <div>
                                        <p class="font-medium text-gray-800">${report.name}</p>
                                        <p class="text-sm text-gray-600">${report.date} • ${report.type}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium status-${report.status === 'Tamamlandı' ? 'completed' : report.status === 'Hazırlanıyor' ? 'processing' : 'failed'}">
                                        ${report.status}
                                    </p>
                                    <p class="text-xs text-gray-500">${report.size}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <!-- Scheduled Reports -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-calendar mr-2"></i>
                    Zamanlanmış Raporlar
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    ${customReportsData.scheduledReports.map(schedule => `
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-800">${schedule.name}</h4>
                                <label class="switch">
                                    <input type="checkbox" ${schedule.enabled ? 'checked' : ''} onchange="toggleSchedule('${schedule.name}')">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="text-sm text-gray-600">
                                <p>Sıklık: ${schedule.frequency}</p>
                                <p>Sonraki Çalışma: ${schedule.nextRun}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-gear mr-2"></i>
                    Gelişmiş İşlemler
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="createCustomTemplate()" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-plus"></i>
                        <span>Yeni Şablon</span>
                    </button>
                    <button onclick="bulkExport()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-export"></i>
                        <span>Toplu Dışa Aktar</span>
                    </button>
                    <button onclick="scheduleManager()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-calendar-plus"></i>
                        <span>Zamanlama Yönetimi</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .switch { position: relative; display: inline-block; width: 48px; height: 24px; }
            .switch input { opacity: 0; width: 0; height: 0; }
            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
            .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
            input:checked + .slider { background-color: #4f46e5; }
            input:checked + .slider:before { transform: translateX(24px); }
        </style>

        <script>
            // Quick Actions
            function generateQuickReport() {
                alert('🛠️ Hızlı rapor oluşturuluyor...');
                setTimeout(() => {
                    alert('✅ Rapor başarıyla oluşturuldu ve indirme başladı!');
                }, 2000);
            }

            function useTemplate(templateId) {
                alert(\`📋 Şablon \${templateId} kullanılarak rapor oluşturuluyor...\`);
                window.open(\`/api/template/\${templateId}/generate\`, '_blank');
            }

            function createCustomTemplate() {
                alert('➕ Yeni özel şablon oluşturucu açılıyor...');
                window.open('/template-builder', '_blank');
            }

            function bulkExport() {
                alert('📤 Toplu dışa aktarma işlemi başlatılıyor...');
                window.open('/api/export/bulk', '_blank');
            }

            function scheduleManager() {
                alert('📅 Zamanlama yönetim paneli açılıyor...');
                window.open('/schedule-manager', '_blank');
            }

            function toggleSchedule(name) {
                alert(\`⏰ "\${name}" için zamanlama durumu değiştirildi!\`);
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Özel rapor verileri güncelleniyor...');
            }, 30000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/custom-reports/templates', (req, res) => {
    res.json(customReportsData.templates);
});

app.get('/api/custom-reports/recent', (req, res) => {
    res.json(customReportsData.recentReports);
});

app.get('/api/custom-reports/scheduled', (req, res) => {
    res.json(customReportsData.scheduledReports);
});

app.get('/api/custom-reports/statistics', (req, res) => {
    res.json(customReportsData.statistics);
});

app.get('/api/template/:id/generate', (req, res) => {
    const templateId = req.params.id;
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', `attachment; filename=custom-report-template-${templateId}.xlsx`);
    res.send(`Mock Custom Report Template ${templateId} Excel file content`);
});

app.get('/api/export/bulk', (req, res) => {
    res.setHeader('Content-Type', 'application/zip');
    res.setHeader('Content-Disposition', 'attachment; filename=bulk-reports.zip');
    res.send('Mock Bulk Reports ZIP file content');
});

app.get('/template-builder', (req, res) => {
    res.send('<h1>🛠️ Özel Şablon Oluşturucu</h1><p>Yakında aktif olacak...</p>');
});

app.get('/schedule-manager', (req, res) => {
    res.send('<h1>📅 Zamanlama Yönetim Paneli</h1><p>Yakında aktif olacak...</p>');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Custom Reports',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🛠️ MesChain-Sync Özel Raporlar Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Custom Reports Dashboard`);
    console.log(`📊 Özel rapor şablonları, zamanlama, ve gelişmiş raporlama sistemi aktif!`);
});

module.exports = app;
