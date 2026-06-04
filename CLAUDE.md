# termux-homelab — Project Context for Claude Code

This is a self-hosted homelab running entirely inside **Termux on Android** (aarch64, no root).
Domain: **al-info.net**. Full wiki: `wiki/` (Obsidian markdown — read it before touching services).

---

## Environment

| Variable | Value |
|---|---|
| `$PREFIX` | `/data/data/com.termux/files/usr` |
| `$HOME` | `/data/data/com.termux/files/home` |
| `$SVDIR` | `$PREFIX/var/service` |

All paths in this project are relative to the above. Standard Linux paths like `/etc/` and `/var/` do not apply.

---

## Stack

| Service | Type | Port / Socket |
|---|---|---|
| `caddy` | Reverse proxy | `:8080` (HTTP only, all interfaces) |
| `cloudflared` | Cloudflare tunnel daemon | outbound only |
| `jellyfin` | Media server | `127.0.0.1:8096` |
| `navidrome` | Music server | `127.0.0.1:4533` |
| `dashy` | Admin dashboard | `:8443` |
| `php-fpm` | PHP FastCGI | `$PREFIX/var/run/php-fpm.sock` |
| `mysqld` | MySQL | `$PREFIX/var/run/mysqld.sock` |
| `redis` | Cache | `127.0.0.1:6379` |
| Nextcloud | PHP app | — no runit service — |

---

## Traffic Flow

```
Internet → Cloudflare Edge (TLS) → cloudflared tunnel → Caddy:8080
  Caddy routes by Host header:
    jellyfin.al-info.net  → 127.0.0.1:8096
    music.al-info.net     → 127.0.0.1:4533
    home.al-info.net      → file_server (~/.config/homepage/)
    cloud.al-info.net     → php_fastcgi unix//…/php-fpm.sock
    admin.al-info.net     → 127.0.0.1:8443  (Tailscale IPs only)
```

Caddy config: `$HOME/.config/caddy/Caddyfile`
cloudflared config: `$HOME/.cloudflared/config.yml`
Tunnel UUID: `c5dbab1d-43f0-4729-bea5-fd78ba38b5f8`

---

## Service Management (runit)

```sh
# Start all
sv up mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared

# Status all
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared

# Individual
sv up|down|restart <service>

# Logs (live)
tail -f $PREFIX/var/log/sv/<service>/current
```

Healthy status line: `run: caddy: (pid 12345) 42s; run: log: (pid 12344) 42s`
Red flags: `down:` = not running · `down: log:` = logger crashed · `fail:` = service dir missing

---

## Known Quirks — Read Before Touching Anything

### 1. Jellyfin — DOTNET_ROOT required
The runit `run` script at `$SVDIR/jellyfin/run` **must** export:
```sh
export DOTNET_ROOT=/data/data/com.termux/files/usr/lib/dotnet
```
Without this, Jellyfin fails with `libhostfxr.so not found` even though .NET 9 is installed. Verify this line exists after any Jellyfin reinstall or runit service recreation.

### 2. cloudflared — log run script required
`$SVDIR/cloudflared/log/run` must exist and call `svlogd`. Without it, `sv status` shows `down: log:` and log output is lost. The tunnel itself may still run, but you lose observability.

### 3. WARP (Cloudflare One Android app) conflicts with cloudflared
WARP intercepts all Termux traffic at the Android OS level, which can destabilise the cloudflared tunnel keepalive. Fix: open the Cloudflare One app → Split Tunneling → exclude `com.termux`.

### 4. Nextcloud has NO runit service
Nextcloud is a PHP app served via php-fpm + Caddy. **Do not** add `nextcloud` to any `sv up` command — it doesn't exist as a service.

### 5. admin.al-info.net is Tailscale-only
The Caddy `@admin` matcher requires `remote_ip 100.64.0.0/10` (Tailscale CGNAT). Public cloudflared traffic comes from `127.0.0.1` and is blocked by this rule. This is intentional.

---

## Key File Locations

| What | Path |
|---|---|
| Caddyfile | `$HOME/.config/caddy/Caddyfile` |
| cloudflared config | `$HOME/.cloudflared/config.yml` |
| Nextcloud config | `$HOME/termux-homelab/config.php` |
| Nextcloud data | `$HOME/termux-homelab/data/` |
| Jellyfin data | `$HOME/.local/share/jellyfin/` |
| Navidrome config | `$HOME/.local/share/navidrome/navidrome.toml` |
| Dashy config | `$HOME/.local/share/dashy/user-data/conf.yml` |
| Homepage files | `$HOME/.config/homepage/` |
| Runit services | `$PREFIX/var/service/` |
| Runit logs | `$PREFIX/var/log/sv/<service>/current` |
| MySQL data | `$PREFIX/var/lib/mysql/` |

---

## Useful Checks

```sh
# Confirm ports are listening
ss -tlnp | grep -E '8080|8096|4533|8443|6379'

# Redis alive
redis-cli ping   # → PONG

# MySQL alive
mysql -e "SELECT 1;"

# Dump all service logs for debugging
for svc in caddy cloudflared jellyfin navidrome dashy mysqld redis php-fpm; do
  echo "=== $svc ===" >> ~/homelab-logs.txt
  tail -100 $PREFIX/var/log/sv/$svc/current >> ~/homelab-logs.txt 2>/dev/null
done
```

---

## Wiki

Detailed documentation lives in `wiki/`:

| File | Contents |
|---|---|
| `wiki/Architecture.md` | Traffic flow diagram, port map, process supervision |
| `wiki/Services.md` | Per-service paths, config, ports, and notes |
| `wiki/Networking.md` | Cloudflare tunnel, WARP conflicts, Tailscale setup |
| `wiki/Operations.md` | Start/stop/log commands, health checks |
| `wiki/Dependencies.md` | Package dependencies |
| `wiki/Restore.md` | Restore/rebuild procedures |
