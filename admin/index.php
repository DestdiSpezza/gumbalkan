<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

// ─── Helper: is admin logged in? ──────────────────────────────────────────────
function is_admin(): bool
{
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

// ─── Helper: redirect ─────────────────────────────────────────────────────────
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ─── POST actions ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── LOGIN ──────────────────────────────────────────────────────────────────
    if ($action === 'login') {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf($csrf)) {
            $_SESSION['flash_error'] = 'Neplatný bezpečnostní token.';
            redirect('index.php');
        }
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $db   = get_db();
            $stmt = $db->prepare('SELECT id, username, password_hash FROM GUM_admin_users WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION[ADMIN_SESSION_KEY] = ['id' => $user['id'], 'username' => $user['username']];
                session_regenerate_id(true);
                redirect('index.php');
            } else {
                $_SESSION['flash_error'] = 'Nesprávné přihlašovací údaje.';
                redirect('index.php');
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Chyba serveru.';
            redirect('index.php');
        }
    }

    // ── LOGOUT ────────────────────────────────────────────────────────────────
    if ($action === 'logout') {
        session_destroy();
        redirect('index.php');
    }

    // ── REQUIRE ADMIN FROM HERE ───────────────────────────────────────────────
    if (!is_admin()) {
        redirect('index.php');
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($csrf)) {
        $_SESSION['flash_error'] = 'Neplatný bezpečnostní token.';
        redirect('index.php');
    }

    // ── DELETE SUPPORTER ──────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $db   = get_db();
                $stmt = $db->prepare('DELETE FROM GUM_supporters WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $_SESSION['flash_success'] = 'Člen smazán.';
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Chyba při mazání.';
            }
        }
        redirect('index.php');
    }

    // ── EDIT NICKNAME ─────────────────────────────────────────────────────────
    if ($action === 'edit_nickname') {
        $id          = (int)($_POST['id'] ?? 0);
        $new_nick    = trim($_POST['new_nickname'] ?? '');

        if ($id <= 0 || $new_nick === '') {
            $_SESSION['flash_error'] = 'Neplatná data.';
            redirect('index.php');
        }
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $new_nick)) {
            $_SESSION['flash_error'] = 'Přezdívka obsahuje nepovolené znaky nebo špatnou délku.';
            redirect('index.php');
        }
        try {
            $db   = get_db();
            $stmt = $db->prepare('UPDATE GUM_supporters SET nickname = :nick WHERE id = :id');
            $stmt->execute([':nick' => $new_nick, ':id' => $id]);
            $_SESSION['flash_success'] = 'Přezdívka změněna.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Chyba – přezdívka může být již obsazená.';
        }
        redirect('index.php');
    }

    // ── ADD INSTAGRAM REEL ────────────────────────────────────────────────────
    if ($action === 'add_reel') {
        $url = normalize_reel_url($_POST['reel_url'] ?? '');
        if (!is_valid_reel_url($url)) {
            $_SESSION['flash_error'] = 'Neplatný Instagram odkaz. Použij odkaz na reel nebo příspěvek (instagram.com/reel/… nebo /p/…).';
            redirect('index.php#reels');
        }
        try {
            add_reel(get_db(), $url);
            $_SESSION['flash_success'] = 'Video přidáno.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Chyba při přidávání videa.';
        }
        redirect('index.php#reels');
    }

    // ── DELETE INSTAGRAM REEL ─────────────────────────────────────────────────
    if ($action === 'delete_reel') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                delete_reel(get_db(), $id);
                $_SESSION['flash_success'] = 'Video smazáno.';
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Chyba při mazání videa.';
            }
        }
        redirect('index.php#reels');
    }

    // ── ADD SPONSOR ───────────────────────────────────────────────────────────
    if ($action === 'add_sponsor') {
        $name = trim($_POST['sponsor_name'] ?? '');
        $url  = trim($_POST['sponsor_url'] ?? '');

        if ($name === '') {
            $_SESSION['flash_error'] = 'Zadej název partnera.';
            redirect('index.php#sponsors');
        }
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $_SESSION['flash_error'] = 'Neplatná webová adresa partnera.';
            redirect('index.php#sponsors');
        }
        if (empty($_FILES['sponsor_logo']) || ($_FILES['sponsor_logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Nahraj logo partnera (PNG, JPG, SVG nebo WEBP).';
            redirect('index.php#sponsors');
        }

        $allowed = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $ext = strtolower(pathinfo($_FILES['sponsor_logo']['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            $_SESSION['flash_error'] = 'Nepodporovaný formát loga.';
            redirect('index.php#sponsors');
        }
        if ($_FILES['sponsor_logo']['size'] > 3 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Logo je moc velké (max 3 MB).';
            redirect('index.php#sponsors');
        }

        $logoDir = __DIR__ . '/../logos';
        if (!is_dir($logoDir)) mkdir($logoDir, 0755, true);
        $fname    = 'sponsor_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destAbs  = $logoDir . '/' . $fname;
        $destRel  = 'logos/' . $fname;

        if (!move_uploaded_file($_FILES['sponsor_logo']['tmp_name'], $destAbs)) {
            $_SESSION['flash_error'] = 'Nepodařilo se uložit logo.';
            redirect('index.php#sponsors');
        }
        try {
            add_sponsor(get_db(), $name, $destRel, $url);
            $_SESSION['flash_success'] = 'Partner přidán.';
        } catch (\Exception $e) {
            @unlink($destAbs);
            $_SESSION['flash_error'] = 'Chyba při ukládání partnera.';
        }
        redirect('index.php#sponsors');
    }

    // ── DELETE SPONSOR ────────────────────────────────────────────────────────
    if ($action === 'delete_sponsor') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $db  = get_db();
                $row = get_sponsor($db, $id);
                delete_sponsor($db, $id);
                if ($row && !empty($row['logo_path'])) {
                    $f = __DIR__ . '/../' . $row['logo_path'];
                    if (is_file($f)) @unlink($f);
                }
                $_SESSION['flash_success'] = 'Partner smazán.';
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Chyba při mazání partnera.';
            }
        }
        redirect('index.php#sponsors');
    }
}

