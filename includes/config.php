<?php
declare(strict_types=1);

// ─── Database connection ──────────────────────────────────────────────────────
define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// ─── Application constants ────────────────────────────────────────────────────

/** Number of registrations that receive "Founding Supporter" status */
define('FOUNDING_LIMIT', 50);

/** Max allowed registrations per IP within the rate limit window */
define('RATE_LIMIT_MAX', 3);

/** Rate limit window in seconds (default: 1 hour) */
define('RATE_LIMIT_WINDOW', 3600);

/** Session key used to store admin authentication state */
define('ADMIN_SESSION_KEY', 'gumbalkan_admin');
