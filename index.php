<!doctype html>
<html lang="en" class="h-full">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GUMBALKAN 2026 – SUMMER EDITION</title>
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
    <div style="max-width:1200px;margin:0 auto;padding:0 24px;display:flex;align-items:center;height:52px;gap:32px;">
      <a href="index.php" class="font-bebas" style="font-size:1.4rem;color:#fff;text-decoration:none;letter-spacing:0.08em;transition:color 0.2s;" onmouseover="this.style.color='#ff003c'" onmouseout="this.style.color='#fff'">GUMBALKÁN</a>
      <a href="alps.php" class="font-oswald" style="font-size:0.8rem;color:#9ca3af;text-decoration:none;letter-spacing:0.25em;text-transform:uppercase;transition:color 0.2s;padding:4px 0;border-bottom:2px solid transparent;" onmouseover="this.style.color='#ff003c';this.style.borderBottomColor='#ff003c'" onmouseout="this.style.color='#9ca3af';this.style.borderBottomColor='transparent'">Training camp in the Alps</a>
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
     <div class="mt-12 flex justify-center gap-6"><a id="hero-instagram" href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="instagram" style="width:28px;height:28px;color:#ff003c;"></i></a> <a id="hero-telegram" href="https://t.me" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="send" style="width:28px;height:28px;color:#ff003c;"></i></a> <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="facebook" style="width:28px;height:28px;color:#ff003c;"></i></a>
     </div><!-- Scroll indicator -->
     <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce"><i data-lucide="chevrons-down" style="width:32px;height:32px;color:#ff003c;"></i>
     </div>
    </div>
   </section><!-- RED DIVIDER -->
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
   <div class="red-line w-full"></div><!-- VIDEO GALLERY -->
   <section class="w-full py-24 px-4 relative" style="background: linear-gradient(180deg, #000 0%, #0a0008 50%, #000 100%);">
    <div class="max-w-6xl mx-auto">
     <div class="text-center reveal mb-16">
      <div class="font-oswald text-xs tracking-[0.4em] text-red-500 uppercase mb-2">
       // ZÁBĚRY //
      </div>
      <h2 class="font-bebas text-5xl md:text-7xl mb-2 glitch-hover">VIDEÁ</h2>
     </div>
     <div class="relative h-96 md:h-[32rem] flex items-center justify-center">
      <div class="absolute inset-0 flex items-center justify-center">
       <div class="relative w-full h-full max-w-4xl" id="video-carousel"><!-- Video items positioned in circle -->
        <div class="video-item reveal absolute transition-all duration-500" style="width:320px;height:200px;left:50%;top:50%;transform:translate(-50%,-50%);z-index:50;">
         <div class="w-full h-full bg-gradient-to-br from-gray-900 to-gray-800 border-3 border-red-600 flex items-center justify-center cursor-pointer group relative overflow-hidden hover:border-red-400">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          <div class="relative z-10 text-center">
           <div class="w-16 h-16 rounded-full border-2 border-red-500 flex items-center justify-center mx-auto mb-3 group-hover:bg-red-600 group-hover:scale-110 transition-all"><i data-lucide="play" style="width:28px;height:28px;color:#fff;"></i>
           </div>
           <div class="font-elite text-gray-300 text-sm">
            VIDEO 01
           </div>
          </div>
         </div>
        </div>
        <div class="video-item reveal absolute transition-all duration-500 opacity-60 hover:opacity-100" style="width:280px;height:175px;">
         <div class="w-full h-full bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gray-700 flex items-center justify-center cursor-pointer group relative overflow-hidden hover:border-red-500">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          <div class="relative z-10 text-center">
           <div class="w-12 h-12 rounded-full border-2 border-red-500 flex items-center justify-center mx-auto mb-2 group-hover:bg-red-600 group-hover:scale-110 transition-all"><i data-lucide="play" style="width:20px;height:20px;color:#ff003c;"></i>
           </div>
           <div class="font-elite text-gray-400 text-xs">
            VIDEO 02
           </div>
          </div>
         </div>
        </div>
        <div class="video-item reveal absolute transition-all duration-500 opacity-60 hover:opacity-100" style="width:280px;height:175px;">
         <div class="w-full h-full bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gray-700 flex items-center justify-center cursor-pointer group relative overflow-hidden hover:border-red-500">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          <div class="relative z-10 text-center">
           <div class="w-12 h-12 rounded-full border-2 border-red-500 flex items-center justify-center mx-auto mb-2 group-hover:bg-red-600 group-hover:scale-110 transition-all"><i data-lucide="play" style="width:20px;height:20px;color:#ff003c;"></i>
           </div>
           <div class="font-elite text-gray-400 text-xs">
            VIDEO 03
           </div>
          </div>
         </div>
        </div>
        <div class="video-item reveal absolute transition-all duration-500 opacity-60 hover:opacity-100" style="width:280px;height:175px;">
         <div class="w-full h-full bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gray-700 flex items-center justify-center cursor-pointer group relative overflow-hidden hover:border-red-500">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          <div class="relative z-10 text-center">
           <div class="w-12 h-12 rounded-full border-2 border-red-500 flex items-center justify-center mx-auto mb-2 group-hover:bg-red-600 group-hover:scale-110 transition-all"><i data-lucide="play" style="width:20px;height:20px;color:#ff003c;"></i>
           </div>
           <div class="font-elite text-gray-400 text-xs">
            VIDEO 04
           </div>
          </div>
         </div>
        </div>
       </div>
      </div><!-- Navigation --> <button onclick="rotateCarousel(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 rounded-full border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center"> <i data-lucide="chevron-left" style="width:24px;height:24px;"></i> </button> <button onclick="rotateCarousel(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-40 w-12 h-12 rounded-full border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center"> <i data-lucide="chevron-right" style="width:24px;height:24px;"></i> </button>
     </div>
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
   <div class="red-line w-full"></div><!-- FOOTER -->
   <footer class="w-full py-16 px-4 text-center">
    <div class="reveal">
     <div class="font-bebas text-3xl md:text-5xl mb-4" style="animation: neonPulse 3s infinite;">
      JEDEME NA JEDNO
     </div>
     <p class="font-elite text-gray-600 text-sm">Když je cesta cílem</p>
     <div class="mt-6 flex justify-center gap-6"><a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="instagram" style="width:24px;height:24px;color:#ff003c;"></i></a> <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="youtube" style="width:24px;height:24px;color:#ff003c;"></i></a> <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="glitch-hover cursor-pointer"><i data-lucide="facebook" style="width:24px;height:24px;color:#ff003c;"></i></a>
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
  instagram_url: 'https://instagram.com',
  telegram_url: 'https://t.me',
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
  const telegramLink = document.getElementById('hero-telegram');
  if (instagramLink) instagramLink.href = config.instagram_url || defaultConfig.instagram_url;
  if (telegramLink) telegramLink.href = config.telegram_url || defaultConfig.telegram_url;

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

// Video Carousel
let currentVideoIndex = 0;
const videoItems = document.querySelectorAll('.video-item');
const totalVideos = videoItems.length;

function updateCarouselPosition() {
  const carouselRadius = 220;
  const angleStep = (360 / totalVideos);
  
  videoItems.forEach((item, index) => {
    const adjustedIndex = (index - currentVideoIndex + totalVideos) % totalVideos;
    const angle = (adjustedIndex * angleStep - 90) * (Math.PI / 180);
    
    const x = Math.cos(angle) * carouselRadius;
    const y = Math.sin(angle) * carouselRadius;
    
    const isCenter = adjustedIndex === 0;
    const scale = isCenter ? 1 : 0.85;
    const zIndex = isCenter ? 50 : 40 - adjustedIndex;
    const opacity = isCenter ? 1 : 0.6;
    
    item.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px)) scale(${scale})`;
    item.style.zIndex = zIndex;
    item.style.opacity = opacity;
  });
}

function rotateCarousel(direction) {
  currentVideoIndex = (currentVideoIndex + direction + totalVideos) % totalVideos;
  updateCarouselPosition();
}

// Initialize carousel
updateCarouselPosition();

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

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
    ['telegram_url', config.telegram_url || defaultConfig.telegram_url],
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