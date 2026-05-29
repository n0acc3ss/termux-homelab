<?php
require_once __DIR__ . '/includes/auth.php';
session_boot(SESS_USER);

$st  = load_json(SET_FILE, default_settings());
$svc = load_json(SVC_FILE, default_services());
$me  = me();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_HTML5, 'UTF-8'); }

$title      = h($st['site_title']  ?? 'al.homelab');
$domain     = h($st['domain']      ?? 'al-info.net');
$nav_badge  = h($st['nav_badge']   ?? 'private');
$admin_url  = h($st['admin_url']   ?? '/admin/');
$hero_label = h($st['hero_label']  ?? 'self-hosted · private · yours');
$hero_h1    = $st['hero_h1']  ?? ['Your data.', 'Your rules.', 'Your <em>grid.</em>'];
$hero_p     = h($st['hero_p']  ?? '');
$pills      = $st['pills']     ?? [];
$cta_text   = h($st['cta_primary_text'] ?? 'browse services →');
$cta_href   = h($st['cta_primary_href'] ?? '#services');
$footer_txt = h($st['footer_text']  ?? 'al.homelab');
$foot_links = $st['footer_links'] ?? [];
$logo_src   = h($st['logo_src']      ?? '/img/logo.png');
$hero_logo  = h($st['hero_logo_src'] ?? '/img/logo.png');
$allow_reg  = !empty($st['registrations']);

