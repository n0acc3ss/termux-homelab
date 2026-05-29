<?php
/**
 * One-time setup helper — generates the admin password hash.
 * Run from CLI: php setup.php
 * Or visit /setup.php in your browser ONCE, then DELETE this file.
 */

// Safety: block web access unless explicitly allowed below
$allow_web = false; // set true to use from browser, then delete this file

if (php_sapi_name() !== 'cli' && !$allow_web) {
    http_response_code(403);
    exit("Run this from the command line:\n  php setup.php\n");
}

if (php_sapi_name() === 'cli') {
    echo "\n=== al.homelab admin setup ===\n\n";
    $pass = '';
    while (strlen($pass) < 8) {
        echo "Enter new admin password (min 8 chars): ";
        system('stty -echo');
        $pass = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        if (strlen($pass) < 8) echo "Too short. Try again.\n";
    }
    echo "Confirm password: ";
    system('stty -echo');
    $conf = trim(fgets(STDIN));
    system('stty echo');
    echo "\n\n";
    if ($pass !== $conf) { echo "Passwords do not match.\n"; exit(1); }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    echo "✓ Hash generated.\n\n";
    echo "Open config.php and set:\n\n";
    echo "  define('ADMIN_HASH', '" . $hash . "');\n\n";
    echo "Then delete this setup.php file.\n\n";
    exit(0);
}

// Web mode
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Setup · al.homelab</title>
<style>
body{font-family:monospace;background:#0a0812;color:#e0daf8;padding:2rem;max-width:600px;margin:0 auto;}
h1{color:#f03ca8;margin-bottom:1rem;}
.box{background:#110f1c;border:1px solid #1e1a30;border-radius:8px;padding:1.5rem;}
input[type=password]{width:100%;background:#0d0b17;border:1px solid #1e1a30;border-radius:6px;color:#e0daf8;padding:8px 12px;font-family:monospace;font-size:13px;margin-bottom:1rem;}
button{background:#f03ca8;color:#0a0812;border:none;border-radius:6px;padding:10px 24px;font-family:monospace;font-size:13px;cursor:pointer;font-weight:600;}
.hash-out{background:#0d0b17;border:1px solid #2a2540;border-radius:6px;padding:12px;word-break:break-all;color:#39ff88;font-size:12px;margin-top:1rem;}
.warn{color:#ffaa44;font-size:11px;margin-top:1rem;}
</style>
</head>
<body>
<h1>al.homelab · Admin Setup</h1>
<div class="box">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = $_POST['pass'] ?? '';
    $c = $_POST['conf'] ?? '';
    if (strlen($p) < 8) { echo '<p style="color:#f03ca8">Password too short (min 8).</p>'; }
    elseif ($p !== $c)  { echo '<p style="color:#f03ca8">Passwords do not match.</p>'; }
    else {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        echo '<p style="color:#39ff88">✓ Hash generated. Copy it below:</p>';
        echo '<div class="hash-out">' . htmlspecialchars($hash) . '</div>';
        echo '<p>Set in <code>config.php</code>:</p>';
        echo '<div class="hash-out">define(\'ADMIN_HASH\', \'' . htmlspecialchars($hash) . '\');</div>';
        echo '<p class="warn">⚠ Delete this setup.php file after configuring.</p>';
        echo '</div></body></html>';
        exit;
    }
}
?>
<form method="POST">
  <p style="margin-bottom:1rem;color:#a89fc8;">Generate a bcrypt hash for your admin password.</p>
  <label style="font-size:11px;color:#6b6490;letter-spacing:.08em;text-transform:uppercase;">Password</label><br>
  <input type="password" name="pass" placeholder="min 8 characters"/>
  <label style="font-size:11px;color:#6b6490;letter-spacing:.08em;text-transform:uppercase;">Confirm</label><br>
  <input type="password" name="conf" placeholder="repeat password"/>
  <button type="submit">Generate hash →</button>
</form>
<p class="warn">⚠ Delete this file after setup.</p>
</div>
</body>
</html>
