# Termux Homelab Wiki

> Android phone running a self-hosted stack entirely inside Termux — no root required.
> Domain: `al-info.net` · Platform: `aarch64` Android / Termux

## Pages

- [[Architecture]] — topology diagram, traffic flow, port map
- [[Services]] — per-service file locations, configs, data dirs
- [[Dependencies]] — startup order and inter-service dependencies
- [[Operations]] — cold start, status checks, log export
- [[Restore]] — full rebuild from a factory-new Android device
- [[Networking]] — Cloudflare Tunnel, Cloudflare One (WARP), Tailscale
- [[Claude-Agent-Skill]] — what to load into a future Claude session

## Quick Reference

| URL | Service | Port |
|-----|---------|------|
| `cloud.al-info.net` | Nextcloud (PHP) | via php-fpm sock |
| `jellyfin.al-info.net` | Jellyfin | 8096 |
| `music.al-info.net` | Navidrome | 4533 |
| `home.al-info.net` | Homepage | static |
| `admin.al-info.net` | Dashy | 8443 |

All public traffic enters via **Cloudflare Tunnel → Caddy (:8080)**.
Admin panel (`admin.al-info.net`) is also accessible over **Tailscale** when the app is on.