// Build safe hero H1 (only <em> allowed)
function render_h1_line(string $raw): string {
    $safe = htmlspecialchars($raw, ENT_QUOTES|ENT_HTML5, 'UTF-8');
    // restore <em> / </em> that we allow
    $safe = preg_replace('/&lt;em&gt;(.*?)&lt;\/em&gt;/s', '<em>$1</em>', $safe);
    return $safe;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= $title ?></title>
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
  --pink2:   #d42490;
  --magenta: #b81e78;
  --glow:    rgba(240,60,168,0.55);
  --glow2:   rgba(240,60,168,0.15);
  --teal:    #00d4c8;
  --green:   #39ff88;
  --mono: 'Share Tech Mono', monospace;
  --orb:  'Orbitron', sans-serif;
  --sans: 'DM Sans', sans-serif;
}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.07) 2px,rgba(0,0,0,0.07) 4px);pointer-events:none;z-index:999;}
body::after{content:'';position:fixed;inset:0;background-image:radial-gradient(rgba(240,60,168,0.08) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;z-index:0;}
.blob{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0;animation:pulse 8s ease-in-out infinite alternate;}
.b1{width:600px;height:600px;background:rgba(240,60,168,0.06);top:-180px;right:-150px;}
.b2{width:500px;height:500px;background:rgba(184,30,120,0.04);bottom:-150px;left:-120px;animation-delay:-4s;}
.b3{width:300px;height:300px;background:rgba(0,212,200,0.03);top:40%;left:35%;animation-delay:-2s;}
@keyframes pulse{from{transform:scale(1);opacity:.7;}to{transform:scale(1.15);opacity:1;}}
/* Nav */
nav{position:fixed;top:0;left:0;right:0;z-index:100;height:66px;display:flex;align-items:center;justify-content:space-between;padding:0 2.5rem;background:rgba(10,8,18,0.85);backdrop-filter:blur(20px);border-bottom:1px solid rgba(240,60,168,0.12);}
.logo{display:flex;align-items:center;gap:14px;text-decoration:none;}
.logo-img{width:42px;height:42px;border-radius:50%;border:1px solid rgba(240,60,168,0.3);box-shadow:0 0 12px var(--glow2);object-fit:contain;background:var(--crust);}
.logo-text-led{font-family:var(--orb);font-size:1.1rem;font-weight:700;color:var(--text);letter-spacing:0.08em;display:inline-flex;align-items:center;}
.led-static{color:var(--text);}
.led-home{color:var(--pink);display:inline-flex;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}
.led-h{animation:led-char-a 7.3s ease-in-out infinite;}
.led-o{animation:led-char-b 9.1s ease-in-out infinite .4s;}
.led-m{animation:led-char-c 11.7s ease-in-out infinite 1.1s;}
.led-e{animation:led-char-a 8.5s ease-in-out infinite 2.3s;}
.led-l{animation:led-char-b 6.9s ease-in-out infinite .7s;}
.led-a{animation:led-flicker 13s ease-in-out infinite 1.8s;}
.led-b{animation:led-char-c 10.3s ease-in-out infinite 3.1s;}
@keyframes led-flicker{0%,100%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}3%{opacity:.4;text-shadow:none;}5%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}7%{opacity:.7;text-shadow:0 0 4px var(--pink);}9%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}52%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}53%{opacity:.2;text-shadow:none;}54%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}56%{opacity:.8;text-shadow:0 0 3px var(--pink);}57%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}78%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}78.5%{opacity:0;text-shadow:none;}79%{opacity:1;text-shadow:0 0 12px var(--pink),0 0 30px var(--glow);}79.5%{opacity:.5;text-shadow:none;}80%{opacity:1;text-shadow:0 0 8px var(--pink),0 0 20px var(--glow);}}
@keyframes led-char-a{0%,100%{opacity:1;}20%{opacity:.1;}21%{opacity:1;}65%{opacity:1;}65.5%{opacity:0;}66%{opacity:1;}}
@keyframes led-char-b{0%,100%{opacity:1;}33%{opacity:.15;}34%{opacity:.8;}34.5%{opacity:.1;}35%{opacity:1;}88%{opacity:1;}88.3%{opacity:0;}88.6%{opacity:1;}}
@keyframes led-char-c{0%,100%{opacity:1;}48%{opacity:.2;}49%{opacity:1;}72%{opacity:1;}72.3%{opacity:.05;}72.6%{opacity:.9;}72.9%{opacity:.3;}73.2%{opacity:1;}}
.nav-r{display:flex;align-items:center;gap:10px;}
.badge{font-family:var(--mono);font-size:10px;color:var(--pink);border:1px solid rgba(240,60,168,0.3);padding:3px 10px;border-radius:4px;letter-spacing:.12em;text-transform:uppercase;box-shadow:0 0 8px var(--glow2);}
.nb{font-family:var(--mono);font-size:12px;background:transparent;border:1px solid var(--s1);color:var(--sub);padding:6px 16px;border-radius:6px;cursor:pointer;transition:all .2s;letter-spacing:.04em;text-decoration:none;display:inline-block;}
.nb:hover{border-color:var(--pink);color:var(--pink);box-shadow:0 0 10px var(--glow2);}
.nb-admin{border-color:rgba(240,60,168,0.4);color:var(--pink);box-shadow:0 0 8px var(--glow2);}
.nb-admin:hover{background:rgba(240,60,168,0.08);box-shadow:0 0 18px var(--glow);}
/* user pill when logged in */
.user-pill{font-family:var(--mono);font-size:11px;color:var(--teal);border:1px solid rgba(0,212,200,.3);padding:4px 12px;border-radius:20px;display:flex;align-items:center;gap:6px;}
.user-pill::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--teal);box-shadow:0 0 6px var(--teal);flex-shrink:0;}
/* Hero */
.page{position:relative;z-index:1;min-height:100vh;padding-top:66px;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:4rem;max-width:1100px;margin:0 auto;padding-left:2.5rem;padding-right:2.5rem;}
.hero-label{font-family:var(--mono);font-size:11px;color:var(--ov0);letter-spacing:.2em;text-transform:uppercase;display:flex;align-items:center;gap:12px;margin-bottom:1.2rem;}
.label-line{flex:none;width:32px;height:1px;background:var(--pink);box-shadow:0 0 6px var(--pink);}
.hero-h1{font-family:var(--orb);font-size:3rem;font-weight:900;line-height:1.1;color:var(--text);margin-bottom:1.2rem;letter-spacing:-0.01em;}
.hero-h1 em{color:var(--pink);font-style:normal;text-shadow:0 0 20px var(--glow),0 0 40px var(--glow2);}
.hero-p{font-size:1rem;color:var(--sub);line-height:1.7;margin-bottom:1.8rem;}
.pills{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:1.8rem;}
.pill{font-family:var(--mono);font-size:10px;color:var(--sub);background:var(--mantle);border:1px solid var(--s0);border-radius:20px;padding:4px 12px;display:flex;align-items:center;gap:6px;}
.pdot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.pdot-g{background:var(--green);box-shadow:0 0 6px var(--green);}
.pdot-p{background:var(--pink);box-shadow:0 0 6px var(--glow);}
.pdot-t{background:var(--teal);box-shadow:0 0 6px var(--teal);}
.cta-row{display:flex;gap:12px;flex-wrap:wrap;}
.cbtn{font-family:var(--mono);font-size:13px;padding:11px 24px;border-radius:8px;text-decoration:none;letter-spacing:.05em;transition:all .25s;cursor:pointer;border:none;display:inline-block;}
.cbtn-fill{background:var(--pink);color:#0a0812;font-weight:600;box-shadow:0 0 20px var(--glow2);}
.cbtn-fill:hover{background:#ff50bb;box-shadow:0 0 30px var(--glow);transform:translateY(-2px);}
.cbtn-ghost{background:transparent;color:var(--sub);border:1px solid var(--s1);}
.cbtn-ghost:hover{border-color:var(--pink);color:var(--pink);box-shadow:0 0 12px var(--glow2);}
/* Hero right */
.hero-right{display:flex;align-items:center;justify-content:center;position:relative;}
.logo-hero{width:min(380px,90%);height:auto;border-radius:50%;filter:drop-shadow(0 0 40px var(--glow)) drop-shadow(0 0 80px rgba(240,60,168,.2));animation:float 6s ease-in-out infinite;}
@keyframes float{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-14px) rotate(1deg);}}
/* Auth card */
.auth-wrap{position:relative;z-index:2;}
.auth-card{background:var(--mantle);border:1px solid var(--s0);border-radius:18px;padding:2rem 2rem 1.8rem;position:relative;overflow:hidden;}
.auth-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--pink),transparent);}
.auth-eyebrow{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.18em;text-transform:uppercase;margin-bottom:1.1rem;}
.auth-tabs{display:flex;gap:4px;background:var(--crust);border-radius:10px;padding:4px;margin-bottom:1.4rem;}
.at{flex:1;font-family:var(--mono);font-size:12px;background:transparent;border:none;color:var(--ov0);padding:8px;border-radius:7px;cursor:pointer;letter-spacing:.06em;transition:all .2s;}
.at.active{background:var(--s0);color:var(--pink);box-shadow:0 0 8px var(--glow2);}
.af{display:flex;flex-direction:column;gap:10px;}
.af.hidden{display:none;}
.fg{display:flex;flex-direction:column;gap:4px;}
.fl{font-family:var(--mono);font-size:10px;color:var(--ov0);letter-spacing:.08em;text-transform:uppercase;}
.fl-req{color:var(--pink);}
.fi{font-family:var(--mono);font-size:13px;color:var(--text);background:var(--crust);border:1px solid var(--s0);border-radius:8px;padding:10px 14px;outline:none;width:100%;transition:border-color .2s,box-shadow .2s;}
.fi::placeholder{color:var(--s2);}
.fi:focus{border-color:rgba(240,60,168,.5);box-shadow:0 0 0 3px rgba(240,60,168,.08);}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.ferr{font-family:var(--mono);font-size:10px;color:var(--pink);display:none;}
.ferr.show{display:block;}
.bsub{font-family:var(--mono);font-size:13px;font-weight:600;color:#0a0812;background:var(--pink);border:none;border-radius:8px;padding:12px;cursor:pointer;width:100%;letter-spacing:.05em;transition:all .2s;margin-top:4px;box-shadow:0 0 16px var(--glow2);}
.bsub:hover{background:#ff50bb;box-shadow:0 0 24px var(--glow);transform:translateY(-1px);}
.bsub:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.asep{display:flex;align-items:center;gap:10px;margin:4px 0;}
.asep::before,.asep::after{content:'';flex:1;height:1px;background:var(--s0);}
.asep span{font-family:var(--mono);font-size:10px;color:var(--s2);letter-spacing:.08em;}
.afoot{font-family:var(--mono);font-size:11px;color:var(--ov0);text-align:center;}
.afoot a{color:var(--pink);cursor:pointer;text-decoration:none;}
/* logged-in card */
.logged-card{background:var(--mantle);border:1px solid rgba(0,212,200,.2);border-radius:18px;padding:2rem;position:relative;overflow:hidden;text-align:center;}
.logged-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--teal),transparent);}
.logged-avatar{width:56px;height:56px;border-radius:50%;background:var(--s0);border:2px solid rgba(0,212,200,.3);display:inline-flex;align-items:center;justify-content:center;font-family:var(--orb);font-size:1.4rem;color:var(--teal);margin-bottom:1rem;}
.logged-name{font-family:var(--orb);font-size:1rem;font-weight:700;color:var(--text);margin-bottom:.3rem;}
.logged-role{font-family:var(--mono);font-size:10px;color:var(--teal);letter-spacing:.12em;text-transform:uppercase;margin-bottom:1.4rem;}
/* Services */
.svc-wrap{position:relative;z-index:1;padding:0 2.5rem 5rem;max-width:1100px;margin:0 auto;}
.divider{display:flex;align-items:center;gap:16px;margin-bottom:2rem;}
.div-line{flex:1;height:1px;background:var(--s0);}
.div-label{font-family:var(--mono);font-size:11px;color:var(--ov0);letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;}
.svc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;}
.sc{background:var(--mantle);border:1px solid var(--s0);border-radius:16px;padding:1.4rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:12px;transition:border-color .25s,transform .25s,box-shadow .25s;position:relative;overflow:hidden;}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--pink),transparent);opacity:0;transition:opacity .3s;}
.sc:hover{border-color:rgba(240,60,168,.3);transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.5);}
.sc:hover::before{opacity:1;}
.sc-icon{width:42px;height:42px;border-radius:10px;background:var(--s0);border:1px solid var(--s1);display:flex;align-items:center;justify-content:center;font-size:18px;}
.sc-name{font-family:var(--orb);font-size:13px;font-weight:600;color:var(--text);margin-bottom:3px;letter-spacing:.03em;}
.sc-desc{font-family:var(--mono);font-size:11px;color:var(--ov0);line-height:1.6;}
.sc-foot{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:8px;border-top:1px solid var(--s0);}
.sc-on{font-family:var(--mono);font-size:10px;color:var(--green);display:flex;align-items:center;gap:5px;}
.sc-on::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);}
.sc-arr{font-family:var(--mono);font-size:12px;color:var(--s2);transition:color .2s,transform .2s;}
.sc:hover .sc-arr{color:var(--pink);transform:translateX(4px);}
.sc-soon{opacity:.72;cursor:default;}
.sc-soon:hover{transform:none;box-shadow:none;border-color:var(--s0);}
.sc-soon:hover::before{opacity:0;}
.sc-admin{border-color:rgba(240,60,168,.15);}
.sc-admin .sc-icon{background:rgba(240,60,168,.08);border-color:rgba(240,60,168,.2);}
.sc-admin:hover{border-color:rgba(240,60,168,.45);box-shadow:0 16px 48px rgba(0,0,0,.5),0 0 30px rgba(240,60,168,.08);}
.sc-ai .sc-icon{background:rgba(0,212,200,.07);border-color:rgba(0,212,200,.2);}
.sc-tag{font-family:var(--mono);font-size:9px;color:var(--pink);border:1px solid rgba(240,60,168,.25);border-radius:4px;padding:2px 6px;letter-spacing:.1em;}
.sc-tag-ai{color:var(--teal);border-color:rgba(0,212,200,.3);}
.sc-maint{font-family:var(--mono);font-size:10px;color:var(--ov0);display:flex;align-items:center;gap:5px;}
.sc-maint::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--ov0);animation:blink-slow 2s ease-in-out infinite;}
@keyframes blink-slow{0%,100%{opacity:1;}50%{opacity:.2;}}
.svc-grid-sm{display:flex;flex-wrap:wrap;gap:8px;}
.sc-sm{display:flex;align-items:center;gap:10px;background:var(--crust);border:1px solid var(--s0);border-radius:10px;padding:10px 14px;text-decoration:none;color:inherit;cursor:default;opacity:.65;min-width:150px;transition:opacity .2s,border-color .2s;}
.sc-sm:hover{opacity:.85;border-color:var(--s1);}
.sc-sm-icon{font-size:16px;flex-shrink:0;}
.sc-sm-name{font-family:var(--mono);font-size:12px;color:var(--sub);white-space:nowrap;}
.sc-sm-tag{font-family:var(--mono);font-size:9px;color:var(--s2);letter-spacing:.08em;text-transform:uppercase;}
.sc-sm-more .sc-sm-name{color:var(--ov0);}
.sc-sm-more .sc-sm-icon{color:var(--pink);opacity:.5;}
/* Footer */
footer{position:relative;z-index:1;border-top:1px solid var(--s0);padding:1.25rem 2.5rem;display:flex;align-items:center;justify-content:space-between;}
.foot-l{font-family:var(--mono);font-size:11px;color:var(--ov0);display:flex;align-items:center;gap:10px;}
.fsep{color:var(--s1);}
.foot-links{display:flex;gap:20px;list-style:none;}
.foot-links a{font-family:var(--mono);font-size:11px;color:var(--ov0);text-decoration:none;transition:color .2s;}
.foot-links a:hover{color:var(--pink);}
/* Toast */
#toast{position:fixed;bottom:1.75rem;right:1.75rem;z-index:9999;background:var(--mantle);border:1px solid var(--s0);border-radius:10px;padding:11px 16px;font-family:var(--mono);font-size:12px;color:var(--text);display:flex;align-items:center;gap:10px;opacity:0;transform:translateY(8px);transition:opacity .3s,transform .3s;pointer-events:none;max-width:300px;}
#toast.show{opacity:1;transform:translateY(0);}
#tdot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
@media(max-width:860px){
  .page{grid-template-columns:1fr;gap:2rem;padding-top:80px;}
  .hero-right{order:-1;}
  .logo-hero{width:180px;}
  .hero-h1{font-size:2.2rem;}
  nav{padding:0 1.25rem;}
  .svc-wrap{padding:0 1.25rem 4rem;}
  .badge{display:none;}
}
@media(max-width:520px){
  .frow{grid-template-columns:1fr;}
  footer{flex-direction:column;gap:.75rem;text-align:center;}
}
</style>
</head>
<body>

