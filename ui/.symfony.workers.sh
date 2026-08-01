#!/bin/bash

MUGIT_DEFAULT_PORT=22
WEBPACK_DEFAULT_PORT=8080

find_next_available_port() {
    local port=$1
    while ss -ltn | awk '{print $4}' | grep -q ":$port$"; do
        ((port++))
    done
    echo $port
}

echo "Starting custom workers..."

# Find first available wp port and run webpack dev server with the selected port
export WEBPACK_PORT=$(find_next_available_port $WEBPACK_DEFAULT_PORT)
echo "Starting WebPack dev server on port $WEBPACK_PORT"
./node_modules/.bin/encore dev-server --hot --port=$WEBPACK_PORT &
WEBPACK_PID=$!  # Capture the Webpack process PID

# Use '.env' file of the core if exists, or default values otherwise
cd ../core
if [ -f ".env" ]; then
    echo "Custom '.env' file detected."
    source ".env"
else 
    echo "No '.env' file detected."
    export TIMEZONE=$(cat /etc/timezone 2> /dev/null || echo "")  
    export MUGIT_UID=$(id -u)
    export MUGIT_GID=$(id -g)
    export MUGIT_DIR="./var/dev/data"
    export MUGIT_PORT=$(find_next_available_port $MUGIT_DEFAULT_PORT)
    export MUGIT_EXTERNAL_NETWORK=false
fi
echo "Starting docker Core container on port $MUGIT_PORT"
mkdir -p $MUGIT_DIR
docker compose up -d
cd ../ui

# Function to stop processes on exit
cleanup() {
    echo "Stopping WebPack dev server..."
    kill $WEBPACK_PID 2>/dev/null
    echo "Stopping docker Core container..."
    cd ../core && docker compose down && cd ../ui
}

# Trap termination signals to execute cleanup
trap cleanup SIGINT SIGTERM

# Wait for child processes to finish
wait $WEBPACK_PID
