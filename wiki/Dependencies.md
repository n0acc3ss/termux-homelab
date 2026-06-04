# Dependencies

## Startup Order

Services must come up in this order — runit starts them all simultaneously, but the ones lower in the chain will retry until their dependencies are ready.

```
Layer 0 — Infrastructure (no deps)
  ├── mysqld
  └── redis

Layer 1 — PHP runtime (depends on mysqld socket)
  └── php-fpm

Layer 2 — Application processes (no hard deps, but need their data dirs)
  ├── jellyfin   (needs DOTNET_ROOT set in run script)
  ├── navidrome
  └── dashy

Layer 3 — Ingress (depends on all of the above being up)
  ├── caddy      (proxies to jellyfin:8096, navidrome:4533, dashy:8443,
  │               php-fpm.sock for Nextcloud, and serves homepage static files)
  └── cloudflared (tunnels external traffic into caddy:8080)
```

## Dependency Matrix

| Service | Depends on | Needed by |
|---------|-----------|-----------|
| mysqld | — | php-fpm, Nextcloud (via php-fpm) |
| redis | — | Nextcloud (via php-fpm) |
| php-fpm | mysqld socket, redis | Caddy (Nextcloud route) |
| jellyfin | .NET 9 runtime, DOTNET_ROOT env | Caddy |
| navidrome | Music dir on shared storage | Caddy |
| dashy | Node.js | Caddy |
| caddy | php-fpm.sock, :8096, :4533, :8443, homepage dir | cloudflared |
| cloudflared | caddy:8080, Cloudflare credentials | public internet access |

## What Nextcloud Actually Needs

Nextcloud has no runit service. It is purely PHP code on disk. The full chain it requires:

```
mysqld  →  php-fpm  →  Caddy  →  cloudflared  →  internet
redis   ↗
```

Do **not** add `nextcloud` to any `sv up` command.
