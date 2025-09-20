#!/bin/bash
# Stop development servers script

echo "🛑 Stopping Blood Donation System development servers..."

# Kill servers by PID if available
if [ -f .dev_server_pids ]; then
    while read pid; do
        if [ ! -z "$pid" ]; then
            kill $pid 2>/dev/null && echo "Stopped server (PID: $pid)"
        fi
    done < .dev_server_pids
    rm .dev_server_pids
fi

# Kill any remaining PHP servers on our ports
lsof -ti:8080 | xargs kill -9 2>/dev/null
lsof -ti:8081 | xargs kill -9 2>/dev/null

echo "✅ Development servers stopped!"
