# Homepage Dynamic Highway Credits Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert `homepage/index.html` into `homepage/index.php` so the animated highway credit strip reads its pills from the admin-editable `data/settings.json` instead of being hardcoded.

**Architecture:** `homepage/index.php` requires `includes/db.php` (already exists) to call `load_json(SET_FILE, default_settings())`, which gives the `credits` array. The credits are split by index across lanes 1 and 3; lane 2 stays hardcoded status pills (services online, 100% private, etc.). A locally defined `render_hw_icon()` handles `di:`, `lucide:`, and emoji — no change to `index.php`. After `index.php` is verified working, `index.html` is deleted so Caddy's `php_fastcgi` serves the PHP version for directory requests to `/homepage/`.

**Tech Stack:** PHP 8, SQLite via PDO (already running), Caddy `php_fastcgi` (already configured for the `al-info.net` block), pure-CSS highway animation (already in the file).

---

## Context

- `~/termux-homelab/homepage/index.html` — static HTML, **this file becomes index.php**
- `~/termux-homelab/includes/db.php` — provides `load_json()`, `save_json()`, `default_settings()`
- `~/termux-homelab/config.php` — defines `SET_FILE`, `DATA_DIR` etc. (required by db.php)
- `~/termux-homelab/data/settings.json` — `credits` key: array of `{"icon":"di:caddy","text":"Caddy"}` objects
- `~/termux-homelab/index.php` — **DO NOT TOUCH** — defines `render_icon()` locally; we copy the same logic
- `~/.config/caddy/Caddyfile` — `@home` block: root=termux-homelab, `php_fastcgi` unix socket, `file_server` — no change needed

**Current credits in settings.json (18 entries):** di:caddy, di:php, di:mysql, di:nextcloud, di:jellyfin, di:navidrome, di:tailscale-light, di:cloudflare, di:android, di:anthropic, di:ubuntu-linux, di:powershell, di:copyparty, di:dashboard-icons, di:obsidian, di:dashy, di:homepage, di:oh-my-posh

**Lane layout after conversion:**
- Lane 1 (34s, left→right): credits indices 0..N/2-1 (first half)
- Lane 2 (22s, right→left): hardcoded status pills (never changes, not admin-editable)
- Lane 3 (15s, left→right): credits indices N/2..N-1 (second half)

**Icon rendering in pills:** `di:NAME` → 12×12px img from jsdelivr CDN with `loading="lazy"`. `lucide:NAME` → `<i data-lucide="NAME">` (Lucide already loaded at bottom of page). Emoji/text → escaped as-is.

---

## File Map

| Action | File |
|--------|------|
| Create | `~/termux-homelab/homepage/index.php` (copy of index.html + PHP additions) |
| Delete | `~/termux-homelab/homepage/index.html` (after index.php is verified) |
| No change | `~/termux-homelab/includes/db.php` |
| No change | `~/termux-homelab/index.php` |
| No change | `~/.config/caddy/Caddyfile` |

---

## Task 1: Create `homepage/index.php` with dynamic highway

**Files:**
- Create: `~/termux-homelab/homepage/index.php`

- [ ] **Step 1: Copy index.html to index.php**

```bash
cp ~/termux-homelab/homepage/index.html ~/termux-homelab/homepage/index.php
```

- [ ] **Step 2: Add PHP header at the very top of index.php**

Open `homepage/index.php`. The file currently starts with `<!DOCTYPE html>`. Insert the following block **before** that line (as the new first lines of the file):

```php
<?php
require_once dirname(__DIR__) . '/includes/db.php';
$st = load_json(SET_FILE, default_settings());

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function render_hw_icon(string $raw): string {
    $raw = trim($raw);
    if (str_starts_with($raw, 'lucide:')) {
        $name = h(substr($raw, 7));
        return "<i data-lucide=\"$name\"></i>";
    }
    if (str_starts_with($raw, 'di:')) {
        $name = h(strtolower(str_replace(' ', '-', substr($raw, 3))));
        return "<img src=\"https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/{$name}.png\" width=\"12\" height=\"12\" loading=\"lazy\" alt=\"\" onerror=\"this.style.display='none'\"/>";
    }
    return h($raw);
}
?>
```

- [ ] **Step 3: Add CSS for pill icons in the highway**

Find the `.pdot-b{...}` line in the `<style>` block (last of the pdot rules, around line 222 after edits). Add these rules immediately after it:

```css
.hw-track img{width:12px;height:12px;flex-shrink:0;border-radius:2px;object-fit:contain;vertical-align:middle;}
.hw-track i[data-lucide]{display:inline-flex;width:12px;height:12px;flex-shrink:0;}
.hw-track i[data-lucide] svg{width:12px;height:12px;stroke:var(--sub);}
```

- [ ] **Step 4: Replace the three hardcoded `hw-lane` divs with PHP**

Find this block in the HTML (around line 700 after the PHP header addition):

```html
    <div class="highway">
      <div class="hw-lane"><div class="hw-track" style="--dur:34s"><span class="pill">...Caddy...</span>...[duplicated]...</div></div>
      <div class="hw-lane"><div class="hw-track" style="--dur:22s"><span class="pill">...services online...</span>...[duplicated]...</div></div>
      <div class="hw-lane"><div class="hw-track" style="--dur:15s"><span class="pill">...Nextcloud...</span>...[duplicated]...</div></div>
    </div>
```

Replace the entire `<div class="highway">...</div>` block with:

