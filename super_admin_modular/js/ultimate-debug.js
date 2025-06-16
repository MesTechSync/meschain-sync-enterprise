/**
 * ULTIMATE SIDEBAR DEBUG TOOL
 * Her türlü CSS ve JS çakışmasını tespit eder
 */

function ultimateSidebarDebug() {
    const testLog = document.getElementById('sidebarTestLog');
    if (!testLog) return;
    
    testLog.style.display = 'block';
    testLog.innerHTML = '🔬 ULTIMATE SIDEBAR DEBUG<br>';
    
    // Test tüm dropdown'ları
    const dropdowns = document.querySelectorAll('.sidebar-dropdown-menu');
    testLog.innerHTML += `📋 ${dropdowns.length} dropdown bulundu<br>`;
    
    dropdowns.forEach((dropdown, index) => {
        const section = dropdown.closest('.sidebar-section');
        const header = section?.querySelector('.sidebar-section-header');
        const sectionTitle = header?.querySelector('span')?.textContent || `Section ${index}`;
        
        testLog.innerHTML += `<br>🔍 ${sectionTitle}:<br>`;
        
        // CSS durumu
        const styles = window.getComputedStyle(dropdown);
        testLog.innerHTML += `  CSS: display=${styles.display}<br>`;
        testLog.innerHTML += `  CSS: max-height=${styles.maxHeight}<br>`;
        testLog.innerHTML += `  CSS: opacity=${styles.opacity}<br>`;
        testLog.innerHTML += `  CSS: visibility=${styles.visibility}<br>`;
        
        // DOM durumu
        const rect = dropdown.getBoundingClientRect();
        testLog.innerHTML += `  Boyut: ${rect.width}x${rect.height}<br>`;
        
        // Parent durumu
        if (section) {
            testLog.innerHTML += `  Active: ${section.classList.contains('active')}<br>`;
            testLog.innerHTML += `  Classes: ${section.className}<br>`;
        }
        
        // Force open test
        if (header) {
            testLog.innerHTML += `  🧪 Manuel açma testi...<br>`;
            
            // Force active
            section.classList.add('active');
            dropdown.style.cssText = `
                display: block !important;
                max-height: 500px !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(0) !important;
                position: relative !important;
                z-index: 999 !important;
                background: #ffffff !important;
                border: 2px solid #ff0000 !important;
                padding: 10px !important;
                margin: 10px !important;
            `;
            
            setTimeout(() => {
                const newRect = dropdown.getBoundingClientRect();
                if (newRect.height > 0) {
                    testLog.innerHTML += `  ✅ Force open başarılı: ${newRect.height}px<br>`;
                } else {
                    testLog.innerHTML += `  ❌ Force open BAŞARISIZ!<br>`;
                }
            }, 100);
        }
    });
    
    testLog.innerHTML += '<br>🏁 Ultimate debug tamamlandı!<br>';
}

// Add button to trigger this
window.ultimateSidebarDebug = ultimateSidebarDebug;