// ─── GET: CSV export ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'export') {
    if (!is_admin()) redirect('index.php');

    try {
        $db      = get_db();
        $results = $db->query(
            'SELECT id, nickname, email, whatsapp_number, whatsapp_group, wants_community, is_founding, ip_address, created_at
               FROM GUM_supporters ORDER BY created_at DESC'
        )->fetchAll();
    } catch (\Exception $e) {
        $results = [];
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="gumbalkan_supporters_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Přezdívka', 'Email', 'WhatsApp', 'Skupina', 'Komunita', 'Founding', 'IP', 'Datum'], ';');
    foreach ($results as $r) {
        fputcsv($out, [
            $r['id'],
            $r['nickname'],
            $r['email'],
            $r['whatsapp_number'] ?? '',
            $r['whatsapp_group']  ?? '',
            $r['wants_community'] ? 'Ano' : 'Ne',
            $r['is_founding']     ? 'Ano' : 'Ne',
            $r['ip_address'],
            $r['created_at'],
        ], ';');
    }
    fclose($out);
    exit;
}

// ─── Dashboard data ───────────────────────────────────────────────────────────
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$db_error   = null;
$supporters = [];
$reels      = [];
$sponsors   = [];
$stats      = ['total' => 0, 'founding' => 0, 'community' => 0, 'today' => 0];
$total_rows = 0;

$per_page = 50;
$page     = max(1, (int)($_GET['page'] ?? 1));

