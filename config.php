<?php
// ── Paths ──────────────────────────────────────────────────────
define('DATA_DIR', __DIR__ . '/data');
define('DB_FILE',  DATA_DIR . '/homelab.db');
define('SVC_FILE', DATA_DIR . '/services.json');
define('SET_FILE', DATA_DIR . '/settings.json');

// ── Admin credentials ──────────────────────────────────────────
// Generate your hash once:  php -r "echo password_hash('YourPass', PASSWORD_DEFAULT);"
// Then paste it as ADMIN_HASH below. Leave empty to enter setup mode.
define('ADMIN_USER', 'admin');
define('ADMIN_HASH', '');   // ← paste bcrypt hash here

// ── Session ────────────────────────────────────────────────────
define('SESSION_TTL',   86400);  // 24 h
define('SESS_USER',  'hl_u');
define('SESS_ADMIN', 'hl_a');

// ── Rate limiting (login) ──────────────────────────────────────
define('RATE_MAX',  5);    // max failed attempts
define('RATE_WIN',  900);  // lock-out window (seconds)
