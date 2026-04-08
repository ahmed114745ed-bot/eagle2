#!/bin/bash
# Ensure port 8000 is free before starting Swoole Octane
# This prevents 'Port 8000 is already in use' during restarts/rolling updates

# Try graceful stop first
php /app/artisan octane:stop 2>/dev/null || true

# Kill any remaining PHP/Swoole processes on port 8000
for i in 1 2 3 4 5 6 7 8 9 10; do
    # Check if any process is using port 8000
    PID=$(fuser 8000/tcp 2>/dev/null)
    if [ -z "$PID" ]; then
        break
    fi
    echo "[octane-start] Port 8000 held by PID $PID, waiting... ($i/10)"
    if [ $i -ge 5 ]; then
        echo "[octane-start] Force killing PID $PID"
        kill -9 $PID 2>/dev/null || true
    fi
    sleep 1
done

echo "[octane-start] Starting Octane on port 8000"
exec php /app/artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
