<?php
require_once dirname(__DIR__) . '/includes/auth.php';
session_boot(SESS_ADMIN);

$setup_mode = !ADMIN_HASH;
$logged_in  = is_admin();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_HTML5, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Admin · al.homelab</title>
<link rel="icon" href="/img/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Share+Tech+Mono&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:      #0a0812;
  --mantle:  #110f1c;
  --crust:   #0d0b17;
  --s0:      #1e1a30;
  --s1:      #2a2540;
  --s2:      #3a3558;
  --ov0:     #6b6490;
  --text:    #e0daf8;
  --sub:     #a89fc8;
  --pink:    #f03ca8;
  --glow:    rgba(240,60,168,0.55);
  --glow2:   rgba(240,60,168,0.15);
  --teal:    #00d4c8;
  --green:   #39ff88;
  --orange:  #ffaa44;
  --red:     #ff4466;
  --mono:'Share Tech Mono', monospace;
  --orb: 'Orbitron', sans-serif;
  --sans:'DM Sans', sans-serif;
}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.07) 2px,rgba(0,0,0,.07) 4px);pointer-events:none;z-index:999;}
body::after{content:'';position:fixed;inset:0;background-image:radial-gradient(rgba(240,60,168,.08) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;z-index:0;}
.blob{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0;animation:pulse 8s ease-in-out infinite alternate;}
.b1{width:600px;height:600px;background:rgba(240,60,168,.06);top:-180px;right:-150px;}
.b2{width:400px;height:400px;background:rgba(184,30,120,.04);bottom:-150px;left:-120px;animation-delay:-4s;}
@keyframes pulse{from{transform:scale(1);opacity:.7;}to{transform:scale(1.15);opacity:1;}}

/* ── Login page ─────────────────────────────────────────── */
.login-wrap{position:relative;z-index:2;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;}
.login-card{background:var(--mantle);border:1px solid var(--s0);border-radius:18px;padding:2.5rem 2.5rem 2rem;width:100%;max-width:400px;position:relative;overflow:hidden;}
.login-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--pink),transparent);}
.login-logo{display:flex;align-items:center;gap:12px;margin-bottom:1.8rem;}
.login-logo img{width:36px;height:36px;border-radius:50%;border:1px solid rgba(240,60,168,.3);box-shadow:0 0 10px var(--glow2);object-fit:contain;background:var(--crust);}
.login-logo-txt{font-family:var(--orb);font-size:1rem;font-weight:700;color:var(--text);}
.login-logo-txt em{color:var(--pink);font-style:normal;}
.login-eyebrow{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.18em;text-transform:uppercase;margin-bottom:1.2rem;}
.fg{display:flex;flex-direction:column;gap:4px;margin-bottom:10px;}
.fl{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.08em;text-transform:uppercase;}
.fi{font-family:var(--mono);font-size:13px;color:var(--text);background:var(--crust);border:1px solid var(--s0);border-radius:8px;padding:10px 14px;outline:none;width:100%;transition:border-color .2s,box-shadow .2s;}
.fi::placeholder{color:var(--s2);}
.fi:focus{border-color:rgba(240,60,168,.5);box-shadow:0 0 0 3px rgba(240,60,168,.08);}
.bsub{font-family:var(--mono);font-size:13px;font-weight:600;color:#0a0812;background:var(--pink);border:none;border-radius:8px;padding:12px;cursor:pointer;width:100%;letter-spacing:.05em;transition:all .2s;margin-top:6px;box-shadow:0 0 16px var(--glow2);}
.bsub:hover{background:#ff50bb;box-shadow:0 0 24px var(--glow);transform:translateY(-1px);}
.bsub:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.ferr{font-family:var(--mono);font-size:11px;color:var(--pink);display:none;margin-top:6px;}
.ferr.show{display:block;}
.setup-notice{font-family:var(--mono);font-size:11px;color:var(--orange);background:rgba(255,170,68,.07);border:1px solid rgba(255,170,68,.2);border-radius:8px;padding:10px 14px;margin-bottom:1.2rem;line-height:1.6;}

/* ── Admin layout ───────────────────────────────────────── */
.admin-wrap{display:flex;min-height:100vh;position:relative;z-index:1;}

/* sidebar */
.sidebar{width:220px;flex-shrink:0;background:rgba(13,11,23,.95);border-right:1px solid var(--s0);display:flex;flex-direction:column;position:fixed;top:0;bottom:0;left:0;z-index:50;}
.sidebar-logo{display:flex;align-items:center;gap:10px;padding:1.25rem 1.25rem 1rem;border-bottom:1px solid var(--s0);}
.sidebar-logo img{width:32px;height:32px;border-radius:50%;border:1px solid rgba(240,60,168,.3);object-fit:contain;background:var(--crust);}
.sidebar-logo-txt{font-family:var(--orb);font-size:.85rem;font-weight:700;color:var(--text);}
.sidebar-logo-txt em{color:var(--pink);font-style:normal;}
.sidebar-badge{font-family:var(--mono);font-size:9px;color:var(--pink);border:1px solid rgba(240,60,168,.3);padding:2px 8px;border-radius:4px;margin-left:auto;letter-spacing:.1em;flex-shrink:0;}
.sidebar-nav{flex:1;padding:1rem 0;overflow-y:auto;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 1.25rem;font-family:var(--mono);font-size:12px;color:var(--ov0);cursor:pointer;transition:all .2s;border-left:2px solid transparent;letter-spacing:.04em;}
.nav-item:hover{color:var(--sub);background:rgba(240,60,168,.04);}
.nav-item.active{color:var(--pink);border-left-color:var(--pink);background:rgba(240,60,168,.06);}
.nav-icon{font-size:14px;flex-shrink:0;width:20px;text-align:center;}
.sidebar-foot{padding:1rem 1.25rem;border-top:1px solid var(--s0);}
.admin-user{font-family:var(--mono);font-size:11px;color:var(--ov0);margin-bottom:.6rem;}
.admin-user strong{color:var(--text);}
.btn-logout{font-family:var(--mono);font-size:11px;background:transparent;border:1px solid var(--s1);color:var(--ov0);padding:6px 14px;border-radius:6px;cursor:pointer;transition:all .2s;width:100%;letter-spacing:.04em;}
.btn-logout:hover{border-color:var(--red);color:var(--red);}

/* main content area */
.main{margin-left:220px;flex:1;padding:2rem;min-height:100vh;}
.main-header{margin-bottom:2rem;}
.page-title{font-family:var(--orb);font-size:1.4rem;font-weight:700;color:var(--text);letter-spacing:.03em;}
.page-title em{color:var(--pink);font-style:normal;}
.page-sub{font-family:var(--mono);font-size:11px;color:var(--ov0);margin-top:.3rem;letter-spacing:.04em;}

/* panels */
.panel{background:var(--mantle);border:1px solid var(--s0);border-radius:16px;margin-bottom:1.5rem;overflow:hidden;}
.panel-head{padding:1rem 1.5rem;border-bottom:1px solid var(--s0);display:flex;align-items:center;justify-content:space-between;}
.panel-title{font-family:var(--orb);font-size:.85rem;font-weight:600;color:var(--text);letter-spacing:.05em;}
.panel-body{padding:1.5rem;}
.panel-body.no-pad{padding:0;}

/* tab content */
.tab-pane{display:none;}
.tab-pane.active{display:block;}

/* form controls (admin) */
.afield{display:flex;flex-direction:column;gap:5px;margin-bottom:1rem;}
.alabel{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.08em;text-transform:uppercase;}
.ainput{font-family:var(--mono);font-size:13px;color:var(--text);background:var(--crust);border:1px solid var(--s0);border-radius:8px;padding:9px 13px;outline:none;width:100%;transition:border-color .2s;}
.ainput:focus{border-color:rgba(240,60,168,.5);}
.ainput::placeholder{color:var(--s2);}
textarea.ainput{resize:vertical;min-height:80px;line-height:1.5;}
.arow{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.arow3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}

/* buttons */
.btn{font-family:var(--mono);font-size:12px;padding:8px 18px;border-radius:7px;cursor:pointer;transition:all .2s;border:none;letter-spacing:.04em;}
.btn-primary{background:var(--pink);color:#0a0812;font-weight:600;box-shadow:0 0 12px var(--glow2);}
.btn-primary:hover{background:#ff50bb;box-shadow:0 0 20px var(--glow);}
.btn-sm{padding:5px 12px;font-size:11px;}
.btn-ghost{background:transparent;border:1px solid var(--s1);color:var(--sub);}
.btn-ghost:hover{border-color:var(--pink);color:var(--pink);}
.btn-danger{background:transparent;border:1px solid rgba(255,68,102,.3);color:var(--red);}
.btn-danger:hover{background:rgba(255,68,102,.1);}
.btn-teal{background:transparent;border:1px solid rgba(0,212,200,.3);color:var(--teal);}
.btn-teal:hover{background:rgba(0,212,200,.08);}
.btn-orange{background:transparent;border:1px solid rgba(255,170,68,.3);color:var(--orange);}
.btn-orange:hover{background:rgba(255,170,68,.08);}

/* service editor list */
.svc-editor-list{display:flex;flex-direction:column;gap:8px;}
.svc-item{background:var(--crust);border:1px solid var(--s0);border-radius:10px;display:flex;align-items:center;gap:12px;padding:10px 14px;}
.svc-item-drag{color:var(--s2);cursor:grab;font-size:16px;flex-shrink:0;}
.svc-item-icon{font-size:20px;width:28px;text-align:center;flex-shrink:0;}
.svc-item-info{flex:1;min-width:0;}
.svc-item-name{font-family:var(--orb);font-size:12px;font-weight:600;color:var(--text);}
.svc-item-url{font-family:var(--mono);font-size:10px;color:var(--ov0);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.svc-item-status{font-family:var(--mono);font-size:9px;padding:2px 8px;border-radius:4px;letter-spacing:.08em;flex-shrink:0;}
.status-online{color:var(--green);border:1px solid rgba(57,255,136,.25);}
.status-soon{color:var(--ov0);border:1px solid var(--s1);}
.status-restricted{color:var(--pink);border:1px solid rgba(240,60,168,.25);}
.svc-item-actions{display:flex;gap:6px;flex-shrink:0;}

/* modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:200;display:none;align-items:center;justify-content:center;padding:1rem;}
.modal-bg.open{display:flex;}
.modal{background:var(--mantle);border:1px solid var(--s0);border-radius:18px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;position:relative;}
.modal::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--pink),transparent);}
.modal-head{padding:1.25rem 1.5rem;border-bottom:1px solid var(--s0);display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-family:var(--orb);font-size:.9rem;font-weight:600;color:var(--text);}
.modal-close{background:transparent;border:none;color:var(--ov0);font-size:18px;cursor:pointer;padding:0 4px;transition:color .2s;}
.modal-close:hover{color:var(--pink);}
.modal-body{padding:1.5rem;}
.modal-foot{padding:1rem 1.5rem;border-top:1px solid var(--s0);display:flex;justify-content:flex-end;gap:8px;}

/* pills editor */
.pill-list{display:flex;flex-direction:column;gap:6px;}
.pill-item{display:flex;align-items:center;gap:8px;background:var(--crust);border:1px solid var(--s0);border-radius:8px;padding:8px 12px;}
.pill-item input[type=text]{flex:1;background:transparent;border:none;outline:none;font-family:var(--mono);font-size:12px;color:var(--text);}
.pill-item select{background:var(--s0);border:1px solid var(--s1);border-radius:4px;color:var(--sub);font-family:var(--mono);font-size:11px;padding:3px 6px;cursor:pointer;}

/* links editor */
.link-list{display:flex;flex-direction:column;gap:6px;}
.link-item{display:flex;align-items:center;gap:8px;}
.link-item .ainput{flex:1;}

/* stats / overview */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;}
.stat-card{background:var(--crust);border:1px solid var(--s0);border-radius:12px;padding:1.2rem;text-align:center;}
.stat-val{font-family:var(--orb);font-size:1.8rem;font-weight:700;color:var(--pink);margin-bottom:.3rem;}
.stat-lbl{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.1em;text-transform:uppercase;}

/* users table */
.users-table{width:100%;border-collapse:collapse;}
.users-table th{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.1em;text-transform:uppercase;padding:10px 14px;border-bottom:1px solid var(--s0);text-align:left;}
.users-table td{font-family:var(--mono);font-size:12px;color:var(--sub);padding:10px 14px;border-bottom:1px solid rgba(30,26,48,.5);}
.users-table tr:last-child td{border-bottom:none;}
.users-table tr:hover td{background:rgba(240,60,168,.03);}
.role-badge{font-size:10px;padding:2px 8px;border-radius:4px;letter-spacing:.08em;display:inline-block;}
.role-user{color:var(--teal);border:1px solid rgba(0,212,200,.25);}
.role-pending{color:var(--orange);border:1px solid rgba(255,170,68,.25);}
.role-banned{color:var(--red);border:1px solid rgba(255,68,102,.25);}

/* divider */
.sep{height:1px;background:var(--s0);margin:1.2rem 0;}

/* section heading */
.section-label{font-family:var(--mono);font-size:11px;color:var(--ov0);letter-spacing:.14em;text-transform:uppercase;margin-bottom:.8rem;display:flex;align-items:center;gap:10px;}
.section-label::after{content:'';flex:1;height:1px;background:var(--s0);}

/* toast */
#toast{position:fixed;bottom:1.75rem;right:1.75rem;z-index:9999;background:var(--mantle);border:1px solid var(--s0);border-radius:10px;padding:11px 16px;font-family:var(--mono);font-size:12px;color:var(--text);display:flex;align-items:center;gap:10px;opacity:0;transform:translateY(8px);transition:opacity .3s,transform .3s;pointer-events:none;max-width:320px;}
#toast.show{opacity:1;transform:translateY(0);}
#tdot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}

/* loading */
.loading{font-family:var(--mono);font-size:12px;color:var(--ov0);padding:1.5rem;text-align:center;}
.loading::after{content:'';animation:dots 1.2s steps(3,end) infinite;}
@keyframes dots{0%{content:'';}33%{content:'.';}66%{content:'..';}100%{content:'...'}}

@media(max-width:768px){
  .sidebar{width:60px;overflow:hidden;}
  .sidebar-logo-txt,.nav-item span:last-child,.sidebar-badge,.admin-user,.sidebar-foot .btn-logout span{display:none;}
  .sidebar-logo{padding:.8rem;justify-content:center;}
  .nav-item{padding:12px;justify-content:center;}
  .nav-icon{width:auto;margin:0;}
  .main{margin-left:60px;padding:1rem;}
  .arow,.arow3{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div class="blob b1"></div>
<div class="blob b2"></div>

<?php if (!$logged_in): ?>
<!-- ── Login ──────────────────────────────────────────────────── -->
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <img src="/img/logo.png" alt="logo"/>
      <span class="login-logo-txt">al.<em>homelab</em></span>
    </div>
    <p class="login-eyebrow">admin terminal</p>

    <?php if ($setup_mode): ?>
    <div class="setup-notice">
      ⚙ Setup mode — <code>ADMIN_HASH</code> is not set in <code>config.php</code>.<br>
      Run: <code>php -r "echo password_hash('YourPass', PASSWORD_DEFAULT);"</code><br>
      Then paste the output as <code>ADMIN_HASH</code>.
    </div>
    <?php endif; ?>

    <div class="fg">
      <label class="fl">username</label>
      <input class="fi" type="text" id="a-user" value="admin" autocomplete="username"/>
    </div>
    <div class="fg">
      <label class="fl">password</label>
      <input class="fi" type="password" id="a-pass" placeholder="••••••••" autocomplete="current-password"/>
    </div>
    <span class="ferr" id="a-err"></span>
    <button class="bsub" id="btn-admin-login" onclick="adminLogin()"<?= $setup_mode ? ' disabled' : '' ?>>
      <?= $setup_mode ? 'configure first →' : 'sign in →' ?>
    </button>
  </div>
</div>

<script>
const CSRF = <?= json_encode(csrf()) ?>;

async function adminLogin() {
  const u = document.getElementById('a-user').value.trim();
  const p = document.getElementById('a-pass').value;
  const err = document.getElementById('a-err');
  err.classList.remove('show');
  if (!u || !p) { err.textContent = 'All fields required.'; err.classList.add('show'); return; }
  const btn = document.getElementById('btn-admin-login');
  btn.disabled = true; btn.textContent = 'verifying...';
  try {
    const r  = await fetch('/api/admin.php?action=login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'login', username:u, password:p, csrf:CSRF})
    });
    const d = await r.json();
    if (d.ok) { location.reload(); }
    else { err.textContent = d.msg || 'Login failed.'; err.classList.add('show'); btn.disabled=false; btn.textContent='sign in →'; }
  } catch { err.textContent='Network error.'; err.classList.add('show'); btn.disabled=false; btn.textContent='sign in →'; }
}
document.getElementById('a-pass').addEventListener('keydown', e => { if(e.key==='Enter') adminLogin(); });
</script>

<?php else: ?>
<!-- ── Dashboard ──────────────────────────────────────────────── -->
<div class="admin-wrap">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="/img/logo.png" alt="logo"/>
      <span class="sidebar-logo-txt">al.<em>homelab</em></span>
      <span class="sidebar-badge">admin</span>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-item active" data-tab="overview" onclick="switchTab('overview')">
        <span class="nav-icon">◈</span><span>Overview</span>
      </div>
      <div class="nav-item" data-tab="services" onclick="switchTab('services')">
        <span class="nav-icon">⊞</span><span>Services</span>
      </div>
      <div class="nav-item" data-tab="content" onclick="switchTab('content')">
        <span class="nav-icon">✎</span><span>Content</span>
      </div>
      <div class="nav-item" data-tab="users" onclick="switchTab('users')">
        <span class="nav-icon">⊙</span><span>Users</span>
      </div>
      <div class="nav-item" data-tab="settings" onclick="switchTab('settings')">
        <span class="nav-icon">⚙</span><span>Settings</span>
      </div>
    </nav>
    <div class="sidebar-foot">
      <div class="admin-user">signed in as <strong>admin</strong></div>
      <button class="btn-logout" onclick="adminLogout()"><span>sign_out</span></button>
    </div>
  </aside>

  <!-- Main -->
  <main class="main">

    <!-- OVERVIEW ──────────────────────────────────────────── -->
    <div class="tab-pane active" id="tab-overview">
      <div class="main-header">
        <h1 class="page-title">al.<em>homelab</em> admin</h1>
        <p class="page-sub">// infrastructure dashboard</p>
      </div>
      <div class="stats-grid" id="overview-stats">
        <div class="stat-card"><div class="stat-val" id="stat-users">—</div><div class="stat-lbl">total users</div></div>
        <div class="stat-card"><div class="stat-val" id="stat-pending">—</div><div class="stat-lbl">pending</div></div>
        <div class="stat-card"><div class="stat-val" id="stat-services">—</div><div class="stat-lbl">services</div></div>
        <div class="stat-card"><div class="stat-val" id="stat-online">—</div><div class="stat-lbl">online</div></div>
      </div>
      <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head"><span class="panel-title">Quick actions</span></div>
        <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:10px;">
          <button class="btn btn-primary" onclick="switchTab('users')">Manage users</button>
          <button class="btn btn-ghost" onclick="switchTab('services')">Edit services</button>
          <button class="btn btn-ghost" onclick="switchTab('content')">Edit content</button>
          <a class="btn btn-teal" href="/" target="_blank">View public site ↗</a>
        </div>
      </div>
    </div>

    <!-- SERVICES ──────────────────────────────────────────── -->
    <div class="tab-pane" id="tab-services">
      <div class="main-header">
        <h1 class="page-title">Services</h1>
        <p class="page-sub">// manage service cards displayed on the public page</p>
      </div>
      <div id="services-editor"><div class="loading">Loading services</div></div>
      <div style="margin-top:1rem;display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-primary" onclick="saveServices()">Save changes</button>
        <button class="btn btn-ghost" onclick="addServiceModal()">+ Add service</button>
      </div>
    </div>

    <!-- CONTENT ───────────────────────────────────────────── -->
    <div class="tab-pane" id="tab-content">
      <div class="main-header">
        <h1 class="page-title">Content</h1>
        <p class="page-sub">// hero section, pills, and footer links</p>
      </div>
      <div id="content-editor"><div class="loading">Loading content</div></div>
    </div>

    <!-- USERS ─────────────────────────────────────────────── -->
    <div class="tab-pane" id="tab-users">
      <div class="main-header">
        <h1 class="page-title">Users</h1>
        <p class="page-sub">// approve, manage, and remove user accounts</p>
      </div>
      <div id="users-panel"><div class="loading">Loading users</div></div>
    </div>

    <!-- SETTINGS ──────────────────────────────────────────── -->
    <div class="tab-pane" id="tab-settings">
      <div class="main-header">
        <h1 class="page-title">Settings</h1>
        <p class="page-sub">// site-wide configuration</p>
      </div>
      <div id="settings-editor"><div class="loading">Loading settings</div></div>
    </div>

  </main>
</div>

<!-- Service edit modal -->
<div class="modal-bg" id="svc-modal">
  <div class="modal">
    <div class="modal-head">
      <span class="modal-title" id="svc-modal-title">Edit service</span>
      <button class="modal-close" onclick="closeModal('svc-modal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="svc-modal-idx"/>
      <input type="hidden" id="svc-modal-section"/>
      <div class="arow">
        <div class="afield">
          <label class="alabel">Icon (emoji)</label>
          <input class="ainput" id="sm-icon" placeholder="☁️"/>
        </div>
        <div class="afield">
          <label class="alabel">Status</label>
          <select class="ainput" id="sm-status">
            <option value="online">online</option>
            <option value="soon">soon / maintenance</option>
            <option value="restricted">restricted</option>
          </select>
        </div>
      </div>
      <div class="afield">
        <label class="alabel">Name</label>
        <input class="ainput" id="sm-name" placeholder="Nextcloud"/>
      </div>
      <div class="afield">
        <label class="alabel">Description</label>
        <input class="ainput" id="sm-desc" placeholder="Short description…"/>
      </div>
      <div class="afield">
        <label class="alabel">URL</label>
        <input class="ainput" id="sm-url" placeholder="https://…"/>
      </div>
      <div class="afield">
        <label class="alabel">Extra CSS classes (optional)</label>
        <input class="ainput" id="sm-classes" placeholder="sc-ai  or  sc-admin"/>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('svc-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveSvcModal()">Save service</button>
    </div>
  </div>
</div>

<div id="toast"><span id="tdot"></span><span id="tmsg"></span></div>

<script>
const CSRF = <?= json_encode(csrf()) ?>;
let svcData = null, settingsData = null;

// ── Utilities ─────────────────────────────────────────────────
function toast(msg, type='info') {
  const c = {success:'#39ff88', error:'#f03ca8', info:'#00d4c8', warn:'#ffaa44'};
  const col = c[type] || c.info;
  const dot = document.getElementById('tdot');
  dot.style.background = col; dot.style.boxShadow = '0 0 6px '+col;
  document.getElementById('tmsg').textContent = msg;
  const el = document.getElementById('toast');
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3800);
}

function h(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function api(action, data={}) {
  const r = await fetch('/api/admin.php?action='+action, {
    method: Object.keys(data).length ? 'POST' : 'GET',
    headers: {'Content-Type':'application/json'},
    body:   Object.keys(data).length ? JSON.stringify({action, csrf:CSRF, ...data}) : undefined
  });
  return r.json();
}

// ── Tabs ──────────────────────────────────────────────────────
function switchTab(name) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  document.querySelector('[data-tab="'+name+'"]').classList.add('active');
  if (name === 'overview')  loadOverview();
  if (name === 'services')  loadServices();
  if (name === 'content')   loadContent();
  if (name === 'users')     loadUsers();
  if (name === 'settings')  loadSettings();
}

// ── Logout ────────────────────────────────────────────────────
async function adminLogout() {
  await api('logout');
  location.reload();
}

// ── Overview ──────────────────────────────────────────────────
async function loadOverview() {
  const [ud, sd] = await Promise.all([api('get_users'), api('get_services')]);
  if (ud.ok) {
    const pending = ud.users.filter(u => u.role === 'pending').length;
    document.getElementById('stat-users').textContent   = ud.users.length;
    document.getElementById('stat-pending').textContent = pending;
  }
  if (sd.ok) {
    const allItems = sd.data.sections.flatMap(s => s.items);
    const online   = allItems.filter(i => i.status === 'online').length;
    document.getElementById('stat-services').textContent = allItems.length;
    document.getElementById('stat-online').textContent   = online;
  }
}

// ── Services ──────────────────────────────────────────────────
async function loadServices() {
  const d = await api('get_services');
  if (!d.ok) { toast('Failed to load services', 'error'); return; }
  svcData = d.data;
  renderServicesEditor();
}

function renderServicesEditor() {
  const wrap = document.getElementById('services-editor');
  let html = '';
  svcData.sections.forEach((sec, si) => {
    html += `<div class="section-label">${h(sec.label)}</div>`;
    html += `<div class="svc-editor-list" id="sec-${si}">`;
    sec.items.forEach((item, ii) => {
      const sc = `status-${item.status || 'soon'}`;
      html += `
      <div class="svc-item" data-si="${si}" data-ii="${ii}">
        <span class="svc-item-drag">⠿</span>
        <span class="svc-item-icon">${h(item.icon||'?')}</span>
        <div class="svc-item-info">
          <div class="svc-item-name">${h(item.name||'')}</div>
          <div class="svc-item-url">${h(item.url||'#')}</div>
        </div>
        <span class="svc-item-status ${sc}">${h(item.status||'soon')}</span>
        <div class="svc-item-actions">
          <button class="btn btn-sm btn-ghost" onclick="editService(${si},${ii})">edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteService(${si},${ii})">del</button>
        </div>
      </div>`;
    });
    html += `</div>`;
    if (si < svcData.sections.length-1) html += `<div class="sep"></div>`;
  });
  wrap.innerHTML = html;
}

function editService(si, ii) {
  const item = svcData.sections[si].items[ii];
  document.getElementById('svc-modal-title').textContent = 'Edit service';
  document.getElementById('svc-modal-idx').value     = ii;
  document.getElementById('svc-modal-section').value = si;
  document.getElementById('sm-icon').value    = item.icon    || '';
  document.getElementById('sm-name').value    = item.name    || '';
  document.getElementById('sm-desc').value    = item.desc    || '';
  document.getElementById('sm-url').value     = item.url     || '';
  document.getElementById('sm-status').value  = item.status  || 'soon';
  document.getElementById('sm-classes').value = item.classes || '';
  openModal('svc-modal');
}

function addServiceModal() {
  document.getElementById('svc-modal-title').textContent = 'Add service';
  document.getElementById('svc-modal-idx').value     = '-1';
  document.getElementById('svc-modal-section').value = '0';
  document.getElementById('sm-icon').value    = '';
  document.getElementById('sm-name').value    = '';
  document.getElementById('sm-desc').value    = '';
  document.getElementById('sm-url').value     = '';
  document.getElementById('sm-status').value  = 'soon';
  document.getElementById('sm-classes').value = '';
  openModal('svc-modal');
}

function saveSvcModal() {
  const si  = parseInt(document.getElementById('svc-modal-section').value);
  const ii  = parseInt(document.getElementById('svc-modal-idx').value);
  const item = {
    id:      document.getElementById('sm-name').value.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,''),
    icon:    document.getElementById('sm-icon').value.trim(),
    name:    document.getElementById('sm-name').value.trim(),
    desc:    document.getElementById('sm-desc').value.trim(),
    url:     document.getElementById('sm-url').value.trim() || '#',
    status:  document.getElementById('sm-status').value,
    classes: document.getElementById('sm-classes').value.trim(),
  };
  if (!item.name) { toast('Name is required.', 'error'); return; }
  if (ii === -1) {
    svcData.sections[si].items.push(item);
  } else {
    item.id = svcData.sections[si].items[ii].id || item.id;
    svcData.sections[si].items[ii] = item;
  }
  closeModal('svc-modal');
  renderServicesEditor();
  toast(ii === -1 ? 'Service added. Click "Save changes" to apply.' : 'Service updated. Click "Save changes" to apply.', 'info');
}

function deleteService(si, ii) {
  if (!confirm('Delete "' + svcData.sections[si].items[ii].name + '"?')) return;
  svcData.sections[si].items.splice(ii, 1);
  renderServicesEditor();
  toast('Removed. Click "Save changes" to apply.', 'warn');
}

async function saveServices() {
  const d = await api('save_services', {data: svcData});
  d.ok ? toast('Services saved successfully!', 'success') : toast(d.msg||'Save failed.', 'error');
}

// ── Content ───────────────────────────────────────────────────
async function loadContent() {
  const d = await api('get_settings');
  if (!d.ok) { toast('Failed to load settings', 'error'); return; }
  settingsData = d.data;
  renderContentEditor();
}

function renderContentEditor() {
  const s = settingsData;
  const pillsHtml = (s.pills||[]).map((p,i) => `
    <div class="pill-item" id="pill-${i}">
      <span style="color:var(--ov0);font-size:10px;font-family:var(--mono)">dot:</span>
      <select onchange="settingsData.pills[${i}].dot=this.value" style="background:var(--s0);border:1px solid var(--s1);border-radius:4px;color:var(--sub);font-family:var(--mono);font-size:11px;padding:3px 6px;">
        <option value="g" ${p.dot==='g'?'selected':''}>green</option>
        <option value="p" ${p.dot==='p'?'selected':''}>pink</option>
        <option value="t" ${p.dot==='t'?'selected':''}>teal</option>
      </select>
      <input type="text" value="${h(p.text||'')}" placeholder="pill text"
        onchange="settingsData.pills[${i}].text=this.value"/>
      <button class="btn btn-sm btn-danger" onclick="removePill(${i})">✕</button>
    </div>`).join('');

  const linksHtml = (s.footer_links||[]).map((l,i) => `
    <div class="link-item">
      <input class="ainput" value="${h(l.label||'')}" placeholder="Label"
        onchange="settingsData.footer_links[${i}].label=this.value" style="max-width:120px;"/>
      <input class="ainput" value="${h(l.url||'')}" placeholder="https://…"
        onchange="settingsData.footer_links[${i}].url=this.value"/>
      <button class="btn btn-sm btn-danger" onclick="removeLink(${i})">✕</button>
    </div>`).join('');

  const h1Html = (s.hero_h1||[]).map((line,i) => `
    <div class="afield">
      <label class="alabel">H1 line ${i+1} <small style="font-size:9px;color:var(--s2)">(use &lt;em&gt; for pink text)</small></label>
      <input class="ainput" value="${h(line||'')}" onchange="settingsData.hero_h1[${i}]=this.value"/>
    </div>`).join('');

  document.getElementById('content-editor').innerHTML = `
    <div class="panel">
      <div class="panel-head"><span class="panel-title">Hero section</span></div>
      <div class="panel-body">
        <div class="afield">
          <label class="alabel">Label (small text above title)</label>
          <input class="ainput" id="c-label" value="${h(s.hero_label||'')}" onchange="settingsData.hero_label=this.value"/>
        </div>
        ${h1Html}
        <div class="afield">
          <label class="alabel">Description paragraph</label>
          <textarea class="ainput" onchange="settingsData.hero_p=this.value" style="min-height:80px;">${h(s.hero_p||'')}</textarea>
        </div>
        <div class="afield">
          <label class="alabel">CTA button text</label>
          <input class="ainput" value="${h(s.cta_primary_text||'')}" onchange="settingsData.cta_primary_text=this.value"/>
        </div>
        <div class="afield">
          <label class="alabel">CTA button href</label>
          <input class="ainput" value="${h(s.cta_primary_href||'')}" onchange="settingsData.cta_primary_href=this.value"/>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <span class="panel-title">Status pills</span>
        <button class="btn btn-sm btn-ghost" onclick="addPill()">+ add pill</button>
      </div>
      <div class="panel-body">
        <div class="pill-list" id="pill-list">${pillsHtml}</div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <span class="panel-title">Footer links</span>
        <button class="btn btn-sm btn-ghost" onclick="addLink()">+ add link</button>
      </div>
      <div class="panel-body">
        <div class="link-list" id="link-list">${linksHtml}</div>
      </div>
    </div>

    <button class="btn btn-primary" onclick="saveContent()">Save content</button>
  `;
}

function addPill() {
  settingsData.pills = settingsData.pills || [];
  settingsData.pills.push({dot:'p', text:''});
  renderContentEditor();
}

function removePill(i) {
  settingsData.pills.splice(i, 1);
  renderContentEditor();
}

function addLink() {
  settingsData.footer_links = settingsData.footer_links || [];
  settingsData.footer_links.push({label:'', url:''});
  renderContentEditor();
}

function removeLink(i) {
  settingsData.footer_links.splice(i, 1);
  renderContentEditor();
}

async function saveContent() {
  const d = await api('save_settings', {data: settingsData});
  d.ok ? toast('Content saved!', 'success') : toast(d.msg||'Save failed.', 'error');
}

// ── Users ─────────────────────────────────────────────────────
async function loadUsers() {
  const d = await api('get_users');
  if (!d.ok) { toast('Failed to load users', 'error'); return; }
  const wrap = document.getElementById('users-panel');
  if (!d.users.length) {
    wrap.innerHTML = '<div class="loading">No users yet</div>';
    return;
  }
  let rows = d.users.map(u => {
    const ts = new Date(u.ts * 1000).toISOString().slice(0,10);
    return `<tr>
      <td>${h(u.username)}</td>
      <td>${h(u.first_name)} ${h(u.last_name)}</td>
      <td><span class="role-badge role-${h(u.role)}">${h(u.role)}</span></td>
      <td>${ts}</td>
      <td>
        ${u.role !== 'user'    ? `<button class="btn btn-sm btn-teal" onclick="setRole(${u.id},'user')">approve</button>` : ''}
        ${u.role !== 'banned'  ? `<button class="btn btn-sm btn-orange" onclick="setRole(${u.id},'banned')">ban</button>` : ''}
        ${u.role !== 'pending' ? `<button class="btn btn-sm btn-orange" onclick="setRole(${u.id},'pending')">pend</button>` : ''}
        <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id},'${h(u.username)}')">del</button>
      </td>
    </tr>`;
  }).join('');
  wrap.innerHTML = `
    <div class="panel">
      <div class="panel-head"><span class="panel-title">Registered users (${d.users.length})</span></div>
      <div class="panel-body no-pad" style="overflow-x:auto;">
        <table class="users-table">
          <thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </div>`;
}

async function setRole(id, role) {
  const d = await api('set_role', {id, role});
  d.ok ? (toast('Role updated.', 'success'), loadUsers()) : toast(d.msg||'Failed.', 'error');
}

async function deleteUser(id, name) {
  if (!confirm('Delete user "' + name + '"? This cannot be undone.')) return;
  const d = await api('delete_user', {id});
  d.ok ? (toast('User deleted.', 'success'), loadUsers()) : toast(d.msg||'Failed.', 'error');
}

// ── Settings ──────────────────────────────────────────────────
async function loadSettings() {
  const d = await api('get_settings');
  if (!d.ok) { toast('Failed to load settings', 'error'); return; }
  settingsData = d.data;
  const s = settingsData;
  document.getElementById('settings-editor').innerHTML = `
    <div class="panel">
      <div class="panel-head"><span class="panel-title">Site</span></div>
      <div class="panel-body">
        <div class="arow">
          <div class="afield">
            <label class="alabel">Site title</label>
            <input class="ainput" value="${h(s.site_title||'')}" onchange="settingsData.site_title=this.value"/>
          </div>
          <div class="afield">
            <label class="alabel">Domain</label>
            <input class="ainput" value="${h(s.domain||'')}" onchange="settingsData.domain=this.value"/>
          </div>
        </div>
        <div class="arow">
          <div class="afield">
            <label class="alabel">Nav badge text</label>
            <input class="ainput" value="${h(s.nav_badge||'')}" onchange="settingsData.nav_badge=this.value"/>
          </div>
          <div class="afield">
            <label class="alabel">Admin panel URL</label>
            <input class="ainput" value="${h(s.admin_url||'')}" onchange="settingsData.admin_url=this.value"/>
          </div>
        </div>
        <div class="afield">
          <label class="alabel">Footer label</label>
          <input class="ainput" value="${h(s.footer_text||'')}" onchange="settingsData.footer_text=this.value"/>
        </div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-head"><span class="panel-title">Logo &amp; assets</span></div>
      <div class="panel-body">
        <div class="afield">
          <label class="alabel">Nav logo src (URL or /img/logo.png)</label>
          <input class="ainput" value="${h(s.logo_src||'')}" onchange="settingsData.logo_src=this.value"/>
        </div>
        <div class="afield">
          <label class="alabel">Hero logo src</label>
          <input class="ainput" value="${h(s.hero_logo_src||'')}" onchange="settingsData.hero_logo_src=this.value"/>
        </div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-head"><span class="panel-title">Access control</span></div>
      <div class="panel-body">
        <div style="display:flex;align-items:center;gap:12px;">
          <label style="font-family:var(--mono);font-size:12px;color:var(--sub);cursor:pointer;">
            <input type="checkbox" ${s.registrations ? 'checked' : ''} onchange="settingsData.registrations=this.checked"/>
            Allow public registrations
          </label>
        </div>
        <p style="font-family:var(--mono);font-size:10px;color:var(--ov0);margin-top:6px;">
          New accounts always start as "pending" and require manual approval.
        </p>
      </div>
    </div>
    <button class="btn btn-primary" onclick="saveSettings()">Save settings</button>
  `;
}

async function saveSettings() {
  const d = await api('save_settings', {data: settingsData});
  d.ok ? toast('Settings saved!', 'success') : toast(d.msg||'Save failed.', 'error');
}

// ── Modal helpers ─────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(bg => {
  bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
});

// ── Init ──────────────────────────────────────────────────────
loadOverview();
</script>

<?php endif; ?>

</body>
</html>
