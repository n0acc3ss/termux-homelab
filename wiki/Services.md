# Services

Shorthand used throughout:
- `$PREFIX` = `/data/data/com.termux/files/usr`
- `$HOME`   = `/data/data/com.termux/files/home`
- `$SVDIR`  = `$PREFIX/var/service`

---

## Caddy

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/caddy` |
| Config | `$HOME/.config/caddy/Caddyfile` |
| Storage (certs, etc.) | `$HOME/.local/share/caddy/` |
| Access log | `$HOME/.local/share/caddy/access.log` |
| Runit service | `$SVDIR/caddy/run` |
| Runit log | `$PREFIX/var/log/sv/caddy/current` |

Key settings: `http_port 8080`, `auto_https off` (Cloudflare handles TLS), `admin off`.

---

## cloudflared (Cloudflare Tunnel)

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/cloudflared` |
| Config | `$HOME/.cloudflared/config.yml` |
| Tunnel credentials | `$HOME/.cloudflared/<tunnel-uuid>.json` |
| Runit service | `$SVDIR/cloudflared/run` |
| Runit log script | `$SVDIR/cloudflared/log/run` |
| Runit log output | `$PREFIX/var/log/sv/cloudflared/current` |

Tunnel UUID: `c5dbab1d-43f0-4729-bea5-fd78ba38b5f8`

Ingress rules (from `config.yml`):
```
jellyfin.al-info.net → http://localhost:8080
cloud.al-info.net    → http://localhost:8080
music.al-info.net    → http://localhost:8080
home.al-info.net     → http://localhost:8080
admin.al-info.net    → http://localhost:8080  (also via Tailscale)
*                    → http_status:404
```

All routes point to Caddy on `:8080`; Caddy does the internal routing by `Host` header.

---

## Nextcloud

Nextcloud is a **PHP application** — it has no runit service of its own. It runs through php-fpm + Caddy.

| Item | Path |
|------|------|
| Web root | `$PREFIX/share/nextcloud/` |
| App config | `$HOME/termux-homelab/config.php` |
| Data dir | `$HOME/termux-homelab/data/` |
| Database | MySQL (socket: `$PREFIX/var/run/mysqld.sock`) |
| Cache | Redis (`127.0.0.1:6379`) |

> **Note:** There is no `sv up nextcloud` — do not add it to startup commands.

---

## PHP-FPM

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/php-fpm` |
| Config dir | `$PREFIX/etc/php-fpm.d/` |
| Unix socket | `$PREFIX/var/run/php-fpm.sock` |
| Runit service | `$SVDIR/php-fpm/run` |
| Runit log | `$PREFIX/var/log/sv/php-fpm/current` |

---

## MySQL (mysqld)

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/mysqld` |
| Data dir | `$PREFIX/var/lib/mysql/` |
| Unix socket | `$PREFIX/var/run/mysqld.sock` |
| Runit service | `$SVDIR/mysqld/run` |
| Runit log | `$PREFIX/var/log/sv/mysqld/current` |

---

## Redis

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/redis-server` |
| Bind | `127.0.0.1:6379` |
| Persistence (RDB) | `$SVDIR/redis/dump.rdb` |
| Runit service | `$SVDIR/redis/run` |
| Runit log | `$PREFIX/var/log/sv/redis/current` |

Run flags: `--bind 127.0.0.1 --port 6379 --daemonize no`

---

## Jellyfin

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/jellyfin` |
| Runtime | .NET 9 (`$PREFIX/lib/dotnet/`) |
| Data dir | `$HOME/.local/share/jellyfin/` |
| Config | `$HOME/.local/share/jellyfin/config/` |
| Metadata | `$HOME/.local/share/jellyfin/metadata/` |
| Plugins | `$HOME/.local/share/jellyfin/plugins/` |
| Log dir | `$HOME/.local/share/jellyfin/log/` |
| Media source | `/storage/emulated/0/` (Android shared storage) |
| Listen port | `8096` |
| Runit service | `$SVDIR/jellyfin/run` |
| Runit log | `$PREFIX/var/log/sv/jellyfin/current` |

**Critical:** The runit `run` script must export `DOTNET_ROOT`:
```sh
export DOTNET_ROOT=/data/data/com.termux/files/usr/lib/dotnet
```
Without this, jellyfin fails with `libhostfxr.so not found` even though .NET is installed.

---

## Navidrome

| Item | Path |
|------|------|
| Binary | `$PREFIX/bin/navidrome` |
| Config | `$HOME/.local/share/navidrome/navidrome.toml` |
| Data dir | `$HOME/.local/share/navidrome/` |
| Music library | `/storage/emulated/0/Music/` |
| Listen port | `4533` (bound to `127.0.0.1`) |
| Runit service | `$SVDIR/navidrome/run` |
| Runit log | `$PREFIX/var/log/sv/navidrome/current` |

---

## Dashy (Admin Panel)

| Item | Path |
|------|------|
| Runtime | Node.js |
| App dir | `$HOME/.local/share/dashy/` |
| Config | `$HOME/.local/share/dashy/user-data/conf.yml` |
| Config backups | `$HOME/.local/share/dashy/user-data/config-backups/` |
| Listen port | `8443` (set via `PORT` env in run script) |
| Runit service | `$SVDIR/dashy/run` |
| Runit log | `$PREFIX/var/log/sv/dashy/current` |

---

## Homepage (home.al-info.net)

| Item | Path |
|------|------|
| Type | Static HTML — no server process |
| Files | `$HOME/.config/homepage/` |
| Served by | Caddy `file_server` |
