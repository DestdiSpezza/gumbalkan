<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ─── Detekce lokálního prostředí ──────────────────────────────────────────────
// Lokál (localhost / CLI) smí jet na SQLite. Reálná doména = produkce.
function _is_local_environment(): bool
{
    // CLI běh (lokální vývoj, migrace, testy) bereme jako lokální.
    if (PHP_SAPI === 'cli') return true;

    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host); // odřízni port

    if ($host === '') return true; // neznámý host → nebraň vývoji
    if (in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)) return true;
    foreach (['.local', '.test', '.localhost'] as $suffix) {
        if (str_ends_with($host, $suffix)) return true;
    }
    return false;
}

// ─── PDO singleton (SQLite auto-fallback) ─────────────────────────────────────
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $useSqlite = (DB_HOST === '' || !extension_loaded('pdo_mysql'));

    // Pojistka: na produkci (reálná doména) nechceme tichý fallback na prázdnou
    // SQLite – vypadalo by to jako „smazaná data". Radši nahlásíme chybu.
    if ($useSqlite && !ALLOW_SQLITE && !_is_local_environment()) {
        $why = (DB_HOST === '')
            ? 'chybí MySQL konfigurace (includes/config.local.php s DB_HOST/DB_NAME/DB_USER/DB_PASS)'
            : 'PHP rozšíření pdo_mysql není načtené';
        error_log('Gumbalkán DB: ' . $why . ' – fallback na SQLite na produkci zablokován.');
        throw new RuntimeException(
            'Databáze není správně nakonfigurovaná: ' . $why . '. '
            . 'Abych omylem nezačal zapisovat do prázdné lokální SQLite, '
            . 'připojení jsem zastavil. Zkontroluj includes/config.local.php na serveru.'
        );
    }

    if ($useSqlite) {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode=WAL');
        _init_sqlite($pdo);
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function _init_sqlite(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS GUM_supporters (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            nickname        TEXT NOT NULL UNIQUE,
            email           TEXT NOT NULL UNIQUE,
            whatsapp_number TEXT,
            whatsapp_group  TEXT,
            wants_community INTEGER NOT NULL DEFAULT 0,
            ip_address      TEXT NOT NULL DEFAULT '',
            is_founding     INTEGER NOT NULL DEFAULT 0,
            created_at      TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_admin_users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at    TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_rate_limits (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            action     TEXT NOT NULL DEFAULT 'register',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_reels (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            url        TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_sponsors (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT NOT NULL,
            logo_path  TEXT NOT NULL DEFAULT '',
            url        TEXT NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_photos (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            file_path  TEXT NOT NULL,
            caption    TEXT NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE TABLE IF NOT EXISTS GUM_apps (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT NOT NULL,
            url        TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE INDEX IF NOT EXISTS idx_rl_ip_action ON GUM_rate_limits(ip_address, action);
        CREATE INDEX IF NOT EXISTS idx_rl_created   ON GUM_rate_limits(created_at);
        CREATE INDEX IF NOT EXISTS idx_sup_created  ON GUM_supporters(created_at);
    ");
}

// ─── CSRF helpers ─────────────────────────────────────────────────────────────
function generate_csrf(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $stored = $_SESSION['csrf_token'] ?? '';
    return hash_equals($stored, $token);
}

// ─── Output sanitization ──────────────────────────────────────────────────────
function sanitize(string $str): string
{
    return htmlspecialchars(trim($str), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── Rate limiting ────────────────────────────────────────────────────────────
function check_rate_limit(PDO $db, string $ip): bool
{
    $cutoff = date('Y-m-d H:i:s', time() - RATE_LIMIT_WINDOW);

    $stmt = $db->prepare('DELETE FROM GUM_rate_limits WHERE created_at < :cutoff');
    $stmt->execute([':cutoff' => $cutoff]);

    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM GUM_rate_limits
          WHERE ip_address = :ip AND action = :action AND created_at >= :cutoff'
    );
    $stmt->execute([':ip' => $ip, ':action' => 'register', ':cutoff' => $cutoff]);

    return (int) $stmt->fetchColumn() < RATE_LIMIT_MAX;
}

function log_rate_limit(PDO $db, string $ip): void
{
    $stmt = $db->prepare(
        'INSERT INTO GUM_rate_limits (ip_address, action) VALUES (:ip, :action)'
    );
    $stmt->execute([':ip' => $ip, ':action' => 'register']);
}

// ─── Client IP ────────────────────────────────────────────────────────────────
function get_client_ip(): string
{
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];
    foreach ($candidates as $key) {
        if (!empty($_SERVER[$key])) {
            // X-Forwarded-For can contain a comma-separated list; take the first
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

// ─── Supporters queries ───────────────────────────────────────────────────────
function get_supporters(PDO $db, int $page, int $per_page = 20): array
{
    $offset = ($page - 1) * $per_page;
    $stmt = $db->prepare(
        'SELECT id, nickname, is_founding, created_at
           FROM GUM_supporters
          ORDER BY created_at DESC
          LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_total_count(PDO $db): int
{
    return (int) $db->query('SELECT COUNT(*) FROM GUM_supporters')->fetchColumn();
}

function get_recent_ticker(PDO $db, int $limit = 10): array
{
    $stmt = $db->prepare(
        'SELECT nickname FROM GUM_supporters ORDER BY created_at DESC LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ─── Instagram reels ──────────────────────────────────────────────────────────
function get_reels(PDO $db): array
{
    return $db->query(
        'SELECT id, url FROM GUM_reels ORDER BY sort_order ASC, id ASC'
    )->fetchAll();
}

/** Normalizuj Instagram URL na čistý permalink (bez query stringu). */
function normalize_reel_url(string $url): string
{
    $url = trim($url);
    $url = preg_replace('/[?#].*$/', '', $url);
    return rtrim($url, '/') . '/';
}

/** Vrátí true, pokud jde o validní Instagram reel/post URL. */
function is_valid_reel_url(string $url): bool
{
    return (bool) preg_match(
        '#^https?://(www\.)?instagram\.com/(reel|reels|p|tv)/[A-Za-z0-9_\-]+/?$#',
        $url
    );
}

function add_reel(PDO $db, string $url): void
{
    $stmt = $db->prepare(
        'INSERT INTO GUM_reels (url, sort_order) VALUES (:url, :sort)'
    );
    $next = (int) $db->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM GUM_reels')->fetchColumn();
    $stmt->execute([':url' => $url, ':sort' => $next]);
}

function delete_reel(PDO $db, int $id): void
{
    $stmt = $db->prepare('DELETE FROM GUM_reels WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

// ─── Firemní sponzoři / partneři ──────────────────────────────────────────────
function get_sponsors(PDO $db): array
{
    return $db->query(
        'SELECT id, name, logo_path, url FROM GUM_sponsors ORDER BY sort_order ASC, id ASC'
    )->fetchAll();
}

function add_sponsor(PDO $db, string $name, string $logo_path, string $url): void
{
    $next = (int) $db->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM GUM_sponsors')->fetchColumn();
    $stmt = $db->prepare(
        'INSERT INTO GUM_sponsors (name, logo_path, url, sort_order)
         VALUES (:name, :logo, :url, :sort)'
    );
    $stmt->execute([':name' => $name, ':logo' => $logo_path, ':url' => $url, ':sort' => $next]);
}

function get_sponsor(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT id, name, logo_path, url FROM GUM_sponsors WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function delete_sponsor(PDO $db, int $id): void
{
    $stmt = $db->prepare('DELETE FROM GUM_sponsors WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

// ─── Foto galerie (spravovaná z adminu) ───────────────────────────────────────
function get_photos(PDO $db): array
{
    return $db->query(
        'SELECT id, file_path, caption FROM GUM_photos ORDER BY sort_order ASC, id ASC'
    )->fetchAll();
}

function add_photo(PDO $db, string $file_path, string $caption = ''): void
{
    $next = (int) $db->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM GUM_photos')->fetchColumn();
    $stmt = $db->prepare(
        'INSERT INTO GUM_photos (file_path, caption, sort_order)
         VALUES (:path, :caption, :sort)'
    );
    $stmt->execute([':path' => $file_path, ':caption' => $caption, ':sort' => $next]);
}

function get_photo(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT id, file_path, caption FROM GUM_photos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function delete_photo(PDO $db, int $id): void
{
    $stmt = $db->prepare('DELETE FROM GUM_photos WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

// ─── Aplikace s QR odkazem (spravované z adminu) ──────────────────────────────
function get_apps(PDO $db): array
{
    return $db->query(
        'SELECT id, name, url FROM GUM_apps ORDER BY sort_order ASC, id ASC'
    )->fetchAll();
}

function add_app(PDO $db, string $name, string $url): void
{
    $next = (int) $db->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM GUM_apps')->fetchColumn();
    $stmt = $db->prepare(
        'INSERT INTO GUM_apps (name, url, sort_order) VALUES (:name, :url, :sort)'
    );
    $stmt->execute([':name' => $name, ':url' => $url, ':sort' => $next]);
}

function delete_app(PDO $db, int $id): void
{
    $stmt = $db->prepare('DELETE FROM GUM_apps WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

// ─── Admin notifikace e-mailem ────────────────────────────────────────────────
function notify_new_supporter(array $supporter): void
{
    if (!defined('ADMIN_NOTIFY_EMAIL') || ADMIN_NOTIFY_EMAIL === '') {
        return;
    }
    $to      = ADMIN_NOTIFY_EMAIL;
    $nick    = $supporter['nickname'] ?? '?';
    $email   = $supporter['email'] ?? '?';
    $wa      = $supporter['whatsapp_number'] ?? '';
    $found   = !empty($supporter['is_founding']) ? 'ANO 🔥' : 'ne';
    $when    = date('j.n.Y H:i');

    $subject = '=?UTF-8?B?' . base64_encode('Gumbalkán – nový podporovatel: ' . $nick) . '?=';
    $body    = "Nový člen komunity Gumbalkán:\n\n"
             . "Přezdívka:  $nick\n"
             . "E-mail:     $email\n"
             . "WhatsApp:   " . ($wa !== '' ? $wa : '–') . "\n"
             . "Founding:   $found\n"
             . "Čas:        $when\n";

    $from    = defined('MAIL_FROM') && MAIL_FROM !== '' ? MAIL_FROM : 'noreply@localhost';
    $headers = "From: Gumbalkán <$from>\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "X-Mailer: PHP/" . phpversion();

    @mail($to, $subject, $body, $headers);
}

// ─── Human-readable time (Czech) ─────────────────────────────────────────────
function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) {
        return 'právě teď';
    }
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins . ' min';
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hod';
    }
    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return $days . ' dní';
    }
    return date('j.n.Y', strtotime($datetime));
}
