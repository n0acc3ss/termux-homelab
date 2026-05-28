<?php
require_once __DIR__ . '/db.php';

// ── Session ────────────────────────────────────────────────────
function session_boot(string $name): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    session_name($name);
    session_set_cookie_params([
        'lifetime' => SESSION_TTL,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

function csrf(): string   { return $_SESSION['csrf'] ?? ''; }
function csrf_ok(string $t): bool { return $t !== '' && hash_equals(csrf(), $t); }

// ── Rate limiting ──────────────────────────────────────────────
function rate_ok(string $ip): bool {
    $st = db()->prepare('SELECT hits, since FROM rate WHERE ip=?');
    $st->execute([$ip]);
    $r = $st->fetch();
    if (!$r || time() - $r['since'] > RATE_WIN) return true;
    return (int)$r['hits'] < RATE_MAX;
}

function rate_hit(string $ip): void {
    $now = time();
    $st  = db()->prepare('SELECT hits, since FROM rate WHERE ip=?');
    $st->execute([$ip]);
    $r = $st->fetch();
    if (!$r || time() - $r['since'] > RATE_WIN) {
        db()->prepare('INSERT OR REPLACE INTO rate (ip,hits,since) VALUES (?,1,?)')->execute([$ip, $now]);
    } else {
        db()->prepare('UPDATE rate SET hits=hits+1 WHERE ip=?')->execute([$ip]);
    }
}

function rate_clear(string $ip): void {
    db()->prepare('DELETE FROM rate WHERE ip=?')->execute([$ip]);
}

// ── User auth ──────────────────────────────────────────────────
function auth_login(string $u, string $p): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!rate_ok($ip)) return ['ok'=>false, 'msg'=>'Too many attempts. Try again later.'];

    $st = db()->prepare('SELECT * FROM users WHERE username=?');
    $st->execute([$u]);
    $row = $st->fetch();

    if (!$row || !password_verify($p, $row['password'])) {
        rate_hit($ip);
        return ['ok'=>false, 'msg'=>'Invalid credentials.'];
    }
    if ($row['role'] === 'pending') return ['ok'=>false, 'msg'=>'Account pending admin approval.'];
    if ($row['role'] === 'banned')  return ['ok'=>false, 'msg'=>'Account suspended.'];

    rate_clear($ip);
    session_regenerate_id(true);
    $_SESSION['uid']  = $row['id'];
    $_SESSION['user'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    return ['ok'=>true, 'user'=>$row['username']];
}

function auth_register(string $first, string $last, string $u, string $p): array {
    if (strlen($p) < 8)    return ['ok'=>false, 'msg'=>'Password must be at least 8 characters.'];
    if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,32}$/', $u))
        return ['ok'=>false, 'msg'=>'Username must be 3–32 chars: letters, digits, _ - .'];

    $st = db()->prepare('SELECT id FROM users WHERE username=?');
    $st->execute([$u]);
    if ($st->fetch()) return ['ok'=>false, 'msg'=>'Username already taken.'];

    db()->prepare('INSERT INTO users (username,first_name,last_name,password) VALUES (?,?,?,?)')
        ->execute([$u, $first, $last, password_hash($p, PASSWORD_DEFAULT)]);
    return ['ok'=>true];
}

function auth_logout(): void { session_unset(); session_destroy(); }

function me(): ?array {
    if (empty($_SESSION['uid'])) return null;
    return ['id'=>$_SESSION['uid'], 'user'=>$_SESSION['user'], 'role'=>$_SESSION['role']];
}

// ── Admin auth ─────────────────────────────────────────────────
function admin_verify(string $u, string $p): bool {
    if ($u !== ADMIN_USER || !ADMIN_HASH) return false;
    return password_verify($p, ADMIN_HASH);
}

function is_admin(): bool    { return !empty($_SESSION['is_admin']); }

function require_admin(): void {
    if (!is_admin()) { header('Location: /admin/'); exit; }
}
