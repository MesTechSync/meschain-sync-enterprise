/**
 * MesChain-Sync Connectivity Check Utility
 * Handles internet connection and local server connectivity
 */

const portConfig = require('../../config/ports.json');

class ConnectivityChecker {
  constructor() {
    this.ports = portConfig.development;
    this.services = portConfig.services;
    this.retryCount = 3;
    this.retryDelay = 1000;
  }

  /**
   * Check local server connectivity
   */
  async checkLocalServer(port, path = '/') {
    const url = `http://localhost:${port}${path}`;
    
    try {
      const response = await fetch(url, {
        method: 'HEAD',
        mode: 'no-cors',
        timeout: 5000
      });
      
      return {
        status: 'connected',
        port: port,
        url: url,
        timestamp: new Date()
      };
    } catch (error) {
      return {
        status: 'failed',
        port: port,
        url: url,
        error: error.message,
        timestamp: new Date()
      };
    }
  }

  /**
   * Check all MesChain-Sync services
   */
  async checkAllServices() {
    const results = {};
    
    for (const [serviceName, config] of Object.entries(this.services)) {
      console.log(`🔍 Checking ${serviceName} on port ${config.port}...`);
      
      const result = await this.checkLocalServer(config.port, config.health_check);
      results[serviceName] = {
        ...result,
        description: config.description
      };
      
      console.log(`${result.status === 'connected' ? '✅' : '❌'} ${serviceName}: ${result.status}`);
    }
    
    return results;
  }

  /**
   * Check internet connectivity (external)
   */
  async checkInternetConnection() {
    const testUrls = [
      'https://google.com',
      'https://github.com',
      'https://npmjs.com'
    ];
    
    for (const url of testUrls) {
      try {
        const response = await fetch(url, {
          method: 'HEAD',
          mode: 'no-cors',
          timeout: 3000
        });
        
        return {
          status: 'connected',
          url: url,
          timestamp: new Date()
        };
      } catch (error) {
        continue;
      }
    }
    
    return {
      status: 'failed',
      error: 'No internet connection available',
      timestamp: new Date()
    };
  }

  /**
   * Start server on specific port
   */
  async startServer(port, directory = '.') {
    const { spawn } = require('child_process');
    
    console.log(`🚀 Starting server on port ${port}...`);
    
    const server = spawn('live-server', [
      '--port=' + port,
      '--entry-file=index.html',
      '--open=false',
      '--quiet'
    ], {
      cwd: directory,
      stdio: 'inherit'
    });
    
    server.on('error', (error) => {
      console.error(`❌ Server error on port ${port}:`, error.message);
    });
    
    server.on('close', (code) => {
      console.log(`🛑 Server on port ${port} closed with code ${code}`);
    });
    
    return server;
  }

  /**
   * Start all MesChain-Sync services
   */
  async startAllServices() {
    console.log('🚀 Starting all MesChain-Sync services...\n');
    
    const servers = {};
    
    // Start main application
    servers.main = await this.startServer(this.ports.main, '.');
    console.log(`✅ Main app started on http://localhost:${this.ports.main}`);
    
    // Start configuration panel
    servers.config = await this.startServer(this.ports.config, './config');
    console.log(`✅ Config panel started on http://localhost:${this.ports.config}/configuration`);
    
    // Start panel manager
    servers.panels = await this.startServer(this.ports.panels, './src/demo');
    console.log(`✅ Panel manager started on http://localhost:${this.ports.panels}/panels`);
    
    console.log('\n🎉 All services started successfully!');
    console.log('\n📋 Service URLs:');
    console.log(`   Main App: http://localhost:${this.ports.main}`);
    console.log(`   Configuration: http://localhost:${this.ports.config}/configuration`);
    console.log(`   Panel Manager: http://localhost:${this.ports.panels}/panels`);
    
    return servers;
  }

  /**
   * Health check for all services
   */
  async healthCheck() {
    console.log('🏥 Running health check on all services...\n');
    
    const results = await this.checkAllServices();
    const internetStatus = await this.checkInternetConnection();
    
    console.log('\n📊 Health Check Results:');
    console.log(`Internet: ${internetStatus.status === 'connected' ? '✅ Connected' : '❌ Disconnected'}`);
    
    for (const [service, result] of Object.entries(results)) {
      console.log(`${service}: ${result.status === 'connected' ? '✅ Online' : '❌ Offline'}`);
    }
    
    return {
      services: results,
      internet: internetStatus,
      timestamp: new Date()
    };
  }
}

module.exports = ConnectivityChecker; 