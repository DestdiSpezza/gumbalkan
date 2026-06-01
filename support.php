<!doctype html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Podpořte nás – GUMBALKAN 2026</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Special+Elite&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { min-height: 100%; background: #000; color: #fff; font-family: 'Oswald', sans-serif; }

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite { font-family: 'Special Elite', cursive; }
.font-oswald { font-family: 'Oswald', sans-serif; }

/* Noise */
.noise {
  position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* Nav */
#nav {
  position: fixed; top: 0; left: 0; width: 100%; height: 52px; z-index: 1000;
  background: rgba(0,0,0,.88); backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,0,60,.22);
  display: flex; align-items: center; gap: 24px; padding: 0 20px;
}
.nav-link { text-decoration: none; transition: color .2s; }
.nav-link-main { font-family: 'Bebas Neue', cursive; font-size: 1.35rem; letter-spacing: .08em; color: #9ca3af; }
.nav-link-main:hover { color: #fff; }
.nav-link-sub { font-family: 'Oswald', sans-serif; font-size: .75rem; letter-spacing: .25em; text-transform: uppercase; color: #6b7280; }
.nav-link-sub:hover { color: #ff003c; }
.nav-link-active { font-family: 'Oswald', sans-serif; font-size: .75rem; letter-spacing: .25em; text-transform: uppercase; color: #ff003c; padding-bottom: 2px; border-bottom: 2px solid #ff003c; }

/* Red line */
.red-line { height: 3px; background: #ff003c; box-shadow: 0 0 15px #ff003c; }

/* Animations */
@keyframes neonPulse {
  0%,100% { text-shadow: 0 0 10px #ff003c, 0 0 20px #ff003c, 0 0 40px #ff003c; }
  50%      { text-shadow: 0 0 5px  #ff003c, 0 0 10px #ff003c; }
}
@keyframes glitchX {
  0%,100% { transform: translate(0); }
  20% { transform: translate(-3px, 2px); }
  40% { transform: translate(3px, -2px); }
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes scanline {
  0%   { top: -10%; }
  100% { top: 110%; }
}
@keyframes heartbeat {
  0%,100% { transform: scale(1); }
  50%     { transform: scale(1.04); }
}

.glitch-hover:hover { animation: glitchX .15s infinite; }

.reveal { opacity: 0; transform: translateY(50px); transition: all .8s cubic-bezier(.16,1,.3,1); }
.reveal.visible { opacity: 1; transform: translateY(0); }

/* QR box */
.qr-wrapper {
  background: #fff;
  padding: 20px;
  display: inline-block;
  position: relative;
}
.qr-wrapper::before, .qr-wrapper::after {
  content: '';
  position: absolute;
  width: 20px; height: 20px;
  border-color: #ff003c; border-style: solid;
}
.qr-wrapper::before { top: -4px; left: -4px; border-width: 3px 0 0 3px; }
.qr-wrapper::after  { bottom: -4px; right: -4px; border-width: 0 3px 3px 0; }

/* Amount pills */
.pill {
  border: 1px solid rgba(255,0,60,.3);
  padding: 10px 24px;
  font-family: 'Bebas Neue', cursive;
  font-size: 1.3rem;
  color: #9ca3af;
  cursor: pointer;
  transition: all .2s;
  background: transparent;
  letter-spacing: .05em;
}
.pill:hover, .pill.active {
  border-color: #ff003c;
  color: #fff;
  background: rgba(255,0,60,.1);
  box-shadow: 0 0 12px rgba(255,0,60,.2);
}

/* Thank you cards */
.tier-card {
  border: 1px solid rgba(255,0,60,.2);
  padding: 24px 20px;
  background: rgba(255,0,60,.03);
  transition: all .3s;
  text-align: center;
}
.tier-card:hover {
  border-color: rgba(255,0,60,.6);
  background: rgba(255,0,60,.07);
  transform: translateY(-4px);
}

/* Copy button */
.copy-btn {
  background: none; border: 1px solid rgba(255,0,60,.35);
  color: #ff003c; font-family: 'Oswald', sans-serif;
  font-size: .7rem; letter-spacing: .2em; text-transform: uppercase;
  padding: 6px 14px; cursor: pointer; transition: all .2s;
}
.copy-btn:hover { background: #ff003c; color: #fff; }
.copy-btn.copied { border-color: #10b981; color: #10b981; }
  </style>
</head>
<body>

<div class="noise"></div>

<!-- NAV -->
<nav id="nav">
  <a href="index.php"  class="nav-link nav-link-main">GUMBALKÁN</a>
  <a href="alps.php"   class="nav-link nav-link-sub">Alps</a>
  <a href="support.php" class="nav-link nav-link-active">Podpora</a>
</nav>

<div style="padding-top:52px;">

  <!-- HERO -->
  <section style="min-height:55vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:60px 20px;position:relative;background:linear-gradient(135deg,#0a0a0a 0%,#1a0008 40%,#0a0a0a 100%);">
    <!-- Diagonal slash -->
    <div style="position:absolute;top:0;right:0;width:33%;height:100%;opacity:.08;background:linear-gradient(135deg,transparent 40%,#ff003c 40%,#ff003c 42%,transparent 42%);pointer-events:none;"></div>
    <div style="position:relative;z-index:1;">
      <!-- Logo -->
      <div style="margin-bottom:32px;animation:fadeUp .8s ease-out both;">
        <img src="https://media.canva.com/v2/image-resize/format:PNG/height:1024/quality:100/uri:ifs%3A%2F%2FM%2Feee6b7ce-125b-4476-b5e/watermark:F/width:1024?csig=AAAAAAAAAAAAAAAAAAAAALWYdDQy3IVyqcWEwd5UIJYd8rXR1t5HMsA1pkP2QkK3&exp=1775724378&osig=AAAAAAAAAAAAAAAAAAAAAJc9-qw4DDXLoeOFDsU0YvoPsJ3hshg64uN6mSF6FsSb&signer=media-rpc&x-canva-quality=screen_2x"
             alt="Gumbalkán Logo" style="height:100px;margin:0 auto;display:block;" loading="lazy"
             onerror="this.style.display='none'">
      </div>
      <div class="font-oswald" style="font-size:.7rem;letter-spacing:.45em;color:#ff003c;text-transform:uppercase;margin-bottom:16px;animation:fadeUp .8s .1s both;">
        // Podpořte výpravu //
      </div>
      <h1 class="font-bebas glitch-hover" style="font-size:clamp(3rem,10vw,8rem);color:#fff;line-height:1;animation:neonPulse 3s infinite;">
        POŠLI NÁS<br><span style="color:#ff003c;">NA BALKÁN</span>
      </h1>
      <p class="font-elite" style="color:#6b7280;margin-top:20px;font-size:1.1rem;max-width:480px;margin-left:auto;margin-right:auto;line-height:1.7;animation:fadeUp .8s .3s both;">
        Čtyři blázni. Jeden vrak. Cesta do Bosny.<br>
        Každá koruna nás přibližuje ke startu — nebo aspoň zaplatí izolepou auto.
      </p>
    </div>
  </section>

  <div class="red-line"></div>

  <!-- QR + BANK INFO -->
  <section style="padding:80px 20px;background:radial-gradient(ellipse at center,#0d0005 0%,#000 70%);">
    <div style="max-width:900px;margin:0 auto;">
      <div class="reveal" style="display:flex;flex-wrap:wrap;gap:60px;align-items:flex-start;justify-content:center;">

        <!-- QR CODE -->
        <div style="text-align:center;flex-shrink:0;">
          <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:20px;">// QR Platba //</div>
          <div class="qr-wrapper">
            <div id="qr-canvas"></div>
          </div>
          <div class="font-elite" style="color:#4b5563;font-size:.8rem;margin-top:14px;">Naskenuj a pošli kolik chceš</div>
        </div>

        <!-- BANK DETAILS -->
        <div style="flex:1;min-width:260px;">
          <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:20px;">// Bankovní spojení //</div>

          <div style="margin-bottom:20px;">
            <div class="font-oswald" style="font-size:.65rem;letter-spacing:.25em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Číslo účtu</div>
            <div style="display:flex;align-items:center;gap:12px;">
              <div class="font-bebas" style="font-size:1.8rem;color:#fff;letter-spacing:.05em;" id="account-display">– – – – – – –</div>
              <button class="copy-btn" onclick="copyText('account-display', this)">KOPÍROVAT</button>
            </div>
          </div>

          <div style="margin-bottom:20px;">
            <div class="font-oswald" style="font-size:.65rem;letter-spacing:.25em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Banka</div>
            <div class="font-elite" style="color:#9ca3af;font-size:1rem;" id="bank-display">– – –</div>
          </div>

          <div style="margin-bottom:32px;">
            <div class="font-oswald" style="font-size:.65rem;letter-spacing:.25em;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">IBAN</div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
              <div class="font-oswald" style="color:#9ca3af;font-size:.95rem;letter-spacing:.05em;" id="iban-display">– – –</div>
              <button class="copy-btn" onclick="copyText('iban-display', this)">KOPÍROVAT</button>
            </div>
          </div>

          <div style="border-top:1px solid rgba(255,255,255,.06);padding-top:24px;">
            <div class="font-oswald" style="font-size:.65rem;letter-spacing:.25em;color:#6b7280;text-transform:uppercase;margin-bottom:12px;">Navrhovaná částka</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;" id="pills">
              <button class="pill" onclick="selectAmount(100, this)">100 Kč</button>
              <button class="pill" onclick="selectAmount(200, this)">200 Kč</button>
              <button class="pill" onclick="selectAmount(500, this)">500 Kč</button>
              <button class="pill" onclick="selectAmount(1000, this)">1 000 Kč</button>
            </div>
            <div class="font-elite" style="color:#374151;font-size:.8rem;margin-top:10px;font-style:italic;">nebo kolik tvé svědomí dovolí</div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <div class="red-line"></div>

  <!-- TIERS / THANK YOU -->
  <section style="padding:80px 20px;background:linear-gradient(180deg,#060003 0%,#000 100%);">
    <div style="max-width:900px;margin:0 auto;">
      <div class="reveal" style="text-align:center;margin-bottom:60px;">
        <div class="font-oswald" style="font-size:.65rem;letter-spacing:.4em;color:#ff003c;text-transform:uppercase;margin-bottom:12px;">// Za co to jde //</div>
        <h2 class="font-bebas glitch-hover" style="font-size:clamp(2.5rem,7vw,5rem);color:#fff;">CO TÍM ZAPLATÍŠ</h2>
      </div>
      <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
        <div class="tier-card">
          <div class="font-bebas" style="font-size:2.5rem;color:#ff003c;">50 Kč</div>
          <div class="font-bebas" style="font-size:1.1rem;color:#fff;margin:8px 0;">IZOLEPÁ</div>
          <div class="font-elite" style="color:#6b7280;font-size:.82rem;line-height:1.5;">Jeden metr izolepY na opravu všeho. Doslova všeho.</div>
        </div>
        <div class="tier-card">
          <div class="font-bebas" style="font-size:2.5rem;color:#ff003c;">100 Kč</div>
          <div class="font-bebas" style="font-size:1.1rem;color:#fff;margin:8px 0;">PIVO</div>
          <div class="font-elite" style="color:#6b7280;font-size:.82rem;line-height:1.5;">Jedno lokální pivo na Balkáně. Zasloužené po 800 km v vraku.</div>
        </div>
        <div class="tier-card">
          <div class="font-bebas" style="font-size:2.5rem;color:#ff003c;">300 Kč</div>
          <div class="font-bebas" style="font-size:1.1rem;color:#fff;margin:8px 0;">NAFTA</div>
          <div class="font-elite" style="color:#6b7280;font-size:.82rem;line-height:1.5;">Plná nádrž paliva někde mezi Maďarskem a Chorvatskem.</div>
        </div>
        <div class="tier-card">
          <div class="font-bebas" style="font-size:2.5rem;color:#ff003c;">500 Kč</div>
          <div class="font-bebas" style="font-size:1.1rem;color:#fff;margin:8px 0;">NOCLEH</div>
          <div class="font-elite" style="color:#6b7280;font-size:.82rem;line-height:1.5;">Jedna noc na kempu. S trochou štěstí se sprchujeme taky.</div>
        </div>
        <div class="tier-card">
          <div class="font-bebas" style="font-size:2.5rem;color:#ff003c;">1000 Kč</div>
          <div class="font-bebas" style="font-size:1.1rem;color:#fff;margin:8px 0;">LEGENDA</div>
          <div class="font-elite" style="color:#6b7280;font-size:.82rem;line-height:1.5;">Tvoje jméno v titulcích videa a věčná vděčnost celé bandy.</div>
        </div>
      </div>
    </div>
  </section>

  <div class="red-line"></div>

  <!-- OUTRO CTA -->
  <section style="padding:80px 20px;text-align:center;background:radial-gradient(ellipse at center,#0d0005 0%,#000 70%);">
    <div class="reveal" style="max-width:560px;margin:0 auto;">
      <p class="font-elite" style="color:#6b7280;font-size:1.1rem;line-height:1.8;margin-bottom:32px;">
        Každá koruna se počítá.<br>
        Každý příspěvek jde přímo na cestu —<br>
        <span style="color:#fff;">ne na ubytování v hotelu, protože hotely neexistují.</span>
      </p>
      <div style="border:2px solid rgba(255,0,60,.35);padding:28px 40px;display:inline-block;background:rgba(255,0,60,.06);">
        <div class="font-bebas" style="font-size:2.2rem;color:#fff;animation:neonPulse 3s infinite;">DÍKY. FAKT.</div>
        <div class="font-elite" style="color:#6b7280;margin-top:6px;">— EDA, MUŠKA, VYSOKÝ DAN & LUKY</div>
      </div>
      <div style="margin-top:40px;">
        <a href="index.php" class="font-oswald" style="color:#ff003c;text-decoration:none;font-size:.75rem;letter-spacing:.3em;text-transform:uppercase;border-bottom:1px solid rgba(255,0,60,.3);padding-bottom:2px;">← zpět na hlavní stránku</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer style="padding:32px 20px;text-align:center;border-top:1px solid rgba(255,255,255,.04);">
    <div class="font-oswald" style="font-size:.65rem;letter-spacing:.3em;color:#374151;text-transform:uppercase;">
      © 2026 Gumbalkán · Veškerý chaos vyhrazen
    </div>
  </footer>

</div><!-- /padding-top -->

<script>
// ─── CONFIG ─────────────────────────────────────────────────
// Sem dosaď číslo účtu, IBAN a název banky:
const BANK_CONFIG = {
  account:  '',        // např. "1234567890/0800"
  iban:     '',        // např. "CZ65 0800 0000 0012 3456 7890"
  bankName: '',        // např. "Česká spořitelna"
  recipient: 'Gumbalkán 2026',
  message:  'Podpora Gumbalkan 2026',
};

// ─── QR GENERATION ──────────────────────────────────────────
function buildQrString(amount) {
  // Czech QR Platba format (SPD)
  if (!BANK_CONFIG.iban) return 'https://gumbalkan.cz'; // fallback
  let s = `SPD*1.0*ACC:${BANK_CONFIG.iban.replace(/\s/g,'')}*`;
  if (amount) s += `AM:${amount}.00*`;
  s += `MSG:${BANK_CONFIG.message}*RN:${BANK_CONFIG.recipient}`;
  return s;
}

let currentAmount = 0;

function renderQr(amount) {
  const container = document.getElementById('qr-canvas');
  container.innerHTML = '';
  new QRCode(container, {
    text: buildQrString(amount),
    width: 200, height: 200,
    colorDark: '#000000', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M,
  });
}

function selectAmount(amount, btn) {
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  currentAmount = amount;
  renderQr(amount);
}

// ─── BANK DETAILS DISPLAY ────────────────────────────────────
function initBankDisplay() {
  if (BANK_CONFIG.account)  document.getElementById('account-display').textContent = BANK_CONFIG.account;
  if (BANK_CONFIG.iban)     document.getElementById('iban-display').textContent    = BANK_CONFIG.iban;
  if (BANK_CONFIG.bankName) document.getElementById('bank-display').textContent    = BANK_CONFIG.bankName;
}

// ─── COPY ────────────────────────────────────────────────────
function copyText(id, btn) {
  const text = document.getElementById(id).textContent.trim();
  if (text === '– – – – – – –' || text === '– – –') return;
  navigator.clipboard.writeText(text).then(() => {
    btn.textContent = 'ZKOPÍROVÁNO ✓';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'KOPÍROVAT'; btn.classList.remove('copied'); }, 2000);
  });
}

// ─── REVEAL ──────────────────────────────────────────────────
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

// ─── INIT ─────────────────────────────────────────────────────
initBankDisplay();
renderQr(0);
</script>
</body>
</html>