<div class="blob b1"></div>
<div class="blob b2"></div>
<div class="blob b3"></div>

<!-- Nav -->
<nav>
  <a class="logo" href="/">
    <img class="logo-img" src="<?= $logo_src ?>" alt="<?= $title ?> logo"/>
    <span class="logo-text-led">
      <span class="led-static">al.</span><span class="led-home"><span class="led-h">h</span><span class="led-o">o</span><span class="led-m">m</span><span class="led-e">e</span><span class="led-l">l</span><span class="led-a">a</span><span class="led-b">b</span></span>
    </span>
  </a>
  <div class="nav-r">
    <span class="badge"><?= $nav_badge ?></span>
    <?php if ($me): ?>
      <span class="user-pill" id="user-pill"><?= h($me['user']) ?></span>
      <button class="nb" id="btn-logout">sign_out</button>
    <?php else: ?>
      <button class="nb" id="btn-signin" onclick="showAuth()">sign_in</button>
    <?php endif; ?>
    <a class="nb nb-admin" href="<?= $admin_url ?>">admin →</a>
  </div>
</nav>

<!-- Hero + Auth -->
<section class="page">

  <!-- Left: copy -->
  <div>
    <p class="hero-label"><span class="label-line"></span><?= $hero_label ?></p>

    <h1 class="hero-h1">
      <?php foreach ($hero_h1 as $i => $line): ?>
        <?= render_h1_line($line) ?><?= $i < count($hero_h1)-1 ? '<br>' : '' ?>
      <?php endforeach; ?>
    </h1>

    <p class="hero-p"><?= $hero_p ?></p>

    <?php if ($pills): ?>
    <div class="pills">
      <?php foreach ($pills as $pill): ?>
        <span class="pill"><span class="pdot pdot-<?= h($pill['dot']) ?>"></span><?= h($pill['text']) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="cta-row">
      <a class="cbtn cbtn-fill" href="<?= $cta_href ?>"><?= $cta_text ?></a>
      <?php if (!$me): ?>
        <button class="cbtn cbtn-ghost" onclick="showAuth()">sign in</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: auth card or logged-in card -->
  <div class="hero-right">
    <?php if ($me): ?>
    <!-- Logged in state -->
    <div class="auth-wrap" style="width:100%;" id="auth-section">
      <div class="logged-card">
        <div class="logged-avatar"><?= mb_strtoupper(mb_substr($me['user'], 0, 1)) ?></div>
        <div class="logged-name"><?= h($me['user']) ?></div>
        <div class="logged-role"><?= h($me['role']) ?> access</div>
        <a class="cbtn cbtn-fill" href="#services" style="display:inline-block">browse services →</a>
        <br><br>
        <button class="nb" id="btn-logout2" style="width:100%;">sign_out</button>
      </div>
    </div>
    <?php else: ?>
    <!-- Auth card -->
    <div class="auth-wrap" id="auth-section" style="width:100%;">
      <div class="auth-card">
        <p class="auth-eyebrow">access terminal</p>

        <div class="auth-tabs">
          <button class="at active" id="tab-login" onclick="switchTab('login')">sign_in</button>
          <button class="at" id="tab-signup" onclick="switchTab('signup')"><?= $allow_reg ? 'register' : 'register' ?></button>
        </div>

        <!-- Login form -->
        <form class="af" id="form-login" onsubmit="handleLogin(event)" novalidate>
          <div class="fg">
            <label class="fl">username <span class="fl-req">*</span></label>
            <input class="fi" type="text" id="l-user" placeholder="username" autocomplete="username"/>
            <span class="ferr" id="l-user-err">Username is required.</span>
          </div>
          <div class="fg">
            <label class="fl">password <span class="fl-req">*</span></label>
            <input class="fi" type="password" id="l-pass" placeholder="••••••••" autocomplete="current-password"/>
            <span class="ferr" id="l-pass-err">Password is required.</span>
          </div>
          <span class="ferr" id="l-general-err" style="font-size:11px;"></span>
          <button class="bsub" type="submit" id="btn-login">sign in →</button>
          <?php if ($allow_reg): ?>
          <div class="asep"><span>or</span></div>
          <p class="afoot">No account? <a onclick="switchTab('signup')">register here</a></p>
          <?php endif; ?>
        </form>

        <!-- Register form -->
        <form class="af hidden" id="form-signup" onsubmit="handleSignup(event)" novalidate>
          <?php if (!$allow_reg): ?>
          <p class="afoot" style="color:var(--pink);margin-bottom:1rem;">Registrations are currently closed.</p>
          <?php endif; ?>
          <div class="frow">
            <div class="fg">
              <label class="fl">first name <span class="fl-req">*</span></label>
              <input class="fi" type="text" id="s-first" placeholder="First"<?= !$allow_reg ? ' disabled' : '' ?>/>
              <span class="ferr" id="s-first-err">Required.</span>
            </div>
            <div class="fg">
              <label class="fl">last name</label>
              <input class="fi" type="text" id="s-last" placeholder="Last"<?= !$allow_reg ? ' disabled' : '' ?>/>
            </div>
          </div>
          <div class="fg">
            <label class="fl">username <span class="fl-req">*</span></label>
            <input class="fi" type="text" id="s-user" placeholder="your-handle" autocomplete="username"<?= !$allow_reg ? ' disabled' : '' ?>/>
            <span class="ferr" id="s-user-err">Username is required.</span>
          </div>
          <div class="fg">
            <label class="fl">password <span class="fl-req">*</span></label>
            <input class="fi" type="password" id="s-pass" placeholder="min. 8 characters" autocomplete="new-password"<?= !$allow_reg ? ' disabled' : '' ?>/>
            <span class="ferr" id="s-pass-err">Must be at least 8 characters.</span>
          </div>
          <div class="fg">
            <label class="fl">confirm password <span class="fl-req">*</span></label>
            <input class="fi" type="password" id="s-confirm" placeholder="repeat password"<?= !$allow_reg ? ' disabled' : '' ?>/>
            <span class="ferr" id="s-confirm-err">Passwords do not match.</span>
          </div>
          <span class="ferr" id="s-general-err" style="font-size:11px;"></span>
          <?php if ($allow_reg): ?>
          <button class="bsub" type="submit" id="btn-register">create account →</button>
          <?php endif; ?>
          <div class="asep"><span>or</span></div>
          <p class="afoot">Have access? <a onclick="switchTab('login')">sign in</a></p>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

