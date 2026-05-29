<?php
require_once dirname(__DIR__) . '/includes/auth.php';
session_boot(SESS_ADMIN);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

function jout(array $d): void { echo json_encode($d); exit; }
function ok(array $x = []): void  { jout(array_merge(['ok'=>true],  $x)); }
function fail(string $m, int $c = 400): void { http_response_code($c); jout(['ok'=>false,'msg'=>$m]); }

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? ($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// ── Admin login (unauthenticated) ──────────────────────────────
if ($action === 'login') {
    if ($method !== 'POST') fail('Method not allowed.', 405);
    // CSRF check
    if (!csrf_ok($body['csrf'] ?? '')) fail('Invalid request token.');
    // Setup-mode guard
    if (!ADMIN_HASH) fail('Admin password not configured. Set ADMIN_HASH in config.php.');
    $u = trim($body['username'] ?? '');
    $p = $body['password'] ?? '';
    if (admin_verify($u, $p)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        ok();
    } else {
        fail('Invalid credentials.');
    }
    exit;
}

if ($action === 'logout') {
    auth_logout();
    ok();
    exit;
}

// ── All other actions require admin session ────────────────────
if (!is_admin()) fail('Unauthorized.', 403);
$csrf = $body['csrf'] ?? '';

switch ($action) {

    // ── Services ────────────────────────────────────────────────
    case 'get_services':
        ok(['data' => load_json(SVC_FILE, default_services())]);
        break;

    case 'save_services':
        if (!csrf_ok($csrf)) fail('CSRF error.');
        $data = $body['data'] ?? null;
        if (!is_array($data) || !isset($data['sections'])) fail('Invalid payload.');
        save_json(SVC_FILE, $data);
        ok();
        break;

    // ── Settings ─────────────────────────────────────────────────
    case 'get_settings':
        ok(['data' => load_json(SET_FILE, default_settings())]);
        break;

    case 'save_settings':
        if (!csrf_ok($csrf)) fail('CSRF error.');
        $data = $body['data'] ?? null;
        if (!is_array($data)) fail('Invalid payload.');
        // Merge with defaults so we never lose keys
        $current = load_json(SET_FILE, default_settings());
        save_json(SET_FILE, array_merge($current, $data));
        ok();
        break;

    // ── Users ─────────────────────────────────────────────────────
    case 'get_users':
        $rows = db()->query('SELECT id,username,first_name,last_name,role,ts FROM users ORDER BY ts DESC')->fetchAll();
        ok(['users' => $rows]);
        break;

    case 'set_role':
        if (!csrf_ok($csrf)) fail('CSRF error.');
        $id   = (int)($body['id'] ?? 0);
        $role = $body['role'] ?? '';
        if (!$id || !in_array($role, ['user','pending','banned'], true)) fail('Invalid params.');
        db()->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role, $id]);
        ok();
        break;

    case 'delete_user':
        if (!csrf_ok($csrf)) fail('CSRF error.');
        $id = (int)($body['id'] ?? 0);
        if (!$id) fail('Invalid id.');
        db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        ok();
        break;

    // ── Admin password change ─────────────────────────────────
    case 'check_setup':
        ok(['configured' => (bool)ADMIN_HASH]);
        break;

    default:
        fail('Unknown action.', 404);
}
