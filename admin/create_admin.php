<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$error   = null;
$success = null;

// Check if any admin already exists
try {
    $db    = get_db();
    $count = (int)$db->query('SELECT COUNT(*) FROM GUM_admin_users')->fetchColumn();
    if ($count > 0) {
        // Admin already exists — redirect to login
        header('Location: index.php?setup=already_done');
        exit;
    }
} catch (\Exception $e) {
    $error = 'Chyba DB: ' . $e->getMessage();
}

$csrf_token = generate_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // CSRF check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($csrf)) {
        $error = 'Neplatný bezpečnostní token. Obnovte stránku.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($username === '' || strlen($username) < 3) {
            $error = 'Uživatelské jméno musí mít alespoň 3 znaky.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $username)) {
            $error = 'Uživatelské jméno smí obsahovat jen písmena, čísla, _ a -.';
        } elseif (strlen($password) < 8) {
            $error = 'Heslo musí mít alespoň 8 znaků.';
        } elseif ($password !== $password2) {
            $error = 'Hesla se neshodují.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $db->prepare('INSERT INTO GUM_admin_users (username, password_hash) VALUES (:u, :h)');
                $stmt->execute([':u' => $username, ':h' => $hash]);
                // Redirect to login with success flag
                header('Location: index.php?setup=ok');
                exit;
            } catch (\Exception $e) {
                $error = 'Chyba při vytváření účtu – uživatelské jméno může být obsazené.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nastavení admina – GUMBALKÁN</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Special+Elite&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { min-height: 100%; background: #000; color: #fff; font-family: 'Oswald', sans-serif; }

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite  { font-family: 'Special Elite', cursive; }

.noise {
  position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
}

@keyframes neonPulse {
  0%,100% { text-shadow: 0 0 10px #ff003c, 0 0 20px #ff003c; }
  50%      { text-shadow: 0 0 5px #ff003c; }
}

.wrap {
  min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
  background: radial-gradient(ellipse at center, #0d0005 0%, #000 70%);
}
.box {
  width: 100%; max-width: 420px; border: 1px solid rgba(255,0,60,0.3); padding: 40px 32px;
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
.flash { padding: 12px 16px; font-size: .85rem; letter-spacing: .08em; margin-bottom: 20px; }
.flash.error   { background: rgba(255,0,60,0.1);  border: 1px solid rgba(255,0,60,0.4);  color: #ff003c; }
.flash.success { background: rgba(0,200,80,0.1);  border: 1px solid rgba(0,200,80,0.35); color: #4ade80; }
  </style>
</head>
<body>
<div class="noise"></div>

<div class="wrap">
  <div class="box">
    <div style="text-align:center;margin-bottom:32px;">
      <div class="font-bebas" style="font-size:2.5rem;animation:neonPulse 3s infinite;">GUMBALKÁN</div>
      <div style="font-size:.65rem;letter-spacing:.45em;color:#ff003c;text-transform:uppercase;margin-top:6px;">PRVNÍ NASTAVENÍ ADMINA</div>
      <p class="font-elite" style="color:#4b5563;font-size:.85rem;margin-top:12px;line-height:1.5;">
        Vytvoř první admin účet. Tato stránka bude po vytvoření nedostupná.
      </p>
    </div>

    <?php if ($error): ?>
      <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="flash success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="create_admin.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

      <div style="margin-bottom:20px;">
        <label class="form-label" for="username">Uživatelské jméno</label>
        <input class="form-input" type="text" id="username" name="username"
               placeholder="admin" maxlength="50" autocomplete="username" required
               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div style="margin-bottom:20px;">
        <label class="form-label" for="password">Heslo <span style="color:#4b5563;">(min. 8 znaků)</span></label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="••••••••" minlength="8" autocomplete="new-password" required>
      </div>

      <div style="margin-bottom:28px;">
        <label class="form-label" for="password2">Heslo znovu</label>
        <input class="form-input" type="password" id="password2" name="password2"
               placeholder="••••••••" minlength="8" autocomplete="new-password" required>
      </div>

      <button type="submit" class="submit-btn">VYTVOŘIT ADMIN ÚČET</button>
    </form>

    <div style="text-align:center;margin-top:20px;">
      <a href="../supporters.php" style="font-size:.7rem;letter-spacing:.2em;color:#4b5563;text-decoration:none;text-transform:uppercase;">← Zpět na web</a>
    </div>
  </div>
</div>
</body>
</html>
