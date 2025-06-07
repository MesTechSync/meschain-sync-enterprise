#!/bin/bash

# SSL/HTTPS Configuration Script for MesChain-Sync Enterprise
# Version: 1.0.0
# Date: June 7, 2025

echo "🔐 MesChain-Sync Enterprise SSL/HTTPS Configuration"
echo "=================================================="
echo ""

# Check if OpenSSL is available
if ! command -v openssl &> /dev/null; then
    echo "❌ OpenSSL not found. Installing..."
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install openssl
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install openssl
    fi
fi

# Create SSL directory
mkdir -p ssl
cd ssl

echo "🔑 Generating SSL certificates for development environment..."

# Generate private key
openssl genrsa -out meschain-private-key.pem 2048

# Generate certificate signing request
openssl req -new -key meschain-private-key.pem -out meschain-csr.pem -subj "/C=TR/ST=Istanbul/L=Istanbul/O=MesChain-Sync Enterprise/OU=Development/CN=localhost"

# Generate self-signed certificate
openssl x509 -req -in meschain-csr.pem -signkey meschain-private-key.pem -out meschain-certificate.pem -days 365

echo "✅ SSL certificates generated:"
echo "   - Private Key: ssl/meschain-private-key.pem"
echo "   - Certificate: ssl/meschain-certificate.pem"
echo ""

# Create HTTPS server configuration template
cat > ../https_server_template.js << 'EOF'
const https = require('https');
const fs = require('fs');
const path = require('path');

// SSL Configuration
const sslOptions = {
    key: fs.readFileSync(path.join(__dirname, 'ssl', 'meschain-private-key.pem')),
    cert: fs.readFileSync(path.join(__dirname, 'ssl', 'meschain-certificate.pem'))
};

// Example HTTPS server setup
function createHTTPSServer(app, port) {
    const httpsServer = https.createServer(sslOptions, app);
    
    httpsServer.listen(port, () => {
        console.log(`🔐 HTTPS Server running on https://localhost:${port}`);
        console.log(`🔑 SSL Certificate: meschain-certificate.pem`);
        console.log(`🗝️  Private Key: meschain-private-key.pem`);
    });
    
    return httpsServer;
}

module.exports = { createHTTPSServer, sslOptions };
EOF

echo "📝 HTTPS server template created: https_server_template.js"
echo ""

# Create HTTPS upgrade script for existing servers
cat > ../upgrade_to_https.sh << 'EOF'
#!/bin/bash

echo "🔐 Upgrading MesChain-Sync servers to HTTPS..."

# List of server files to upgrade
servers=(
    "super_admin_server.js"
    "advanced_cross_marketplace_server.js" 
    "port_3012_trendyol_seller_server.js"
    "port_3005_product_management_server.js"
)

for server in "${servers[@]}"; do
    if [ -f "$server" ]; then
        echo "🔧 Creating HTTPS version of $server..."
        cp "$server" "${server%.js}_https.js"
        
        # Add HTTPS imports at the top
        sed -i.bak '1i\
const https = require('\''https'\'');\
const fs = require('\''fs'\'');\
const path = require('\''path'\'');
' "${server%.js}_https.js"
        
        echo "✅ HTTPS version created: ${server%.js}_https.js"
    fi
done

echo "🎯 HTTPS upgrade complete!"
echo "💡 To start HTTPS servers, modify the app.listen() calls to use https.createServer()"
EOF

chmod +x ../upgrade_to_https.sh

echo "🚀 SSL/HTTPS Configuration Complete!"
echo ""
echo "📋 Next Steps:"
echo "   1. Run: ./upgrade_to_https.sh to create HTTPS versions"
echo "   2. Modify server files to use HTTPS"
echo "   3. Update all localhost URLs to use https://"
echo "   4. For production: Replace with CA-signed certificates"
echo ""
echo "⚠️  Note: Self-signed certificates will show browser warnings"
echo "   Accept the certificates in browser for development use"
