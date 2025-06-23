#!/bin/bash

# SSL Certificate Generation Script for OpenCart Multi-Port Setup
# This script generates self-signed SSL certificates for development/testing

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SSL_DIR="/etc/ssl"
CERTS_DIR="$SSL_DIR/certs"
PRIVATE_DIR="$SSL_DIR/private"
CSR_DIR="$SSL_DIR/csr"

# Certificate details
COUNTRY="TR"
STATE="Istanbul"
CITY="Istanbul"
ORGANIZATION="MesChain Development"
ORGANIZATIONAL_UNIT="IT Department"
COMMON_NAME_8080="opencart-8080.local"
COMMON_NAME_8090="opencart-8090.local"
EMAIL="admin@meschain.com"

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ✓ SUCCESS:${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ✗ ERROR:${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠ WARNING:${NC} $1"
}

# Function to create directories
create_directories() {
    log "Creating SSL directories..."

    sudo mkdir -p "$CERTS_DIR"
    sudo mkdir -p "$PRIVATE_DIR"
    sudo mkdir -p "$CSR_DIR"

    # Set proper permissions
    sudo chmod 755 "$CERTS_DIR"
    sudo chmod 700 "$PRIVATE_DIR"
    sudo chmod 755 "$CSR_DIR"

    log_success "SSL directories created"
}

# Function to generate private key
generate_private_key() {
    local port=$1
    local key_file="$PRIVATE_DIR/opencart-$port.key"

    log "Generating private key for port $port..."

    sudo openssl genrsa -out "$key_file" 2048
    sudo chmod 600 "$key_file"

    log_success "Private key generated: $key_file"
}

# Function to generate certificate signing request
generate_csr() {
    local port=$1
    local common_name=$2
    local key_file="$PRIVATE_DIR/opencart-$port.key"
    local csr_file="$CSR_DIR/opencart-$port.csr"

    log "Generating CSR for port $port..."

    # Create config file for CSR
    local config_file="/tmp/opencart-$port.conf"
    cat > "$config_file" << EOF
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = v3_req

[dn]
C=$COUNTRY
ST=$STATE
L=$CITY
O=$ORGANIZATION
OU=$ORGANIZATIONAL_UNIT
CN=$common_name
emailAddress=$EMAIL

[v3_req]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = $common_name
DNS.2 = localhost
DNS.3 = 127.0.0.1
IP.1 = 127.0.0.1
IP.2 = ::1
EOF

    sudo openssl req -new -key "$key_file" -out "$csr_file" -config "$config_file"
    sudo chmod 644 "$csr_file"

    # Clean up config file
    rm "$config_file"

    log_success "CSR generated: $csr_file"
}

# Function to generate self-signed certificate
generate_certificate() {
    local port=$1
    local common_name=$2
    local key_file="$PRIVATE_DIR/opencart-$port.key"
    local csr_file="$CSR_DIR/opencart-$port.csr"
    local cert_file="$CERTS_DIR/opencart-$port.crt"

    log "Generating self-signed certificate for port $port..."

    # Create config file for certificate
    local config_file="/tmp/opencart-$port-cert.conf"
    cat > "$config_file" << EOF
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = v3_req

[dn]
C=$COUNTRY
ST=$STATE
L=$CITY
O=$ORGANIZATION
OU=$ORGANIZATIONAL_UNIT
CN=$common_name
emailAddress=$EMAIL

[v3_req]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @alt_names
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = $common_name
DNS.2 = localhost
DNS.3 = 127.0.0.1
IP.1 = 127.0.0.1
IP.2 = ::1
EOF

    sudo openssl x509 -req -in "$csr_file" -signkey "$key_file" -out "$cert_file" -days 365 -extensions v3_req -extfile "$config_file"
    sudo chmod 644 "$cert_file"

    # Clean up config file
    rm "$config_file"

    log_success "Certificate generated: $cert_file"
}

# Function to verify certificate
verify_certificate() {
    local port=$1
    local cert_file="$CERTS_DIR/opencart-$port.crt"

    log "Verifying certificate for port $port..."

    # Check certificate details
    local cert_info=$(sudo openssl x509 -in "$cert_file" -text -noout)

    if echo "$cert_info" | grep -q "Subject:.*CN=opencart-$port.local"; then
        log_success "Certificate verification passed for port $port"

        # Show certificate details
        log "Certificate details for port $port:"
        sudo openssl x509 -in "$cert_file" -subject -dates -noout | sed 's/^/  /'
    else
        log_error "Certificate verification failed for port $port"
        return 1
    fi
}

# Function to create certificate bundle
create_certificate_bundle() {
    local port=$1
    local cert_file="$CERTS_DIR/opencart-$port.crt"
    local key_file="$PRIVATE_DIR/opencart-$port.key"
    local bundle_file="$CERTS_DIR/opencart-$port-bundle.pem"

    log "Creating certificate bundle for port $port..."

    sudo cat "$cert_file" "$key_file" | sudo tee "$bundle_file" > /dev/null
    sudo chmod 600 "$bundle_file"

    log_success "Certificate bundle created: $bundle_file"
}

# Function to update hosts file
update_hosts_file() {
    log "Updating /etc/hosts file..."

    # Check if entries already exist
    if ! grep -q "opencart-8080.local" /etc/hosts; then
        echo "127.0.0.1    opencart-8080.local" | sudo tee -a /etc/hosts > /dev/null
        log_success "Added opencart-8080.local to hosts file"
    else
        log_warning "opencart-8080.local already exists in hosts file"
    fi

    if ! grep -q "opencart-8090.local" /etc/hosts; then
        echo "127.0.0.1    opencart-8090.local" | sudo tee -a /etc/hosts > /dev/null
        log_success "Added opencart-8090.local to hosts file"
    else
        log_warning "opencart-8090.local already exists in hosts file"
    fi
}

# Function to set up certificate permissions
setup_permissions() {
    log "Setting up certificate permissions..."

    # Make sure web server can read certificates
    sudo chown root:ssl-cert "$CERTS_DIR/opencart-"*.crt 2>/dev/null || true
    sudo chown root:ssl-cert "$PRIVATE_DIR/opencart-"*.key 2>/dev/null || true

    # Set proper permissions
    sudo chmod 644 "$CERTS_DIR/opencart-"*.crt 2>/dev/null || true
    sudo chmod 640 "$PRIVATE_DIR/opencart-"*.key 2>/dev/null || true

    log_success "Certificate permissions configured"
}

# Function to generate installation instructions
generate_instructions() {
    local instructions_file="$SCRIPT_DIR/ssl-installation-instructions.md"

    log "Generating SSL installation instructions..."

    cat > "$instructions_file" << 'EOF'
# SSL Certificate Installation Instructions

## Generated Certificates

The following SSL certificates have been generated for your OpenCart multi-port setup:

### Port 8080 (Integrated System)
- Certificate: `/etc/ssl/certs/opencart-8080.crt`
- Private Key: `/etc/ssl/private/opencart-8080.key`
- Domain: `opencart-8080.local`

### Port 8090 (Clean System)
- Certificate: `/etc/ssl/certs/opencart-8090.crt`
- Private Key: `/etc/ssl/private/opencart-8090.key`
- Domain: `opencart-8090.local`

## Apache Configuration

To enable SSL in Apache, uncomment the SSL lines in your virtual host configurations:

```apache
# In /etc/apache2/sites-available/opencart-8080.conf
SSLEngine on
SSLCertificateFile /etc/ssl/certs/opencart-8080.crt
SSLCertificateKeyFile /etc/ssl/private/opencart-8080.key

# In /etc/apache2/sites-available/opencart-8090.conf
SSLEngine on
SSLCertificateFile /etc/ssl/certs/opencart-8090.crt
SSLCertificateKeyFile /etc/ssl/private/opencart-8090.key
```

Then restart Apache:
```bash
sudo systemctl restart apache2
```

## Nginx Configuration

To enable SSL in Nginx, uncomment the SSL lines in your server configurations:

```nginx
# In /etc/nginx/sites-available/opencart-8080.conf
listen 8443 ssl http2;
ssl_certificate /etc/ssl/certs/opencart-8080.crt;
ssl_certificate_key /etc/ssl/private/opencart-8080.key;

# In /etc/nginx/sites-available/opencart-8090.conf
listen 8453 ssl http2;
ssl_certificate /etc/ssl/certs/opencart-8090.crt;
ssl_certificate_key /etc/ssl/private/opencart-8090.key;
```

Then restart Nginx:
```bash
sudo systemctl restart nginx
```

## Testing SSL

After enabling SSL, you can test the connections:

```bash
# Test port 8080 SSL
curl -k https://opencart-8080.local:8443/

# Test port 8090 SSL
curl -k https://opencart-8090.local:8453/
```

## Browser Access

Add the following entries to your browser's trusted certificates or accept the security warnings:

- https://opencart-8080.local:8443/
- https://opencart-8090.local:8453/

## Security Note

These are self-signed certificates intended for development and testing purposes only. For production use, obtain certificates from a trusted Certificate Authority (CA).

## Troubleshooting

If you encounter issues:

1. Check certificate permissions:
   ```bash
   ls -la /etc/ssl/certs/opencart-*.crt
   ls -la /etc/ssl/private/opencart-*.key
   ```

2. Verify certificate validity:
   ```bash
   openssl x509 -in /etc/ssl/certs/opencart-8080.crt -text -noout
   openssl x509 -in /etc/ssl/certs/opencart-8090.crt -text -noout
   ```

3. Check web server error logs:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   # or
   sudo tail -f /var/log/nginx/error.log
   ```
EOF

    log_success "SSL installation instructions generated: $instructions_file"
}

# Main execution
main() {
    log "Starting SSL certificate generation for OpenCart multi-port setup..."

    # Check if running with sudo
    if [[ $EUID -ne 0 ]] && ! sudo -n true 2>/dev/null; then
        log_error "This script requires sudo privileges. Please run with sudo."
        exit 1
    fi

    # Check if OpenSSL is installed
    if ! command -v openssl >/dev/null 2>&1; then
        log_error "OpenSSL is not installed. Please install OpenSSL and try again."
        exit 1
    fi

    # Create directories
    create_directories

    # Generate certificates for port 8080
    log "=========================================="
    log "    GENERATING CERTIFICATES FOR PORT 8080"
    log "=========================================="
    generate_private_key 8080
    generate_csr 8080 "$COMMON_NAME_8080"
    generate_certificate 8080 "$COMMON_NAME_8080"
    verify_certificate 8080
    create_certificate_bundle 8080

    # Generate certificates for port 8090
    log ""
    log "=========================================="
    log "    GENERATING CERTIFICATES FOR PORT 8090"
    log "=========================================="
    generate_private_key 8090
    generate_csr 8090 "$COMMON_NAME_8090"
    generate_certificate 8090 "$COMMON_NAME_8090"
    verify_certificate 8090
    create_certificate_bundle 8090

    # Update hosts file
    log ""
    log "=========================================="
    log "         UPDATING SYSTEM CONFIGURATION"
    log "=========================================="
    update_hosts_file
    setup_permissions

    # Generate instructions
    generate_instructions

    log ""
    log_success "SSL certificate generation completed successfully!"
    log ""
    log "Next steps:"
    log "1. Review the generated certificates in /etc/ssl/"
    log "2. Enable SSL in your web server configuration"
    log "3. Restart your web server"
    log "4. Test SSL connections using the provided instructions"
    log ""
    log "For detailed instructions, see: $SCRIPT_DIR/ssl-installation-instructions.md"
}

# Run main function
main "$@"
