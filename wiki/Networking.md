# Networking

## Cloudflare Tunnel (cloudflared)

The tunnel creates an **outbound-only** encrypted connection from Termux to Cloudflare's edge. No inbound ports are opened on the Android device or router. Cloudflare terminates TLS from visitors and forwards HTTP to the tunnel, which arrives at Caddy on `:8080`.

```
Visitor → HTTPS → Cloudflare Edge → Tunnel → cloudflared → Caddy:8080
```

Config: `~/.cloudflared/config.yml`
Credentials: `~/.cloudflared/c5dbab1d-43f0-4729-bea5-fd78ba38b5f8.json`

All five routes (`jellyfin`, `cloud`, `music`, `home`, `admin`) point to `http://localhost:8080`. Caddy distinguishes them by the `Host` header.

---

## Cloudflare One (WARP / Zero Trust) — Android App

The **Cloudflare One** Android app enrolls the device into Cloudflare Zero Trust and routes device traffic through WARP (a VPN).

### Does it conflict with cloudflared?

Potentially, yes. Both use Cloudflare's network infrastructure but in opposite directions:

- **cloudflared** = outbound tunnel from Termux → Cloudflare
- **WARP** = device-level VPN, intercepts all traffic including Termux's

WARP can interfere with cloudflared's keepalive connection to Cloudflare's edge, causing the log process to crash or the tunnel to become unstable.

**If cloudflared becomes unstable while WARP is on:**
1. Open the Cloudflare One app → Settings → VPN Configuration → **Split Tunneling**
2. Add Termux (`com.termux`) to the **exclude** list so its traffic bypasses WARP
3. Or add `127.0.0.1` and `::1` to Local Domain Fallback

---

## Tailscale

Tailscale creates a private mesh VPN between your devices. Currently configured for **admin access only**.

- `admin.al-info.net` is routed by Caddy to `127.0.0.1:8443` (Dashy)
- When Tailscale is active, the admin panel is also reachable via the phone's Tailscale IP directly on port 8443
- Tailscale app must be running on the Android device for this to work

### Current State

`admin.al-info.net` is **Tailscale-only**. The Caddy `@admin` matcher requires both the correct hostname AND a source IP in `100.64.0.0/10` (the Tailscale CGNAT range):

```caddy
@admin {
    host admin.al-info.net
    remote_ip 100.64.0.0/10
}
```

Public traffic arriving via the Cloudflare tunnel comes from `127.0.0.1` — it fails the IP check and gets the 404 catch-all. Tailscale traffic arrives directly from the phone's Tailscale IP and passes through to Dashy on `:8443`.
