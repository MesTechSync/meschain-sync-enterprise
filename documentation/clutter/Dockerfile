# 🐳 MesChain-Sync Enterprise - Production Docker Image
# Multi-stage build for optimized production deployment

# ====================================
# 🏗️ BUILD STAGE
# ====================================
FROM node:18-alpine AS builder

# Set working directory
WORKDIR /app

# Install dependencies for building
RUN apk add --no-cache \
    python3 \
    make \
    g++ \
    && rm -rf /var/cache/apk/*

# Copy package files
COPY package*.json ./

# Install all dependencies (including devDependencies for build)
RUN npm ci --silent

# Copy source code
COPY . .

# Build arguments
ARG VERSION=latest
ARG BUILD_TIME
ARG COMMIT_SHA

# Set environment variables for build
ENV REACT_APP_VERSION=${VERSION}
ENV REACT_APP_BUILD_TIME=${BUILD_TIME}
ENV REACT_APP_COMMIT_SHA=${COMMIT_SHA}
ENV NODE_ENV=production

# Build the application
RUN npm run build

# ====================================
# 🚀 PRODUCTION STAGE
# ====================================
FROM nginx:alpine AS production

# Install security updates and utilities
RUN apk update && apk upgrade && \
    apk add --no-cache \
    curl \
    ca-certificates \
    tzdata \
    && rm -rf /var/cache/apk/*

# Create non-root user for security
RUN addgroup -g 1001 -S meschain && \
    adduser -S meschain -u 1001

# Copy custom nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Copy built application from builder stage
COPY --from=builder /app/build /usr/share/nginx/html

# Copy security headers configuration
COPY docker/security-headers.conf /etc/nginx/conf.d/security-headers.conf

# Set proper permissions
RUN chown -R meschain:meschain /usr/share/nginx/html && \
    chown -R meschain:meschain /var/cache/nginx && \
    chown -R meschain:meschain /var/log/nginx && \
    chown -R meschain:meschain /etc/nginx/conf.d && \
    touch /var/run/nginx.pid && \
    chown -R meschain:meschain /var/run/nginx.pid

# Create health check script
COPY docker/health-check.sh /usr/local/bin/health-check.sh
RUN chmod +x /usr/local/bin/health-check.sh && \
    chown meschain:meschain /usr/local/bin/health-check.sh

# Switch to non-root user
USER meschain

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD /usr/local/bin/health-check.sh

# Labels for metadata
LABEL \
    org.opencontainers.image.title="MesChain-Sync Enterprise" \
    org.opencontainers.image.description="Enterprise marketplace integration platform" \
    org.opencontainers.image.version="${VERSION}" \
    org.opencontainers.image.created="${BUILD_TIME}" \
    org.opencontainers.image.revision="${COMMIT_SHA}" \
    org.opencontainers.image.vendor="MesChain Technologies" \
    org.opencontainers.image.licenses="Proprietary" \
    org.opencontainers.image.source="https://github.com/MesTechSync/meschain-sync-enterprise"

# Start nginx
CMD ["nginx", "-g", "daemon off;"] 