if (is_admin()) {
    try {
        $db = get_db();

        // Stats
        $stats['total']     = (int)$db->query('SELECT COUNT(*) FROM GUM_supporters')->fetchColumn();
        $stats['founding']  = (int)$db->query('SELECT COUNT(*) FROM GUM_supporters WHERE is_founding = 1')->fetchColumn();
        $stats['community'] = (int)$db->query('SELECT COUNT(*) FROM GUM_supporters WHERE wants_community = 1')->fetchColumn();
        $today_start = date('Y-m-d 00:00:00');
        $stmt_today  = $db->prepare("SELECT COUNT(*) FROM GUM_supporters WHERE created_at >= :start");
        $stmt_today->execute([':start' => $today_start]);
        $stats['today'] = (int)$stmt_today->fetchColumn();

        $total_rows = $stats['total'];
        $offset     = ($page - 1) * $per_page;

        $stmt = $db->prepare(
            'SELECT id, nickname, email, whatsapp_number, whatsapp_group, wants_community, is_founding, ip_address, created_at
               FROM GUM_supporters
              ORDER BY created_at DESC
              LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $supporters = $stmt->fetchAll();

        $reels    = get_reels($db);
        $sponsors = get_sponsors($db);
    } catch (\Exception $e) {
        $db_error = 'Chyba DB: ' . $e->getMessage();
    }
}

$csrf_token = generate_csrf();
$total_pages = (int)ceil($total_rows / $per_page);
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – GUMBALKÁN Komunita</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Special+Elite&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { min-height: 100%; background: #000; color: #fff; font-family: 'Oswald', sans-serif; }

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite  { font-family: 'Special Elite', cursive; }
.font-oswald { font-family: 'Oswald', sans-serif; }

.noise {
  position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
}

@keyframes neonPulse {
  0%,100% { text-shadow: 0 0 10px #ff003c, 0 0 20px #ff003c; }
  50%      { text-shadow: 0 0 5px #ff003c; }
}
@keyframes glitchX {
  0%,100% { transform: translate(0); }
  33% { transform: translate(-2px,1px); }
  66% { transform: translate(2px,-1px); }
}

/* ── Login ──────────────────────────────────────────────────── */
.login-wrap {
  min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
  background: radial-gradient(ellipse at center, #0d0005 0%, #000 70%);
}
.login-box {
  width: 100%; max-width: 400px; border: 1px solid rgba(255,0,60,0.3); padding: 40px 32px;
  background: rgba(255,255,255,0.02);
}
.form-input {
  width: 100%; background: transparent; border: none; border-bottom: 2px solid rgba(255,0,60,0.4);
  color: #fff; font-family: 'Oswald', sans-serif; font-size: 1rem; letter-spacing: .05em;
  padding: 10px 4px; outline: none; transition: border-color .2s;
}
.form-input:focus { border-bottom-color: #ff003c; }
.form-input::placeholder { color: rgba(255,255,255,0.2); }
.form-label {
  display: block; font-size: .65rem; letter-spacing: .3em; text-transform: uppercase; color: #6b7280; margin-bottom: 8px;
}
.submit-btn {
  background: #ff003c; color: #fff; border: none; cursor: pointer;
  font-family: 'Oswald', sans-serif; font-size: .85rem; letter-spacing: .25em;
  text-transform: uppercase; padding: 13px 32px; width: 100%;
  clip-path: polygon(3% 0%,100% 0%,97% 100%,0% 100%);
  transition: background .2s, color .2s;
}
.submit-btn:hover { background: #fff; color: #000; }

/* ── Dashboard nav ──────────────────────────────────────────── */
#admin-nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(0,0,0,.95); backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,0,60,.25);
  display: flex; align-items: center; gap: 20px; padding: 0 20px; height: 52px;
}
.red-line { height: 3px; background: #ff003c; box-shadow: 0 0 12px #ff003c; }

/* ── Stats ──────────────────────────────────────────────────── */
.stats-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px;
  background: rgba(255,0,60,0.15); border: 1px solid rgba(255,0,60,0.15);
  margin-bottom: 28px;
}
@media (min-width: 640px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stat-box { background: #000; padding: 20px 16px; text-align: center; }
.stat-num { font-family: 'Bebas Neue', cursive; font-size: 2.5rem; color: #fff; animation: neonPulse 4s infinite; }
.stat-label { font-size: .65rem; letter-spacing: .3em; color: #ff003c; text-transform: uppercase; margin-top: 4px; }

/* ── Table ──────────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; border: 1px solid rgba(255,0,60,0.2); }
table { width: 100%; border-collapse: collapse; min-width: 700px; }
th { background: #0a0003; color: #ff003c; font-size: .65rem; letter-spacing: .25em; text-transform: uppercase; padding: 12px 14px; text-align: left; border-bottom: 1px solid rgba(255,0,60,0.25); white-space: nowrap; }
td { padding: 10px 14px; font-size: .85rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
tr:hover td { background: rgba(255,0,60,0.04); }
.action-btn {
  background: transparent; border: 1px solid rgba(255,0,60,0.3); color: #ff003c;
  font-family: 'Oswald',sans-serif; font-size:.65rem; letter-spacing:.15em; text-transform: uppercase;
  padding: 4px 10px; cursor: pointer; transition: all .2s; white-space: nowrap;
}
.action-btn:hover { background: #ff003c; color: #fff; }
.action-btn.del:hover { background: #ff003c; border-color: #ff003c; }

/* ── Search ─────────────────────────────────────────────────── */
.search-input {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,0,60,0.25); color: #fff;
  font-family: 'Oswald',sans-serif; font-size:.9rem; letter-spacing:.05em;
  padding: 10px 14px; outline: none; width: 100%; max-width: 360px;
  transition: border-color .2s;
}
.search-input:focus { border-color: #ff003c; }
.search-input::placeholder { color: rgba(255,255,255,0.2); }

/* ── Flash messages ─────────────────────────────────────────── */
.flash { padding: 12px 18px; font-size: .85rem; letter-spacing: .1em; margin-bottom: 20px; }
.flash.success { background: rgba(0,200,80,0.1); border: 1px solid rgba(0,200,80,0.35); color: #4ade80; }
.flash.error   { background: rgba(255,0,60,0.1);  border: 1px solid rgba(255,0,60,0.4);  color: #ff003c; }

/* ── Modal edit ─────────────────────────────────────────────── */
.modal-bg {
  position: fixed; inset: 0; background: rgba(0,0,0,.85); z-index: 200;
  display: flex; align-items: center; justify-content: center; padding: 20px;
  display: none;
}
.modal-box { background: #0a0003; border: 1px solid rgba(255,0,60,.4); padding: 32px; width: 100%; max-width: 400px; }

/* ── Badge ──────────────────────────────────────────────────── */
.badge-founding { background: rgba(255,0,60,0.15); border: 1px solid rgba(255,0,60,0.4); color: #ff003c; font-size: .6rem; letter-spacing: .2em; padding: 2px 6px; }
.badge-community { background: rgba(0,200,80,0.1); border: 1px solid rgba(0,200,80,0.3); color: #4ade80; font-size: .6rem; letter-spacing: .2em; padding: 2px 6px; }

/* ── Pagination ─────────────────────────────────────────────── */
.page-link {
  display: inline-block; padding: 6px 12px; font-size: .75rem; letter-spacing: .1em;
  border: 1px solid rgba(255,0,60,0.25); color: #9ca3af; text-decoration: none; transition: all .2s;
}
.page-link:hover, .page-link.active { border-color: #ff003c; color: #ff003c; background: rgba(255,0,60,0.08); }
  </style>
</head>
<body>
<div class="noise"></div>

<?php if (!is_admin()): ?>
<!-- ════════════════════════════════════ LOGIN FORM ═══════════════════════════ -->
<div class="login-wrap">
  <div class="login-box">
    <div style="text-align:center;margin-bottom:32px;">
      <div class="font-bebas" style="font-size:2.5rem;animation:neonPulse 3s infinite;">GUMBALKÁN</div>
      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.45em;color:#ff003c;text-transform:uppercase;margin-top:6px;">ADMIN PŘÍSTUP</div>
    </div>

    <?php if ($flash_error): ?>
      <div class="flash error" style="margin-bottom:20px;"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

      <div style="margin-bottom:20px;">
        <label class="form-label" for="username">Uživatelské jméno</label>
        <input class="form-input" type="text" id="username" name="username" placeholder="admin" autocomplete="username" required>
      </div>

      <div style="margin-bottom:28px;">
        <label class="form-label" for="password">Heslo</label>
        <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
      </div>

      <button type="submit" class="submit-btn">PŘIHLÁSIT SE</button>
    </form>

    <div style="text-align:center;margin-top:20px;">
      <a href="../supporters.php" style="font-size:.7rem;letter-spacing:.2em;color:#4b5563;text-decoration:none;text-transform:uppercase;">← Zpět na web</a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ════════════════════════════════════ DASHBOARD ════════════════════════════ -->

<!-- Admin nav -->
<nav id="admin-nav">
  <span class="font-bebas" style="font-size:1.4rem;animation:neonPulse 3s infinite;">GUMBALKÁN</span>
  <span class="font-oswald" style="font-size:.7rem;letter-spacing:.25em;color:#4b5563;text-transform:uppercase;">ADMIN</span>
  <span style="margin-left:auto;display:flex;align-items:center;gap:12px;">
    <span class="font-oswald" style="font-size:.75rem;color:#6b7280;letter-spacing:.1em;">
      <?= htmlspecialchars($_SESSION[ADMIN_SESSION_KEY]['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </span>
    <form method="POST" action="index.php" style="display:inline;">
      <input type="hidden" name="action" value="logout">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="action-btn" style="padding:6px 14px;">ODHLÁSIT</button>
    </form>
  </span>
</nav>

<div class="red-line"></div>

<div style="max-width:1400px;margin:0 auto;padding:28px 20px 60px;">

  <!-- Flash messages -->
  <?php if ($flash_success): ?>
    <div class="flash success"><?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($flash_error || $db_error): ?>
    <div class="flash error"><?= htmlspecialchars($flash_error ?? $db_error ?? '', ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <!-- Page title + export -->
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div>
      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:4px;">// SPRÁVA //</div>
      <h1 class="font-bebas" style="font-size:2.2rem;color:#fff;">KOMUNITA SUPPORTERŮ</h1>
    </div>
    <a href="index.php?action=export" class="action-btn" style="padding:10px 20px;text-decoration:none;align-self:flex-end;">
      ↓ CSV Export
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-box">
      <div class="stat-num"><?= $stats['total'] ?></div>
      <div class="stat-label">Celkem</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:#ff003c;"><?= $stats['founding'] ?></div>
      <div class="stat-label">Founding</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:#4ade80;"><?= $stats['community'] ?></div>
      <div class="stat-label">Komunita</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" style="color:#60a5fa;"><?= $stats['today'] ?></div>
      <div class="stat-label">Dnes</div>
    </div>
  </div>

  <!-- Search -->
  <div style="margin-bottom:20px;">
    <input class="search-input" type="text" id="search-input" placeholder="Hledat přezdívku…" oninput="filterTable(this.value)">
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table id="supporters-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Přezdívka</th>
          <th>Email</th>
          <th>WhatsApp</th>
          <th>Skupina</th>
          <th>Komunita</th>
          <th>Founding</th>
          <th>Datum</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($supporters)): ?>
          <tr>
            <td colspan="9" style="text-align:center;color:#4b5563;padding:32px;font-family:'Special Elite',cursive;">
              Žádní supporteři zatím.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($supporters as $i => $s): ?>
            <tr data-nickname="<?= htmlspecialchars(strtolower($s['nickname']), ENT_QUOTES, 'UTF-8') ?>">
              <td style="color:#4b5563;"><?= ($page - 1) * $per_page + $i + 1 ?></td>
              <td>
                <span class="font-bebas" style="font-size:1.1rem;"><?= htmlspecialchars($s['nickname'], ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td style="color:#9ca3af;font-size:.8rem;"><?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="color:#9ca3af;font-size:.8rem;"><?= htmlspecialchars($s['whatsapp_number'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="color:#9ca3af;font-size:.8rem;"><?= htmlspecialchars($s['whatsapp_group']  ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:center;">
                <?php if ($s['wants_community']): ?>
                  <span class="badge-community">✓</span>
                <?php else: ?>
                  <span style="color:#333;">✗</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <?php if ($s['is_founding']): ?>
                  <span class="badge-founding">🔥</span>
                <?php else: ?>
                  <span style="color:#333;">–</span>
                <?php endif; ?>
              </td>
              <td style="color:#6b7280;font-size:.75rem;white-space:nowrap;"><?= htmlspecialchars(substr($s['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button class="action-btn" onclick="openEdit(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['nickname']), ENT_QUOTES, 'UTF-8') ?>')">
                    Upravit
                  </button>
                  <form method="POST" action="index.php" onsubmit="return confirm('Smazat člena <?= htmlspecialchars(addslashes($s['nickname']), ENT_QUOTES, 'UTF-8') ?>?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="action-btn del">Smazat</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:20px;align-items:center;">
      <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="index.php?page=<?= $p ?>"
           class="page-link <?= $p === $page ? 'active' : '' ?>">
          <?= $p ?>
        </a>
      <?php endfor; ?>
      <span style="font-size:.7rem;letter-spacing:.15em;color:#4b5563;margin-left:8px;">
        Celkem <?= $total_rows ?> záznamů
      </span>
    </div>
  <?php endif; ?>

  <!-- ── Instagram videa ───────────────────────────────────────────────── -->
  <div id="reels" style="margin-top:56px;">
    <div style="margin-bottom:20px;">
      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:4px;">// VIDEA NA WEBU //</div>
      <h2 class="font-bebas" style="font-size:2rem;color:#fff;">INSTAGRAM REELS</h2>
      <p class="font-oswald" style="font-size:.8rem;color:#6b7280;letter-spacing:.05em;margin-top:6px;">
        Na Instagramu u reelu klikni na <b style="color:#9ca3af;">··· → Kopírovat odkaz</b> a vlož ho sem. Objeví se na hlavní stránce v sekci VIDEÁ.
      </p>
    </div>

    <!-- Add form -->
    <form method="POST" action="index.php#reels" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;align-items:center;">
      <input type="hidden" name="action" value="add_reel">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
      <input class="search-input" type="text" name="reel_url" required
             placeholder="https://www.instagram.com/reel/…"
             style="max-width:480px;flex:1;min-width:240px;">
      <button type="submit" class="submit-btn" style="width:auto;padding:11px 26px;">+ Přidat video</button>
    </form>

    <!-- Reels list -->
    <?php if (empty($reels)): ?>
      <div style="border:1px dashed rgba(255,0,60,0.3);padding:28px;text-align:center;color:#4b5563;font-family:'Special Elite',cursive;">
        Zatím žádná videa. Přidej první odkaz výše.
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Odkaz</th><th>Přidáno</th><th>Akce</th></tr>
          </thead>
          <tbody>
            <?php foreach ($reels as $i => $r): ?>
              <tr>
                <td style="color:#4b5563;"><?= $i + 1 ?></td>
                <td style="font-size:.8rem;">
                  <a href="<?= htmlspecialchars($r['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                     style="color:#60a5fa;text-decoration:none;word-break:break-all;">
                    <?= htmlspecialchars($r['url'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </td>
                <td style="color:#6b7280;font-size:.75rem;white-space:nowrap;"><?= htmlspecialchars(substr((string)($r['created_at'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <form method="POST" action="index.php#reels" onsubmit="return confirm('Smazat tohle video?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_reel">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="action-btn del">Smazat</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── Firemní partneři ──────────────────────────────────────────────── -->
  <div id="sponsors" style="margin-top:56px;">
    <div style="margin-bottom:20px;">
      <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:4px;">// PARTNEŘI //</div>
      <h2 class="font-bebas" style="font-size:2rem;color:#fff;">FIREMNÍ SPONZOŘI</h2>
      <p class="font-oswald" style="font-size:.8rem;color:#6b7280;letter-spacing:.05em;margin-top:6px;">
        Nahraj logo partnera, jeho jméno a odkaz na web. Objeví se na hlavní stránce v sekci PARTNEŘI.
      </p>
    </div>

    <!-- Add form -->
    <form method="POST" action="index.php#sponsors" enctype="multipart/form-data"
          style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;align-items:center;">
      <input type="hidden" name="action" value="add_sponsor">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
      <input class="search-input" type="text" name="sponsor_name" required placeholder="Název firmy" style="max-width:220px;">
      <input class="search-input" type="url" name="sponsor_url" placeholder="https://web-partnera.cz (nepovinné)" style="max-width:300px;flex:1;min-width:200px;">
      <input type="file" name="sponsor_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif" required
             style="color:#9ca3af;font-family:'Oswald',sans-serif;font-size:.8rem;max-width:260px;">
      <button type="submit" class="submit-btn" style="width:auto;padding:11px 26px;">+ Přidat partnera</button>
    </form>

    <!-- Sponsors list -->
    <?php if (empty($sponsors)): ?>
      <div style="border:1px dashed rgba(255,0,60,0.3);padding:28px;text-align:center;color:#4b5563;font-family:'Special Elite',cursive;">
        Zatím žádní partneři. Přidej prvního výše.
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Logo</th><th>Název</th><th>Web</th><th>Akce</th></tr>
          </thead>
          <tbody>
            <?php foreach ($sponsors as $sp): ?>
              <tr>
                <td><img src="../<?= htmlspecialchars($sp['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="height:38px;max-width:120px;object-fit:contain;background:#fff;padding:3px;"></td>
                <td><span class="font-bebas" style="font-size:1.1rem;"><?= htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td style="font-size:.8rem;">
                  <?php if (!empty($sp['url'])): ?>
                    <a href="<?= htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" style="color:#60a5fa;text-decoration:none;word-break:break-all;"><?= htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8') ?></a>
                  <?php else: ?>
                    <span style="color:#4b5563;">–</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="POST" action="index.php#sponsors" onsubmit="return confirm('Smazat partnera <?= htmlspecialchars(addslashes($sp['name']), ENT_QUOTES, 'UTF-8') ?>?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_sponsor">
                    <input type="hidden" name="id" value="<?= (int)$sp['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="action-btn del">Smazat</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div><!-- /container -->

<!-- ── Edit nickname modal ────────────────────────────────────────────── -->
<div class="modal-bg" id="edit-modal" onclick="if(event.target===this)closeEdit();">
  <div class="modal-box">
    <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:8px;">// ÚPRAVA //</div>
    <h3 class="font-bebas" style="font-size:1.8rem;margin-bottom:20px;">ZMĚNA PŘEZDÍVKY</h3>

    <form method="POST" action="index.php" id="edit-form">
      <input type="hidden" name="action" value="edit_nickname">
      <input type="hidden" name="id" id="edit-id" value="">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

      <div style="margin-bottom:20px;">
        <label class="form-label" for="new_nickname">Nová přezdívka</label>
        <input class="form-input" type="text" id="new_nickname" name="new_nickname"
               placeholder="nova_prezdivka" maxlength="30" pattern="[a-zA-Z0-9_\-]+" required
               style="border-bottom:2px solid rgba(255,0,60,0.4);width:100%;background:transparent;color:#fff;font-family:'Oswald',sans-serif;font-size:1rem;padding:10px 4px;outline:none;transition:border-color .2s;">
      </div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="submit-btn" style="width:auto;flex:1;">ULOŽIT</button>
        <button type="button" onclick="closeEdit()" style="flex:1;background:transparent;border:1px solid rgba(255,255,255,0.15);color:#6b7280;font-family:'Oswald',sans-serif;font-size:.85rem;letter-spacing:.2em;text-transform:uppercase;padding:13px;cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='#ff003c';this.style.color='#ff003c'" onmouseout="this.style.borderColor='rgba(255,255,255,0.15)';this.style.color='#6b7280'">ZRUŠIT</button>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>

<script>
function filterTable(query) {
  const q = query.toLowerCase().trim();
  document.querySelectorAll('#supporters-table tbody tr[data-nickname]').forEach(row => {
    const nick = row.getAttribute('data-nickname') || '';
    row.style.display = nick.includes(q) ? '' : 'none';
  });
}

function openEdit(id, currentNick) {
  document.getElementById('edit-id').value = id;
  document.getElementById('new_nickname').value = currentNick;
  document.getElementById('edit-modal').style.display = 'flex';
  setTimeout(() => document.getElementById('new_nickname').focus(), 50);
}

function closeEdit() {
  document.getElementById('edit-modal').style.display = 'none';
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeEdit();
});
</script>
</body>
</html>