```php
    <div class="highway">
<?php
$credits = $st['credits'] ?? [];
$half    = (int)ceil(count($credits) / 2);
$lane1   = array_slice($credits, 0, $half);
$lane3   = array_slice($credits, $half);

// Helper: render one set of pills for a track (called twice for seamless loop)
function hw_pills(array $items): string {
    $out = '';
    foreach ($items as $cr) {
        $icon = render_hw_icon($cr['icon'] ?? '');
        $text = h($cr['text'] ?? '');
        $out .= "<span class=\"pill\">{$icon}{$text}</span>";
    }
    return $out;
}

// Lane 1 — first half of credits, slow
$p1 = hw_pills($lane1);
echo "      <div class=\"hw-lane\"><div class=\"hw-track\" style=\"--dur:34s\">{$p1}{$p1}</div></div>\n";

// Lane 2 — hardcoded status strip, reversed
$status = [
    ['dot'=>'pdot-g','text'=>'services online'],
    ['dot'=>'pdot-p','text'=>'100% private'],
    ['dot'=>'pdot-t','text'=>'self-hosted'],
    ['dot'=>'pdot-p','text'=>'restricted access'],
    ['dot'=>'pdot-g','text'=>'zero tracking'],
    ['dot'=>'pdot-t','text'=>'open source'],
];
$p2 = '';
foreach ($status as $s) {
    $p2 .= "<span class=\"pill\"><span class=\"pdot {$s['dot']}\"></span>" . h($s['text']) . "</span>";
}
echo "      <div class=\"hw-lane\"><div class=\"hw-track\" style=\"--dur:22s\">{$p2}{$p2}</div></div>\n";

// Lane 3 — second half of credits, fast
$p3 = hw_pills($lane3);
echo "      <div class=\"hw-lane\"><div class=\"hw-track\" style=\"--dur:15s\">{$p3}{$p3}</div></div>\n";
?>
    </div>
```

- [ ] **Step 5: Verify PHP syntax**

```bash
php -l ~/termux-homelab/homepage/index.php
```

Expected output: `No syntax errors detected in .../homepage/index.php`

- [ ] **Step 6: Test the page renders via Caddy**

```bash
curl -si http://localhost:8080/homepage/index.php -H "Host: al-info.net" | head -5
```

Expected: `HTTP/1.1 200 OK` and `Content-Type: text/html`

- [ ] **Step 7: Verify credits appear in the output**

```bash
curl -s http://localhost:8080/homepage/index.php -H "Host: al-info.net" | python3 -c "
import sys, re
html = sys.stdin.read()
pills = re.findall(r'class=\"pill\"[^>]*>.*?</span>', html)
print(f'Pill count (including duplicates): {len(pills)}')
# Should be (18 credits + 6 status) * 2 = 48
for p in pills[:6]: print(' ', p[:80])
"
```

Expected: `Pill count (including duplicates): 48` and pill content showing credit names.

---

## Task 2: Replace index.html with index.php as the directory default

**Files:**
- Delete: `~/termux-homelab/homepage/index.html`

When both `index.html` and `index.php` exist, Caddy's `file_server` serves `index.html` for directory requests (`/homepage/`). Removing `index.html` forces Caddy's `php_fastcgi` to handle the directory request via `index.php`.

- [ ] **Step 1: Confirm index.php is working before deleting index.html**

```bash
curl -si http://localhost:8080/homepage/index.php -H "Host: al-info.net" | head -3
```

Expected: `HTTP/1.1 200 OK` — only proceed if this passes.

- [ ] **Step 2: Delete index.html**

```bash
rm ~/termux-homelab/homepage/index.html
```

- [ ] **Step 3: Verify directory request now serves PHP**

```bash
curl -si http://localhost:8080/homepage/ -H "Host: al-info.net" | head -5
```

Expected: `HTTP/1.1 200 OK` with `Content-Type: text/html` (PHP-rendered, not a redirect).

```bash
curl -s http://localhost:8080/homepage/ -H "Host: al-info.net" | python3 -c "
import sys
html = sys.stdin.read()
print('Has highway:', 'hw-lane' in html)
print('Has Caddy pill:', 'Caddy' in html)
print('Has services online:', 'services online' in html)
"
```

Expected: all three `True`.

- [ ] **Step 4: Commit**

```bash
cd ~/termux-homelab
git add homepage/index.php
git rm homepage/index.html
git commit -m "feat: homepage highway reads credits from admin-editable settings.json"
```

---

## Verification Checklist

| Check | Command | Expected |
|-------|---------|----------|
| PHP syntax clean | `php -l homepage/index.php` | No syntax errors |
| Page loads | `curl -si localhost:8080/homepage/ -H "Host: al-info.net" \| head -1` | `HTTP/1.1 200 OK` |
| Credits dynamic | Change a credit in admin panel → reload `/homepage/` | New text appears in highway |
| Pill count correct | grep/count `.pill` spans | (N_credits + 6_status) × 2 |
| No index.html left | `ls homepage/` | Only `index.php` listed |
| Lucide icons work | Add `lucide:shield` credit in admin → reload | Shield SVG renders in pill |
| DI icons work | `di:caddy` credit shows in lane | 12×12px Caddy logo in pill |

---

## Notes for the implementer

- `dirname(__DIR__)` from `homepage/index.php` resolves to `termux-homelab/` (the project root), which is where `includes/db.php` and `config.php` live. This is correct — do not change the path.
- `hw_pills()` is defined inside the PHP block that replaces the highway HTML. PHP function definitions are global-scoped, so calling `hw_pills()` twice (for lane 1 and lane 3) works fine.
- If `$credits` is empty (e.g. settings.json missing), lane 1 and lane 3 render no pills — the highway shows only the status lane. This is acceptable graceful degradation.
- The Lucide script is already loaded at the bottom of `index.html`. After converting to PHP, verify it's still there and that `lucide.createIcons()` is called. If not, add `<script>lucide.createIcons();</script>` before `</body>`.
- Do NOT add `session_boot()` or auth checks — `homepage/index.php` is public and reads only settings, not user data.