</section>

<!-- Services -->
<section class="svc-wrap" id="services">
<?php foreach ($svc['sections'] ?? [] as $section): ?>
  <div class="divider"<?= $section !== ($svc['sections'][0] ?? null) ? ' style="margin-top:2.8rem;"' : '' ?>>
    <span class="div-line"></span>
    <span class="div-label"><?= h($section['label'] ?? '') ?></span>
    <span class="div-line"></span>
  </div>

  <?php if (($section['type'] ?? 'grid') === 'grid'): ?>
  <div class="svc-grid">
    <?php foreach ($section['items'] ?? [] as $item):
      $status  = $item['status'] ?? 'soon';
      $classes = trim('sc ' . ($item['classes'] ?? ''));
      $url     = $item['url'] ?? '#';
      $isLink  = ($status === 'online' || $status === 'restricted') && $url !== '#';
      if ($status === 'soon') $classes .= ' sc-soon';
    ?>
    <<?= $isLink ? 'a href="' . h($url) . '" target="_blank"' : 'div' ?> class="<?= h($classes) ?>"<?= !$isLink ? ' role="article"' : '' ?>>
      <div class="sc-icon"><?= h($item['icon'] ?? '?') ?></div>
      <div>
        <div class="sc-name"><?= h($item['name'] ?? '') ?></div>
        <div class="sc-desc"><?= h($item['desc'] ?? '') ?></div>
      </div>
      <div class="sc-foot">
        <?php if ($status === 'online'): ?>
          <span class="sc-on">online</span><span class="sc-arr">→</span>
        <?php elseif ($status === 'restricted'): ?>
          <span class="sc-on">restricted</span>
          <span class="sc-tag">PRIVATE</span>
          <span class="sc-arr">→</span>
        <?php else: ?>
          <span class="sc-maint">maintenance</span>
          <span class="sc-tag<?= strpos($classes, 'sc-ai') !== false ? ' sc-tag-ai' : '' ?>"><?= strpos($classes, 'sc-ai') !== false ? 'AI · API' : 'SOON' ?></span>
        <?php endif; ?>
      </div>
    </<?= $isLink ? 'a' : 'div' ?>>
    <?php endforeach; ?>
  </div>

  <?php else: /* grid-sm */ ?>
  <div class="svc-grid-sm">
    <?php foreach ($section['items'] ?? [] as $item):
      $classes = trim('sc-sm ' . ($item['classes'] ?? ''));
    ?>
    <a class="<?= h($classes) ?>" href="<?= h($item['url'] ?? '#') ?>" onclick="return false;">
      <span class="sc-sm-icon"><?= h($item['icon'] ?? '') ?></span>
      <div>
        <div class="sc-sm-name"><?= h($item['name'] ?? '') ?></div>
        <div class="sc-sm-tag">available soon</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
