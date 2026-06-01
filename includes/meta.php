<?php
declare(strict_types=1);

/**
 * Vykreslí <meta> tagy do <head>: SEO popis, favicon, Open Graph a Twitter card.
 * Absolutní URL se počítají z aktuálního requestu, takže to funguje
 * na localhostu i na eda.borec.cz/gumbalkan/ bez ručního nastavování.
 */
function render_head_meta(string $title, string $description): void
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'eda.borec.cz';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base   = $scheme . '://' . $host . $dir . '/';
    $url    = $base . basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $img    = $base . 'og-image.jpg';

    $e = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    ?>
  <meta name="description" content="<?= $e($description) ?>">
  <link rel="canonical" href="<?= $e($url) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= $e($base) ?>favicon-32.png">
  <link rel="apple-touch-icon" href="<?= $e($base) ?>apple-touch-icon.png">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Gumbalkán 2026">
  <meta property="og:title" content="<?= $e($title) ?>">
  <meta property="og:description" content="<?= $e($description) ?>">
  <meta property="og:url" content="<?= $e($url) ?>">
  <meta property="og:image" content="<?= $e($img) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="cs_CZ">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $e($title) ?>">
  <meta name="twitter:description" content="<?= $e($description) ?>">
  <meta name="twitter:image" content="<?= $e($img) ?>">
<?php
}
