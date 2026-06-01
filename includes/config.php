<?php
declare(strict_types=1);

// ─── Database ────────────────────────────────────────────────────────────────
// SQLite – cesta k souboru (automaticky se vytvoří)
define('DB_SQLITE_PATH', __DIR__ . '/../database/gumbalkan.sqlite');

// MySQL (vyplň zde nebo v config.local.php)
define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// Lokální přepsání credentials (gitignored)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// ─── Application constants ────────────────────────────────────────────────────

/** Number of registrations that receive "Founding Supporter" status */
define('FOUNDING_LIMIT', 50);

/** Max allowed registrations per IP within the rate limit window */
define('RATE_LIMIT_MAX', 3);

/** Rate limit window in seconds (default: 1 hour) */
define('RATE_LIMIT_WINDOW', 3600);

/** Session key used to store admin authentication state */
define('ADMIN_SESSION_KEY', 'gumbalkan_admin');
