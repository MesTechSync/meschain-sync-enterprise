/**
 * MesChain-Sync Super Admin Panel - Notification System Module
 * Version: 4.1
 * Description: Advanced notification system with multiple types and animations
 */

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.meschain-notification');
    existingNotifications.forEach(notif => notif.remove());

    // Create notification
    const notification = document.createElement('div');
    notification.className = 'meschain-notification fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transition-all duration-300 transform translate-x-full';

    // Set colors based on type
    let bgColor, textColor, icon;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            icon = 'ph-check-circle';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            icon = 'ph-x-circle';
            break;
        case 'warning':
            bgColor = 'bg-yellow-500';
            textColor = 'text-white';
            icon = 'ph-warning';
            break;
        default:
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            icon = 'ph-info';
    }

    notification.className += ` ${bgColor} ${textColor}`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="ph ${icon} text-lg"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Toast notification system with different styles
function showToast(title, message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full';

    let bgClass, iconClass, borderClass;
    switch (type) {
        case 'success':
            bgClass = 'bg-green-50 dark:bg-green-900/20';
            iconClass = 'text-green-500';
            borderClass = 'border-l-4 border-green-500';
            break;
        case 'error':
            bgClass = 'bg-red-50 dark:bg-red-900/20';
            iconClass = 'text-red-500';
            borderClass = 'border-l-4 border-red-500';
            break;
        case 'warning':
            bgClass = 'bg-yellow-50 dark:bg-yellow-900/20';
            iconClass = 'text-yellow-500';
            borderClass = 'border-l-4 border-yellow-500';
            break;
        default:
            bgClass = 'bg-blue-50 dark:bg-blue-900/20';
            iconClass = 'text-blue-500';
            borderClass = 'border-l-4 border-blue-500';
    }

    toast.className += ` ${bgClass} ${borderClass}`;
    toast.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="ph ph-info ${iconClass} text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">${title}</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    // Auto remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Progress notification for long-running operations
function showProgressNotification(title, initialMessage = '') {
    const progressId = 'progress-' + Date.now();
    const notification = document.createElement('div');
    notification.id = progressId;
    notification.className = 'fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 max-w-sm transition-all duration-300';

    notification.innerHTML = `
        <div class="flex items-center space-x-3 mb-3">
            <div class="flex-shrink-0">
                <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">${title}</h4>
            </div>
        </div>
        <div class="space-y-2">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div id="${progressId}-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="${progressId}-message" class="text-xs text-gray-600 dark:text-gray-400">${initialMessage}</p>
        </div>
    `;

    document.body.appendChild(notification);

    return {
        updateProgress: (percentage, message = '') => {
            const bar = document.getElementById(`${progressId}-bar`);
            const messageEl = document.getElementById(`${progressId}-message`);
            if (bar) {bar.style.width = `${percentage}%`;}
            if (messageEl && message) {messageEl.textContent = message;}
        },
        complete: (message = 'Completed!') => {
            const notification = document.getElementById(progressId);
            if (notification) {
                // Clear existing content
                notification.innerHTML = '';
                
                // Create elements safely
                const container = document.createElement('div');
                container.className = 'flex items-center space-x-3';
                
                const iconContainer = document.createElement('div');
                iconContainer.className = 'flex-shrink-0';
                
                const icon = document.createElement('i');
                icon.className = 'ph ph-check-circle text-green-500 text-lg';
                
                const contentContainer = document.createElement('div');
                contentContainer.className = 'flex-1';
                
        error: (message = 'Operation failed!') => {
            const notification = document.getElementById(progressId);
            if (notification) {
                // Clear existing content
                notification.innerHTML = '';
                
                // Create elements safely
                const container = document.createElement('div');
                container.className = 'flex items-center space-x-3';
                
                const iconContainer = document.createElement('div');
                iconContainer.className = 'flex-shrink-0';
                
                const icon = document.createElement('i');
                icon.className = 'ph ph-x-circle text-red-500 text-lg';
                
                const contentContainer = document.createElement('div');
                contentContainer.className = 'flex-1';
                
                const messageEl = document.createElement('p');
                messageEl.className = 'text-sm font-medium text-gray-900 dark:text-gray-100';
                messageEl.textContent = message;
                
                // Assemble elements
                iconContainer.appendChild(icon);
                contentContainer.appendChild(messageEl);
                container.appendChild(iconContainer);
                container.appendChild(contentContainer);
                notification.appendChild(container);
                
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        },
            if (notification) {
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <i class="ph ph-x-circle text-red-500 text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">${message}</p>
                        </div>
                    </div>
                `;
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        },
        remove: () => {
            const notification = document.getElementById(progressId);
            if (notification) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }
    };
}

// Confirmation dialog system
function showConfirmDialog(title, message, onConfirm, onCancel = null) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4';

    const dialog = document.createElement('div');
    dialog.className = 'bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6';

    dialog.innerHTML = `
        <div class="flex items-center space-x-3 mb-4">
            <div class="flex-shrink-0">
                <i class="ph ph-question text-yellow-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">${title}</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 mb-6">${message}</p>
        <div class="flex space-x-3 justify-end">
            <button id="cancelBtn" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                Cancel
            </button>
            <button id="confirmBtn" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                Confirm
            </button>
        </div>
    `;

    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    // Event handlers
    const confirmBtn = dialog.querySelector('#confirmBtn');
    const cancelBtn = dialog.querySelector('#cancelBtn');

    const closeDialog = () => {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    };

    confirmBtn.addEventListener('click', () => {
        closeDialog();
        if (onConfirm) {onConfirm();}
    });

    cancelBtn.addEventListener('click', () => {
        closeDialog();
        if (onCancel) {onCancel();}
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeDialog();
            if (onCancel) {onCancel();}
        }
    });

    // Focus trap
    confirmBtn.focus();
}

// Initialize notification system
function initializeNotificationSystem() {
    // Add notification container styles if needed
    const style = document.createElement('style');
    style.innerHTML = `
        .meschain-notification {
            max-width: 400px;
            word-wrap: break-word;
            white-space: pre-line;
        }
        
        .meschain-notification .ph {
            flex-shrink: 0;
        }
    `;
    document.head.appendChild(style);
}

// Make functions globally available
window.showNotification = showNotification;
window.showToast = showToast;
window.showProgressNotification = showProgressNotification;
window.showConfirmDialog = showConfirmDialog;
window.initializeNotificationSystem = initializeNotificationSystem;
