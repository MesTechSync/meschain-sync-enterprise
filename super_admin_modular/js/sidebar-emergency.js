/**
 * SIDEBAR BACKUP ÇÖZÜMÜ - EMERGENCY FİX
 * Eğer onclick events çalışmıyorsa bu dosya devreye girer
 */

// Emergency sidebar fix
function emergencySidebarFix() {
    console.log('🚨 Emergency sidebar fix activated');
    
    // Remove all existing onclick handlers
    document.querySelectorAll('.sidebar-section-header').forEach(header => {
        header.removeAttribute('onclick');
        
        // Add new event listener
        header.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const section = this.parentElement;
            const dropdown = section.querySelector('.sidebar-dropdown-menu');
            
            if (!dropdown) return;
            
            // Close all other sections
            document.querySelectorAll('.sidebar-section').forEach(s => {
                if (s !== section) {
                    s.classList.remove('active');
                    const otherDropdown = s.querySelector('.sidebar-dropdown-menu');
                    if (otherDropdown) {
                        otherDropdown.style.maxHeight = '0';
                        otherDropdown.style.opacity = '0';
                        otherDropdown.style.visibility = 'hidden';
                    }
                }
            });
            
            // Toggle current section
            const isActive = section.classList.contains('active');
            
            if (isActive) {
                // Close
                section.classList.remove('active');
                dropdown.style.maxHeight = '0';
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
            } else {
                // Open
                section.classList.add('active');
                dropdown.style.maxHeight = '500px';
                dropdown.style.opacity = '1';
                dropdown.style.visibility = 'visible';
                dropdown.style.transform = 'translateY(0)';
                dropdown.style.pointerEvents = 'auto';
            }
            
            console.log('Sidebar toggled:', section.classList.contains('active') ? 'opened' : 'closed');
        });
    });
    
    console.log('✅ Emergency sidebar fix completed');
}

// Add to global scope
window.emergencySidebarFix = emergencySidebarFix;

// Auto-run if needed
document.addEventListener('DOMContentLoaded', function() {
    // Test if normal sidebar works
    setTimeout(() => {
        const testHeader = document.querySelector('.sidebar-section-header');
        if (testHeader && !testHeader.onclick && !testHeader.getAttribute('onclick')) {
            console.log('🚨 Normal sidebar not working, activating emergency fix');
            emergencySidebarFix();
        }
    }, 2000);
});
