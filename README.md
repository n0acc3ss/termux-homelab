# termux-homelab

Android Termux homelab running on al-info.net — managed with runit (`sv`).

## Services

| Service | Status | URL |
|---|---|---|
| Caddy | reverse proxy | — |
| cloudflared | Cloudflare tunnel | — |
| Jellyfin | media server | jellyfin.al-info.net |
| Navidrome | music server | music.al-info.net |
| Nextcloud | cloud storage | cloud.al-info.net |
| Dashy | dashboard | admin.al-info.net |
| Homepage | landing page | home.al-info.net |
| MySQL | database | local |
| Redis | cache | local |
| php-fpm | PHP FastCGI | local |

## Layout

```
services/
  caddy/        Caddyfile + runit run script
  cloudflared/  config.yml (tunnel config, no credentials)
  navidrome/    navidrome.toml
  dashy/        runit run script
  jellyfin/     runit run script
  mysqld/       runit run script
  php-fpm/      runit run script
  redis/        runit run script
  crond/        runit run script
homepage/       Static homepage served at home.al-info.net
```

## Applying changes

```sh
# After editing a service config, restart it:
sv restart caddy
sv restart navidrome
# etc.
```

## Secrets not in repo

- `~/.cloudflared/*.json` — Cloudflare tunnel credentials
- `~/.config/secrets/` — any API keys
