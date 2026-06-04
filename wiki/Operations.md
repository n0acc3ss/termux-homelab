# Operations

## Cold Start (All Services)

Run this after a fresh Termux boot or after killing everything:

```sh
sv up mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared
```

Wait ~15 seconds, then verify:

```sh
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared
```

All lines should show `run: <name>: (pid XXXXX) Ns`.

## Start / Stop / Restart Individual Services

```sh
sv up <service>       # start
sv down <service>     # stop
sv restart <service>  # restart
```

Examples:
```sh
sv restart caddy
sv restart cloudflared
sv restart jellyfin
```

## Status Check

```sh
# All homelab services at once
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared

# Short form once all are running
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared \
  | awk '{print $1, $2, $3}'
```

Healthy output looks like:
```
run: caddy: (pid 25022) 3874s; run: log: (pid 25021) 3874s
run: cloudflared: (pid 25030) 3874s; run: log: (pid 25029) 3874s
...
```

Red flags:
- `down:` — service is not running
- `down: log:` — the logger for a service crashed (service may still run)
- `fail:` — runit can't find the service directory

## Log Export

### Tail live logs

```sh
tail -f $PREFIX/var/log/sv/caddy/current
tail -f $PREFIX/var/log/sv/cloudflared/current
tail -f $PREFIX/var/log/sv/jellyfin/current
tail -f $PREFIX/var/log/sv/navidrome/current
tail -f $PREFIX/var/log/sv/dashy/current
tail -f $PREFIX/var/log/sv/mysqld/current
tail -f $PREFIX/var/log/sv/redis/current
tail -f $PREFIX/var/log/sv/php-fpm/current
```

### Export last N lines to file

```sh
tail -500 $PREFIX/var/log/sv/jellyfin/current > ~/jellyfin-export.log
```

### Export all service logs at once (for debugging)

```sh
for svc in caddy cloudflared jellyfin navidrome dashy mysqld redis php-fpm; do
  echo "=== $svc ===" >> ~/homelab-logs.txt
  tail -100 $PREFIX/var/log/sv/$svc/current >> ~/homelab-logs.txt 2>/dev/null
done
```

### Caddy access log

```sh
tail -f $HOME/.local/share/caddy/access.log
```

### Jellyfin application log (separate from runit log)

```sh
ls $HOME/.local/share/jellyfin/log/
tail -f $HOME/.local/share/jellyfin/log/jellyfin*.log
```

## Useful Checks

```sh
# Confirm Caddy is listening on 8080
ss -tlnp | grep 8080

# Confirm Jellyfin is listening on 8096
ss -tlnp | grep 8096

# Confirm Navidrome is listening on 4533
ss -tlnp | grep 4533

# Confirm Redis is up
redis-cli ping   # should return PONG

# Confirm MySQL is up
mysql -e "SELECT 1;"
```
