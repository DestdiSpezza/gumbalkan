<?php
declare(strict_types=1);

// ─── Database ────────────────────────────────────────────────────────────────
// SQLite – cesta k souboru (automaticky se vytvoří)
define('DB_SQLITE_PATH', __DIR__ . '/../database/gumbalkan.sqlite');

// Lokální credentials (gitignored) – načti je první, ať mají přednost
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// MySQL – výchozí prázdné hodnoty (pokud nejsou v config.local.php)
if (!defined('DB_HOST')) define('DB_HOST', '');
if (!defined('DB_NAME')) define('DB_NAME', '');
if (!defined('DB_USER')) define('DB_USER', '');
if (!defined('DB_PASS')) define('DB_PASS', '');

// ─── Application constants ────────────────────────────────────────────────────

/** Number of registrations that receive "Founding Supporter" status */
define('FOUNDING_LIMIT', 50);

/** Max allowed registrations per IP within the rate limit window */
define('RATE_LIMIT_MAX', 3);

/** Rate limit window in seconds (default: 1 hour) */
define('RATE_LIMIT_WINDOW', 3600);

/** Session key used to store admin authentication state */
define('ADMIN_SESSION_KEY', 'gumbalkan_admin');

/**
 * Odkaz na pozvánku do WhatsApp skupiny podporovatelů.
 * Získáš ho ve WhatsApp: skupina → Pozvat odkazem → Kopírovat odkaz.
 * Nech prázdné, dokud nemáš skupinu – tlačítko se pak nezobrazí.
 * Příklad: 'https://chat.whatsapp.com/XXXXXXXXXXXXXXX'
 */
if (!defined('WHATSAPP_GROUP_URL')) define('WHATSAPP_GROUP_URL', '');