<?php endforeach; ?>
</section>

<!-- Footer -->
<footer>
  <div class="foot-l">
    <span><?= $footer_txt ?></span>
    <span class="fsep">·</span>
    <span><?= $domain ?></span>
    <span class="fsep">·</span>
    <span>self-hosted</span>
  </div>
  <ul class="foot-links">
    <?php foreach ($foot_links as $lnk): ?>
      <li><a href="<?= h($lnk['url']) ?>"><?= h($lnk['label']) ?></a></li>
    <?php endforeach; ?>
  </ul>
</footer>

<div id="toast"><span id="tdot"></span><span id="tmsg"></span></div>

<script>
const CSRF = <?= json_encode(csrf()) ?>;

function toast(msg, type='info') {
  const c = {success:'#39ff88', error:'#f03ca8', info:'#00d4c8'};
  const col = c[type] || c.info;
  const dot = document.getElementById('tdot');
  dot.style.background   = col;
  dot.style.boxShadow    = '0 0 6px ' + col;
  document.getElementById('tmsg').textContent = msg;
  const el = document.getElementById('toast');
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3600);
}

function switchTab(t) {
  const isLogin = t === 'login';
  document.getElementById('tab-login').classList.toggle('active', isLogin);
  document.getElementById('tab-signup').classList.toggle('active', !isLogin);
  document.getElementById('form-login').classList.toggle('hidden', !isLogin);
  document.getElementById('form-signup').classList.toggle('hidden', isLogin);
  document.querySelectorAll('.ferr').forEach(e => e.classList.remove('show'));
}

