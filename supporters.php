<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/meta.php';

// ─── AJAX: GET ?action=load&page=N ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'load') {
    header('Content-Type: application/json; charset=utf-8');
    $page = max(1, (int)($_GET['page'] ?? 1));
    try {
        $db = get_db();
        $supporters = get_supporters($db, $page);
        $total      = get_total_count($db);
        $has_more   = ($page * 20) < $total;
        echo json_encode([
            'supporters' => $supporters,
            'has_more'   => $has_more,
            'total'      => $total,
        ]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
    }
    exit;
}

// ─── AJAX: POST action=register ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Parse JSON body
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // Also support form-encoded
    if (empty($body)) {
        $body = $_POST;
    }

    $action = $body['action'] ?? '';

    if ($action !== 'register') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    // 1. CSRF
    $csrf = $body['csrf_token'] ?? '';
    if (!verify_csrf($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Neplatný bezpečnostní token. Obnovte stránku.']);
        exit;
    }

    // 2. Honeypot
    $honeypot = $body['website'] ?? '';
    if ($honeypot !== '') {
        // Silently pretend success for bots
        echo json_encode(['success' => true, 'message' => 'Přidáno!']);
        exit;
    }

    // 3. Rate limit
    $ip = get_client_ip();
    try {
        $db = get_db();
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Chyba serveru. Zkuste to znovu.']);
        exit;
    }

    if (!check_rate_limit($db, $ip)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Příliš mnoho pokusů. Zkuste to za hodinu.']);
        exit;
    }

    // 4. Validate inputs
    $nickname         = trim($body['nickname'] ?? '');
    $email            = trim($body['email'] ?? '');
    $whatsapp_number  = trim($body['whatsapp_number'] ?? '');
    $whatsapp_group   = trim($body['whatsapp_group'] ?? '');
    $wants_community  = isset($body['wants_community']) && $body['wants_community'] ? 1 : 0;
    $gdpr_consent     = isset($body['gdpr_consent']) && $body['gdpr_consent'] ? 1 : 0;

    if (!$gdpr_consent) {
        echo json_encode(['success' => false, 'message' => 'Bez souhlasu se zpracováním údajů se nemůžeš zaregistrovat.', 'field' => 'gdpr_consent']);
        exit;
    }
    if ($nickname === '') {
        echo json_encode(['success' => false, 'message' => 'Přezdívka je povinná.']);
        exit;
    }
    if (strlen($nickname) < 3 || strlen($nickname) > 30) {
        echo json_encode(['success' => false, 'message' => 'Přezdívka musí mít 3–30 znaků.']);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $nickname)) {
        echo json_encode(['success' => false, 'message' => 'Přezdívka smí obsahovat jen písmena, čísla, _ a -.']);
        exit;
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Zadejte platný e-mail.']);
        exit;
    }

    // Check duplicates
    $stmt = $db->prepare('SELECT id FROM GUM_supporters WHERE nickname = :nickname LIMIT 1');
    $stmt->execute([':nickname' => $nickname]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tato přezdívka je již obsazena. Zvolte jinou.', 'field' => 'nickname']);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM GUM_supporters WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tento e-mail je již registrován.', 'field' => 'email']);
        exit;
    }

    // 5. Determine founding status
    $total       = get_total_count($db);
    $is_founding = ($total < FOUNDING_LIMIT) ? 1 : 0;

    // 6. Insert
    try {
        $stmt = $db->prepare(
            'INSERT INTO GUM_supporters (nickname, email, whatsapp_number, whatsapp_group, wants_community, ip_address, is_founding)
             VALUES (:nickname, :email, :whatsapp_number, :whatsapp_group, :wants_community, :ip_address, :is_founding)'
        );
        $stmt->execute([
            ':nickname'        => $nickname,
            ':email'           => $email,
            ':whatsapp_number' => $whatsapp_number ?: null,
            ':whatsapp_group'  => $whatsapp_group ?: null,
            ':wants_community' => $wants_community,
            ':ip_address'      => $ip,
            ':is_founding'     => $is_founding,
        ]);
        $new_id = (int)$db->lastInsertId();

        // 7. Log rate limit
        log_rate_limit($db, $ip);

        // Fetch the inserted row for response
        $stmt = $db->prepare('SELECT id, nickname, is_founding, created_at FROM GUM_supporters WHERE id = :id');
        $stmt->execute([':id' => $new_id]);
        $supporter = $stmt->fetch();

        // Notifikace adminovi (selže-li mail, registrace tím netrpí)
        notify_new_supporter([
            'nickname'        => $nickname,
            'email'           => $email,
            'whatsapp_number' => $whatsapp_number,
            'is_founding'     => $is_founding,
        ]);

        echo json_encode([
            'success'    => true,
            'message'    => $is_founding
                ? 'Vítej v posádce! Jsi Founding Supporter 🔥'
                : 'Přidán/a do komunity! Vítej na palubě.',
            'supporter'  => $supporter,
            'group_url'  => WHATSAPP_GROUP_URL,
        ]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Chyba při registraci. Zkuste to znovu.']);
    }
    exit;
}

