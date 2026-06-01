<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ─── PDO singleton ────────────────────────────────────────────────────────────
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
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
    // Purge entries older than the window first
    $stmt = $db->prepare(
        'DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL :window SECOND)'
    );
    $stmt->execute([':window' => RATE_LIMIT_WINDOW]);

    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM rate_limits
          WHERE ip_address = :ip
            AND action = :action
            AND created_at >= DATE_SUB(NOW(), INTERVAL :window SECOND)'
    );
    $stmt->execute([
        ':ip'     => $ip,
        ':action' => 'register',
        ':window' => RATE_LIMIT_WINDOW,
    ]);
    $count = (int) $stmt->fetchColumn();

    return $count < RATE_LIMIT_MAX;
}

function log_rate_limit(PDO $db, string $ip): void
{
    $stmt = $db->prepare(
        'INSERT INTO rate_limits (ip_address, action) VALUES (:ip, :action)'
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
           FROM supporters
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
    return (int) $db->query('SELECT COUNT(*) FROM supporters')->fetchColumn();
}

function get_recent_ticker(PDO $db, int $limit = 10): array
{
    $stmt = $db->prepare(
        'SELECT nickname FROM supporters ORDER BY created_at DESC LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
