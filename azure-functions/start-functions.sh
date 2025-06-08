#!/bin/bash

# MesChain-Sync Enterprise Azure Functions Starter Script
# This script ensures we use Node.js 18 for Azure Functions

export PATH="/opt/homebrew/opt/node@18/bin:$PATH"
export FUNCTIONS_WORKER_RUNTIME=node
export WEBSITE_NODE_DEFAULT_VERSION=~18
export languageWorkers__node__defaultExecutablePath=/opt/homebrew/opt/node@18/bin/node

echo "🚀 Starting MesChain-Sync Azure Functions..."
echo "📋 Node.js Version: $(node --version)"
echo "🔧 Azure Functions Core Tools Version: $(func --version)"
echo "🌐 Starting on port 7071..."

cd "$(dirname "$0")"
func start --port 7071 --verbose
