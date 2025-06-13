<!DOCTYPE html>
<html lang="tr" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔗 MesChain-Sync Süper Admin Panel - Official v4.1</title>
    
    <!-- External Dependencies -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://static2.sharepointonline.com/files/fabric/office-ui-fabric-core/11.0.0/css/fabric.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- API Infrastructure -->
    <script src="meschain_api_infrastructure.js"></script>
    
    <style>
        /* Birleştirilmiş ve optimize edilmiş CSS */
        :root {
            /* Light Theme Variables */
            --bg-primary-light: #ffffff;
            --bg-secondary-light: #f8f9fa;
            --bg-tertiary-light: #e9ecef;
            --text-primary-light: #212529;
            --text-secondary-light: #6c757d;
            --border-light: #dee2e6;
            --shadow-light: rgba(0, 0, 0, 0.1);
            
            /* Dark Theme Variables */
            --bg-primary-dark: #1a1a1a;
            --bg-secondary-dark: #2d2d30;
            --bg-tertiary-dark: #3e3e42;
            --text-primary-dark: #ffffff;
            --text-secondary-dark: #cccccc;
            --border-dark: #484848;
            --shadow-dark: rgba(0, 0, 0, 0.3);
            
            /* Accent Colors */
            --accent-primary: #6d28d9;
            --accent-secondary: #8b5cf6;
            --accent-glow: rgba(109, 40, 217, 0.1);
        }

        /* Light Theme */
        [data-theme="light"] {
            --bg-primary: var(--bg-primary-light);
            --bg-secondary: var(--bg-secondary-light);
            --bg-tertiary: var(--bg-tertiary-light);
            --text-primary: var(--text-primary-light);
            --text-secondary: var(--text-secondary-light);
            --border-color: var(--border-light);
            --shadow-color: var(--shadow-light);
        }

        /* Dark Theme */
        [data-theme="dark"] {
            --bg-primary: var(--bg-primary-dark);
            --bg-secondary: var(--bg-secondary-dark);
            --bg-tertiary: var(--bg-tertiary-dark);
            --text-primary: var(--text-primary-dark);
            --text-secondary: var(--text-secondary-dark);
            --border-color: var(--border-dark);
            --shadow-color: var(--shadow-dark);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Glassmorphism Components */
        .meschain-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar Styles */
        .meschain-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 64px);
            scrollbar-width: thin;
            scrollbar-color: var(--accent-primary) transparent;
        }

        .meschain-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .meschain-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .meschain-sidebar::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 10px;
        }

        /* Navigation Links */
        .meschain-nav-link {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            margin: 4px 8px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .meschain-nav-link:hover {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-primary);
            transform: translateX(4px);
        }

        .meschain-nav-link.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.2) 0%, transparent 100%);
            color: var(--accent-primary);
            border-left: 3px solid var(--accent-primary);
        }

        /* Sidebar Sections */
        .sidebar-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: 4px 8px;
        }

        .sidebar-section-header:hover {
            background: rgba(139, 92, 246, 0.05);
        }

        .sidebar-dropdown-menu {
            display: none;
            padding-left: 20px;
        }

        .sidebar-section.active .sidebar-dropdown-menu {
            display: block;
        }

        .sidebar-section.active .sidebar-section-header i.ph-caret-down {
            transform: rotate(180deg);
        }

        /* Theme Toggle */
        .theme-toggle {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .theme-toggle:hover {
            background: var(--bg-tertiary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .meschain-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .meschain-sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        /* Animation Classes */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Status Indicators */
        .status-indicator {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-indicator.success {
            background: #dcfce7;
            color: #166534;
        }

        .status-indicator.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-indicator.error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900" data-theme="light">
    <!-- Loading Screen -->
    <div id="initial-loader" class="fixed inset-0 bg-white dark:bg-gray-900 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">MesChain-Sync yükleniyor...</p>
        </div>
    </div>

    <!-- Emergency Display -->
    <div id="emergency-display" class="fixed inset-0 bg-red-600 text-white z-40 flex items-center justify-center" style="display: none;">
        <div class="text-center">
            <h1 class="text-2xl font-bold mb-4">🚨 Sistem Hatası</h1>
            <p>Lütfen sayfayı yenileyin veya destek ekibiyle iletişime geçin.</p>
        </div>
    </div>

    <!-- Header -->
    <header class="meschain-glass fixed top-0 left-0 right-0 z-40 h-16">
        <div class="flex items-center justify-between h-full px-6">
            <!-- Logo and Title -->
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="ph ph-list text-xl"></i>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">M</span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">MesChain-Sync</h1>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="theme-toggle">
                    <i class="ph ph-sun theme-icon"></i>
                    <span class="theme-text">Light</span>
                </button>

                <!-- User Profile -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">SA</span>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Super Admin</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Full Access</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex pt-16">
        <!-- Sidebar -->
        <nav class="meschain-sidebar fixed left-0 top-16 w-64 h-full z-30">
            <div class="p-4">
                <!-- Dashboard Section -->
                <div class="mb-6">
                    <div class="sidebar-section-header">
                        <div class="flex items-center">
                            <i class="ph ph-squares-four mr-3 text-purple-600"></i>
                            <span class="font-semibold">Dashboard</span>
                        </div>
                    </div>
                    <div class="pl-4">
                        <a href="#dashboard" class="meschain-nav-link active">
                            <i class="ph ph-chart-line mr-3"></i>
                            <span>Ana Dashboard</span>
                        </a>
                        <a href="#analytics" class="meschain-nav-link">
                            <i class="ph ph-chart-bar mr-3"></i>
                            <span>Analitik</span>
                        </a>
                    </div>
                </div>

                <!-- System Management -->
                <div class="mb-6 sidebar-section">
                    <div class="sidebar-section-header" onclick="toggleSidebarSection(this)">
                        <div class="flex items-center">
                            <i class="ph ph-gear mr-3 text-blue-600"></i>
                            <span class="font-semibold">Sistem Yönetimi</span>
                        </div>
                        <i class="ph ph-caret-down transition-transform"></i>
                    </div>
                    <div class="sidebar-dropdown-menu">
                        <a href="#users" class="meschain-nav-link">
                            <i class="ph ph-users mr-3"></i>
                            <span>Kullanıcılar</span>
                        </a>
                        <a href="#settings" class="meschain-nav-link">
                            <i class="ph ph-gear-six mr-3"></i>
                            <span>Ayarlar</span>
                        </a>
                        <a href="#logs" class="meschain-nav-link">
                            <i class="ph ph-file-text mr-3"></i>
                            <span>Loglar</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Dashboard Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        MesChain-Sync Enterprise Dashboard
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Süper Admin Panel - Sistem durumu ve performans izleme
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="meschain-glass p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Toplam Kullanıcı</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">1,247</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <i class="ph ph-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="meschain-glass p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Aktif Sistemler</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">23</p>
                            </div>
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <i class="ph ph-check-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="meschain-glass p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Güvenlik Skoru</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">97.5%</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <i class="ph ph-shield text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="meschain-glass p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Performans</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">99.1%</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                                <i class="ph ph-lightning text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="meschain-glass p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sistem Durumu</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="font-medium">API Gateway</span>
                            </div>
                            <span class="status-indicator success">Aktif</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <span class="font-medium">Database</span>
                            </div>
                            <span class="status-indicator warning">Yavaş</span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="font-medium">Cache</span>
                            </div>
                            <span class="status-indicator success">Optimal</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Theme Management
        function initializeTheme() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeIcon = document.querySelector('.theme-icon');
            const themeText = document.querySelector('.theme-text');
            
            let isDark = localStorage.getItem('theme') === 'dark';
            
            function updateTheme() {
                if (isDark) {
                    body.setAttribute('data-theme', 'dark');
                    body.classList.add('dark');
                    themeIcon.className = 'ph ph-moon theme-icon';
                    themeText.textContent = 'Dark';
                } else {
                    body.setAttribute('data-theme', 'light');
                    body.classList.remove('dark');
                    themeIcon.className = 'ph ph-sun theme-icon';
                    themeText.textContent = 'Light';
                }
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }
            
            themeToggle.addEventListener('click', () => {
                isDark = !isDark;
                updateTheme();
            });
            
            updateTheme();
        }

        // Sidebar Management
        function toggleSidebarSection(header) {
            const section = header.closest('.sidebar-section');
            const isActive = section.classList.contains('active');
            
            // Close all sections
            document.querySelectorAll('.sidebar-section').forEach(s => {
                s.classList.remove('active');
            });
            
            // Open clicked section if it wasn't active
            if (!isActive) {
                section.classList.add('active');
            }
        }

        // Mobile Sidebar Toggle
        function initializeMobileSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.meschain-sidebar');
            
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
            });
        }

        // Loading Screen
        function hideLoadingScreen() {
            const loader = document.getElementById('initial-loader');
            if (loader) {
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.remove(), 500);
                }, 1000);
            }
        }

        // Initialize on DOM Load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 MesChain-Sync Dashboard Loaded');
            
            initializeTheme();
            initializeMobileSidebar();
            hideLoadingScreen();
            
            // Add fade-in animation to main content
            document.querySelector('main').classList.add('fade-in-up');
        });

        // Error Handling
        window.addEventListener('error', function(event) {
            console.error('🚨 Page Error:', event.error);
            const emergency = document.getElementById('emergency-display');
            if (emergency) emergency.style.display = 'flex';
        });
    </script>
</body>
</html>

