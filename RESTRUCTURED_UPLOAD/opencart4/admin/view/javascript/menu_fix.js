// Menu fix script for OpenCart 4.0.2.3
document.addEventListener('DOMContentLoaded', function() {
    // Fix for Extension menu - Ensure Cron Jobs link is present
    const extensionMenu = document.querySelector('#menu-extension');
    if (extensionMenu) {
        const extensionSubmenu = extensionMenu.querySelector('ul');
        if (extensionSubmenu) {
            // Check if Cron link already exists
            const cronExists = Array.from(extensionSubmenu.querySelectorAll('a')).some(a => a.textContent.includes('Cron'));
            
            if (!cronExists) {
                // Get user token from any existing link
                let userToken = '';
                const links = document.querySelectorAll('a[href*="user_token="]');
                if (links.length > 0) {
                    const href = links[0].getAttribute('href');
                    const match = href.match(/user_token=([^&]*)/);
                    if (match) {
                        userToken = match[1];
                    }
                }
                
                if (userToken) {
                    // Create Cron Jobs link
                    const cronItem = document.createElement('li');
                    const cronLink = document.createElement('a');
                    cronLink.href = 'index.php?route=marketplace/cron&user_token=' + userToken;
                    cronLink.textContent = 'Cron Jobs';
                    cronItem.appendChild(cronLink);
                    extensionSubmenu.appendChild(cronItem);
                }
            }
        }
    }
    
    // Fix for User section links in System menu
    const systemMenu = document.querySelector('#menu-system');
    if (systemMenu) {
        const systemSubmenu = systemMenu.querySelector('ul');
        if (systemSubmenu) {
            // Find Users submenu
            let usersSection = null;
            
            Array.from(systemSubmenu.children).forEach(li => {
                const firstLink = li.querySelector('a');
                if (firstLink && firstLink.textContent.includes('Users')) {
                    usersSection = li;
                }
            });
            
            if (usersSection) {
                const usersSubmenu = usersSection.querySelector('ul');
                
                if (usersSubmenu) {
                    // Get user token from any existing link
                    let userToken = '';
                    const links = document.querySelectorAll('a[href*="user_token="]');
                    if (links.length > 0) {
                        const href = links[0].getAttribute('href');
                        const match = href.match(/user_token=([^&]*)/);
                        if (match) {
                            userToken = match[1];
                        }
                    }
                    
                    if (userToken) {
                        // Check if Users link exists
                        const usersExists = Array.from(usersSubmenu.querySelectorAll('a')).some(a => 
                            a.textContent.includes('Users') && a.getAttribute('href').includes('user/user'));
                        
                        if (!usersExists) {
                            // Add Users link
                            const usersItem = document.createElement('li');
                            const usersLink = document.createElement('a');
                            usersLink.href = 'index.php?route=user/user&user_token=' + userToken;
                            usersLink.textContent = 'Users';
                            usersItem.appendChild(usersLink);
                            usersSubmenu.appendChild(usersItem);
                        }
                        
                        // Check if User Groups link exists
                        const userGroupsExists = Array.from(usersSubmenu.querySelectorAll('a')).some(a => 
                            a.textContent.includes('User Groups') && a.getAttribute('href').includes('user/user_permission'));
                        
                        if (!userGroupsExists) {
                            // Add User Groups link
                            const userGroupsItem = document.createElement('li');
                            const userGroupsLink = document.createElement('a');
                            userGroupsLink.href = 'index.php?route=user/user_permission&user_token=' + userToken;
                            userGroupsLink.textContent = 'User Groups';
                            userGroupsItem.appendChild(userGroupsLink);
                            usersSubmenu.appendChild(userGroupsItem);
                        }
                        
                        // Check if API link exists
                        const apiExists = Array.from(usersSubmenu.querySelectorAll('a')).some(a => 
                            a.textContent.includes('API') && a.getAttribute('href').includes('user/api'));
                        
                        if (!apiExists) {
                            // Add API link
                            const apiItem = document.createElement('li');
                            const apiLink = document.createElement('a');
                            apiLink.href = 'index.php?route=user/api&user_token=' + userToken;
                            apiLink.textContent = 'API';
                            apiItem.appendChild(apiLink);
                            usersSubmenu.appendChild(apiItem);
                        }
                    }
                }
            }
        }
    }
});
