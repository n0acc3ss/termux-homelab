# Architecture

## Traffic Flow

```
Internet
   │
   ▼
Cloudflare Edge  (DNS + TLS termination for al-info.net)
   │
   ▼  (outbound tunnel — cloudflared initiates, no inbound ports needed)
cloudflared daemon  (~/.cloudflared/config.yml)
   │  routes *.al-info.net → localhost:8080
   ▼
Caddy  (:8080, HTTP only — TLS handled by Cloudflare)
   │
   ├── jellyfin.al-info.net   →  reverse_proxy 127.0.0.1:8096
   ├── music.al-info.net      →  reverse_proxy 127.0.0.1:4533
   ├── home.al-info.net       →  file_server (~/.config/homepage/)
   ├── cloud.al-info.net      →  php_fastcgi unix//…/php-fpm.sock
   └── admin.al-info.net      →  reverse_proxy 127.0.0.1:8443
                                         │
                              also reachable via Tailscale VPN
                              (when Tailscale app is running)

Supporting services (localhost only, not exposed):
   ├── php-fpm   (unix socket: $PREFIX/var/run/php-fpm.sock)
   ├── mysqld    (unix socket: $PREFIX/var/run/mysqld.sock)
   └── redis     (127.0.0.1:6379)
```

## Port Map

| Port | Service | Bound to |
|------|---------|----------|
| 8080 | Caddy (HTTP ingress) | 0.0.0.0 (all interfaces) |
| 8096 | Jellyfin | 127.0.0.1 |
| 4533 | Navidrome | 127.0.0.1 |
| 8443 | Dashy | 0.0.0.0 (NODE PORT env) |
| 6379 | Redis | 127.0.0.1 |
| — | MySQL | unix socket only |
| — | PHP-FPM | unix socket only |

## Process Supervision

All services are managed by **runit** via `sv`.

- Service definitions live in: `$SVDIR` = `$PREFIX/var/service/`
- Logs written by `svlogd` to: `$PREFIX/var/log/sv/<service>/current`
- `$PREFIX` = `/data/data/com.termux/files/usr`
