# Claude Agent Skill — Homelab Context

This page defines what a future Claude agent session needs to know to work effectively on this homelab.

---

## What to Load at Session Start

Paste this into the first message of a new Claude Code session, or save it as a skill file:

---

```
## Termux Homelab — Session Context

This is a self-hosted homelab running entirely inside Termux on Android (aarch64, no root).
Domain: al-info.net
Wiki: ~/termux-homelab/wiki/ (Obsidian-compatible markdown)

### Key paths
- $PREFIX  = /data/data/com.termux/files/usr
- $HOME    = /data/data/com.termux/files/home
- $SVDIR   = $PREFIX/var/service          ← runit service definitions
- Logs     = $PREFIX/var/log/sv/<service>/current
- Caddy config = $HOME/.config/caddy/Caddyfile
- Cloudflared config = $HOME/.cloudflared/config.yml
- Nextcloud web root = $PREFIX/share/nextcloud/  (PHP app, no runit service)
- Navidrome config   = $HOME/.local/share/navidrome/navidrome.toml
- Dashy app+config   = $HOME/.local/share/dashy/

### Services managed by runit (sv)
mysqld, redis, php-fpm, jellyfin, navidrome, dashy, caddy, cloudflared
NOTE: "nextcloud" is NOT a runit service — it's PHP served through php-fpm+caddy.

### Start all services
sv up mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared

### Check status
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared

### Known quirks / past fixes
1. jellyfin runit run script MUST export DOTNET_ROOT=$PREFIX/lib/dotnet
   Without it: "libhostfxr.so not found" even though dotnet9.0 is installed.
   Fix is in $SVDIR/jellyfin/run.

2. cloudflared log/ dir needs its own run script ($SVDIR/cloudflared/log/run).
   Without it: "down: log:" in sv status. Fix: svlogd run script pointing to
   $PREFIX/var/log/sv/cloudflared.

3. Cloudflare One (WARP) Android app can destabilise the cloudflared tunnel.
   If tunnel drops while WARP is on: add Termux to WARP Split Tunnel excludes.

4. Tailscale gives private access to admin.al-info.net → Dashy (:8443).
   No config needed — works at OS level when Tailscale app is running.

### Traffic flow
Internet → Cloudflare (TLS) → cloudflared tunnel → Caddy:8080 → services
All *.al-info.net routes point to Caddy; Caddy routes by Host header.

### Full wiki
Read ~/termux-homelab/wiki/ for per-service file locations, dependency order,
restore instructions, and networking details.
```

---

## Skill File Location

If using the Superpowers skill system, save the above block as:

```
~/.claude/plugins/<your-plugin>/skills/termux-homelab.md
```

With frontmatter:
```yaml
---
name: termux-homelab
description: Use when working on the al-info.net Termux homelab — provides service locations, known quirks, startup commands, and topology.
---
```

---

## Memory File

Save to `~/.claude/projects/.../memory/project_termux_homelab.md`:

```markdown
---
name: termux-homelab-setup
description: Termux homelab running caddy+cloudflared+nextcloud+jellyfin+navidrome+dashy on Android
metadata:
  type: project
---

Android homelab on al-info.net. Runs in Termux ($PREFIX=/data/data/com.termux/files/usr).
Services: mysqld, redis, php-fpm, caddy, cloudflared, jellyfin, navidrome, dashy (all via runit sv).
Nextcloud is PHP-only — no runit service for it.
Full wiki at ~/termux-homelab/wiki/.

**Why:** Self-hosted cloud/media/music stack on a phone, no router config needed (Cloudflare tunnel).

**How to apply:** Before touching any service, read [[Services]] and [[Dependencies]] wiki pages.
Check sv status before assuming anything is running.
```
