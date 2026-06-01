<?php
/* ─── Instagram reels / videa ──────────────────────────────────────────────
   Videa se spravují z admin panelu (admin/index.php → INSTAGRAM REELS).
   Tady se jen načtou z databáze a vykreslí v sekci VIDEÁ.                   */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/meta.php';
$instagram_reels = [];
$sponsors        = [];
try {
    $db = get_db();
    foreach (get_reels($db) as $r) {
        $instagram_reels[] = $r['url'];
    }
    $sponsors = get_sponsors($db);
} catch (\Throwable $e) {
    $instagram_reels = []; // DB nedostupná → ukáže se prázdný stav
    $sponsors        = [];
}
?>
<!doctype html>
<html lang="en" class="h-full">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GUMBALKÁN 2026 – Summer Edition</title>
<?php render_head_meta(
  'GUMBALKÁN 2026 – Summer Edition',
  'Čtyři blázni, levné auto a žádný plán. Road trip přes Balkán až do Bosny. Sleduj přípravy, podpoř nás a přidej se k partě.'
); ?>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>
  <script src="/_sdk/element_sdk.js"></script>
  <script src="/_sdk/image_sdk.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&amp;family=Special+Elite&amp;family=Oswald:wght@400;700&amp;display=swap" rel="stylesheet">
  <style>
