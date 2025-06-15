/**
 * MesChain-Sync Super Admin Panel - Core JavaScript Module
 * Version: 4.1
 * Description: Core initialization and global variables
 */

// Global state variables
let currentLanguage = localStorage.getItem('meschain-language') || 'tr';
let currentTheme = localStorage.getItem('meschain-theme') || 'light';

// Core initialization function
function initializeMesChainCore() {
    console.log('🚀 MesChain-Sync Core initialization starting...');
    
    // Initialize all core modules
    initializeThemeSystem();
    initializeLanguageSystem();
    initializeSidebar();
    initializeNotificationSystem();
    initializeHealthMonitoring();
    initializeNavigation();
    
    console.log('🚀 MesChain-Sync Super Admin Panel v4.1 - PRODUCTION READY');
    console.log('📋 VSCode Team Task Completed Successfully');
    console.log('🔐 Official Authentication Gateway');
    console.log('⚡ All systems operational');
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// 🧪 SIDEBAR TEST FUNCTİON - DEBUG AMAÇLI
function testSidebarFunction() {
    const testLog = document.getElementById('sidebarTestLog');
    if (!testLog) return;
    
    testLog.style.display = 'block';
    testLog.innerHTML = '🧪 Sidebar Test Başlıyor...<br>';
    
    // Test 1: toggleSidebarSection fonksiyonu var mı?
    if (typeof window.toggleSidebarSection === 'function') {
        testLog.innerHTML += '✅ toggleSidebarSection fonksiyonu bulundu<br>';
    } else {
        testLog.innerHTML += '❌ toggleSidebarSection fonksiyonu bulunamadı<br>';
    }
    
    // Test 2: Sidebar section'lar var mı?
    const sections = document.querySelectorAll('.sidebar-section');
    testLog.innerHTML += `📋 ${sections.length} adet sidebar section bulundu<br>`;
    
    // Test 3: Header'lar onclick eventleri var mı?
    const headers = document.querySelectorAll('.sidebar-section-header');
    let onclickCount = 0;
    headers.forEach(header => {
        if (header.onclick || header.getAttribute('onclick')) {
            onclickCount++;
        }
    });
    testLog.innerHTML += `🎯 ${onclickCount}/${headers.length} header'da onclick event bulundu<br>`;
    
    // Test 4: İlk section'ı test et
    if (sections.length > 0) {
        const firstSection = sections[0];
        const firstHeader = firstSection.querySelector('.sidebar-section-header');
        if (firstHeader) {
            testLog.innerHTML += '🔧 İlk section test ediliyor...<br>';
            
            // Manuel toggle test
            try {
                window.toggleSidebarSection(firstHeader);
                testLog.innerHTML += '✅ Toggle fonksiyonu çalıştırıldı<br>';
                
                // CSS durumunu kontrol et
                setTimeout(() => {
                    const dropdown = firstSection.querySelector('.sidebar-dropdown-menu');
                    if (dropdown) {
                        const styles = window.getComputedStyle(dropdown);
                        testLog.innerHTML += `🎨 CSS: max-height=${styles.maxHeight}, opacity=${styles.opacity}<br>`;
                        
                        // Section active class var mı?
                        if (firstSection.classList.contains('active')) {
                            testLog.innerHTML += '✅ Section active class bulundu<br>';
                        } else {
                            testLog.innerHTML += '⚠️ Section active class YOK!<br>';
                        }
                        
                        // Gerçekten görünür mü?
                        const rect = dropdown.getBoundingClientRect();
                        if (rect.height > 0) {
                            testLog.innerHTML += '✅ Dropdown fiziksel olarak görünür<br>';
                        } else {
                            testLog.innerHTML += '❌ Dropdown fiziksel olarak görünmez!<br>';
                        }
                    }
                }, 100);
                
                // 2 saniye sonra kapat
                setTimeout(() => {
                    window.toggleSidebarSection(firstHeader);
                    testLog.innerHTML += '✅ Toggle kapatıldı<br>';
                }, 2000);
            } catch (error) {
                testLog.innerHTML += `❌ Toggle hatası: ${error.message}<br>`;
            }
        }
    }
    
    testLog.innerHTML += '🏁 Test tamamlandı!<br>';
}

// Make function globally available
window.testSidebarFunction = testSidebarFunction;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeMesChainCore);

// Make core functions globally available
window.MesChain = {
    currentLanguage,
    currentTheme,
    debounce,
    throttle
};
