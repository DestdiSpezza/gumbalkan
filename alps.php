<?php
// Photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    header('Content-Type: application/json');
    $location = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['location'] ?? '');
    if (!$location) { echo json_encode(['success' => false]); exit; }
    $uploadDir = "photos/$location/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $allowed = ['jpg','jpeg','png','webp','gif'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => true, 'file' => $uploadDir . $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'move failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'invalid']);
    }
    exit;
}

// Photo list
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['photos'])) {
    header('Content-Type: application/json');
    $location = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['photos']);
    $dir = "photos/$location/";
    $photos = [];
    if (is_dir($dir)) {
        $files = glob($dir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
        usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
        $photos = array_values($files);
    }
    echo json_encode($photos);
    exit;
}
?><!doctype html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Training Camp in the Alps – GUMBALKAN 2026</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Special+Elite&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; background: #000; color: #fff; overflow: hidden; font-family: 'Oswald', sans-serif; }

.font-bebas { font-family: 'Bebas Neue', cursive; }
.font-elite { font-family: 'Special Elite', cursive; }
.font-oswald { font-family: 'Oswald', sans-serif; }

/* ─── NOISE ────────────────────────────────────────────────── */
.noise {
  position: fixed; inset: 0; pointer-events: none; z-index: 99998; opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ─── GATE ─────────────────────────────────────────────────── */
#gate {
  position: fixed; inset: 0; z-index: 9999;
  background: #000;
  display: flex; align-items: center; justify-content: center;
  transition: opacity .6s ease;
}
.gate-inner { text-align: center; padding: 24px; }
.gate-label { font-family: 'Oswald', sans-serif; font-size: .7rem; letter-spacing: .45em; color: #ff003c; text-transform: uppercase; margin-bottom: 28px; }
.gate-title { font-family: 'Bebas Neue', cursive; font-size: clamp(3rem, 9vw, 7rem); line-height: 1; color: #fff; animation: neonPulse 3s infinite; }
.gate-title span { color: #ff003c; }
.gate-sub { font-family: 'Special Elite', cursive; color: #4b5563; font-size: 1rem; margin: 16px 0 48px; }
.gate-input {
  background: transparent; border: none; border-bottom: 2px solid #ff003c;
  color: #fff; font-family: 'Bebas Neue', cursive; font-size: 2.2rem;
  text-align: center; letter-spacing: .35em; outline: none; width: 220px; padding: 8px 4px;
}
.gate-input::placeholder { color: rgba(255,255,255,.18); }
.gate-btn {
  display: inline-block; margin-top: 28px;
  background: #ff003c; color: #fff; border: none;
  font-family: 'Oswald', sans-serif; font-size: .85rem; letter-spacing: .25em;
  text-transform: uppercase; padding: 13px 52px; cursor: pointer;
  clip-path: polygon(5% 0%,100% 0%,95% 100%,0% 100%);
  transition: background .2s, color .2s;
}
.gate-btn:hover { background: #fff; color: #000; }
.gate-error {
  font-family: 'Oswald', sans-serif; letter-spacing: .25em; color: #ff003c;
  font-size: .8rem; margin-top: 14px; opacity: 0; transition: opacity .2s;
}
.gate-error.show { opacity: 1; animation: glitchX .15s 3; }

/* ─── NAV ──────────────────────────────────────────────────── */
#nav {
  position: fixed; top: 0; left: 0; width: 100%; height: 52px; z-index: 1100;
  background: rgba(0,0,0,.88); backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,0,60,.22);
  display: flex; align-items: center; gap: 24px; padding: 0 20px;
}
.nav-link { text-decoration: none; transition: color .2s; }
.nav-link-main { font-family: 'Bebas Neue', cursive; font-size: 1.35rem; letter-spacing: .08em; color: #9ca3af; }
.nav-link-main:hover { color: #fff; }
.nav-link-active { font-family: 'Oswald', sans-serif; font-size: .75rem; letter-spacing: .28em; text-transform: uppercase; color: #ff003c; padding-bottom: 2px; border-bottom: 2px solid #ff003c; }

/* ─── SIDEBAR ──────────────────────────────────────────────── */
#sidebar {
  position: fixed; top: 52px; left: 0; bottom: 0; width: 270px; z-index: 1000;
  background: rgba(5,0,3,.92); backdrop-filter: blur(14px);
  border-right: 1px solid rgba(255,0,60,.18);
  overflow-y: auto; overflow-x: hidden;
  transition: transform .3s cubic-bezier(.16,1,.3,1);
}
#sidebar::-webkit-scrollbar { width: 3px; }
#sidebar::-webkit-scrollbar-thumb { background: rgba(255,0,60,.3); }

.sb-header { padding: 20px 18px 10px; border-bottom: 1px solid rgba(255,255,255,.05); }
.sb-header-label { font-size: .65rem; letter-spacing: .4em; color: #ff003c; text-transform: uppercase; margin-bottom: 4px; }
.sb-header-title { font-family: 'Bebas Neue', cursive; font-size: 1.6rem; color: #fff; line-height: 1.1; }

.day-block { border-bottom: 1px solid rgba(255,255,255,.04); }
.day-head {
  display: flex; align-items: center; gap: 10px; padding: 14px 18px 10px;
  cursor: pointer; transition: background .2s;
}
.day-head:hover { background: rgba(255,255,255,.03); }
.day-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.day-name { font-family: 'Bebas Neue', cursive; font-size: 1.1rem; color: #fff; flex: 1; }
.day-date { font-size: .65rem; letter-spacing: .2em; opacity: .5; }

.place-row {
  display: flex; align-items: center; gap: 10px;
  padding: 7px 18px 7px 34px; cursor: pointer;
  color: #6b7280; font-size: .78rem; letter-spacing: .05em;
  transition: color .2s, background .2s;
  position: relative;
}
.place-row::before {
  content: ''; position: absolute; left: 22px; top: 0; bottom: 0; width: 1px;
  background: rgba(255,255,255,.08);
}
.place-row:hover { color: #fff; background: rgba(255,255,255,.03); }
.place-row .pdot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.place-count { margin-left: auto; font-size: .65rem; color: #ff003c; opacity: 0; transition: opacity .2s; }
.place-row:hover .place-count { opacity: 1; }

/* ─── MAP ──────────────────────────────────────────────────── */
#map {
  position: fixed; top: 52px; left: 270px; right: 0; bottom: 0;
  z-index: 0; transition: left .3s cubic-bezier(.16,1,.3,1);
}

/* Leaflet overrides */
.leaflet-popup-content-wrapper {
  background: rgba(8,0,4,.96) !important; color: #fff !important;
  border: 1px solid rgba(255,0,60,.35) !important; border-radius: 0 !important;
  box-shadow: 0 0 30px rgba(255,0,60,.15) !important;
}
.leaflet-popup-tip-container { display: none !important; }
.leaflet-popup-close-button { color: #ff003c !important; font-size: 18px !important; top: 8px !important; right: 8px !important; }
.leaflet-control-zoom a { background: rgba(8,0,4,.9) !important; color: #ff003c !important; border-color: rgba(255,0,60,.3) !important; }
.leaflet-control-zoom a:hover { background: #ff003c !important; color: #fff !important; }

/* ─── DRAWER ───────────────────────────────────────────────── */
#drawer {
  position: fixed; left: 270px; right: 0; bottom: 0; z-index: 1050;
  background: rgba(5,0,3,.97); backdrop-filter: blur(20px);
  border-top: 2px solid rgba(255,0,60,.4);
  transform: translateY(100%); transition: transform .4s cubic-bezier(.16,1,.3,1);
  display: flex; flex-direction: column; max-height: 65vh;
}
#drawer.open { transform: translateY(0); }

.drawer-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px 12px; border-bottom: 1px solid rgba(255,255,255,.06); flex-shrink: 0;
}
.drawer-day { font-size: .65rem; letter-spacing: .3em; color: #ff003c; text-transform: uppercase; }
.drawer-name { font-family: 'Bebas Neue', cursive; font-size: 2.2rem; color: #fff; line-height: 1; }
.drawer-sub { font-size: .75rem; color: #6b7280; margin-top: 2px; }
.drawer-close {
  background: none; border: 1px solid rgba(255,0,60,.35); color: #ff003c;
  width: 38px; height: 38px; font-size: 1.1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .2s; flex-shrink: 0;
}
.drawer-close:hover { background: #ff003c; color: #fff; }

.drawer-body { flex: 1; overflow-y: auto; padding: 20px 24px; }
.drawer-body::-webkit-scrollbar { width: 3px; }
.drawer-body::-webkit-scrollbar-thumb { background: rgba(255,0,60,.3); }

/* Photo grid */
.photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 6px; margin-bottom: 20px; }
.photo-thumb {
  aspect-ratio: 1; object-fit: cover; cursor: pointer;
  filter: grayscale(.4) contrast(1.2);
  transition: all .3s; display: block; width: 100%;
}
.photo-thumb:hover { filter: none; transform: scale(1.04); box-shadow: 0 0 20px rgba(255,0,60,.3); }

.upload-zone {
  border: 2px dashed rgba(255,0,60,.3); padding: 32px 20px; text-align: center;
  cursor: pointer; transition: all .3s;
}
.upload-zone:hover, .upload-zone.dragover {
  border-color: #ff003c; background: rgba(255,0,60,.05);
}
.upload-icon { font-size: 2rem; color: rgba(255,0,60,.6); margin-bottom: 8px; }
.upload-label { font-size: .72rem; letter-spacing: .2em; color: #4b5563; text-transform: uppercase; }
.upload-progress { padding: 12px 0; font-size: .72rem; letter-spacing: .2em; color: #ff003c; display: none; }

/* Empty state */
.empty-state { font-family: 'Special Elite', cursive; color: #374151; font-size: .95rem; font-style: italic; padding: 16px 0 24px; }

/* ─── LIGHTBOX ─────────────────────────────────────────────── */
#lightbox {
  position: fixed; inset: 0; z-index: 9000;
  background: rgba(0,0,0,.97);
  display: none; align-items: center; justify-content: center;
}
#lightbox.open { display: flex; }
#lb-img { max-width: 90vw; max-height: 85vh; object-fit: contain; }
.lb-btn {
  position: absolute; background: none; border: none; color: rgba(255,255,255,.6);
  font-size: 3rem; cursor: pointer; padding: 20px; transition: color .2s; z-index: 10;
}
.lb-btn:hover { color: #ff003c; }
#lb-close { top: 16px; right: 16px; font-size: 1.8rem; }
#lb-prev { left: 0; top: 50%; transform: translateY(-50%); }
#lb-next { right: 0; top: 50%; transform: translateY(-50%); }

/* ─── HAMBURGER ────────────────────────────────────────────── */
#ham-btn {
  display: none; background: none; border: none; color: #ff003c; cursor: pointer; padding: 4px; margin-right: 8px;
}

/* ─── ANIMATIONS ───────────────────────────────────────────── */
@keyframes neonPulse {
  0%,100% { text-shadow: 0 0 10px #ff003c,0 0 20px #ff003c,0 0 40px #ff003c; }
  50% { text-shadow: 0 0 5px #ff003c,0 0 10px #ff003c; }
}
@keyframes glitchX {
  0%,100% { transform: translate(0); }
  25% { transform: translate(-4px,2px); }
  75% { transform: translate(4px,-2px); }
}
@keyframes markerPulse {
  0%,100% { box-shadow: 0 0 0 0 currentColor; }
  50% { box-shadow: 0 0 0 6px transparent; }
}

/* ─── MOBILE ───────────────────────────────────────────────── */
@media (max-width: 768px) {
  #ham-btn { display: flex; align-items: center; justify-content: center; }
  #sidebar { transform: translateX(-100%); }
  #sidebar.open { transform: translateX(0); box-shadow: 4px 0 40px rgba(0,0,0,.8); }
  #map { left: 0; }
  #drawer { left: 0; max-height: 72vh; }
  .photo-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
}
  </style>
</head>
<body>

<div class="noise"></div>

<!-- ─── GATE ─────────────────────────────────────────────────── -->
<div id="gate">
  <div class="gate-inner">
    <div class="gate-label">// Restricted access //</div>
    <h1 class="gate-title font-bebas">TRAINING CAMP<br><span>IN THE ALPS</span></h1>
    <p class="gate-sub font-elite">Gumbalkán 2026 · Tajná výprava</p>
    <div>
      <input id="gate-input" type="password" class="gate-input" placeholder="HESLO" autocomplete="off" spellcheck="false">
    </div>
    <div class="gate-error font-oswald" id="gate-error">PŘÍSTUP ODEPŘEN</div>
    <div>
      <button class="gate-btn font-oswald" onclick="checkPassword()">VSTOUPIT</button>
    </div>
  </div>
</div>

<!-- ─── APP ──────────────────────────────────────────────────── -->
<div id="app" style="display:none;">

  <nav id="nav">
    <button id="ham-btn" onclick="toggleSidebar()">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    <a href="index.php" class="nav-link nav-link-main">GUMBALKÁN</a>
    <a href="alps.php" class="nav-link nav-link-active">Training camp in the Alps</a>
    <a href="support.php" class="nav-link font-oswald" style="font-size:.75rem;letter-spacing:.25em;text-transform:uppercase;color:#6b7280;text-decoration:none;margin-left:auto;" onmouseover="this.style.color='#ff003c'" onmouseout="this.style.color='#6b7280'">❤ Podpořte nás</a>
  </nav>

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="sb-header">
      <div class="sb-header-label">// Trasa výpravy //</div>
      <div class="sb-header-title font-bebas">ALPS<br>2026</div>
    </div>
    <div id="day-list"></div>
  </aside>

  <!-- MAP -->
  <div id="map"></div>

  <!-- PHOTO DRAWER -->
  <div id="drawer">
    <div class="drawer-head">
      <div>
        <div class="drawer-day font-oswald" id="drawer-day"></div>
        <div class="drawer-name font-bebas" id="drawer-name"></div>
        <div class="drawer-sub font-oswald" id="drawer-sub"></div>
      </div>
      <button class="drawer-close" onclick="closeDrawer()">✕</button>
    </div>
    <div class="drawer-body">
      <div class="photo-grid" id="photo-grid"></div>
      <div class="upload-zone" id="upload-zone"
           onclick="document.getElementById('file-input').click()"
           ondragover="onDragOver(event)" ondrop="onDrop(event)" ondragleave="this.classList.remove('dragover')">
        <div class="upload-icon">+</div>
        <div class="upload-label font-oswald">Přidat fotky &nbsp;·&nbsp; drag &amp; drop nebo klikni</div>
      </div>
      <div class="upload-progress font-oswald" id="upload-progress">NAHRÁVÁM...</div>
      <input type="file" id="file-input" multiple accept="image/*" style="display:none;" onchange="handleFiles(this.files)">
    </div>
  </div>

  <!-- Sidebar overlay (mobile) -->
  <div id="sb-overlay" onclick="toggleSidebar()"
       style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;"></div>

</div>

<!-- LIGHTBOX -->
<div id="lightbox">
  <button class="lb-btn" id="lb-close" onclick="closeLb()">✕</button>
  <button class="lb-btn" id="lb-prev" onclick="lbMove(-1)">&#8249;</button>
  <img id="lb-img" src="" alt="">
  <button class="lb-btn" id="lb-next" onclick="lbMove(1)">&#8250;</button>
</div>

<script>
// ─── DATA ────────────────────────────────────────────────────
const DAYS = [
  {
    date: '27.5.2026', label: 'Den 1', color: '#ff003c',
    places: [
      { id: 'alternmarkt', name: 'Alternmarkt im Pongau', sub: 'Haus am Hirschberg', lat: 47.4775, lng: 13.4617 }
    ]
  },
  {
    date: '28.5.2026', label: 'Den 2', color: '#ff6b35',
    places: [
      { id: 'hallstatt',      name: 'Hallstatt',      lat: 47.5622, lng: 13.6493 },
      { id: 'gosausee',       name: 'Gosausee',        lat: 47.5347, lng: 13.5186 },
      { id: 'hoche_gosausee', name: 'Hoché Gosausee',  lat: 47.5197, lng: 13.5314 }
    ]
  },
  {
    date: '29.5.2026', label: 'Den 3', color: '#f59e0b',
    places: [
      { id: 'grossglockner', name: 'Grossglockner', lat: 47.0742, lng: 12.6947 },
      { id: 'therme_amadé',  name: 'Therme Amadé',  lat: 47.3848, lng: 13.4594 }
    ]
  },
  {
    date: '30.5.2026', label: 'Den 4', color: '#06b6d4',
    places: [
      { id: 'johanneswasserfall', name: 'Johanneswasserfall', lat: 47.2721, lng: 12.7594 },
      { id: 'filzmoos',           name: 'Filzmoos',            lat: 47.4378, lng: 13.5219 },
      { id: 'jagersee',           name: 'Jägersee',            lat: 47.3842, lng: 13.5506 }
    ]
  }
];

const PASS = 'Veslo';

// ─── GATE ────────────────────────────────────────────────────
document.getElementById('gate-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') checkPassword();
  document.getElementById('gate-error').classList.remove('show');
});

function checkPassword() {
  const val = document.getElementById('gate-input').value;
  if (val === PASS) {
    sessionStorage.setItem('alps_auth', '1');
    const gate = document.getElementById('gate');
    gate.style.opacity = '0';
    setTimeout(() => {
      gate.style.display = 'none';
      document.getElementById('app').style.display = 'block';
      initMap();
    }, 600);
  } else {
    const err = document.getElementById('gate-error');
    err.classList.add('show');
    document.getElementById('gate-input').value = '';
    setTimeout(() => err.classList.remove('show'), 1800);
  }
}

if (sessionStorage.getItem('alps_auth') === '1') {
  document.getElementById('gate').style.display = 'none';
  document.getElementById('app').style.display = 'block';
  document.addEventListener('DOMContentLoaded', initMap);
}

// ─── MAP ─────────────────────────────────────────────────────
let map;
let currentPlace = null, currentDay = null;
let lbPhotos = [], lbIndex = 0;

function initMap() {
  map = L.map('map', { zoomControl: false, attributionControl: false })
         .setView([47.32, 13.18], 9);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '©OpenStreetMap ©CARTO', subdomains: 'abcd', maxZoom: 20
  }).addTo(map);

  L.control.attribution({ prefix: false, position: 'bottomleft' })
    .addAttribution('<span style="color:#ff003c;font-size:.6rem;">©OpenStreetMap ©CARTO</span>')
    .addTo(map);

  L.control.zoom({ position: 'bottomright' }).addTo(map);

  DAYS.forEach((day, di) => {
    // Dashed route line
    if (day.places.length > 1) {
      L.polyline(day.places.map(p => [p.lat, p.lng]), {
        color: day.color, weight: 2.5, opacity: .55, dashArray: '7 7'
      }).addTo(map);
    }

    day.places.forEach((place, pi) => {
      const label = pi === 0 ? String(di + 1) : `${di + 1}${String.fromCharCode(96 + pi)}`;
      const icon = L.divIcon({
        className: '',
        html: `<div class="map-pin" style="
          width:38px;height:38px;border-radius:50%;
          background:rgba(4,0,2,.9);
          border:2px solid ${day.color};
          box-shadow:0 0 14px ${day.color}88,0 0 0 0 ${day.color}44;
          display:flex;align-items:center;justify-content:center;
          font-family:'Bebas Neue',cursive;font-size:14px;color:${day.color};
          cursor:pointer;transition:transform .15s,box-shadow .15s;
          animation:markerPulse 2.5s infinite;
        " onmouseover="this.style.transform='scale(1.35)';this.style.boxShadow='0 0 24px ${day.color}cc'"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 0 14px ${day.color}88'">${label}</div>`,
        iconSize: [38, 38], iconAnchor: [19, 19]
      });

      const marker = L.marker([place.lat, place.lng], { icon }).addTo(map);
      marker.on('click', () => {
        map.flyTo([place.lat, place.lng], 13, { duration: .9 });
        openDrawer(place, day);
      });
    });
  });

  buildSidebar();
}

// ─── SIDEBAR ─────────────────────────────────────────────────
function buildSidebar() {
  const container = document.getElementById('day-list');
  DAYS.forEach((day, di) => {
    const block = document.createElement('div');
    block.className = 'day-block';

    const head = document.createElement('div');
    head.className = 'day-head';
    head.innerHTML = `
      <span class="day-dot" style="background:${day.color};box-shadow:0 0 6px ${day.color};"></span>
      <span class="day-name font-bebas">${day.label}</span>
      <span class="day-date font-oswald">${day.date}</span>
    `;
    head.onclick = () => flyToDay(di);
    block.appendChild(head);

    day.places.forEach(place => {
      const row = document.createElement('div');
      row.className = 'place-row font-oswald';
      row.innerHTML = `
        <span class="pdot" style="background:${day.color};"></span>
        <span>${place.name}</span>
        <span class="place-count" id="cnt-${place.id}"></span>
      `;
      row.onclick = () => {
        map.flyTo([place.lat, place.lng], 13, { duration: .9 });
        openDrawer(place, day);
        if (window.innerWidth <= 768) toggleSidebar(false);
      };
      block.appendChild(row);
    });

    container.appendChild(block);
  });

  // Load photo counts for badges
  DAYS.forEach(day => day.places.forEach(p => fetchCount(p.id)));
}

function fetchCount(id) {
  fetch(`alps.php?photos=${id}`)
    .then(r => r.json())
    .then(photos => {
      const el = document.getElementById(`cnt-${id}`);
      if (el && photos.length) el.textContent = photos.length;
    }).catch(() => {});
}

function flyToDay(di) {
  const day = DAYS[di];
  const bounds = L.latLngBounds(day.places.map(p => [p.lat, p.lng]));
  map.flyToBounds(bounds, { padding: [80, 80], duration: 1.2 });
  if (window.innerWidth <= 768) toggleSidebar(false);
}

// ─── DRAWER ──────────────────────────────────────────────────
function openDrawer(place, day) {
  currentPlace = place;
  currentDay = day;
  document.getElementById('drawer-day').textContent = `${day.label} · ${day.date}`;
  document.getElementById('drawer-name').textContent = place.name;
  document.getElementById('drawer-sub').textContent = place.sub || '';
  document.getElementById('drawer').classList.add('open');
  loadPhotos(place.id);
}

function closeDrawer() {
  document.getElementById('drawer').classList.remove('open');
  currentPlace = null;
}

// ─── PHOTOS ──────────────────────────────────────────────────
function loadPhotos(id) {
  const grid = document.getElementById('photo-grid');
  grid.innerHTML = '<div class="empty-state">Načítám...</div>';
  fetch(`alps.php?photos=${id}`)
    .then(r => r.json())
    .then(photos => renderPhotos(photos))
    .catch(() => { grid.innerHTML = ''; });
}

function renderPhotos(photos) {
  const grid = document.getElementById('photo-grid');
  lbPhotos = photos;
  if (!photos.length) {
    grid.innerHTML = '<div class="empty-state">Žádné fotky zatím. Přidej první!</div>';
    return;
  }
  grid.innerHTML = photos.map((src, i) =>
    `<img src="${src}" class="photo-thumb" alt="" onclick="openLb(${i})" loading="lazy">`
  ).join('');
}

function handleFiles(files) {
  if (!currentPlace) return;
  const prog = document.getElementById('upload-progress');
  prog.style.display = 'block';
  const uploads = Array.from(files).map(file => {
    const fd = new FormData();
    fd.append('photo', file);
    fd.append('location', currentPlace.id);
    return fetch('alps.php', { method: 'POST', body: fd });
  });
  Promise.all(uploads).then(() => {
    prog.style.display = 'none';
    document.getElementById('file-input').value = '';
    loadPhotos(currentPlace.id);
    fetchCount(currentPlace.id);
  });
}

function onDragOver(e) { e.preventDefault(); document.getElementById('upload-zone').classList.add('dragover'); }
function onDrop(e) {
  e.preventDefault();
  document.getElementById('upload-zone').classList.remove('dragover');
  handleFiles(e.dataTransfer.files);
}

// ─── LIGHTBOX ────────────────────────────────────────────────
function openLb(i) {
  lbIndex = i;
  document.getElementById('lb-img').src = lbPhotos[i];
  document.getElementById('lightbox').classList.add('open');
}
function closeLb() { document.getElementById('lightbox').classList.remove('open'); }
function lbMove(dir) {
  lbIndex = (lbIndex + dir + lbPhotos.length) % lbPhotos.length;
  document.getElementById('lb-img').src = lbPhotos[lbIndex];
}

document.addEventListener('keydown', e => {
  if (document.getElementById('lightbox').classList.contains('open')) {
    if (e.key === 'ArrowLeft') lbMove(-1);
    if (e.key === 'ArrowRight') lbMove(1);
    if (e.key === 'Escape') closeLb();
  } else if (document.getElementById('drawer').classList.contains('open')) {
    if (e.key === 'Escape') closeDrawer();
  }
});

// ─── SIDEBAR TOGGLE ──────────────────────────────────────────
function toggleSidebar(force) {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sb-overlay');
  const open = force !== undefined ? force : !sb.classList.contains('open');
  sb.classList.toggle('open', open);
  ov.style.display = open ? 'block' : 'none';
}
</script>
</body>
</html>
