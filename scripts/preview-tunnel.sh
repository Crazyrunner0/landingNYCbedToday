#!/bin/bash
set -e

TUNNEL_NAME="wordpress-preview"
CREDENTIALS_DIR="$HOME/.cloudflare"
CREDENTIALS_FILE="$CREDENTIALS_DIR/$TUNNEL_NAME.json"
TUNNEL_CONFIG="./cloudflare/tunnel.yml"
TUNNEL_URL_FILE="/tmp/cloudflare_tunnel_url.txt"

usage() {
    echo "Usage: $0 {up|down|status}"
    echo ""
    echo "Commands:"
    echo "  up     - Start Cloudflare Tunnel"
    echo "  down   - Stop Cloudflare Tunnel"
    echo "  status - Check tunnel status"
    exit 1
}

tunnel_up() {
    echo "🚀 Starting Cloudflare Tunnel for local preview..."
    
    # Check if cloudflared is installed
    if ! command -v cloudflared &> /dev/null; then
        echo "❌ Error: cloudflared is not installed"
        echo ""
        echo "Install cloudflared:"
        echo "  macOS:  brew install cloudflare/cloudflare/cloudflared"
        echo "  Linux:  curl -L --output cloudflared.tgz https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.tgz && tar -xzf cloudflared.tgz && sudo mv cloudflared /usr/local/bin/"
        echo "  Windows: https://github.com/cloudflare/cloudflared/releases/latest"
        exit 1
    fi
    
    # Ensure credentials directory exists
    mkdir -p "$CREDENTIALS_DIR"
    
    # Check if tunnel is already running
    if pgrep -f "cloudflared.*tunnel" > /dev/null; then
        echo "⚠️  Tunnel already running. Stopping existing tunnel..."
        "$0" down
        sleep 2
    fi
    
    # Start tunnel in background with output redirection
    cloudflared tunnel run --config "$TUNNEL_CONFIG" > "$TUNNEL_URL_FILE" 2>&1 &
    TUNNEL_PID=$!
    
    # Wait for tunnel to start and capture URL
    echo "⏳ Waiting for tunnel to start..."
    sleep 3
    
    # Check if the tunnel started successfully
    if ! kill -0 $TUNNEL_PID 2>/dev/null; then
        echo "❌ Failed to start tunnel"
        cat "$TUNNEL_URL_FILE" 2>/dev/null || echo "Check cloudflared installation"
        exit 1
    fi
    
    # Try to extract URL from cloudflared output
    TUNNEL_URL=""
    for i in {1..10}; do
        if [ -f "$TUNNEL_URL_FILE" ]; then
            TUNNEL_URL=$(grep -oE 'https://[^ ]+\.trycloudflare\.com' "$TUNNEL_URL_FILE" 2>/dev/null | head -1)
            if [ -n "$TUNNEL_URL" ]; then
                break
            fi
        fi
        sleep 1
    done
    
    if [ -z "$TUNNEL_URL" ]; then
        echo "⏳ Tunnel starting... waiting for URL..."
        # Try a few more times with longer intervals
        for i in {1..5}; do
            sleep 2
            if [ -f "$TUNNEL_URL_FILE" ]; then
                TUNNEL_URL=$(grep -oE 'https://[^ ]+\.trycloudflare\.com' "$TUNNEL_URL_FILE" 2>/dev/null | head -1)
                if [ -n "$TUNNEL_URL" ]; then
                    break
                fi
            fi
        done
    fi
    
    if [ -z "$TUNNEL_URL" ]; then
        echo "⚠️  Could not automatically capture tunnel URL"
        echo "🔗 Check cloudflared output for tunnel URL:"
        tail -20 "$TUNNEL_URL_FILE"
        echo ""
        echo "Once you have the URL, update it in .env.local by setting:"
        echo "  WP_HOME='https://your-tunnel-url.trycloudflare.com'"
        echo ""
        exit 1
    fi
    
    # Store tunnel URL
    echo "$TUNNEL_URL" > "$TUNNEL_URL_FILE"
    
    echo "✅ Tunnel started successfully!"
    echo ""
    echo "🔗 Your preview URL: $TUNNEL_URL"
    echo ""
    echo "Next steps:"
    echo "1. Update your WordPress URLs by creating/editing .env.local:"
    echo "   WP_HOME='$TUNNEL_URL'"
    echo "   WP_SITEURL='\${WP_HOME}/wp'"
    echo ""
    echo "2. Reload WordPress:"
    echo "   make wp CMD='cache flush'"
    echo ""
    echo "3. Visit your site: $TUNNEL_URL"
    echo ""
    echo "To stop the tunnel, run: make preview.down"
    echo ""
}

tunnel_down() {
    echo "🛑 Stopping Cloudflare Tunnel..."
    
    if pgrep -f "cloudflared.*tunnel" > /dev/null; then
        pkill -f "cloudflared.*tunnel" || true
        sleep 1
        echo "✅ Tunnel stopped"
    else
        echo "ℹ️  No tunnel running"
    fi
    
    rm -f "$TUNNEL_URL_FILE"
}

tunnel_status() {
    echo "📊 Tunnel Status:"
    
    if pgrep -f "cloudflared.*tunnel" > /dev/null; then
        echo "✅ Tunnel is running"
        
        if [ -f "$TUNNEL_URL_FILE" ]; then
            TUNNEL_URL=$(cat "$TUNNEL_URL_FILE")
            echo "🔗 Tunnel URL: $TUNNEL_URL"
        fi
    else
        echo "❌ Tunnel is not running"
    fi
}

# Main
case "${1:-help}" in
    up)
        tunnel_up
        ;;
    down)
        tunnel_down
        ;;
    status)
        tunnel_status
        ;;
    *)
        usage
        ;;
esac