function showAuth() {
  const sec = document.getElementById('auth-section');
  if (sec) sec.scrollIntoView({behavior:'smooth', block:'center'});
}

function ferr(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.classList.add('show'); }
}

async function handleLogin(e) {
  e.preventDefault();
  document.querySelectorAll('.ferr').forEach(el => el.classList.remove('show'));
  const user = document.getElementById('l-user').value.trim();
  const pass = document.getElementById('l-pass').value;
  if (!user) { ferr('l-user-err', 'Username is required.'); return; }
  if (!pass) { ferr('l-pass-err', 'Password is required.'); return; }

  const btn = document.getElementById('btn-login');
  btn.disabled = true; btn.textContent = 'connecting...';

  try {
    const r = await fetch('/api/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action:'login', username:user, password:pass, csrf:CSRF})
    });
    const d = await r.json();
    if (d.ok) {
      toast('Welcome back, ' + d.user + '. Loading…', 'success');
      setTimeout(() => location.reload(), 900);
    } else {
      ferr('l-general-err', d.msg || 'Login failed.');
      btn.disabled = false; btn.textContent = 'sign in →';
    }
  } catch {
    ferr('l-general-err', 'Network error. Try again.');
    btn.disabled = false; btn.textContent = 'sign in →';
  }
}

async function handleSignup(e) {
  e.preventDefault();
  document.querySelectorAll('.ferr').forEach(el => el.classList.remove('show'));
  const first = document.getElementById('s-first').value.trim();
  const last  = document.getElementById('s-last').value.trim();
  const user  = document.getElementById('s-user').value.trim();
  const pass  = document.getElementById('s-pass').value;
  const conf  = document.getElementById('s-confirm').value;

  if (!first) { ferr('s-first-err', 'Required.'); return; }
  if (!user)  { ferr('s-user-err',  'Username is required.'); return; }
  if (pass.length < 8) { ferr('s-pass-err', 'Must be at least 8 characters.'); return; }
  if (pass !== conf)   { ferr('s-confirm-err', 'Passwords do not match.'); return; }

  const btn = document.getElementById('btn-register');
  if (btn) { btn.disabled = true; btn.textContent = 'creating…'; }

  try {
    const r = await fetch('/api/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action:'register', first_name:first, last_name:last, username:user, password:pass, confirm:conf, csrf:CSRF})
    });
    const d = await r.json();
    if (d.ok) {
      toast('Account created. Awaiting admin approval.', 'info');
      switchTab('login');
    } else {
      ferr('s-general-err', d.msg || 'Registration failed.');
      if (btn) { btn.disabled = false; btn.textContent = 'create account →'; }
    }
  } catch {
    ferr('s-general-err', 'Network error. Try again.');
    if (btn) { btn.disabled = false; btn.textContent = 'create account →'; }
  }
}

// Logout handlers
async function doLogout() {
  const r = await fetch('/api/auth.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action:'logout', csrf:CSRF})
  });
  if ((await r.json()).ok) location.reload();
}
const logoutBtn  = document.getElementById('btn-logout');
const logoutBtn2 = document.getElementById('btn-logout2');
if (logoutBtn)  logoutBtn.addEventListener('click', doLogout);
if (logoutBtn2) logoutBtn2.addEventListener('click', doLogout);
</script>
</body>
</html>
