# CLAUDE.md

Guidance for Claude Code (and other devs) working in this repository.

## Co to je

**GUMBALKÁN 2026** je promo / komunitní web k road tripu přes Balkán. Jde o
klasickou **PHP** stránku (žádný framework, žádný build step) stylizovanou do
glitch / VHS / graffiti estetiky. Frontend používá **Tailwind přes CDN**,
ikony Lucide a Google Fonts (Bebas Neue, Special Elite, Oswald). Texty v UI a
komentáře v kódu jsou **česky**.

## Spuštění lokálně

Není potřeba build ani composer – stačí PHP s rozšířením `pdo_sqlite`:

```bash
php -S localhost:8000
```

Pak otevři `http://localhost:8000/`. Databáze se vytvoří automaticky (viz níže).

## Databáze

`includes/db.php` má **PDO singleton** `get_db()` s automatickým fallbackem:

- Pokud v `includes/config.local.php` **není** definované `DB_HOST` (nebo chybí
  `pdo_mysql`), použije se **SQLite** v `database/gumbalkan.sqlite`. Schéma se
  vytvoří samo přes `_init_sqlite()` – pro lokální vývoj nemusíš dělat nic.
- V produkci se nastaví `DB_HOST/DB_NAME/DB_USER/DB_PASS` v
  `includes/config.local.php` (gitignored) a jede se na **MySQL**. Schéma pro
  MySQL je v `database/supporters.sql`.

> **Pojistka proti tichému fallbacku:** na reálné doméně (ne localhost / ne CLI)
> se SQLite fallback **nepoužije** – když chybí MySQL konfigurace nebo
> `pdo_mysql`, `get_db()` vyhodí `RuntimeException` (a zaloguje), aby appka
> nezačala potichu jet na prázdné SQLite a netvářila se, že „zmizela data".
> Lokální vývoj (`php -S localhost`) i CLI jedou na SQLite jako dřív. Pokud bys
> SQLite na produkci opravdu chtěl, nastav `define('ALLOW_SQLITE', true);` v
> `config.local.php`.

Tabulky (prefix `GUM_`): `GUM_supporters`, `GUM_admin_users`,
`GUM_rate_limits`, `GUM_reels`, `GUM_sponsors`, `GUM_photos`, `GUM_apps`.

> Drž obě schémata (SQLite v `_init_sqlite()` a MySQL v `database/supporters.sql`)
> synchronizovaná, když měníš strukturu tabulek.

## Konfigurace

- `includes/config.php` – veřejné konstanty a defaulty. Načítá
  `config.local.php` **jako první**, aby měly lokální hodnoty přednost.
- `includes/config.local.php` – **gitignored**, sem patří DB credentials a
  citlivé override (WhatsApp odkaz, notifikační e-mail, `MAIL_FROM`).

Klíčové konstanty: `FOUNDING_LIMIT` (prvních N podporovatelů = „Founding
Supporter“), `RATE_LIMIT_MAX` / `RATE_LIMIT_WINDOW` (rate limit registrací na
IP), `ADMIN_SESSION_KEY`.

## Struktura

| Soubor / složka | Role |
|---|---|
| `index.php` | Hlavní landing page. Načítá Instagram reels a sponzory z DB. |
| `support.php` | Stránka „podpoř nás“ – QR kód (qrcodejs), platební info. |
| `supporters.php` | Komunitní zeď + registrace podporovatele. AJAX: `GET ?action=load&page=N` (JSON, stránkování po 20), `POST` = registrace. |
| `alps.php` | Foto galerie po lokalitách s uploadem (`POST` + `$_FILES['photo']` → `photos/<location>/`). |
| `admin/index.php` | Admin panel – login a správa reels / sponzorů / podporovatelů / foto galerie (sekce `#gallery`, hromadný upload → `photos/gallery/`) / aplikací s QR (sekce `#apps`, název + odkaz → QR na `index.php`). Vše se zobrazí na `index.php`. |
| `admin/create_admin.php` | Jednorázové založení prvního admina (poté přesměruje na login). |
| `admin/logout.php` | Odhlášení. |
| `includes/db.php` | DB vrstva, CSRF, sanitizace, rate limit, dotazy (reels/sponsors/supporters), notifikace. |
| `includes/config.php` | Konstanty a načtení lokální konfigurace. |
| `includes/meta.php` | `render_head_meta()` – SEO / Open Graph / Twitter meta tagy, absolutní URL z requestu. |
| `database/supporters.sql` | MySQL schéma. |

Gitignored runtime složky: `photos/`, `logos/`, `database/*.sqlite*`.

## Konvence a bezpečnost

- **Vždy používej helpery z `includes/db.php`**: `get_db()`, prepared statements
  (PDO) pro veškeré dotazy, `sanitize()` na výstup, `generate_csrf()` /
  `verify_csrf()` u všech formulářů (POST), `check_rate_limit()` /
  `log_rate_limit()` u registrací, `get_client_ip()` pro IP.
- Soubory mají `declare(strict_types=1);` – drž to.
- Žádný build pipeline: CSS/JS se píše inline v jednotlivých `.php` stránkách,
  Tailwind jen přes CDN. Při úpravách stylu zůstaň u stávajícího inline přístupu.
- Komentáře a uživatelské texty piš **česky**, ať to ladí se zbytkem kódu.

## Git

Hlavní pracovní větev je **`main`**. Vyvíjej a pushuj sem (pokud uživatel
neřekne jinak).
