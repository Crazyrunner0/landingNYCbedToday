# Cloudflare Tunnel Live Preview

Generate a public HTTPS URL for your local WordPress development environment using Cloudflare Tunnel. Perfect for sharing demos, testing on mobile devices, or sharing with team members without VPN access.

## Quick Start

### Prerequisites

1. **cloudflared CLI** - Download and install from: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/

   **Installation by OS:**
   
   **macOS:**
   ```bash
   brew install cloudflare/cloudflare/cloudflared
   ```
   
   **Linux (Debian/Ubuntu):**
   ```bash
   curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
   sudo dpkg -i cloudflared.deb
   ```
   
   **Linux (other):**
   ```bash
   curl -L --output cloudflared.tgz https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.tgz
   tar -xzf cloudflared.tgz
   sudo mv cloudflared /usr/local/bin/
   ```
   
   **Windows:**
   Download installer from: https://github.com/cloudflare/cloudflared/releases/latest

2. **Docker services running**
   ```bash
   make up
   ```

### Start Preview

```bash
make preview.up
```

This will:
- Launch a Cloudflare Tunnel connecting to your local Nginx container
- Print a public HTTPS URL (e.g., `https://unique-name.trycloudflare.com`)
- Display instructions for updating WordPress URLs

### Configure WordPress URLs

When the tunnel starts, you'll see a URL like:
```
🔗 Your preview URL: https://example-tunnel.trycloudflare.com
```

Update your WordPress configuration in `.env.local`:
```bash
# .env.local
WP_HOME='https://example-tunnel.trycloudflare.com'
WP_SITEURL='${WP_HOME}/wp'
DISALLOW_INDEXING='true'
```

Then flush WordPress cache:
```bash
make wp CMD='cache flush'
```

### Share Your Preview

Your site is now publicly accessible at the tunnel URL. Share it with team members, clients, or test it on mobile devices.

**Important:** The tunnel URL is randomly generated and temporary. If you restart the tunnel, you'll get a new URL. For permanent URLs, use Cloudflare Tunnel's named tunnel feature (see below).

### Stop Preview

```bash
make preview.down
```

## Advanced Usage

### Named Tunnels (Persistent URLs)

For a permanent preview URL that persists across restarts:

1. **Create a Cloudflare account** (if you don't have one)
2. **Authenticate cloudflared:**
   ```bash
   cloudflared tunnel login
   ```
   This opens a browser to authenticate and stores credentials locally.

3. **Create a named tunnel:**
   ```bash
   cloudflared tunnel create wordpress-preview
   ```

4. **Update tunnel configuration** in `cloudflare/tunnel.yml`:
   ```yaml
   tunnel: wordpress-preview
   credentials-file: /home/user/.cloudflare/wordpress-preview.json
   
   ingress:
     - hostname: your-domain.example.com
       service: http://nginx:80
     - service: http_status:404
   ```

5. **Create CNAME record** in your Cloudflare DNS pointing to:
   ```
   your-domain.example.com CNAME wordpress-preview.cfargotunnel.com
   ```

6. **Start the named tunnel:**
   ```bash
   make preview.up
   ```

### Check Tunnel Status

```bash
make preview.status
```

Shows whether the tunnel is running and its URL (if available).

### Debug Tunnel Issues

**Tunnel won't start:**
- Verify Docker services are running: `docker compose ps`
- Check cloudflared logs: `tail -f /tmp/cloudflare_tunnel_url.txt`

**Site serves 404:**
- Verify Nginx is accessible: `curl http://localhost:8080`
- Check tunnel configuration points to `http://nginx:80`

**Links redirect to localhost:**
- Update `WP_HOME` in `.env.local` to tunnel URL
- Flush cache: `make wp CMD='cache flush'`

## Environment Variables

### Preview Configuration

Add to `.env.local` or `.env.local.preview`:

```bash
# Required: Cloudflare Tunnel URL (set automatically or manually)
WP_HOME='https://your-tunnel.trycloudflare.com'
WP_SITEURL='${WP_HOME}/wp'

# Recommended: Disable search engine indexing on preview
DISALLOW_INDEXING='true'

# Optional: Enable debug logging
WP_DEBUG='true'
WP_DEBUG_LOG='true'
```

## Security Considerations

1. **DISALLOW_INDEXING=true** - Automatically set to prevent search engines from indexing the preview
2. **Temporary URLs** - Default tunnel URLs are randomly generated and expire when stopped
3. **No authentication** - Tunnel URLs are publicly accessible; don't share sensitive previews
4. **HTTPS only** - All tunnel traffic is encrypted end-to-end
5. **Auto-logout** - Close tunnel when done sharing to prevent unauthorized access

## Tunnel Configuration

### Default Configuration (Random URL)

File: `cloudflare/tunnel.yml`

```yaml
tunnel: wordpress-preview
credentials-file: /home/cloudflared/.cloudflare/wordpress-preview.json

ingress:
  - hostname: localhost.internal
    service: http://nginx:80
  - service: http_status:404
```

- **tunnel**: Tunnel name (used internally)
- **credentials-file**: Path to cloudflared credentials (created by `cloudflared tunnel login`)
- **ingress**: Rules for routing traffic
  - First rule: Routes localhost.internal → local Nginx (generates random URL)
  - Fallback: Returns 404 for unmatched routes

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| `cloudflared: command not found` | Install cloudflared (see Prerequisites) |
| Tunnel starts but no URL displayed | Wait 5-10 seconds for URL to print |
| "Permission denied" on credentials | Run `cloudflared tunnel login` again |
| Site returns 502 Bad Gateway | Verify `make up` is running and Nginx is healthy |
| Links don't work (redirect to localhost) | Update `WP_HOME` in `.env.local` and flush cache |

## Makefile Commands

| Command | Purpose |
|---------|---------|
| `make preview.up` | Start Cloudflare Tunnel, print public URL |
| `make preview.down` | Stop Cloudflare Tunnel |
| `make preview.status` | Check tunnel status and URL |

## Development Workflow

1. **Start local development:**
   ```bash
   make up
   ```

2. **When ready to preview:**
   ```bash
   make preview.up
   ```
   
   Note the tunnel URL printed.

3. **Configure WordPress:**
   ```bash
   # .env.local
   WP_HOME='https://your-tunnel-url.trycloudflare.com'
   WP_SITEURL='${WP_HOME}/wp'
   DISALLOW_INDEXING='true'
   ```
   
   ```bash
   make wp CMD='cache flush'
   ```

4. **Share preview URL** with team/clients

5. **When done:**
   ```bash
   make preview.down
   ```

## Performance & Limitations

- **Latency**: Tunnel adds ~50-100ms latency (acceptable for previews)
- **Bandwidth**: No rate limiting on free tier
- **Concurrent Connections**: Effectively unlimited
- **Session Timeout**: Tunnel stays active as long as process runs

## More Information

- [Cloudflare Tunnel Documentation](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [cloudflared CLI Reference](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/tunnel-guide/)
- [Tunnel Configuration](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/tunnel-guide/local-management/configuration-file/)

## Support

For issues with:
- **Cloudflare Tunnel**: See [Cloudflare Troubleshooting](https://developers.cloudflare.com/cloudflare-one/troubleshooting/)
- **Local setup**: Run `make healthcheck` to verify Docker services
- **WordPress URLs**: Verify `WP_HOME` and `WP_SITEURL` in `.env.local`
