// 📤 MesChain-Sync Veri Dışa Aktarma Sunucusu - Port 3025
// Enterprise Grade Data Export Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3025;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 📤 VERİ DIŞA AKTARMA MOCK DATABASE
const exportData = {
    availableDataSets: [
        { name: 'Tüm Satış Verileri', size: '24.7 MB', records: 15432, lastUpdated: '2025-06-13 14:30' },
        { name: 'Envanter Durumu', size: '8.3 MB', records: 3247, lastUpdated: '2025-06-13 14:25' },
        { name: 'Müşteri Verileri', size: '12.1 MB', records: 8965, lastUpdated: '2025-06-13 14:20' },
        { name: 'Ürün Kataloğu', size: '18.9 MB', records: 3247, lastUpdated: '2025-06-13 14:15' },
        { name: 'Finansal Kayıtlar', size: '5.4 MB', records: 2156, lastUpdated: '2025-06-13 14:10' },
        { name: 'Marketplace Verileri', size: '31.2 MB', records: 22847, lastUpdated: '2025-06-13 14:05' }
    ],
    exportFormats: [
        { format: 'Excel (.xlsx)', icon: 'ph-microsoft-excel-logo', supported: true, popular: true },
        { format: 'CSV', icon: 'ph-file-csv', supported: true, popular: true },
        { format: 'JSON', icon: 'ph-file-js', supported: true, popular: false },
        { format: 'XML', icon: 'ph-file-xml', supported: true, popular: false },
        { format: 'PDF', icon: 'ph-file-pdf', supported: true, popular: true },
        { format: 'SQL Dump', icon: 'ph-database', supported: true, popular: false }
    ],
    recentExports: [
        { fileName: 'sales_data_june_2025.xlsx', size: '24.7 MB', date: '2025-06-13 14:30', status: 'Tamamlandı', downloads: 3 },
        { fileName: 'inventory_report_daily.csv', size: '8.3 MB', date: '2025-06-13 12:15', status: 'Tamamlandı', downloads: 1 },
        { fileName: 'customer_analysis_q2.pdf', size: '12.1 MB', date: '2025-06-13 10:45', status: 'Tamamlandı', downloads: 5 },
        { fileName: 'product_catalog_full.json', size: '18.9 MB', date: '2025-06-12 16:20', status: 'Tamamlandı', downloads: 2 },
        { fileName: 'financial_summary_may.xlsx', size: '5.4 MB', date: '2025-06-12 14:30', status: 'Arşivlendi', downloads: 8 }
    ],
    scheduledExports: [
        { name: 'Günlük Satış Verileri', frequency: 'Günlük', time: '09:00', format: 'Excel', enabled: true },
        { name: 'Haftalık Envanter Raporu', frequency: 'Haftalık', time: '08:00', format: 'CSV', enabled: true },
        { name: 'Aylık Müşteri Analizi', frequency: 'Aylık', time: '10:00', format: 'PDF', enabled: false },
        { name: 'Çeyreklik Mali Rapor', frequency: 'Çeyreklik', time: '09:30', format: 'Excel', enabled: true }
    ],
    statistics: {
        totalExports: 1847,
        totalSize: '2.4 TB',
        avgExportTime: 42,
        successRate: 98.7,
        popularFormat: 'Excel (.xlsx)',
        largestExport: '247 MB'
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
        <title>📤 MesChain-Sync Veri Dışa Aktarma - Port 3025</title>
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
            .dataset-card {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .dataset-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }
            .format-option {
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .format-option:hover {
                transform: scale(1.02);
            }
            .format-option.selected {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
                transform: scale(1.05);
            }
            .progress-bar {
                height: 6px;
                background: #e5e7eb;
                border-radius: 3px;
                overflow: hidden;
            }
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #10b981, #059669);
                transition: width 0.3s ease;
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-cyan-50 to-blue-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-download mr-3 text-cyan-600"></i>
                            Veri Dışa Aktarma Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Data Export Center - Port 3025</p>
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
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-cyan-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Dışa Aktarım</h3>
                            <p class="text-3xl font-bold text-cyan-600">${exportData.statistics.totalExports}</p>
                            <p class="text-sm text-cyan-600 mt-1">Başarılı işlem</p>
                        </div>
                        <div class="bg-cyan-100 p-3 rounded-full">
                            <i class="ph ph-export text-2xl text-cyan-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Veri</h3>
                            <p class="text-3xl font-bold text-blue-600">${exportData.statistics.totalSize}</p>
                            <p class="text-sm text-blue-600 mt-1">Aktarılan veri</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="ph ph-hard-drives text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Başarı Oranı</h3>
                            <p class="text-3xl font-bold text-green-600">%${exportData.statistics.successRate}</p>
                            <p class="text-sm text-green-600 mt-1">Hatasız işlem</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-check-circle text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Ortalama Süre</h3>
                            <p class="text-3xl font-bold text-purple-600">${exportData.statistics.avgExportTime}s</p>
                            <p class="text-sm text-purple-600 mt-1">İşlem süresi</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="ph ph-clock text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Selection -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-database mr-2"></i>
                    Dışa Aktarılacak Veri Seti Seçin
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${exportData.availableDataSets.map((dataset, index) => `
                        <div class="dataset-card border-2 rounded-lg p-4 hover:border-cyan-300" onclick="selectDataset(${index})">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-800">${dataset.name}</h4>
                                <input type="checkbox" class="w-4 h-4 text-cyan-600 rounded" id="dataset-${index}">
                            </div>
                            <div class="space-y-1 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Boyut:</span>
                                    <span class="font-medium">${dataset.size}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kayıt:</span>
                                    <span class="font-medium">${dataset.records.toLocaleString()}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Güncelleme:</span>
                                    <span class="font-medium">${dataset.lastUpdated}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Format Selection -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-file-arrow-down mr-2"></i>
                    Dışa Aktarma Formatı Seçin
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    ${exportData.exportFormats.map((format, index) => `
                        <div class="format-option border-2 rounded-lg p-4 text-center ${format.popular ? 'border-cyan-200 bg-cyan-50' : 'border-gray-200'}" onclick="selectFormat(${index})">
                            <div class="mb-2">
                                <i class="${format.icon} text-3xl ${format.popular ? 'text-cyan-600' : 'text-gray-600'}"></i>
                            </div>
                            <h4 class="font-medium text-gray-800 text-sm">${format.format}</h4>
                            ${format.popular ? '<span class="text-xs text-cyan-600 font-medium">Popüler</span>' : ''}
                            ${!format.supported ? '<span class="text-xs text-red-500">Yakında</span>' : ''}
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Export Options -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-gear mr-2"></i>
                    Dışa Aktarma Seçenekleri
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tarih Aralığı</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option>Tüm Veriler</option>
                            <option>Son 7 Gün</option>
                            <option>Son 30 Gün</option>
                            <option>Son 3 Ay</option>
                            <option>Bu Yıl</option>
                            <option>Özel Tarih Aralığı</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sıkıştırma</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option>Sıkıştırma Yok</option>
                            <option>ZIP Arşivi</option>
                            <option>RAR Arşivi</option>
                            <option>7z Arşivi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-posta Bildirimi</label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-cyan-600 rounded" checked>
                                <span class="ml-2 text-sm text-gray-700">Tamamlandığında bildir</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-center">
                    <button onclick="startExport()" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-all flex items-center space-x-2">
                        <i class="ph ph-download-simple"></i>
                        <span>Dışa Aktarmayı Başlat</span>
                    </button>
                </div>
            </div>

            <!-- Recent Exports & Scheduled -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-clock-clockwise mr-2"></i>
                        Son Dışa Aktarımlar
                    </h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        ${exportData.recentExports.map(exp => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full bg-${exp.status === 'Tamamlandı' ? 'green' : exp.status === 'Arşivlendi' ? 'blue' : 'yellow'}-500"></div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm">${exp.fileName}</p>
                                        <p class="text-xs text-gray-600">${exp.date} • ${exp.size}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button onclick="downloadFile('${exp.fileName}')" class="text-cyan-600 hover:text-cyan-800 text-sm">
                                        <i class="ph ph-download"></i>
                                    </button>
                                    <p class="text-xs text-gray-500">${exp.downloads} indirme</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-calendar mr-2"></i>
                        Zamanlanmış Dışa Aktarımlar
                    </h3>
                    <div class="space-y-3">
                        ${exportData.scheduledExports.map(schedule => `
                            <div class="border rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-800 text-sm">${schedule.name}</h4>
                                    <label class="switch">
                                        <input type="checkbox" ${schedule.enabled ? 'checked' : ''}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Sıklık:</span>
                                        <span>${schedule.frequency}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Saat:</span>
                                        <span>${schedule.time}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Format:</span>
                                        <span>${schedule.format}</span>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <!-- Export Progress (Hidden by default) -->
            <div id="exportProgress" class="bg-white rounded-lg shadow-lg p-6 mb-8 hidden">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-spinner mr-2"></i>
                    Dışa Aktarma İlerleyişi
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Veri Hazırlama</span>
                            <span id="progressPercent">0%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressBar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p>Tahmini kalan süre: <span id="estimatedTime">--</span></p>
                        <p>İşlenen kayıt: <span id="processedRecords">0</span> / <span id="totalRecords">0</span></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-lightning mr-2"></i>
                    Hızlı İşlemler
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="bulkExport()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-stack"></i>
                        <span>Toplu Dışa Aktarım</span>
                    </button>
                    <button onclick="scheduleExport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-calendar-plus"></i>
                        <span>Zamanlama Ekle</span>
                    </button>
                    <button onclick="exportHistory()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-clock-clockwise"></i>
                        <span>Dışa Aktarım Geçmişi</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
            .switch input { opacity: 0; width: 0; height: 0; }
            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
            .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
            input:checked + .slider { background-color: #06b6d4; }
            input:checked + .slider:before { transform: translateX(20px); }
        </style>

        <script>
            let selectedDatasets = [];
            let selectedFormat = 0;

            function selectDataset(index) {
                const checkbox = document.getElementById(\`dataset-\${index}\`);
                checkbox.checked = !checkbox.checked;
                
                if (checkbox.checked) {
                    selectedDatasets.push(index);
                } else {
                    selectedDatasets = selectedDatasets.filter(i => i !== index);
                }
                
                console.log('Seçili veri setleri:', selectedDatasets);
            }

            function selectFormat(index) {
                // Remove previous selection
                document.querySelectorAll('.format-option').forEach(el => el.classList.remove('selected'));
                
                // Add selection to clicked format
                event.currentTarget.classList.add('selected');
                selectedFormat = index;
                
                console.log('Seçili format:', ${JSON.stringify(exportData.exportFormats)}[index].format);
            }

            function startExport() {
                if (selectedDatasets.length === 0) {
                    alert('⚠️ Lütfen en az bir veri seti seçin!');
                    return;
                }

                // Show progress
                document.getElementById('exportProgress').classList.remove('hidden');
                
                // Simulate export progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);
                        setTimeout(() => {
                            alert('✅ Dışa aktarım tamamlandı! Dosya indiriliyor...');
                            document.getElementById('exportProgress').classList.add('hidden');
                        }, 500);
                    }
                    
                    document.getElementById('progressBar').style.width = progress + '%';
                    document.getElementById('progressPercent').textContent = Math.round(progress) + '%';
                    document.getElementById('estimatedTime').textContent = Math.round((100 - progress) / 10) + ' saniye';
                    document.getElementById('processedRecords').textContent = Math.round(progress * 100);
                    document.getElementById('totalRecords').textContent = '10000';
                }, 500);
            }

            function downloadFile(fileName) {
                alert(\`📤 "\${fileName}" dosyası indiriliyor...\`);
                window.open(\`/api/download/\${fileName}\`, '_blank');
            }

            function bulkExport() {
                alert('📦 Toplu dışa aktarım işlemi başlatılıyor...');
            }

            function scheduleExport() {
                alert('📅 Yeni zamanlama ekleme paneli açılıyor...');
            }

            function exportHistory() {
                alert('📋 Dışa aktarım geçmişi açılıyor...');
                window.open('/export-history', '_blank');
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Dışa aktarım verileri güncelleniyor...');
            }, 30000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/export/datasets', (req, res) => {
    res.json(exportData.availableDataSets);
});

app.get('/api/export/formats', (req, res) => {
    res.json(exportData.exportFormats);
});

app.get('/api/export/recent', (req, res) => {
    res.json(exportData.recentExports);
});

app.get('/api/export/scheduled', (req, res) => {
    res.json(exportData.scheduledExports);
});

app.get('/api/export/statistics', (req, res) => {
    res.json(exportData.statistics);
});

app.get('/api/download/:filename', (req, res) => {
    const filename = req.params.filename;
    res.setHeader('Content-Type', 'application/octet-stream');
    res.setHeader('Content-Disposition', `attachment; filename=${filename}`);
    res.send(`Mock file content for ${filename}`);
});

app.get('/export-history', (req, res) => {
    res.send('<h1>📋 Dışa Aktarım Geçmişi</h1><p>Yakında aktif olacak...</p>');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Data Export',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`📤 MesChain-Sync Veri Dışa Aktarma Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Data Export Dashboard`);
    console.log(`📊 Veri dışa aktarma, zamanlama, ve bulk export sistemi aktif!`);
});

module.exports = app;
