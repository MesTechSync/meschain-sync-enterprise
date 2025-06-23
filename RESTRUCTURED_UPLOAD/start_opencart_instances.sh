#!/bin/bash

# Script to start both OpenCart instances
echo "Starting OpenCart instances..."

# Kill any existing PHP servers on ports 8080 and 8090
echo "Checking for existing PHP servers..."
pkill -f "php -S localhost:8080" 2>/dev/null
pkill -f "php -S localhost:8090" 2>/dev/null

# Define paths
BASEDIR="/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD"
OPENCART_NEW="$BASEDIR/opencart_new"
OPENCART_NEW2="$BASEDIR/opencart_new2"

# Function to check if a port is in use
is_port_in_use() {
  lsof -i:$1 >/dev/null 2>&1
  return $?
}

# Start the first instance (port 8080)
echo "Starting OpenCart instance 1 on port 8080..."
if is_port_in_use 8080; then
  echo "Error: Port 8080 is already in use. Cannot start OpenCart instance 1."
else
  cd "$OPENCART_NEW" || { echo "Error: Directory $OPENCART_NEW not found"; exit 1; }
  php -S localhost:8080 &
  INSTANCE1_PID=$!
  echo "OpenCart instance 1 started with PID: $INSTANCE1_PID"
fi

# Start the second instance (port 8090)
echo "Starting OpenCart instance 2 on port 8090..."
if is_port_in_use 8090; then
  echo "Error: Port 8090 is already in use. Cannot start OpenCart instance 2."
else
  cd "$OPENCART_NEW2" || { echo "Error: Directory $OPENCART_NEW2 not found"; exit 1; }
  php -S localhost:8090 &
  INSTANCE2_PID=$!
  echo "OpenCart instance 2 started with PID: $INSTANCE2_PID"
fi

echo ""
echo "OpenCart instances are now running:"
echo "- Instance 1: http://localhost:8080/"
echo "- Instance 2: http://localhost:8090/"
echo ""
echo "Admin panels:"
echo "- Instance 1 Admin: http://localhost:8080/admin/"
echo "- Instance 2 Admin: http://localhost:8090/admin/"
echo ""
echo "To stop the servers, run: pkill -f 'php -S localhost:808[0-9]'"
echo ""
