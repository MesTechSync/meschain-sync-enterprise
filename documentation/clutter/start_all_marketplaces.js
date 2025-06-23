/**
 * All const servers = [
    { name: 'Amazon TR', port: 3002, file: 'amazon_admin_server_3002.js' },
    { name: 'N11 Admin', port: 3003, file: 'n11_admin_server_3003.js' },
    { name: 'Hepsiburada', port: 3007, file: 'hepsiburada_server_3007.js' },
    { name: 'GittiGidiyor', port: 3008, file: 'gittigidiyor_server_3008.js' },
    { name: 'Trendyol Advanced', port: 3011, file: 'trendyol_server_3011.js' },
    { name: 'Modular Super Admin', port: 3024, file: 'modular_server_3024.js' }
];ace Servers Starter
 * Tüm marketplace sunucularını tek seferde başlatır
 * Date: 17 Haziran 2025
 */

const { spawn } = require('child_process');
const path = require('path');

console.log('🚀 Starting All MesChain-Sync Marketplace Servers...\n');

const servers = [
    { name: 'Amazon TR', port: 3002, file: 'amazon_admin_server_3002.js' },
    { name: 'N11 Enhanced Integration', port: 3014, file: 'enhanced_n11_server_3014.js' },
    { name: 'Hepsiburada Yeni', port: 3004, file: 'hepsiburada_admin_server_3004.js' },
    { name: 'Pazarama Yeni', port: 3005, file: 'pazarama_admin_server_3005.js' },
    { name: 'PttAVM Yeni', port: 3006, file: 'pttavm_admin_server_3006.js' },
    { name: 'eBay Yeni', port: 3007, file: 'ebay_admin_server_3007.js' },
    { name: 'GittiGidiyor Yeni', port: 3008, file: 'gittigidiyor_admin_server_3008.js' },
    { name: 'Trendyol Gelişmiş', port: 3009, file: 'enhanced_trendyol_server_3009.js' },
    { name: 'Modular Super Admin', port: 3024, file: 'modular_super_admin_server_3024.js' }
];

const processes = [];

servers.forEach(server => {
    console.log(`🎯 Starting ${server.name} on port ${server.port}...`);

    const serverProcess = spawn('node', [server.file], {
        cwd: process.cwd(),
        stdio: ['inherit', 'pipe', 'pipe']
    });

    serverProcess.stdout.on('data', (data) => {
        console.log(`[${server.name}] ${data.toString().trim()}`);
    });

    serverProcess.stderr.on('data', (data) => {
        console.error(`[${server.name} ERROR] ${data.toString().trim()}`);
    });

    serverProcess.on('close', (code) => {
        console.log(`[${server.name}] Process exited with code ${code}`);
    });

    processes.push({
        name: server.name,
        port: server.port,
        process: serverProcess
    });
});

console.log('\n✅ All marketplace servers starting...');
console.log('\n📋 Server List:');
servers.forEach(server => {
    console.log(`   • ${server.name}: http://localhost:${server.port}`);
});

console.log('\n🎯 Main Admin Panel: http://localhost:3024');
console.log('🔧 Trendyol Advanced: http://localhost:3011/trendyol-admin.html');
console.log('\n💡 Press Ctrl+C to stop all servers');

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down all servers...');
    processes.forEach(p => {
        console.log(`   Stopping ${p.name}...`);
        p.process.kill('SIGTERM');
    });

    setTimeout(() => {
        console.log('✅ All servers stopped');
        process.exit(0);
    }, 2000);
});

// Keep the process alive
process.stdin.resume();