// ─── HTML PAGE ────────────────────────────────────────────────────────────────
try {
    $db             = get_db();
    $total          = get_total_count($db);
    $supporters_list = get_supporters($db, 1);
    $has_more       = $total > 20;
    $ticker_items   = get_recent_ticker($db, 12);
    $founding_left  = max(0, FOUNDING_LIMIT - $total);
    $csrf_token     = generate_csrf();
} catch (\Exception $e) {
    $total           = 0;
    $supporters_list = [];
    $has_more        = false;
    $ticker_items    = [];
    $founding_left   = FOUNDING_LIMIT;
    $csrf_token      = generate_csrf();
}

// Fallback ticker placeholders
if (empty($ticker_items)) {
    $ticker_items = ['GUMBALKAN', 'READY', 'JEDEM', 'CHAOS', 'BALKÁN', 'POSÁDKA'];
}
$ticker_doubled = array_merge($ticker_items, $ticker_items);
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Komunita – GUMBALKÁN 2026</title>
<?php render_head_meta(
  'Komunita – GUMBALKÁN 2026',
  'Přidej se k partě Jedeme na jedno. Zapiš se na zeď podporovatelů, staň se Founding Supporterem a jeď s námi celý Balkán.'
); ?>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Special+Elite&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { min-height: 100%; background: #000; color: #fff; font-family: 'Oswald', sans-serif; overflow-x: hidden; }

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite  { font-family: 'Special Elite', cursive; }
.font-oswald { font-family: 'Oswald', sans-serif; }

/* ── Noise overlay ───────────────────────────────────────────── */
.noise {
  position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ── Nav ─────────────────────────────────────────────────────── */
#nav {
  position: fixed; top: 0; left: 0; width: 100%; height: 52px; z-index: 1000;
  background: rgba(0,0,0,.88); backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,0,60,.22);
  display: flex; align-items: center; gap: 24px; padding: 0 20px;
}
.nav-link { text-decoration: none; transition: color .2s; }
.nav-link-main { font-family: 'Bebas Neue', cursive; font-size: 1.35rem; letter-spacing: .08em; color: #fff; }
.nav-link-main:hover { color: #ff003c; }
.nav-link-sub { font-family: 'Oswald', sans-serif; font-size: .75rem; letter-spacing: .25em; text-transform: uppercase; color: #6b7280; }
.nav-link-sub:hover { color: #ff003c; }
.nav-link-active { font-family: 'Oswald', sans-serif; font-size: .75rem; letter-spacing: .25em; text-transform: uppercase; color: #ff003c; padding-bottom: 2px; border-bottom: 2px solid #ff003c; }
.nav-right { margin-left: auto; }

/* ── Animations ──────────────────────────────────────────────── */
@keyframes neonPulse {
  0%,100% { text-shadow: 0 0 10px #ff003c, 0 0 20px #ff003c, 0 0 40px #ff003c; }
  50%      { text-shadow: 0 0 5px  #ff003c, 0 0 10px #ff003c; }
}
@keyframes glitchX {
  0%,100% { transform: translate(0) skewX(0deg); }
  20%  { transform: translate(-3px, 2px) skewX(-2deg); }
  40%  { transform: translate(3px, -2px) skewX(2deg); }
  60%  { transform: translate(-2px,-1px) skewX(-1deg); }
  80%  { transform: translate(2px, 1px) skewX(1deg); }
}
@keyframes countGlitch {
  0%,90%,100% { transform: skewX(0deg); }
  93% { transform: skewX(-5deg); }
  96% { transform: skewX(3deg); }
}
@keyframes heartbeat {
  0%,100% { transform: scale(1); }
  50%      { transform: scale(1.05); }
}
@keyframes float {
  0%,100% { transform: translateY(0px) rotateZ(-45deg); }
  50%      { transform: translateY(20px) rotateZ(-45deg); }
}
@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}
@keyframes slideIn {
  from { transform: translateY(-20px) scaleY(0.95); opacity: 0; }
  to   { transform: translateY(0)     scaleY(1);    opacity: 1; }
}
@keyframes tickerScroll {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.glitch-anim { animation: glitchX 0.15s infinite; }

/* ── Red line ────────────────────────────────────────────────── */
.red-line { height: 3px; background: #ff003c; box-shadow: 0 0 15px #ff003c; }

/* ── Reveal ──────────────────────────────────────────────────── */
.reveal { opacity: 0; transform: translateY(40px); transition: all 0.7s cubic-bezier(0.16,1,0.3,1); }
.reveal.visible { opacity: 1; transform: translateY(0); }

/* ── Member card ─────────────────────────────────────────────── */
.member-card {
  border-left: 4px solid #ff003c;
  background: linear-gradient(90deg, rgba(255,0,60,0.05) 0%, transparent 100%);
  transition: all 0.3s;
}
.member-card:hover {
  border-left-color: #fff;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 0%, transparent 100%);
  transform: translateX(6px);
}
.member-card-new {
  animation: slideIn 0.4s ease-out;
}

/* ── Ticker ──────────────────────────────────────────────────── */
.ticker-track {
  display: flex;
  white-space: nowrap;
  animation: tickerScroll 20s linear infinite;
  will-change: transform;
}
.ticker-track:hover { animation-play-state: paused; }

/* ── Form inputs ─────────────────────────────────────────────── */
.form-input {
  width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,0,60,0.25);
  color: #fff; font-family: 'Oswald', sans-serif; font-size: .95rem; letter-spacing: .05em;
  padding: 10px 14px; outline: none; transition: border-color .2s, box-shadow .2s;
}
.form-input:focus { border-color: #ff003c; box-shadow: 0 0 8px rgba(255,0,60,0.3); }
.form-input::placeholder { color: rgba(255,255,255,0.25); }
.form-label {
  display: block; font-family: 'Oswald', sans-serif; font-size: .7rem;
  letter-spacing: .3em; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px;
}

/* ── Submit button ───────────────────────────────────────────── */
.submit-btn {
  background: #ff003c; color: #fff; border: none; cursor: pointer;
  font-family: 'Oswald', sans-serif; font-size: .85rem; letter-spacing: .25em;
  text-transform: uppercase; padding: 13px 32px; width: 100%;
  clip-path: polygon(3% 0%, 100% 0%, 97% 100%, 0% 100%);
  transition: background .2s, color .2s;
  animation: heartbeat 2s infinite;
}
.submit-btn:hover { background: #fff; color: #000; animation: none; }
.submit-btn:disabled { background: #333; color: #666; animation: none; cursor: not-allowed; }

/* ── Founding badge ──────────────────────────────────────────── */
.founding-badge {
  display: inline-block; background: rgba(255,0,60,0.15); border: 1px solid rgba(255,0,60,0.5);
  color: #ff003c; font-family: 'Oswald', sans-serif; font-size: .65rem;
  letter-spacing: .2em; text-transform: uppercase; padding: 3px 8px;
}

/* ── Honeypot ────────────────────────────────────────────────── */
.hp-field { position: absolute; opacity: 0; height: 0; overflow: hidden; pointer-events: none; }

/* ── Checkbox ────────────────────────────────────────────────── */
.custom-cb { accent-color: #ff003c; width: 16px; height: 16px; cursor: pointer; }

/* ── Load more btn ───────────────────────────────────────────── */
.load-more-btn {
  background: transparent; border: 1px solid rgba(255,0,60,0.35); color: #ff003c;
  font-family: 'Oswald', sans-serif; font-size: .8rem; letter-spacing: .25em;
  text-transform: uppercase; padding: 10px 28px; cursor: pointer;
  transition: all .2s;
  clip-path: polygon(3% 0%, 100% 0%, 97% 100%, 0% 100%);
}
.load-more-btn:hover { background: rgba(255,0,60,0.1); border-color: #ff003c; }
.load-more-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Counter glitch ──────────────────────────────────────────── */
.counter-num {
  animation: countGlitch 4s infinite, neonPulse 3s infinite;
  display: inline-block;
}
  </style>
</head>
<body>
<div class="noise"></div>

<!-- ── NAV ──────────────────────────────────────────────────────────── -->
<nav id="nav">
  <a href="index.php" class="nav-link nav-link-main">GUMBALKÁN</a>
  <a href="alps.php"  class="nav-link nav-link-sub">Training camp</a>
  <a href="support.php" class="nav-link nav-link-sub">Podpora</a>
  <a href="supporters.php" class="nav-link nav-link-active nav-right">KOMUNITA</a>
</nav>

<!-- ── HERO ─────────────────────────────────────────────────────────── -->
<section style="padding-top:52px;min-height:60vh;position:relative;background:linear-gradient(135deg,#0a0a0a 0%,#1a0008 40%,#0a0a0a 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;">

  <!-- Floating shapes -->
  <div style="position:absolute;top:20px;left:10%;width:120px;height:120px;border:2px solid #ff003c;opacity:0.08;animation:float 6s ease-in-out infinite;transform:rotateZ(-45deg);pointer-events:none;"></div>
  <div style="position:absolute;bottom:30px;right:8%;width:180px;height:180px;border:2px solid #ff003c;opacity:0.04;animation:float 9s ease-in-out infinite 1.5s;transform:rotateZ(-45deg);pointer-events:none;"></div>
  <div style="position:absolute;top:0;right:0;width:33%;height:100%;opacity:0.07;background:linear-gradient(135deg,transparent 40%,#ff003c 40%,#ff003c 42%,transparent 42%);pointer-events:none;"></div>

  <div style="position:relative;z-index:2;text-align:center;padding:60px 24px 40px;">
    <div class="font-oswald" style="font-size:.7rem;letter-spacing:.5em;color:#ff003c;text-transform:uppercase;margin-bottom:16px;animation:fadeInUp .8s ease-out both;">
      // POSÁDKA //
    </div>
    <h1 class="font-bebas" style="font-size:clamp(2.5rem,10vw,7rem);color:#fff;line-height:1;animation:neonPulse 3s infinite;">
      THE CREW IS GROWING
    </h1>
    <div id="hero-counter" class="font-bebas counter-num" style="font-size:clamp(2rem,7vw,5rem);color:#ff003c;margin:12px 0;">
      <?= htmlspecialchars((string)$total, ENT_QUOTES, 'UTF-8') ?> BLÁZNŮ JE READY
    </div>
    <p class="font-elite" style="color:#6b7280;font-size:1rem;margin:16px 0 32px;animation:slideUp 1s .3s both;">
      Přidej se k posádce. Žádné hotely. Žádný plán. Čistý chaos.
    </p>
    <a href="#form-section" onclick="document.getElementById('form-section').scrollIntoView({behavior:'smooth'});return false;"
       class="font-oswald" style="display:inline-block;background:#ff003c;color:#fff;text-decoration:none;font-size:.85rem;letter-spacing:.25em;text-transform:uppercase;padding:13px 36px;clip-path:polygon(4% 0%,100% 0%,96% 100%,0% 100%);animation:heartbeat 2s infinite;transition:background .2s,color .2s;"
       onmouseover="this.style.background='#fff';this.style.color='#000';this.style.animationPlayState='paused';"
       onmouseout="this.style.background='#ff003c';this.style.color='#fff';this.style.animationPlayState='running';">
      PŘIDAT SE DO KOMUNITY
    </a>
  </div>
</section>

<!-- ── TICKER ────────────────────────────────────────────────────────── -->
<div style="background:#0a0003;border-top:1px solid rgba(255,0,60,0.2);border-bottom:1px solid rgba(255,0,60,0.2);padding:12px 0;overflow:hidden;position:relative;">
  <div style="display:flex;align-items:center;gap:0;">
    <div style="flex-shrink:0;font-family:'Oswald',sans-serif;font-size:.65rem;letter-spacing:.3em;color:#ff003c;text-transform:uppercase;padding:0 20px;white-space:nowrap;border-right:1px solid rgba(255,0,60,0.3);">
      // NOVĚ PŘIDANÍ //
    </div>
    <div style="overflow:hidden;flex:1;">
      <div class="ticker-track">
        <?php foreach ($ticker_doubled as $nick): ?>
          <span style="font-family:'Bebas Neue',cursive;font-size:1rem;color:#fff;padding:0 8px;letter-spacing:.1em;">
            <?= htmlspecialchars($nick, ENT_QUOTES, 'UTF-8') ?>
          </span>
          <span style="color:#ff003c;padding:0 4px;">·</span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="red-line"></div>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────── -->
<section style="padding:48px 20px 80px;background:linear-gradient(180deg,#060003 0%,#000 100%);">
  <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr;gap:40px;" id="main-grid">

    <!-- ── LEFT: FORM ─────────────────────────────────────── -->
    <div id="form-section" class="reveal" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,0,60,0.2);padding:32px;">

      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:8px;">
        // REGISTRACE //
      </div>
      <h2 class="font-bebas" style="font-size:2.2rem;color:#fff;margin-bottom:24px;">PŘIDEJ SE K POSÁDCE</h2>

      <!-- Founding callout -->
      <div id="founding-callout" style="border:1px solid rgba(255,0,60,0.35);background:rgba(255,0,60,0.06);padding:12px 16px;margin-bottom:24px;display:<?= $founding_left > 0 ? 'block' : 'none' ?>;">
        <span style="font-family:'Oswald',sans-serif;font-size:.8rem;letter-spacing:.1em;color:#ff003c;">
          🔥 Prvních <?= FOUNDING_LIMIT ?> = Founding Supporter badge — zbývá
          <strong id="founding-left-num"><?= $founding_left ?></strong> míst
        </span>
      </div>

      <!-- Messages -->
      <div id="form-message" style="display:none;padding:12px 16px;margin-bottom:20px;font-family:'Oswald',sans-serif;font-size:.85rem;letter-spacing:.1em;"></div>

      <!-- WhatsApp skupina (zobrazí se po úspěšné registraci, pokud je nastavený odkaz) -->
      <div id="wa-join-wrap" style="display:none;margin-bottom:20px;padding:18px 16px;border:1px solid rgba(37,211,102,0.4);background:rgba(37,211,102,0.08);text-align:center;">
        <div style="font-family:'Oswald',sans-serif;font-size:.8rem;letter-spacing:.1em;color:#9ca3af;margin-bottom:12px;text-transform:uppercase;">Poslední krok — přidej se do WhatsApp skupiny</div>
        <a id="wa-join-link" href="#" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;font-family:'Oswald',sans-serif;font-weight:700;padding:12px 22px;background:#25D366;color:#000;text-decoration:none;letter-spacing:.08em;text-transform:uppercase;clip-path:polygon(4% 0%,100% 0%,96% 100%,0% 100%);">
          <i data-lucide="message-circle" style="width:20px;height:20px;"></i> Přidej se do skupiny
        </a>
      </div>

      <form id="reg-form" novalidate style="position:relative;">
        <!-- Honeypot -->
        <div class="hp-field" aria-hidden="true">
          <label for="website">Leave empty</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" value="">
        </div>

        <div style="margin-bottom:18px;">
          <label class="form-label" for="nickname">Přezdívka *</label>
          <input class="form-input" type="text" id="nickname" name="nickname"
                 placeholder="tvoje_jméno" maxlength="30" autocomplete="off"
                 pattern="[a-zA-Z0-9_\-]+"
                 title="Jen písmena, čísla, _ a -">
          <div id="nickname-hint" style="font-family:'Oswald',sans-serif;font-size:.65rem;color:#6b7280;margin-top:5px;letter-spacing:.1em;">3–30 znaků, jen písmena, čísla, _ a -</div>
        </div>

        <div style="margin-bottom:18px;">
          <label class="form-label" for="email">E-mail *</label>
          <input class="form-input" type="email" id="email" name="email"
                 placeholder="tvuj@email.cz" maxlength="255" autocomplete="email">
        </div>

        <div style="margin-bottom:18px;">
          <label class="form-label" for="whatsapp_number">WhatsApp číslo <span style="color:#4b5563;">(nepovinné)</span></label>
          <input class="form-input" type="text" id="whatsapp_number" name="whatsapp_number"
                 placeholder="+420 XXX XXX XXX" maxlength="30" autocomplete="tel">
        </div>

        <div style="margin-bottom:18px;">
          <label class="form-label" for="whatsapp_group">WhatsApp skupina <span style="color:#4b5563;">(nepovinné)</span></label>
          <input class="form-input" type="text" id="whatsapp_group" name="whatsapp_group"
                 placeholder="název skupiny" maxlength="100">
        </div>

        <div style="margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;">
          <input class="custom-cb" type="checkbox" id="wants_community" name="wants_community" value="1" style="margin-top:2px;flex-shrink:0;">
          <label for="wants_community" style="font-family:'Special Elite',cursive;font-size:.9rem;color:#9ca3af;cursor:pointer;line-height:1.4;">
            Chci být součástí komunity a dostávat info o Gumbalkán akcích
          </label>
        </div>

        <div style="margin-bottom:24px;display:flex;align-items:flex-start;gap:10px;">
          <input class="custom-cb" type="checkbox" id="gdpr_consent" name="gdpr_consent" value="1" required style="margin-top:2px;flex-shrink:0;">
          <label for="gdpr_consent" style="font-family:'Special Elite',cursive;font-size:.82rem;color:#9ca3af;cursor:pointer;line-height:1.4;">
            Souhlasím se zpracováním zadaných údajů (přezdívka, e-mail, příp. WhatsApp)
            pro účely komunity Gumbalkán.
            <a href="#" onclick="document.getElementById('gdpr-modal').style.display='flex';return false;"
               style="color:#ff003c;text-decoration:underline;">Zásady zpracování</a>
          </label>
        </div>

        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit" class="submit-btn" id="submit-btn">PŘIDAT SE DO POSÁDKY</button>
      </form>
    </div>

    <!-- ── RIGHT: WALL ────────────────────────────────────── -->
    <div class="reveal" id="wall-section">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
          <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:4px;">
            // KOMUNITA //
          </div>
          <h2 class="font-bebas" style="font-size:2rem;color:#fff;line-height:1;">NAŠI BLÁZNI</h2>
        </div>
        <div style="background:rgba(255,0,60,0.1);border:1px solid rgba(255,0,60,0.35);padding:6px 14px;">
          <span class="font-bebas" style="font-size:1.4rem;color:#ff003c;" id="wall-count"><?= $total ?></span>
          <span class="font-oswald" style="font-size:.65rem;letter-spacing:.25em;color:#6b7280;text-transform:uppercase;margin-left:6px;">členů</span>
        </div>
      </div>

      <!-- Feed -->
      <div id="supporters-feed">
        <?php if (empty($supporters_list)): ?>
          <div id="empty-state" style="text-align:center;padding:60px 20px;border:1px dashed rgba(255,0,60,0.15);">
            <div class="font-bebas" style="font-size:2rem;color:#333;margin-bottom:8px;">ZATÍM NIKDO</div>
            <p class="font-elite" style="color:#4b5563;">Buď první! Přidej se k posádce.</p>
          </div>
        <?php else: ?>
          <?php foreach ($supporters_list as $s): ?>
            <?php include_supporter_card($s); ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Load more -->
      <div style="text-align:center;margin-top:24px;" id="load-more-wrap" <?= !$has_more ? 'style="display:none;text-align:center;margin-top:24px;"' : '' ?>>
        <button class="load-more-btn" id="load-more-btn" onclick="loadMore()" <?= !$has_more ? 'style="display:none;"' : '' ?>>
          Načíst další ↓
        </button>
      </div>
    </div>

  </div>
</section>

<!-- ── GDPR modal ─────────────────────────────────────────────────────── -->
<div id="gdpr-modal" onclick="if(event.target===this)this.style.display='none';"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:300;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#0a0003;border:1px solid rgba(255,0,60,.4);padding:32px;max-width:520px;width:100%;max-height:85vh;overflow-y:auto;">
    <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:8px;">// SOUKROMÍ //</div>
    <h3 class="font-bebas" style="font-size:1.8rem;margin-bottom:16px;color:#fff;">ZÁSADY ZPRACOVÁNÍ ÚDAJŮ</h3>
    <div style="font-family:'Oswald',sans-serif;font-size:.85rem;color:#9ca3af;line-height:1.6;">
      <p style="margin-bottom:12px;"><b style="color:#fff;">Jaké údaje sbíráme:</b> přezdívku, e-mail a nepovinně WhatsApp kontakt. Pro ochranu proti spamu dočasně ukládáme i IP adresu.</p>
      <p style="margin-bottom:12px;"><b style="color:#fff;">Proč:</b> abychom tě mohli vést v komunitě Gumbalkán a posílat ti info o akcích a průběhu cesty.</p>
      <p style="margin-bottom:12px;"><b style="color:#fff;">Komu je předáváme:</b> nikomu. Údaje nepředáváme třetím stranám ani neprodáváme.</p>
      <p style="margin-bottom:12px;"><b style="color:#fff;">Jak dlouho:</b> dokud trvá komunita, nebo dokud nepožádáš o smazání.</p>
      <p style="margin-bottom:0;"><b style="color:#fff;">Tvá práva:</b> kdykoliv můžeš požádat o výpis nebo smazání svých údajů na <a href="mailto:dest.di.spezza@gmail.com" style="color:#ff003c;">dest.di.spezza@gmail.com</a>.</p>
    </div>
    <button type="button" onclick="document.getElementById('gdpr-modal').style.display='none';"
            class="submit-btn" style="margin-top:24px;">ROZUMÍM</button>
  </div>
</div>

<div class="red-line"></div>

<!-- ── FOOTER ────────────────────────────────────────────────────────── -->
<footer style="padding:48px 20px;text-align:center;background:#000;">
  <div class="font-bebas" style="font-size:2rem;animation:neonPulse 3s infinite;margin-bottom:8px;">JEDEME NA JEDNO</div>
  <p class="font-elite" style="color:#4b5563;font-size:.9rem;">Když je cesta cílem</p>
  <div style="margin-top:16px;">
    <a href="index.php" style="font-family:'Oswald',sans-serif;font-size:.7rem;letter-spacing:.25em;color:#6b7280;text-decoration:none;text-transform:uppercase;margin:0 12px;">Domů</a>
    <a href="alps.php"  style="font-family:'Oswald',sans-serif;font-size:.7rem;letter-spacing:.25em;color:#6b7280;text-decoration:none;text-transform:uppercase;margin:0 12px;">Alps</a>
    <a href="support.php" style="font-family:'Oswald',sans-serif;font-size:.7rem;letter-spacing:.25em;color:#6b7280;text-decoration:none;text-transform:uppercase;margin:0 12px;">Podpora</a>
  </div>
  <div style="margin-top:16px;font-family:'Oswald',sans-serif;font-size:.65rem;letter-spacing:.3em;color:#333;text-transform:uppercase;">
    © 2026 Gumbalkán · Veškerý chaos vyhrazen
  </div>
</footer>

<?php
function include_supporter_card(array $s): void {
    $nick      = htmlspecialchars($s['nickname'],   ENT_QUOTES, 'UTF-8');
    $founding  = (int)$s['is_founding'];
    $time_text = htmlspecialchars(time_ago($s['created_at']), ENT_QUOTES, 'UTF-8');
    $id        = (int)$s['id'];
    $badge     = $founding ? '<span class="founding-badge">FOUNDING SUPPORTER 🔥</span>' : '';
    echo '<div class="member-card" id="card-' . $id . '" style="padding:14px 16px;margin-bottom:10px;">';
    echo '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;">';
    echo '<div>';
    echo '<div class="font-bebas" style="font-size:1.5rem;color:#fff;line-height:1.1;">' . $nick . '</div>';
    echo $badge;
    echo '</div>';
    echo '<div class="font-oswald" style="font-size:.65rem;letter-spacing:.15em;color:#4b5563;text-transform:uppercase;white-space:nowrap;">' . $time_text . '</div>';
    echo '</div>';
    echo '</div>';
}
?>

<script>
// ── State ──────────────────────────────────────────────────────────────────
let currentPage = 1;
let isLoading   = false;
let totalCount  = <?= $total ?>;

// ── Responsive grid ────────────────────────────────────────────────────────
(function applyGrid() {
  const grid = document.getElementById('main-grid');
  if (!grid) return;
  function setGrid() {
    grid.style.gridTemplateColumns = window.innerWidth >= 768 ? '2fr 3fr' : '1fr';
  }
  setGrid();
  window.addEventListener('resize', setGrid);
})();

// ── Scroll reveal ──────────────────────────────────────────────────────────
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

// ── Build card HTML ────────────────────────────────────────────────────────
function buildCardHTML(s) {
  const nick     = escHtml(s.nickname);
  const timeText = escHtml(s.created_at ? formatTimeAgo(s.created_at) : 'právě teď');
  const founding = parseInt(s.is_founding) === 1
    ? '<span class="founding-badge">FOUNDING SUPPORTER 🔥</span>' : '';
  return `<div class="member-card member-card-new" id="card-${s.id}" style="padding:14px 16px;margin-bottom:10px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <div>
        <div class="font-bebas" style="font-size:1.5rem;color:#fff;line-height:1.1;">${nick}</div>
        ${founding}
      </div>
      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.15em;color:#4b5563;text-transform:uppercase;white-space:nowrap;">${timeText}</div>
    </div>
  </div>`;
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}

function formatTimeAgo(datetimeStr) {
  const diff = Math.floor((Date.now() - new Date(datetimeStr.replace(' ', 'T')).getTime()) / 1000);
  if (diff < 60)    return 'právě teď';
  if (diff < 3600)  return Math.floor(diff / 60) + ' min';
  if (diff < 86400) return Math.floor(diff / 3600) + ' hod';
  if (diff < 604800)return Math.floor(diff / 86400) + ' dní';
  const d = new Date(datetimeStr.replace(' ', 'T'));
  return d.getDate() + '.' + (d.getMonth()+1) + '.' + d.getFullYear();
}

// ── Show message ───────────────────────────────────────────────────────────
function showMessage(msg, isSuccess) {
  const el = document.getElementById('form-message');
  el.style.display = 'block';
  el.style.background = isSuccess ? 'rgba(0,200,80,0.1)' : 'rgba(255,0,60,0.1)';
  el.style.border = isSuccess ? '1px solid rgba(0,200,80,0.4)' : '1px solid rgba(255,0,60,0.4)';
  el.style.color  = isSuccess ? '#4ade80' : '#ff003c';
  el.textContent  = msg;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ── Update counter ─────────────────────────────────────────────────────────
function updateCounter(newTotal) {
  totalCount = newTotal;
  document.getElementById('hero-counter').textContent = newTotal + ' BLÁZNŮ JE READY';
  document.getElementById('wall-count').textContent   = newTotal;

  // Update founding callout
  const left = Math.max(0, <?= FOUNDING_LIMIT ?> - newTotal);
  const leftEl = document.getElementById('founding-left-num');
  const callout = document.getElementById('founding-callout');
  if (leftEl) leftEl.textContent = left;
  if (callout) callout.style.display = left > 0 ? 'block' : 'none';
}

// ── Form submit ────────────────────────────────────────────────────────────
document.getElementById('reg-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.textContent = 'REGISTRUJI…';

  const csrf = document.getElementById('csrf_token').value;
  const payload = {
    action:           'register',
    nickname:         document.getElementById('nickname').value,
    email:            document.getElementById('email').value,
    whatsapp_number:  document.getElementById('whatsapp_number').value,
    whatsapp_group:   document.getElementById('whatsapp_group').value,
    wants_community:  document.getElementById('wants_community').checked ? '1' : '0',
    gdpr_consent:     document.getElementById('gdpr_consent').checked ? '1' : '0',
    website:          document.getElementById('website').value, // honeypot
    csrf_token:       csrf,
  };

  try {
    const resp = await fetch('supporters.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await resp.json();

    if (data.success) {
      showMessage(data.message, true);
      document.getElementById('reg-form').reset();

      // WhatsApp skupina – pokud je nastavený odkaz, ukaž tlačítko pro připojení
      if (data.group_url) {
        const wrap = document.getElementById('wa-join-wrap');
        const link = document.getElementById('wa-join-link');
        if (wrap && link) {
          link.href = data.group_url;
          wrap.style.display = 'block';
          wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }

      // Prepend new card to feed
      if (data.supporter) {
        const feed = document.getElementById('supporters-feed');
        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.remove();

        const cardHtml = buildCardHTML(data.supporter);
        feed.insertAdjacentHTML('afterbegin', cardHtml);
      }

      updateCounter(totalCount + 1);
    } else {
      showMessage(data.message || 'Chyba. Zkuste to znovu.', false);

      // Highlight specific field
      if (data.field) {
        const fieldEl = document.getElementById(data.field);
        if (fieldEl) {
          fieldEl.style.borderColor = '#ff003c';
          fieldEl.focus();
        }
      }
    }
  } catch (err) {
    showMessage('Síťová chyba. Zkontrolujte připojení.', false);
  } finally {
    btn.disabled = false;
    btn.textContent = 'PŘIDAT SE DO POSÁDKY';
  }
});

// ── Load more ──────────────────────────────────────────────────────────────
async function loadMore() {
  if (isLoading) return;
  isLoading = true;
  const btn = document.getElementById('load-more-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Načítám…'; }

  currentPage++;
  try {
    const resp = await fetch(`supporters.php?action=load&page=${currentPage}`);
    const data = await resp.json();

    const feed = document.getElementById('supporters-feed');
    (data.supporters || []).forEach(s => {
      feed.insertAdjacentHTML('beforeend', buildCardHTML(s));
    });

    if (!data.has_more) {
      const wrap = document.getElementById('load-more-wrap');
      if (wrap) wrap.style.display = 'none';
    } else {
      if (btn) { btn.disabled = false; btn.textContent = 'Načíst další ↓'; }
    }

    updateCounter(data.total || totalCount);
  } catch (err) {
    currentPage--;
    if (btn) { btn.disabled = false; btn.textContent = 'Načíst další ↓'; }
  } finally {
    isLoading = false;
  }
}

// ── Periodic counter refresh (every 30s) ──────────────────────────────────
setInterval(async () => {
  try {
    const resp = await fetch('supporters.php?action=load&page=1');
    const data = await resp.json();
    if (data.total !== undefined) updateCounter(data.total);
  } catch (_) {}
}, 30000);
</script>
</body>
</html>
