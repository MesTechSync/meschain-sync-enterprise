# MesChain Secure API Gateway

## 🔐 Enterprise-grade API Gateway with Advanced Security Features

This package provides a comprehensive, enterprise-grade API Gateway solution with advanced security features including:

- OAuth 2.0 Provider (all grant types)
- Enhanced JWT Security with RS256 signing
- Dynamic and Adaptive Rate Limiting
- Service Mesh Integration
- Comprehensive observability and metrics

## 📋 Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)

## ✨ Features

### OAuth 2.0 Provider
- Complete OAuth 2.0 server implementation
- Support for Authorization Code, Client Credentials, and Refresh Token flows
- Token generation, validation and revocation
- Client registration and management
- Scope validation

### JWT Security
- RS256 asymmetric token signing
- Key pair generation and management
- Token validation with audience and issuer verification
- Token revocation with Redis-backed blacklist
- Token refresh mechanism

### Advanced Rate Limiting
- Dynamic rate limiting based on multiple factors
- Adaptive limits based on system load
- User/client-specific limits
- Route-specific configurations
- Penalty mechanisms for abusive clients
- IP whitelisting and blacklisting
- Redis-backed distributed rate limiting

### Service Mesh Integration
- Support for Istio, Linkerd, and Consul Connect
- Circuit breaker pattern implementation
- Service discovery and registration
- Health checking
- Load balancing
- Distributed tracing with headers

### Additional Features
- Comprehensive metrics with Prometheus
- Detailed logging
- Security headers
- CORS configuration
- HTTP/2 support
- Compression
- Request ID tracking

## 🏗️ Architecture

The API Gateway follows a modular architecture with the following components:

```
┌─────────────────────────────┐
│         API Gateway         │
├─────────────────────────────┤
│    ┌─────────────────────┐  │
│    │   Gateway Core      │  │
│    └─────────────────────┘  │
│    ┌─────────────────────┐  │
│    │  OAuth2 Provider    │  │
│    └─────────────────────┘  │
│    ┌─────────────────────┐  │
│    │  JWT Security       │  │
│    └─────────────────────┘  │
│    ┌─────────────────────┐  │
│    │  Rate Limiter       │  │
│    └─────────────────────┘  │
│    ┌─────────────────────┐  │
│    │  Service Mesh       │  │
│    └─────────────────────┘  │
└─────────────────────────────┘
```

## 🔧 Installation

### Prerequisites

- Node.js >= 16.0.0
- Redis server
- Service Mesh (optional, for production)

### Install Dependencies

```bash
cd /path/to/api-gateway
npm install
```

### Generate Keys

```bash
npm run generate-keys
```

## ⚙️ Configuration

Create a `.env` file in the root directory with the following variables:

```env
# Server configuration
API_GATEWAY_PORT=3000
NODE_ENV=production

# Redis configuration
REDIS_URL=redis://localhost:6379
REDIS_USERNAME=
REDIS_PASSWORD=

# JWT configuration
JWT_KEY_PATH=./keys
JWT_ISSUER=meschain-api-gateway
JWT_AUDIENCE=meschain-clients

# Rate limiting
DEFAULT_RATE_LIMIT=100
RATE_WINDOW=60

# Service Mesh
SERVICE_MESH_TYPE=istio
SERVICE_NAME=api-gateway
NAMESPACE=default
```

## 🚀 Usage

### Basic Usage

```bash
# Start the API Gateway
npm start
```

### Development Mode

```bash
# Start in development mode with hot reload
npm run dev
```

### Production Deployment

For production deployment, it is recommended to use a process manager like PM2:

```bash
npm install -g pm2
pm2 start index.js --name "api-gateway" --max-memory-restart 1G
```

## 📝 API Documentation

### OAuth 2.0 Endpoints

#### Authorization Code Flow

1. Authorize endpoint:
```
GET /api/oauth/authorize
```

Query Parameters:
- `client_id` - OAuth client ID
- `redirect_uri` - Where to redirect after authorization
- `response_type` - Must be "code"
- `scope` - Space-separated list of scopes
- `state` - Optional state parameter

2. Token endpoint:
```
POST /api/oauth/token
```

Request Body:
```json
{
  "grant_type": "authorization_code",
  "client_id": "<client_id>",
  "client_secret": "<client_secret>",
  "code": "<authorization_code>",
  "redirect_uri": "<redirect_uri>"
}
```

#### Client Credentials Flow

```
POST /api/oauth/token
```

Request Body:
```json
{
  "grant_type": "client_credentials",
  "client_id": "<client_id>",
  "client_secret": "<client_secret>",
  "scope": "<scope>"
}
```

#### Refresh Token Flow

```
POST /api/oauth/token
```

Request Body:
```json
{
  "grant_type": "refresh_token",
  "client_id": "<client_id>",
  "client_secret": "<client_secret>",
  "refresh_token": "<refresh_token>"
}
```

### API Routes

All API routes are accessible under `/api/*` and require authentication:

```
GET /api/protected/resource
```

Headers:
```
Authorization: Bearer <access_token>
```

### Gateway Status

```
GET /health
```

### Metrics

```
GET /metrics
```

### Service Status

```
GET /api/services/status
```

## 💻 Development

### Project Structure

```
/api-gateway
  /keys          - JWT key pairs
  /scripts       - Utility scripts
  /tests         - Test files
  index.js       - Main entry point
  gateway_core.js - Core gateway functionality
  gateway_routes.js - Route definitions
  oauth2_provider.js - OAuth 2.0 implementation
  jwt_security_provider.js - JWT security implementation
  advanced_rate_limiter.js - Rate limiting implementation
  service_mesh_integration.js - Service mesh integration
  package.json   - Dependencies and scripts
  .env           - Environment variables
  README.md      - Documentation
```

### Extending the Gateway

To add custom routes:

1. Create a new route file
2. Import and use the Gateway Core instance
3. Register your routes

Example:

```javascript
// custom_routes.js
module.exports = function(gateway) {
  const app = gateway.getApp();
  const { jwt } = gateway;
  
  app.get('/api/custom', jwt.verifyToken, (req, res) => {
    res.json({ message: 'Custom route' });
  });
}
```

## 🧪 Testing

Run tests:

```bash
# Run all tests
npm test

# Run with coverage report
npm run test:coverage

# Continuous testing during development
npm run test:watch
```

## 📦 Deployment

### Docker Deployment

A Dockerfile is provided for containerized deployment:

```bash
# Build Docker image
docker build -t meschain/api-gateway .

# Run container
docker run -p 3000:3000 --env-file .env meschain/api-gateway
```

### Azure Deployment

For Azure deployments, use Azure Container Instances or Azure Kubernetes Service.

See `deployment/azure.md` for detailed instructions.

## 📄 License

Proprietary and confidential. Copyright MesChain, Inc. 2025.
