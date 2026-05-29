<?php
require_once dirname(__DIR__) . '/includes/auth.php';
session_boot(SESS_USER);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

function jout(array $d): void { echo json_encode($d); exit; }
function ok(array $x = []): void  { jout(array_merge(['ok'=>true],  $x)); }
function fail(string $m, int $c = 400): void { http_response_code($c); jout(['ok'=>false,'msg'=>$m]); }

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? ($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// ── GET status ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'status') {
    $u = me();
    ok($u ? ['authed'=>true,'user'=>$u['user'],'role'=>$u['role']] : ['authed'=>false]);
}

// ── GET csrf token ────────────────────────────────────────────
if ($method === 'GET' && $action === 'csrf') {
    ok(['token' => csrf()]);
}

if ($method !== 'POST') fail('Method not allowed.', 405);

// All POST requests require CSRF
$tok = $body['csrf'] ?? '';
if (!csrf_ok($tok)) fail('Invalid request token. Refresh and try again.');

switch ($action) {

    case 'login':
        $u = trim($body['username'] ?? '');
        $p = $body['password'] ?? '';
        if (!$u || !$p) fail('Missing fields.');
        $r = auth_login($u, $p);
        if ($r['ok']) ok(['user'=>$r['user']]); else fail($r['msg']);
        break;

    case 'register':
        $settings = load_json(SET_FILE, default_settings());
        if (empty($settings['registrations'])) fail('Registrations are currently closed.');
        $first = trim($body['first_name'] ?? '');
        $last  = trim($body['last_name']  ?? '');
        $u     = trim($body['username']   ?? '');
        $p     = $body['password']         ?? '';
        $conf  = $body['confirm']          ?? '';
        if (!$first || !$u || !$p) fail('Missing required fields.');
        if ($p !== $conf)           fail('Passwords do not match.');
        $r = auth_register($first, $last, $u, $p);
        if ($r['ok']) ok(); else fail($r['msg']);
        break;

    case 'logout':
        auth_logout();
        ok();
        break;

    default:
        fail('Unknown action.', 404);
}
