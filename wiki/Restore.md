# Restore — Factory-New Android Device

Complete rebuild guide. Assumes a fresh Android device with nothing installed.

---

## 1. Install Termux

Install **Termux** from [F-Droid](https://f-droid.org/packages/com.termux/) (not Play Store — the Play Store version is outdated and unsupported).

```sh
# After opening Termux for the first time:
pkg update && pkg upgrade -y
```

---

## 2. Install Core Packages

```sh
pkg install -y \
  runit \
  caddy \
  cloudflared \
  php php-fpm \
  mariadb \
  redis \
  navidrome \
  jellyfin \
  nodejs \
  dotnet9.0 \
  git curl wget
```

---

## 3. Set Up runit Service Directory

```sh
# runit SVDIR is already set to $PREFIX/var/service by Termux
# Verify:
echo $SVDIR   # should be /data/data/com.termux/files/usr/var/service
```

---

## 4. Restore Service Run Scripts

Clone your homelab repo (or copy from backup):

```sh
git clone <your-repo-url> ~/termux-homelab
```

Then install the runit run scripts:

### caddy
```sh
mkdir -p $SVDIR/caddy/log
# run script is managed by the caddy package — verify it exists:
ls $SVDIR/caddy/run
```

### cloudflared
```sh
mkdir -p $SVDIR/cloudflared/log
cat > $SVDIR/cloudflared/run << 'EOF'
#!/data/data/com.termux/files/usr/bin/sh
exec cloudflared tunnel --config /data/data/com.termux/files/home/.cloudflared/config.yml run 2>&1
EOF
chmod +x $SVDIR/cloudflared/run

# Create the log run script (critical — without it, cloudflared log crashes)
mkdir -p $PREFIX/var/log/sv/cloudflared
cat > $SVDIR/cloudflared/log/run << 'EOF'
#!/data/data/com.termux/files/usr/bin/sh
exec svlogd -tt /data/data/com.termux/files/usr/var/log/sv/cloudflared
EOF
chmod +x $SVDIR/cloudflared/log/run
```

### jellyfin
```sh
mkdir -p $SVDIR/jellyfin/log
cat > $SVDIR/jellyfin/run << 'EOF'
#!/data/data/com.termux/files/usr/bin/sh
export DOTNET_ROOT=/data/data/com.termux/files/usr/lib/dotnet
exec /data/data/com.termux/files/usr/bin/jellyfin 2>&1
EOF
chmod +x $SVDIR/jellyfin/run
```

> **Why DOTNET_ROOT?** Runit starts services with a minimal environment. Without this export, jellyfin fails with `libhostfxr.so not found` even though dotnet9.0 is installed.

### dashy
```sh
mkdir -p $HOME/.local/share/dashy
# Install dashy:
cd $HOME/.local/share/dashy
git clone https://github.com/Lissy93/dashy.git . && yarn install && yarn build

mkdir -p $SVDIR/dashy/log
cat > $SVDIR/dashy/run << 'EOF'
#!/data/data/com.termux/files/usr/bin/sh
export PORT=8443
export NODE_ENV=production
cd /data/data/com.termux/files/home/.local/share/dashy
exec node server.js 2>&1
EOF
chmod +x $SVDIR/dashy/run
```

---

## 5. Restore Configs

```sh
# Caddy
mkdir -p $HOME/.config/caddy
cp ~/termux-homelab/wiki/reference/Caddyfile $HOME/.config/caddy/Caddyfile

# Cloudflare tunnel credentials (from backup — contains secrets, do not commit)
mkdir -p $HOME/.cloudflared
# Copy config.yml and <tunnel-uuid>.json from your secure backup

# Navidrome
mkdir -p $HOME/.local/share/navidrome
cat > $HOME/.local/share/navidrome/navidrome.toml << 'EOF'
DataFolder  = "/data/data/com.termux/files/home/.local/share/navidrome"
MusicFolder = "/storage/emulated/0/Music"
Address     = "127.0.0.1"
Port        = 4533
LogLevel    = "info"
EOF

# Dashy config
cp ~/termux-homelab/wiki/reference/conf.yml $HOME/.local/share/dashy/user-data/conf.yml

# Homepage static files
mkdir -p $HOME/.config/homepage
cp ~/termux-homelab/homepage/* $HOME/.config/homepage/
```

---

## 6. Set Up MySQL

```sh
mysql_install_db   # initialise data directory
sv up mysqld
sleep 3
mysql_secure_installation

# Create Nextcloud database
mysql -e "CREATE DATABASE nextcloud CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -e "CREATE USER 'nextcloud'@'localhost' IDENTIFIED BY '<password>';"
mysql -e "GRANT ALL ON nextcloud.* TO 'nextcloud'@'localhost';"
```

---

## 7. Deploy Nextcloud (PHP app)

```sh
# Nextcloud web root is already part of the homelab repo
ls ~/termux-homelab/   # index.php, config.php, data/, etc.

# Edit config.php — set ADMIN_HASH:
php -r "echo password_hash('YourAdminPassword', PASSWORD_DEFAULT);"
# Paste output into config.php ADMIN_HASH constant
```

---

## 8. Grant Storage Permission

Termux needs access to Android shared storage for Jellyfin and Navidrome:

```sh
termux-setup-storage
# Accept the permission prompt on the Android system dialog
# Music will be at /storage/emulated/0/Music/
# Jellyfin watches /storage/emulated/0/download/
```

---

## 9. Start Everything

```sh
sv up mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared
sleep 15
sv status mysqld redis php-fpm jellyfin navidrome dashy caddy cloudflared
```

---

## 10. Cloudflare DNS

Log into the Cloudflare dashboard and verify the tunnel (`c5dbab1d-43f0-4729-bea5-fd78ba38b5f8`) is connected. All DNS records for `al-info.net` should point to the tunnel — no IP addresses needed.

---

## What Is NOT in This Repo (Secrets)

These must come from a secure backup — never commit them:

| File | Contents |
|------|----------|
| `~/.cloudflared/config.yml` | Tunnel UUID |
| `~/.cloudflared/<uuid>.json` | Tunnel credentials (secret) |
| `~/termux-homelab/config.php` | `ADMIN_HASH` value |
| MySQL passwords | Set during `mysql_secure_installation` |