html, body { height: 100%; margin: 0; }
body { background: #000; color: #fff; overflow-x: hidden; }

@keyframes glitchX {
  0%, 100% { transform: translate(0); }
  20% { transform: translate(-3px, 2px); }
  40% { transform: translate(3px, -2px); }
  60% { transform: translate(-2px, -1px); }
  80% { transform: translate(2px, 1px); }
}
@keyframes flicker {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.85; }
  75% { opacity: 0.95; }
}
@keyframes scanline {
  0% { top: -10%; }
  100% { top: 110%; }
}
@keyframes sprayin {
  from { clip-path: inset(0 100% 0 0); opacity: 0; }
  to { clip-path: inset(0 0 0 0); opacity: 1; }
}
@keyframes slideUp {
  from { transform: translateY(80px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-1deg); }
  75% { transform: rotate(1deg); }
}
@keyframes neonPulse {
  0%, 100% { text-shadow: 0 0 10px #ff003c, 0 0 20px #ff003c, 0 0 40px #ff003c; }
  50% { text-shadow: 0 0 5px #ff003c, 0 0 10px #ff003c, 0 0 20px #ff003c; }
}
@keyframes countGlitch {
  0%, 90%, 100% { transform: skewX(0deg); }
  93% { transform: skewX(-5deg); }
  96% { transform: skewX(3deg); }
}
@keyframes drift {
  0%, 100% { transform: translateX(0) rotate(0deg); }
  50% { transform: translateX(8px) rotate(0.5deg); }
}
@keyframes roadDrift {
  0% { transform: translateY(0); }
  100% { transform: translateY(60px); }
}
@keyframes float {
  0%, 100% { transform: translateY(0px) rotateZ(-45deg); }
  50% { transform: translateY(20px) rotateZ(-45deg); }
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes cardFlip {
  0% { transform: rotateY(0deg) rotateX(0deg); }
  100% { transform: rotateY(5deg) rotateX(-3deg); }
}
@keyframes sectionSlide {
  from { opacity: 0; transform: translateY(60px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes heartbeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite { font-family: 'Special Elite', cursive; }
.font-oswald { font-family: 'Oswald', sans-serif; }

.glitch-hover:hover { animation: glitchX 0.15s infinite; }
.shake-hover:hover { animation: shake 0.1s infinite; }

/* Navigace – animovaný odkaz */
.nav-link {
  position: relative;
  font-family: 'Oswald', sans-serif;
  font-size: 0.8rem;
  color: #9ca3af;
  text-decoration: none;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  padding: 6px 2px;
  transition: color 0.25s ease, letter-spacing 0.25s ease, text-shadow 0.25s ease;
}
.nav-link::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 0;
  width: 0;
  height: 2px;
  background: #ff003c;
  box-shadow: 0 0 8px #ff003c;
  transform: translateX(-50%);
  transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.nav-link:hover {
  color: #ff003c;
  letter-spacing: 0.32em;
  text-shadow: 0 0 12px rgba(255,0,60,0.6);
}
.nav-link:hover::after { width: 100%; }
.nav-brand {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.4rem;
  color: #fff;
  text-decoration: none;
  letter-spacing: 0.08em;
  transition: color 0.25s ease, text-shadow 0.25s ease, transform 0.25s ease;
}
.nav-brand:hover {
  color: #ff003c;
  text-shadow: 0 0 16px rgba(255,0,60,0.7);
  transform: scale(1.05);
}

/* Sdílecí tlačítka */
.share-btn {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Oswald', sans-serif; font-size: .8rem; font-weight: 700;
  letter-spacing: .12em; text-transform: uppercase;
  padding: 11px 20px; cursor: pointer; text-decoration: none;
  color: #fff; background: transparent;
  border: 1px solid rgba(255,0,60,0.35);
  transition: all .2s;
  clip-path: polygon(4% 0%,100% 0%,96% 100%,0% 100%);
}
.share-btn:hover { background: #ff003c; border-color: #ff003c; color: #fff; transform: translateY(-2px); }

.reveal { opacity: 0; transform: translateY(60px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
.reveal.visible { opacity: 1; transform: translateY(0); }

.noise-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  pointer-events: none; z-index: 9999; opacity: 0.04;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
}

.scanline::after {
  content: ''; position: absolute; left: 0; width: 100%; height: 2px;
  background: rgba(255,0,60,0.15); animation: scanline 3s linear infinite; pointer-events: none;
}

.polaroid {
  background: #111; border: 3px solid #222; padding: 12px 12px 40px 12px;
  transform: rotate(-2deg); box-shadow: 4px 4px 20px rgba(0,0,0,0.8);
  transition: transform 0.3s, box-shadow 0.3s;
}
.polaroid:hover { transform: rotate(0deg) scale(1.03); box-shadow: 0 0 30px rgba(255,0,60,0.3); }

.spray-text {
  position: relative;
  -webkit-text-stroke: 1px rgba(255,0,60,0.3);
}

.red-line { height: 3px; background: #ff003c; box-shadow: 0 0 15px #ff003c; }

.countdown-num {
  animation: countGlitch 4s infinite, flicker 2s infinite;
  text-shadow: 0 0 10px #ff003c, 0 0 30px rgba(255,0,60,0.4);
}

.member-card {
  border-left: 4px solid #ff003c;
  background: linear-gradient(90deg, rgba(255,0,60,0.05) 0%, transparent 100%);
  transition: all 0.3s;
}
.member-card:hover {
  border-left-color: #fff;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 0%, transparent 100%);
  transform: translateX(8px);
}

.gallery-item {
  filter: grayscale(0.6) contrast(1.3);
  transition: all 0.4s;
}
.gallery-item:hover {
  filter: grayscale(0) contrast(1.1);
  transform: scale(1.05) rotate(-1deg);
  box-shadow: 0 0 30px rgba(255,0,60,0.4);
}
</style>
  <style>body { box-sizing: border-box; }</style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
 </head>
 <body class="h-full">
  <div class="noise-overlay"></div>
  <!-- NAV -->
  <nav style="position:fixed;top:0;left:0;width:100%;z-index:10000;background:rgba(0,0,0,0.85);backdrop-filter:blur(8px);border-bottom:1px solid rgba(255,0,60,0.2);">
    <div style="max-width:1200px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:center;flex-wrap:wrap;height:auto;min-height:52px;gap:16px 28px;">
      <a href="index.php" class="nav-brand">GUMBALKÁN</a>
      <a href="alps.php" class="nav-link">Training camp in the Alps</a>
      <a href="supporters.php" class="nav-link">Supporters</a>
      <a href="support.php" class="nav-link">❤ Podpořte nás</a>
    </div>
  </nav>
  <div id="app-wrapper" class="w-full" style="height:100%; overflow-y:auto; overflow-x:hidden; padding-top:52px;"><!-- HERO -->
   <section id="hero-section" class="relative w-full scanline" style="height:100%; perspective: 1000px;">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #0a0a0a 0%, #1a0008 40%, #0a0a0a 100%);"></div><!-- Animated road stripes -->
    <div class="absolute inset-0 opacity-20" style="animation: roadDrift 4s linear infinite;">
     <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg"><defs>
       <pattern id="road" x="0" y="0" width="60" height="60" patternunits="userSpaceOnUse">
        <line x1="30" y1="0" x2="30" y2="20" stroke="#ff003c" stroke-width="2" stroke-dasharray="4,8" />
        <rect x="0" y="0" width="60" height="60" fill="none" stroke="#222" stroke-width="0.5" />
       </pattern>
      </defs> <rect width="100%" height="100%" fill="url(#road)" />
     </svg>
    </div><!-- Floating geometric shapes (parallax) -->
    <div class="absolute top-20 left-10 w-32 h-32 border-2 border-red-600 opacity-10" style="animation: float 6s ease-in-out infinite; transform: rotateZ(-45deg);"></div>
    <div class="absolute bottom-32 right-20 w-48 h-48 border-2 border-red-500 opacity-5" style="animation: float 8s ease-in-out infinite 1s; transform: rotateZ(-45deg);"></div><!-- Diagonal slash -->
    <div class="absolute top-0 right-0 w-1/3 h-full opacity-10" style="background: linear-gradient(135deg, transparent 40%, #ff003c 40%, #ff003c 42%, transparent 42%);"></div>
    <div class="absolute bottom-0 left-0 w-1/2 h-1/2 opacity-5" style="background: linear-gradient(-45deg, transparent 60%, #ff003c 60%, #ff003c 61%, transparent 61%);"></div>
    <div class="relative z-10 flex flex-col items-center justify-center h-full px-4 text-center" style="z-index: 10;">
     <div class="mb-6 reveal" style="animation: slideUp 0.8s ease-out;"><img src="https://media.canva.com/v2/image-resize/format:PNG/height:1024/quality:100/uri:ifs%3A%2F%2FM%2Feee6b7ce-125b-4476-b5e/watermark:F/width:1024?csig=AAAAAAAAAAAAAAAAAAAAALWYdDQy3IVyqcWEwd5UIJYd8rXR1t5HMsA1pkP2QkK3&amp;exp=1775724378&amp;osig=AAAAAAAAAAAAAAAAAAAAAJc9-qw4DDXLoeOFDsU0YvoPsJ3hshg64uN6mSF6FsSb&amp;signer=media-rpc&amp;x-canva-quality=screen_2x" alt="Gumbalkán Logo" class="h-24 md:h-32 mx-auto glitch-hover" loading="lazy" onerror="console.error('Logo failed to load:', this.src); this.style.display='none';">
     </div>
     <div class="mb-4 font-oswald text-sm tracking-[0.5em] text-red-500 uppercase" style="animation: sprayin 1.5s ease-out forwards;">
      SUMMER EDITION
     </div>
     <h1 id="hero-title" class="font-bebas spray-text glitch-hover leading-none" style="font-size: clamp(3rem, 12vw, 10rem); color: #fff; animation: neonPulse 3s infinite;">JEDEM NA JEDNO</h1>
     <div class="mt-2 font-bebas text-red-500" style="font-size: clamp(1.2rem, 4vw, 3rem); letter-spacing: 0.1em;">
      – GUMBALKÁN 2026 –
     </div>
     <p id="hero-tagline" class="mt-6 font-elite text-lg md:text-2xl text-gray-400" style="animation: slideUp 1s 0.5s both;">Když se cesta stává cílem a splněným snem.</p>
     <div class="mt-10 flex gap-4"><button onclick="document.getElementById('countdown-section').scrollIntoView({behavior:'smooth'})" class="font-oswald font-bold px-8 py-3 bg-red-600 text-white uppercase tracking-wider hover:bg-white hover:text-black transition-all shake-hover" style="clip-path: polygon(4% 0%, 100% 0%, 96% 100%, 0% 100%); animation: heartbeat 2s infinite;"> ODPOČÍTÁVÁNÍ </button>
     </div>
     <div class="mt-12 flex justify-center gap-3 items-center"><a id="hero-instagram" href="https://instagram.com/jedem_na_jedno" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer flex items-center gap-2" style="color:#ff003c;text-decoration:none;"><i data-lucide="instagram" style="width:28px;height:28px;color:#ff003c;"></i><span class="font-oswald" style="font-size:0.95rem;letter-spacing:0.05em;">@jedem_na_jedno</span></a>
     </div><!-- Scroll indicator -->
     <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce"><i data-lucide="chevrons-down" style="width:32px;height:32px;color:#ff003c;"></i>
     </div>
    </div>
   </section><!-- RED DIVIDER -->

   <!-- STATS STRIP -->
   <section class="w-full py-0" style="background:#050002;border-top:1px solid rgba(255,0,60,0.15);border-bottom:1px solid rgba(255,0,60,0.15);">
    <div class="max-w-5xl mx-auto">
     <div class="grid grid-cols-2 md:grid-cols-4">
      <div class="reveal text-center py-10 px-4" style="border-right:1px solid rgba(255,0,60,0.12);">
       <div class="countdown-num font-bebas text-6xl md:text-8xl text-white">~3000</div>
       <div class="font-oswald text-xs tracking-[0.35em] text-red-500 uppercase mt-2">Kilometrů</div>
      </div>
      <div class="reveal text-center py-10 px-4" style="border-right:1px solid rgba(255,0,60,0.12);">
       <div class="countdown-num font-bebas text-6xl md:text-8xl text-white">5</div>
       <div class="font-oswald text-xs tracking-[0.35em] text-red-500 uppercase mt-2">Zemí</div>
      </div>
      <div class="reveal text-center py-10 px-4" style="border-right:1px solid rgba(255,0,60,0.12);">
       <div class="countdown-num font-bebas text-6xl md:text-8xl text-white">14</div>
       <div class="font-oswald text-xs tracking-[0.35em] text-red-500 uppercase mt-2">Dní chaosu</div>
      </div>
      <div class="reveal text-center py-10 px-4">
       <div class="countdown-num font-bebas text-6xl md:text-8xl text-white">4</div>
       <div class="font-oswald text-xs tracking-[0.35em] text-red-500 uppercase mt-2">Blázni</div>
      </div>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- ABOUT SECTION -->
   <section class="w-full py-24 px-4 relative" style="background: linear-gradient(180deg, #0a0008 0%, #000 50%, #0a0008 100%); animation: sectionSlide 0.8s ease-out forwards; opacity: 0;">
    <div class="max-w-4xl mx-auto">
     <div class="reveal mb-12">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4" style="animation: fadeInUp 0.6s ease-out;">
       // O GUMBALKÁNU //
      </div>
      <h2 class="font-bebas text-5xl md:text-7xl mb-8 glitch-hover" style="animation: fadeInUp 0.8s ease-out 0.1s both;">GUMBALKÁN LETNÍ EDICE 2026</h2>
      <div class="space-y-6 font-elite text-gray-300 text-base md:text-lg leading-relaxed">
       <p class="text-white font-bold text-xl">Tohle není dovolená.<br><span class="text-red-500">Tohle je čistý chaos na kolech.</span></p>
       <p>Zapomeň na all-inclusive, lehátka a plánování.<br>
         Tady bereš auto za pár korun, partu bláznů a vyrážíš vstříc Balkánu, kde se každý den může změnit úplně všechno.</p>
       <div class="border-l-4 border-red-600 pl-6 py-4 my-8" style="background: rgba(255,0,60,0.05);">
        <p class="text-white font-bold mb-3">🚗🔥 STARTUJ. NEPŘEMÝŠLEJ.</p>
        <ul class="space-y-2 text-gray-400">
         <li>• staré káry, co drží pohromadě silou vůle</li>
         <li>• stovky lidí na stejné vlně</li>
         <li>• kilometry bez jistoty, kam vlastně dojedeš</li>
         <li>• checkpointy, výzvy a situace, co nevymyslíš</li>
        </ul>
       </div>
       <p><span class="text-red-500 font-bold">👉 Tohle není event.</span><br><span class="text-red-500 font-bold">👉 Tohle je festival na kolech.</span></p>
       <p class="text-xl font-bold text-white mt-8">🌍💥 CO SE STANE NA GUMBALKÁNU…<br><span class="text-red-500">…nejde naplánovat.</span></p>
       <p>Jednou piješ pivo s cizíma lidma někde v horách.<br>
         O pár hodin později opravuješ auto izolepou uprostřed ničeho.<br>
         Spíš kdekoliv. Jedeš kamkoliv. Řešíš cokoliv.</p>
       <p><span class="text-white font-bold">A přesně o to jde.</span></p>
       <p class="text-xl font-bold text-white mt-8">🧠⚡ PRO KOHO TO JE?</p>
       <p class="text-gray-400 italic">Pro ty, co:</p>
       <ul class="space-y-2 text-gray-400">
        <li>• chtějí víc než jen „jet na dovolenou"</li>
        <li>• milují chaos, svobodu a nečekané situace</li>
        <li>• neřeší komfort, ale zážitky</li>
        <li>• řeknou „jedem" místo „co když"</li>
       </ul>
       <div class="border-l-4 border-red-600 pl-6 py-4 my-8" style="background: rgba(255,0,60,0.05);">
        <p class="text-white font-bold mb-3">🏕️🚫 ŽÁDNÁ PRAVIDLA. ŽÁDNÝ KOMFORT.</p>
        <ul class="space-y-2 text-gray-400">
         <li>• žádné hotely na jistotu</li>
         <li>• žádný servis, co tě zachrání</li>
         <li>• žádný plán, co tě podrží</li>
        </ul>
        <p class="text-white font-bold mt-4">👉 Máš jen auto, lidi a cestu před sebou.</p>
       </div>
       <p class="text-xl font-bold text-white mt-8">🔥❤️ PROČ TO DĚLAT?</p>
       <p class="text-gray-400">Protože jednou chceš říct:</p>
       <ul class="space-y-2 text-red-500 italic">
        <li>• „Dojeli jsme bez brzd."</li>
        <li>• „Spali jsme na střeše auta."</li>
        <li>• „Nevěděli jsme kde jsme… ale bylo to nejlepší."</li>
       </ul>
       <div class="border-2 border-red-600 p-6 my-8" style="background: rgba(255,0,60,0.1);">
        <p class="text-white font-bold mb-4">💬 POSLEDNÍ VAROVÁNÍ</p>
        <p class="text-gray-300">Gumbalkán není pro každého.</p>
        <p class="text-gray-300 mt-4">Ale pokud tohle čteš a něco v tobě říká<br><span class="text-white font-bold">„ty vole, jedu"…</span></p>
        <p class="text-red-500 font-bold mt-4">👉 tak už jsi rozhodnutý.</p>
       </div>
       <p class="text-2xl font-bold text-white mt-8 text-center">🚀 GUMBALKÁN 2026</p>
       <p class="text-center text-red-500 font-bold text-lg">Nastartuj. Ztrať se. Zažij všechno.</p>
      </div>
     </div>
    </div>
   </section>
   <div class="red-line w-full"></div><!-- ROUTE -->
   <section class="w-full py-24 px-4 relative" style="background: linear-gradient(180deg, #000 0%, #060003 50%, #000 100%); overflow:hidden;">
    <div class="max-w-6xl mx-auto">
     <div class="reveal text-center mb-16">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4">// TRASA //</div>
      <h2 class="font-bebas text-5xl md:text-7xl glitch-hover">KUDY JEDEME</h2>
     </div>
     <!-- Route timeline -->
     <div class="reveal" style="overflow-x:auto;padding-bottom:24px;">
      <div style="display:flex;align-items:center;gap:0;min-width:600px;position:relative;padding:32px 0;">
       <!-- Connecting line -->
       <div style="position:absolute;top:50%;left:40px;right:40px;height:2px;background:linear-gradient(90deg,#ff003c,#ff6b35,#f59e0b,#06b6d4,#10b981,#ff003c);opacity:0.4;transform:translateY(-50%);z-index:0;"></div>
       <!-- Stops -->
       <?php
       $stops = [
         ['flag'=>'🇨🇿','country'=>'ČESKO',    'city'=>'Start · Bílá',  'color'=>'#ff003c'],
         ['flag'=>'🇸🇰','country'=>'SLOVENSKO', 'city'=>'Průjezd',       'color'=>'#ff6b35'],
         ['flag'=>'🇭🇺','country'=>'MAĎARSKO',  'city'=>'Průjezd',       'color'=>'#f59e0b'],
         ['flag'=>'🇭🇷','country'=>'CHORVATSKO','city'=>'Průjezd',        'color'=>'#06b6d4'],
         ['flag'=>'🇧🇦','country'=>'BOSNA',     'city'=>'Cíl · někde v Bosně 🤷','color'=>'#10b981'],
         ['flag'=>'🇨🇿','country'=>'ČESKO',     'city'=>'Domů. Živí?',   'color'=>'#ff003c'],
       ];
       foreach ($stops as $i => $stop):
       ?>
       <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1;<?= $i > 0 ? 'margin-left:-8px;' : '' ?>">
        <div style="font-size:2rem;margin-bottom:10px;line-height:1;"><?= $stop['flag'] ?></div>
        <div style="
          width:36px;height:36px;border-radius:50%;
          border:2px solid <?= $stop['color'] ?>;
          background:rgba(0,0,0,0.9);
          box-shadow:0 0 12px <?= $stop['color'] ?>66;
          display:flex;align-items:center;justify-content:center;
          font-family:'Bebas Neue',cursive;font-size:13px;color:<?= $stop['color'] ?>;
          margin-bottom:10px;
        "><?= $i + 1 ?></div>
        <div class="font-bebas" style="font-size:.85rem;color:#fff;text-align:center;line-height:1.2;white-space:nowrap;"><?= $stop['country'] ?></div>
        <div class="font-oswald" style="font-size:.6rem;letter-spacing:.15em;color:<?= $stop['color'] ?>;text-transform:uppercase;margin-top:3px;"><?= $stop['city'] ?></div>
       </div>
       <?php endforeach; ?>
      </div>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- RULES -->
   <section class="w-full py-24 px-4 relative" style="background: linear-gradient(180deg, #060003 0%, #000 100%);">
    <div class="max-w-5xl mx-auto">
     <div class="reveal text-center mb-16">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4">// KODEX //</div>
      <h2 class="font-bebas text-5xl md:text-7xl glitch-hover">PRAVIDLA CHAOSU</h2>
      <p class="font-elite text-gray-500 mt-4 text-lg">Málo jich je. Ale ty co jsou — platí.</p>
     </div>
     <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-4">
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">01</div>
       <div class="font-bebas text-2xl text-white mb-2">VRAK, NE LIMUZÍNA</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Auto max za <span class="text-red-500 font-bold">25 000 Kč</span>. Čím víc toho chybí, tím líp. Servisní knížka nepovinná. Lepenka povinná.</div>
      </div>
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">02</div>
       <div class="font-bebas text-2xl text-white mb-2">ŽÁDNÝ HOTEL</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Spíš kde to vyjde. Auto, stan, u cizinců, na střeše — vše je lepší než pohodlí.</div>
      </div>
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">03</div>
       <div class="font-bebas text-2xl text-white mb-2">ŽÁDNÝ PLÁN</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Směr znáš — Balkán. Co se stane cestou, to se stane. Adaptuj se nebo hni.</div>
      </div>
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">04</div>
       <div class="font-bebas text-2xl text-white mb-2">DOKUMENTUJ VŠE</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Každá katastrofa si zaslouží být zvěčněna. Kamera jede vždy. I když ty ne.</div>
      </div>
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">05</div>
       <div class="font-bebas text-2xl text-white mb-2">ZÁCHRANA Z VLASTNÍCH SIL</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Auto se pokazí? Opravíš ho sám. Izolepou, drátkem nebo modlitbou — ale sám.</div>
      </div>
      <div style="border:1px solid rgba(255,0,60,0.2);padding:24px;background:rgba(255,0,60,0.03);transition:all .3s;" onmouseover="this.style.borderColor='rgba(255,0,60,0.6)';this.style.background='rgba(255,0,60,0.07)'" onmouseout="this.style.borderColor='rgba(255,0,60,0.2)';this.style.background='rgba(255,0,60,0.03)'">
       <div class="font-bebas text-4xl text-red-500 mb-2">06</div>
       <div class="font-bebas text-2xl text-white mb-2">NIKDO NEJEDE SÁM</div>
       <div class="font-elite text-gray-400 text-sm leading-relaxed">Banda jde nebo padá dohromady. Jeden se zasekne — ostatní čekají. Vždy.</div>
      </div>
     </div>
     <div class="reveal text-center mt-12">
      <div style="border:2px solid rgba(255,0,60,0.4);display:inline-block;padding:20px 40px;background:rgba(255,0,60,0.08);">
       <p class="font-bebas text-3xl text-white">OSTATNÍ SI VYMYSLÍME ZA JÍZDY</p>
       <p class="font-elite text-gray-500 mt-2">— jak to tak chodí</p>
      </div>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- MANIFESTO -->
   <section class="w-full py-20 px-4 relative" style="background: linear-gradient(180deg, #0a0008 0%, #000 100%);">
    <div class="max-w-4xl mx-auto text-center reveal">
     <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4">
      // MANIFEST //
     </div>
     <p class="font-elite text-xl md:text-3xl leading-relaxed text-gray-300">Jeden vrak. Jedna cesta přes <span class="text-red-500">Balkán</span>.<br>
       Bez rozumu. Bez luxusu. Bez slitování.<br><br><span class="text-white font-bold">Jen chaos, prach a otevřená silnice.</span></p>
    </div>
   </section><!-- TEAM -->
   <section class="w-full py-20 px-4 relative">
    <div class="absolute inset-0 opacity-5">
     <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg"><defs>
       <pattern id="xhatch" width="10" height="10" patternunits="userSpaceOnUse">
        <path d="M0,0 L10,10 M10,0 L0,10" stroke="#ff003c" stroke-width="0.5" />
       </pattern>
      </defs> <rect width="100%" height="100%" fill="url(#xhatch)" />
     </svg>
    </div>
    <div class="relative z-10 max-w-5xl mx-auto">
     <div class="reveal mb-16 text-center" style="animation: sectionSlide 0.8s ease-out;">
<!-- <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2" style="animation: fadeInUp 0.6s ease-out;">
       // POSÁDKA //
      </div>
      <h2 class="font-bebas text-6xl md:text-8xl glitch-hover" style="animation: fadeInUp 0.8s ease-out 0.1s both;">TÝM</h2>
     </div> -->
     
     <!-- EDA -->
     
     <div class="relative z-10 max-w-5xl mx-auto"> 

  <!-- HEADER -->
  <div class="reveal mb-16 text-center" style="animation: sectionSlide 0.8s ease-out;">
    <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">
      // POSÁDKA //
    </div>
  
    
    <h2 class="font-bebas text-6xl md:text-8xl glitch-hover">TÝM</h2> 
  </div>

  <!-- EDA -->
  <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row gap-6 items-start">
    
    <div class="polaroid flex-shrink-0 w-48 h-56 p-2 bg-white shadow-2xl border border-gray-300" style="transform:rotate(-3deg);">
      <img src="1.png" alt="EDA" class="w-full h-full object-cover grayscale contrast-125 transition duration-300 hover:scale-105">
    </div>

    <div class="flex-1">
      <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">EDA</h3>
      <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        NAVIGÁTOR · DOKUMENTÁTOR · AGENT CHAOSU
      </div>
      <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Kancelářská krysa přes den. <span class="text-white">Tvůrce chaosu po hospodské otvíračce.</span></p>
        <p>Cestovatel, který viděl víc hranic jak Google maps.</p>
        <p>Překladatel, který dokáže přesvědčit nabíječku, aby mu prodala naftu.</p>
        <p>Řidič, který věří <span class="text-red-500">instinktu více než pravidlům.</span></p>
        <p class="mt-4 text-gray-500 italic">Fotograf/videopublikátor, který změní každou katastrofu v legendu.</p>
      </div>
    </div>

  </div>

  <!-- MUŠKA -->
  <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row-reverse gap-6 items-start">

    <div class="polaroid flex-shrink-0 w-48 h-56 p-2 bg-white shadow-2xl border border-gray-300" style="transform:rotate(2deg);">
      <img src="3.png" alt="MUŠKA" class="w-full h-full object-cover grayscale contrast-125 transition duration-300 hover:scale-105">
    </div>

    <div class="flex-1">
      <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">MUŠKA</h3>
      <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        ŘIDIČ · MECHANIK · ŠÍLENÝ
      </div>
      <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Řidič s více Off-road kilometry než má průměrný člověk dalničních kilometrů.</p>
        <p>Mechanik, který věci udržuje naživu <span class="text-red-500">mimo hranice fyziky.</span></p>
        <p class="mt-4 text-white font-bold">Šílený, který řekl: "Jo, já tam jsem."</p>
        <p class="text-gray-500 italic">A od té chvíle… nebyla cesta zpátky.</p>
      </div>
    </div>

  </div>

  <!-- VYSOKÝ DAN -->
  <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row gap-6 items-start">

    <div class="polaroid flex-shrink-0 w-48 h-56 p-2 bg-white shadow-2xl border border-gray-300" style="transform:rotate(-2deg);">
      <img src="4.png" alt="Vysoký Dan" class="w-full h-full object-cover grayscale contrast-125 transition duration-300 hover:scale-105">
    </div>

    <div class="flex-1">
      <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">VYSOKÝ DAN</h3>
      <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        MECHANIK · STROJNÍK · ŘIDIČ
      </div>
      <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Mechanik s láskou k <span class="text-red-500">těžkým strojům a nemožným ideám.</span></p>
        <p class="mt-4 text-white font-bold">"To projedem, hlavně se toho nebát."</p>
        <p>A všichni vědí… jakmile z něho něco vypadne — <span class="text-red-500">to se stane.</span></p>
        <p class="text-gray-500 italic">Odborný krotitel chaosu.</p>
      </div>
    </div>

  </div>

  <!-- LUKY -->
  <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row-reverse gap-6 items-start">

    <div class="polaroid flex-shrink-0 w-48 h-56 p-2 bg-white shadow-2xl border border-gray-300" style="transform:rotate(3deg);">
      <img src="2.png" alt="LUKY" class="w-full h-full object-cover grayscale contrast-125 transition duration-300 hover:scale-105">
    </div>

    <div class="flex-1">
      <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">LUKY</h3>
      <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        ŘIDIČ · HUMORISTA · FAKTOR CHAOSU
      </div>
      <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Poslední, kdo se připojil. <span class="text-white font-bold">První, kdo řekl: "JÁ JSEM READY."</span></p>
        <p>Nebojí se práce, potíží ani blbých nápadů.</p>
        <p>Řidič, který si sedne za volant <span class="text-red-500">bez otázek.</span></p>
        <p>Humorista, který zlomí každý vážný moment.</p>
        <p class="text-gray-500 italic">A faktor chaosu, který nám chyběl.</p>
      </div>
    </div>

  </div>

</div>
      <!-- 
     <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row gap-6 items-start" style="animation: sectionSlide 0.8s ease-out 0.2s both; opacity: 0; transform: perspective(1000px) rotateX(5deg);">
      <div class="polaroid flex-shrink-0 w-48 h-56 flex items-center justify-center" style="transform:rotate(-3deg); animation: cardFlip 3s ease-in-out infinite;">
       <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center"><span class="font-bebas text-4xl text-red-500 opacity-40"><img src="1.png">
                                                                                                                                                                         </span>
       </div>
      </div>
   
      
      <div class="flex-1">
       <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">EDA</h3>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        NAVIGÁTOR · DOKUMENTÁTOR · AGENT CHAOSU
       </div>
       <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Kancelářská krysa přes den. <span class="text-white">Tvůrce chaosu po hospodské otvíračce.</span></p>
        <p>Cestovatel, který viděl víc hranic jak Google maps.</p>
        <p>Překladatel, který dokáže přesvědčit nabíječku, aby mu prodala naftu.</p>
        <p>Řidič, který věří <span class="text-red-500">instinktu více než pravidlům.</span></p>
        <p class="mt-4 text-gray-500 italic">Fotograf/videopublikátor, který změní každou katastrofu v legendu. Sociální sítě běží protože někdo musí dokumentovat zhroucení.</p>
       </div>
      </div>
     </div>
     <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row-reverse gap-6 items-start">
      <div class="polaroid flex-shrink-0 w-48 h-56 flex items-center justify-center" style="transform:rotate(2deg);">
       <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center"><span class="font-bebas text-4xl text-red-500 opacity-40"><img src="3.png" ></span>
       </div>
      </div>
      <div class="flex-1">
       <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">MUŠKA</h3>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        ŘIDIČ · MECHANIK · ŠÍLENÝ
       </div>
       <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Řidič s více Off-road kilometry než má průměrný člověk dalníčních kilometrů.</p>
        <p>Mechanik, který věci udržuje naživu <span class="text-red-500">mimo hranice fyziky.</span></p>
        <p class="mt-4 text-white font-bold">Šílený, který řekl: "Jo, já tam jsem."</p>
        <p class="text-gray-500 italic">A od té chvíle… nebyla cesta zpátky.</p>
       </div>
      </div>
     </div>
     <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row gap-6 items-start">
      <div class="polaroid flex-shrink-0 w-48 h-56 flex items-center justify-center" style="transform:rotate(-1deg);">
       <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center"><span class="font-bebas text-3xl text-red-500 opacity-40"><img src="4.png">
                                                                                                                                                                         </span>
       </div>
      </div>
      <div class="flex-1">
       <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">VYSOKÝ DAN</h3>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        MECHANIK · STROJNÍK · ŘIDIČ
       </div>
       <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Mechanik s láskou k <span class="text-red-500">těžkým strojům a nemožným ideám.</span></p>
        <p class="mt-4 text-white font-bold">"To projedem, hlavě se toho nebát."</p>
        <p>A všichni vědí… jakmile z něho něco vypadne — <span class="text-red-500">to se stane.</span></p>
        <p class="text-gray-500 italic">Odborný krotikel chaosu, že to vlastně dává smysl?</p>
       </div>
      </div>
     </div>
     <div class="member-card reveal mb-12 p-6 md:p-10 flex flex-col md:flex-row-reverse gap-6 items-start">
      <div class="polaroid flex-shrink-0 w-48 h-56 flex items-center justify-center" style="transform:rotate(3deg);">
       <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center"><span class="font-bebas text-4xl text-red-500 opacity-40"><img src="2.png"></span>
       </div>
      </div>
      <div class="flex-1">
       <h3 class="font-bebas text-5xl md:text-7xl text-white glitch-hover">LUKY</h3>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mb-4">
        ŘIDIČ · HUMORISTA · FAKTOR CHAOSU
       </div>
       <div class="font-elite text-gray-400 space-y-2 text-base md:text-lg">
        <p>Poslední, kdo se připojil. <span class="text-white font-bold">První, kdo řekl: "JÁ JSEM READY."</span></p>
        <p>Nebojí se práce, potíží ani blbých nápadů.</p>
        <p>Řidič, který si sedne za volant <span class="text-red-500">bez otázek.</span></p>
        <p>Humorista, který zlomí každý vážný moment.</p>
        <p class="text-gray-500 italic">A faktor chaosu, který nám chyběl.</p>
       </div>
      </div>
     </div>
    </div> 
            
           -->
   </section>

   <div class="red-line w-full"></div><!-- TRAINING CAMP TEASER -->
   <section class="w-full py-24 px-4 relative" style="background:linear-gradient(135deg,#050002 0%,#0a0010 50%,#050002 100%);">
    <div class="max-w-5xl mx-auto">
     <div class="reveal">
      <div class="flex flex-col md:flex-row gap-12 items-center">
       <div class="flex-1">
        <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4">// PŘÍPRAVA //</div>
        <h2 class="font-bebas text-5xl md:text-7xl glitch-hover mb-6" style="line-height:1;">TRAINING CAMP<br><span style="color:#ff003c;">IN THE ALPS</span></h2>
        <p class="font-elite text-gray-400 text-lg leading-relaxed mb-4">Před Balkánem přišly Alpy. Týden v horách, kde jsme zjistili co auto vydá, co vydržíme my a jak moc boli spát na kameních.</p>
        <p class="font-elite text-gray-400 leading-relaxed mb-8">Grossglockner. Hallstatt. Gosausee. Čtyři dny. Čtyři blázni. Jedno auto.</p>
        <div class="flex gap-4 flex-wrap">
         <a href="alps.php" class="font-oswald font-bold px-8 py-3 bg-red-600 text-white uppercase tracking-wider hover:bg-white hover:text-black transition-all inline-block" style="clip-path:polygon(4% 0%,100% 0%,96% 100%,0% 100%);">ZOBRAZIT MAPU →</a>
         <div class="font-oswald text-xs tracking-[0.2em] text-gray-600 uppercase self-center">27.–30. Května 2026</div>
        </div>
       </div>
       <!-- Stats from alps -->
       <div class="flex-shrink-0 grid grid-cols-2 gap-4 w-full md:w-72">
        <div style="border:1px solid rgba(255,0,60,0.25);padding:20px 16px;text-align:center;background:rgba(255,0,60,0.03);">
         <div class="font-bebas text-4xl text-white">4</div>
         <div class="font-oswald text-xs tracking-[0.25em] text-red-500 uppercase mt-1">Dny</div>
        </div>
        <div style="border:1px solid rgba(255,0,60,0.25);padding:20px 16px;text-align:center;background:rgba(255,0,60,0.03);">
         <div class="font-bebas text-4xl text-white">9</div>
         <div class="font-oswald text-xs tracking-[0.25em] text-red-500 uppercase mt-1">Míst</div>
        </div>
        <div style="border:1px solid rgba(255,0,60,0.25);padding:20px 16px;text-align:center;background:rgba(255,0,60,0.03);">
         <div class="font-bebas text-4xl text-white">1</div>
         <div class="font-oswald text-xs tracking-[0.25em] text-red-500 uppercase mt-1">Grossglockner</div>
        </div>
        <div style="border:1px solid rgba(255,0,60,0.25);padding:20px 16px;text-align:center;background:rgba(255,0,60,0.03);">
         <div class="font-bebas text-4xl text-white">∞</div>
         <div class="font-oswald text-xs tracking-[0.25em] text-red-500 uppercase mt-1">Kamarádství</div>
        </div>
       </div>
      </div>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- COUNTDOWN -->
   <section id="countdown-section" class="w-full py-24 px-4 text-center relative" style="background: radial-gradient(ellipse at center, #1a0008 0%, #000 70%);">
    <div class="reveal">
     <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">
      // ODPOČÍTÁVÁNÍ //
     </div>
     <h2 class="font-bebas text-5xl md:text-7xl mb-12 glitch-hover">ODPOČÍTÁVÁNÍ NA CHAOS</h2>
     <div id="countdown" class="flex justify-center gap-4 md:gap-8 flex-wrap">
      <div class="text-center">
       <div class="countdown-num font-bebas text-5xl md:text-8xl text-white" id="cd-days">
        ---
       </div>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mt-2">
        Dny
       </div>
      </div>
      <div class="font-bebas text-5xl md:text-8xl text-red-600 self-start">
       :
      </div>
      <div class="text-center">
       <div class="countdown-num font-bebas text-5xl md:text-8xl text-white" id="cd-hours">
        --
       </div>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mt-2">
        Hodin
       </div>
      </div>
      <div class="font-bebas text-5xl md:text-8xl text-red-600 self-start">
       :
      </div>
      <div class="text-center">
       <div class="countdown-num font-bebas text-5xl md:text-8xl text-white" id="cd-mins">
        --
       </div>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mt-2">
        Minut
       </div>
      </div>
      <div class="font-bebas text-5xl md:text-8xl text-red-600 self-start">
       :
      </div>
      <div class="text-center">
       <div class="countdown-num font-bebas text-5xl md:text-8xl text-white" id="cd-secs">
        --
       </div>
       <div class="font-oswald text-xs tracking-[0.3em] text-red-500 uppercase mt-2">
        Sekund
       </div>
      </div>
     </div>
    </div>
   </section>
   <div class="red-line w-full"></div><!-- VIDEO GALLERY (Instagram) -->
   <section class="w-full py-24 px-4 relative" style="background: linear-gradient(180deg, #000 0%, #0a0008 50%, #000 100%);">
    <div class="max-w-6xl mx-auto">
     <div class="text-center reveal mb-12">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">
       // ZÁBĚRY //
      </div>
      <h2 class="font-bebas text-5xl md:text-7xl mb-3 glitch-hover">VIDEÁ</h2>
      <a href="https://instagram.com/jedem_na_jedno" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 font-oswald text-sm tracking-[0.15em] uppercase" style="color:#ff003c;text-decoration:none;">
       <i data-lucide="instagram" style="width:18px;height:18px;"></i> @jedem_na_jedno
      </a>
     </div>
<?php if (!empty($instagram_reels)): ?>
     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 justify-items-center">
<?php foreach ($instagram_reels as $url):
        // ořízni query string a sjednoť tvar permalinku
        $clean = preg_replace('/\?.*$/', '', trim($url));
        $clean = rtrim($clean, '/') . '/';
?>
      <div class="reveal w-full max-w-[340px]" style="border:1px solid rgba(255,0,60,0.25);padding:6px;background:rgba(255,0,60,0.03);">
       <blockquote class="instagram-media" data-instgrm-permalink="<?= htmlspecialchars($clean, ENT_QUOTES) ?>" data-instgrm-version="14" style="background:#000;border:0;margin:0;padding:0;width:100%;"></blockquote>
      </div>
<?php endforeach; ?>
     </div>
     <script async src="https://www.instagram.com/embed.js"></script>
<?php else: ?>
     <div class="reveal max-w-2xl mx-auto text-center" style="border:1px dashed rgba(255,0,60,0.35);padding:48px 24px;background:rgba(255,0,60,0.03);">
      <i data-lucide="film" style="width:44px;height:44px;color:#ff003c;margin:0 auto 16px;display:block;"></i>
      <div class="font-bebas text-3xl text-white mb-2">ZATÍM SE NATÁČÍ</div>
      <p class="font-elite text-gray-400 text-sm">Videa z trasy přibydou tady. Sleduj nás na Instagramu — všechno padá tam jako první.</p>
      <a href="https://instagram.com/jedem_na_jedno" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-6 font-oswald font-bold px-6 py-3 bg-red-600 text-white uppercase tracking-wider hover:bg-white hover:text-black transition-all" style="clip-path:polygon(4% 0%,100% 0%,96% 100%,0% 100%);text-decoration:none;">
       <i data-lucide="instagram" style="width:20px;height:20px;"></i> Sleduj @jedem_na_jedno
      </a>
     </div>
<?php endif; ?>
    </div>
   </section>
   <div class="red-line w-full"></div><!-- GALLERY -->
   <section class="w-full py-24 px-4 relative">
    <div class="max-w-6xl mx-auto">
     <div class="reveal text-center mb-16">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">
       // EVIDENCE //
      </div>
      <h2 class="font-bebas text-5xl md:text-7xl glitch-hover">PHOTO GALLERY</h2>
     </div>
     <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
      <div class="gallery-item reveal aspect-square bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 flex items-center justify-center cursor-pointer" style="animation-delay:0.1s;">
       <div class="text-center">
        <i data-lucide="camera" style="width:32px;height:32px;color:#ff003c;margin:0 auto;"></i>
        <div class="font-elite text-gray-600 text-sm mt-2">
         Photo 01
        </div>
       </div>
      </div>
      <div class="gallery-item reveal aspect-square bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 flex items-center justify-center cursor-pointer" style="animation-delay:0.2s;">
       <div class="text-center">
        <i data-lucide="camera" style="width:32px;height:32px;color:#ff003c;margin:0 auto;"></i>
        <div class="font-elite text-gray-600 text-sm mt-2">
         Photo 02
        </div>
       </div>
      </div>
      <div class="gallery-item reveal aspect-square bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 flex items-center justify-center cursor-pointer" style="animation-delay:0.3s;">
       <div class="text-center">
        <i data-lucide="camera" style="width:32px;height:32px;color:#ff003c;margin:0 auto;"></i>
        <div class="font-elite text-gray-600 text-sm mt-2">
         Photo 03
        </div>
       </div>
      </div>
      <div class="gallery-item reveal aspect-square bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 flex items-center justify-center cursor-pointer" style="animation-delay:0.4s;">
       <div class="text-center">
        <i data-lucide="camera" style="width:32px;height:32px;color:#ff003c;margin:0 auto;"></i>
        <div class="font-elite text-gray-600 text-sm mt-2">
         Photo 04
        </div>
       </div>
      </div>
      <div class="gallery-item reveal aspect-square bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 flex items-center justify-center cursor-pointer col-span-2" style="animation-delay:0.5s;">
       <div class="text-center">
        <i data-lucide="camera" style="width:32px;height:32px;color:#ff003c;margin:0 auto;"></i>
        <div class="font-elite text-gray-600 text-sm mt-2">
         Photo 05
        </div>
       </div>
      </div>
     </div>
    </div>
   </section>
<?php if (!empty($sponsors)): ?>
   <div class="red-line w-full"></div><!-- PARTNERS -->
   <section class="w-full py-24 px-4 relative" style="background:linear-gradient(180deg,#000 0%,#0a0008 100%);">
    <div class="max-w-6xl mx-auto">
     <div class="text-center reveal mb-14">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">// DÍKY ZA PODPORU //</div>
      <h2 class="font-bebas text-5xl md:text-7xl glitch-hover">PARTNEŘI</h2>
      <p class="font-elite text-gray-400 text-sm mt-3">Firmy, co nás drží na cestě.</p>
     </div>
     <div class="reveal flex flex-wrap justify-center items-center gap-6">
<?php foreach ($sponsors as $sp):
        $logo = htmlspecialchars($sp['logo_path'], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8');
        $href = trim((string)($sp['url'] ?? ''));
        $tag  = $href !== '' ? 'a' : 'div';
        $attr = $href !== '' ? ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer"' : '';
?>
      <<?= $tag ?><?= $attr ?> title="<?= $name ?>"
         style="display:flex;align-items:center;justify-content:center;width:180px;height:110px;padding:18px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);transition:all .3s;text-decoration:none;"
         onmouseover="this.style.borderColor='#ff003c';this.style.background='rgba(255,255,255,0.1)'"
         onmouseout="this.style.borderColor='rgba(255,255,255,0.08)';this.style.background='rgba(255,255,255,0.04)'">
       <img src="<?= $logo ?>" alt="<?= $name ?>" loading="lazy" style="max-width:100%;max-height:100%;object-fit:contain;filter:grayscale(1) brightness(1.6);transition:filter .3s;" onmouseover="this.style.filter='grayscale(0) brightness(1)'" onmouseout="this.style.filter='grayscale(1) brightness(1.6)'">
      </<?= $tag ?>>
<?php endforeach; ?>
     </div>
    </div>
   </section>
<?php endif; ?>
   <div class="red-line w-full"></div><!-- FOLLOW -->
   <section class="w-full py-24 px-4 relative" style="background:radial-gradient(ellipse at center,#0d0005 0%,#000 70%);">
    <div class="max-w-4xl mx-auto text-center reveal">
     <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-4">// SLEDUJ NAŽIVO //</div>
     <h2 class="font-bebas text-5xl md:text-7xl glitch-hover mb-4">BUĎ U TOHO</h2>
     <p class="font-elite text-gray-400 text-lg mb-12">Fotky, videa a živý chaos rovnou z trasy. Každý den nová katastrofa.</p>
     <div class="flex justify-center mb-12">
      <a id="follow-instagram" href="https://instagram.com/jedem_na_jedno" target="_blank" rel="noopener noreferrer"
         style="border:1px solid rgba(255,0,60,0.25);padding:40px 48px;display:block;text-decoration:none;transition:all .3s;background:rgba(255,0,60,0.03);max-width:420px;width:100%;"
         onmouseover="this.style.borderColor='#ff003c';this.style.background='rgba(255,0,60,0.1)';this.style.transform='translateY(-4px)'"
         onmouseout="this.style.borderColor='rgba(255,0,60,0.25)';this.style.background='rgba(255,0,60,0.03)';this.style.transform='translateY(0)'">
       <i data-lucide="instagram" style="width:48px;height:48px;color:#ff003c;margin:0 auto 16px;display:block;"></i>
       <div class="font-bebas text-3xl text-white mb-1">@jedem_na_jedno</div>
       <div class="font-oswald text-xs tracking-[0.2em] text-gray-500 uppercase">Fotky, Stories a živý chaos z trasy</div>
      </a>
     </div>
     <div style="border-top:1px solid rgba(255,255,255,.05);padding-top:40px;">
      <p class="font-elite text-gray-600 text-sm">Gumbalkán 2026 · Startujeme <span class="text-red-500">4. července</span> · Dojezd negarantujeme</p>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- SHARE -->
   <section class="w-full py-20 px-4 relative" style="background:#050003;">
    <div class="max-w-3xl mx-auto text-center reveal">
     <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-3">// POŠLI DÁL //</div>
     <h2 class="font-bebas text-4xl md:text-6xl glitch-hover mb-3">ŘEKNI TO KÁMOŠŮM</h2>
     <p class="font-elite text-gray-400 text-sm mb-10">Čím víc bláznů ví, tím větší jízda. Hoď to dál.</p>
     <div class="flex flex-wrap justify-center gap-3">
      <button onclick="nativeShare()" class="share-btn" style="background:#ff003c;color:#fff;border-color:#ff003c;">
       <i data-lucide="share-2" style="width:18px;height:18px;"></i> Sdílet
      </button>
      <a id="share-wa" href="#" target="_blank" rel="noopener noreferrer" class="share-btn">
       <i data-lucide="message-circle" style="width:18px;height:18px;"></i> WhatsApp
      </a>
      <a id="share-fb" href="#" target="_blank" rel="noopener noreferrer" class="share-btn">
       <i data-lucide="facebook" style="width:18px;height:18px;"></i> Facebook
      </a>
      <a id="share-tg" href="#" target="_blank" rel="noopener noreferrer" class="share-btn">
       <i data-lucide="send" style="width:18px;height:18px;"></i> Telegram
      </a>
      <a id="share-x" href="#" target="_blank" rel="noopener noreferrer" class="share-btn">
       <i data-lucide="twitter" style="width:18px;height:18px;"></i> X
      </a>
      <button onclick="copyShareLink(this)" class="share-btn">
       <i data-lucide="link" style="width:18px;height:18px;"></i> <span>Kopírovat odkaz</span>
      </button>
     </div>
    </div>
   </section>

   <div class="red-line w-full"></div><!-- FOOTER -->
   <footer class="w-full py-16 px-4 text-center">
    <div class="reveal">
     <div class="font-bebas text-3xl md:text-5xl mb-4" style="animation: neonPulse 3s infinite;">
      JEDEME NA JEDNO
     </div>
     <p class="font-elite text-gray-600 text-sm">Když je cesta cílem</p>
     <div class="mt-6 flex justify-center"><a href="https://instagram.com/jedem_na_jedno" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer flex items-center gap-2" style="color:#ff003c;text-decoration:none;"><i data-lucide="instagram" style="width:24px;height:24px;color:#ff003c;"></i><span class="font-oswald" style="font-size:0.85rem;letter-spacing:0.05em;">@jedem_na_jedno</span></a>
     </div>
     <div class="mt-8 font-oswald text-xs tracking-[0.3em] text-gray-700 uppercase">
      © 2026 Jedeme na jedno · Veškerý chaos vyhrazen
     </div>
    </div>
   </footer>
  </div>
  <script>
const defaultConfig = {
  hero_title: 'GUMBALKAN 2026',
  hero_tagline: 'No rules. No plan. Just the road.',
  instagram_url: 'https://instagram.com/jedem_na_jedno',
  countdown_date: '2026-07-04',
  background_color: '#000000',
  accent_color: '#ff003c',
  text_color: '#ffffff',
  surface_color: '#111111',
  muted_color: '#9ca3af',
  font_family: 'Bebas Neue',
  font_size: 16
};

function applyConfig(config) {
  const title = document.getElementById('hero-title');
  const tagline = document.getElementById('hero-tagline');
  if (title) title.textContent = config.hero_title || defaultConfig.hero_title;
  if (tagline) tagline.textContent = config.hero_tagline || defaultConfig.hero_tagline;

  // Update social links
  const instagramLink = document.getElementById('hero-instagram');
  const followInstagram = document.getElementById('follow-instagram');
  const igUrl = config.instagram_url || defaultConfig.instagram_url;
  if (instagramLink) instagramLink.href = igUrl;
  if (followInstagram) followInstagram.href = igUrl;

  // Colors
  const accent = config.accent_color || defaultConfig.accent_color;
  const bg = config.background_color || defaultConfig.background_color;
  const txt = config.text_color || defaultConfig.text_color;
  document.body.style.backgroundColor = bg;
  document.body.style.color = txt;

  document.querySelectorAll('.red-line').forEach(el => {
    el.style.background = accent;
    el.style.boxShadow = `0 0 15px ${accent}`;
  });

  // Font
  const font = config.font_family || defaultConfig.font_family;
  const baseSize = config.font_size || defaultConfig.font_size;
  document.querySelectorAll('.font-bebas').forEach(el => {
    el.style.fontFamily = `${font}, 'Bebas Neue', cursive`;
  });
  document.querySelectorAll('.font-elite').forEach(el => {
    el.style.fontSize = `${baseSize}px`;
  });
}

// Countdown
let countdownTarget = new Date('2026-07-04T11:00:00');
function updateCountdown(config) {
  const dateStr = (config && config.countdown_date) || defaultConfig.countdown_date;
  countdownTarget = new Date(dateStr + 'T00:00:00');
}
function tickCountdown() {
  const now = new Date();
  const diff = countdownTarget - now;
  if (diff <= 0) {
    document.getElementById('cd-days').textContent = '000';
    document.getElementById('cd-hours').textContent = '00';
    document.getElementById('cd-mins').textContent = '00';
    document.getElementById('cd-secs').textContent = '00';
    return;
  }
  const d = Math.floor(diff / 86400000);
  const h = Math.floor((diff % 86400000) / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  document.getElementById('cd-days').textContent = String(d).padStart(3, '0');
  document.getElementById('cd-hours').textContent = String(h).padStart(2, '0');
  document.getElementById('cd-mins').textContent = String(m).padStart(2, '0');
  document.getElementById('cd-secs').textContent = String(s).padStart(2, '0');
}
setInterval(tickCountdown, 1000);
tickCountdown();

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// ── Sdílení ─────────────────────────────────────────────────────────────────
const shareUrl  = window.location.origin + window.location.pathname;
const shareText = 'GUMBALKÁN 2026 – čtyři blázni, levné auto a Balkán. Jedeme na jedno!';
(function initShare(){
  const u = encodeURIComponent(shareUrl);
  const t = encodeURIComponent(shareText);
  const set = (id, href) => { const el = document.getElementById(id); if (el) el.href = href; };
  set('share-wa', `https://wa.me/?text=${t}%20${u}`);
  set('share-fb', `https://www.facebook.com/sharer/sharer.php?u=${u}`);
  set('share-tg', `https://t.me/share/url?url=${u}&text=${t}`);
  set('share-x',  `https://twitter.com/intent/tweet?url=${u}&text=${t}`);
})();
function nativeShare(){
  if (navigator.share) {
    navigator.share({ title: 'GUMBALKÁN 2026', text: shareText, url: shareUrl }).catch(()=>{});
  } else {
    document.getElementById('share-wa')?.click();
  }
}
function copyShareLink(btn){
  const done = () => {
    const span = btn.querySelector('span');
    if (span){ const o = span.textContent; span.textContent = 'Zkopírováno!'; setTimeout(()=>span.textContent=o, 1800); }
  };
  if (navigator.clipboard) {
    navigator.clipboard.writeText(shareUrl).then(done).catch(done);
  } else {
    const ta = document.createElement('textarea'); ta.value = shareUrl; document.body.appendChild(ta);
    ta.select(); try { document.execCommand('copy'); } catch(e){} ta.remove(); done();
  }
}

// Element SDK
window.elementSdk.init({
  defaultConfig,
  onConfigChange: async (config) => {
    applyConfig(config);
    updateCountdown(config);
  },
  mapToCapabilities: (config) => ({
    recolorables: [
      { get: () => config.background_color || defaultConfig.background_color, set: (v) => { config.background_color = v; window.elementSdk.setConfig({ background_color: v }); } },
      { get: () => config.surface_color || defaultConfig.surface_color, set: (v) => { config.surface_color = v; window.elementSdk.setConfig({ surface_color: v }); } },
      { get: () => config.text_color || defaultConfig.text_color, set: (v) => { config.text_color = v; window.elementSdk.setConfig({ text_color: v }); } },
      { get: () => config.accent_color || defaultConfig.accent_color, set: (v) => { config.accent_color = v; window.elementSdk.setConfig({ accent_color: v }); } },
      { get: () => config.muted_color || defaultConfig.muted_color, set: (v) => { config.muted_color = v; window.elementSdk.setConfig({ muted_color: v }); } },
    ],
    borderables: [],
    fontEditable: { get: () => config.font_family || defaultConfig.font_family, set: (v) => { config.font_family = v; window.elementSdk.setConfig({ font_family: v }); } },
    fontSizeable: { get: () => config.font_size || defaultConfig.font_size, set: (v) => { config.font_size = v; window.elementSdk.setConfig({ font_size: v }); } },
  }),
  mapToEditPanelValues: (config) => new Map([
    ['hero_title', config.hero_title || defaultConfig.hero_title],
    ['hero_tagline', config.hero_tagline || defaultConfig.hero_tagline],
    ['instagram_url', config.instagram_url || defaultConfig.instagram_url],
    ['countdown_date', config.countdown_date || defaultConfig.countdown_date],
  ])
});

// Image SDK
if (window.imageSdk) {
  window.imageSdk.getSelectedImage().then(image => {
    if (image && image.url) {
      const heroImg = document.getElementById('hero-image');
      if (heroImg) {
        heroImg.src = image.url;
      }
    }
  }).catch(err => console.error('Image SDK error:', err));
}

lucide.createIcons();
</script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9e96e54b53c936f5',t:'MTc3NTcxMDI3Ni4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>