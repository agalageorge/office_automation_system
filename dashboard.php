
<?php
// ── Session Guard ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/Auth.php';

Auth::startSession();

// Redirect to login if not authenticated
if (!Auth::check()) {
    Auth::setFlash('error', 'You must be logged in to access this page.');
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}

// Optional: restrict to ICT Officer role only
// if (!Auth::hasRole('ict_officer')) {
//     header('Location: ' . BASE_URL . '/modules/auth/login.php');
//     exit;
// }

// Session timeout — log out after 30 minutes of inactivity
$timeout = 30 * 60; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    Auth::logout();
    Auth::setFlash('error', 'Your session expired. Please sign in again.');
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}
$_SESSION['last_activity'] = time();

$currentUser = Auth::user(); // make user data available to the page
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ICT Officer Portal — DVC Office Automation System</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
/* ═══════════════════════════════════════════════════════
   DESIGN SYSTEM — Industrial/Technical aesthetic
   Dark navy + Electric cyan + Mono typography
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:        #0a0f1a;
  --ink2:       #0e1628;
  --ink3:       #141e33;
  --ink4:       #1c2a45;
  --panel:      #111827;
  --panel2:     #162035;

  --cyan:       #00d4ff;
  --cyan-dim:   rgba(0,212,255,.15);
  --cyan-glow:  0 0 20px rgba(0,212,255,.25);

  --green:      #22c55e;
  --green-dim:  rgba(34,197,94,.12);
  --amber:      #f59e0b;
  --amber-dim:  rgba(245,158,11,.12);
  --red:        #ef4444;
  --red-dim:    rgba(239,68,68,.12);
  --purple:     #a78bfa;
  --purple-dim: rgba(167,139,250,.12);

  --border:     rgba(255,255,255,.07);
  --border2:    rgba(0,212,255,.2);

  --t1:  #f0f4ff;
  --t2:  #8899b8;
  --t3:  #4a5a78;

  --sb-w:     240px;
  --top-h:    56px;
  --r:        10px;
  --r-sm:     7px;

  --font-head: 'Syne', sans-serif;
  --font-body: 'DM Sans', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
}

html { scroll-behavior: smooth; }
body {
  margin: 5px;
  font-family: var(--font-body);
  background: var(--ink);
  color: var(--t1);
  font-size: 14px;
  line-height: 1.5;
  overflow-x: hidden;
}

/* ── NOISE TEXTURE ── */
body::before {
  content: '';
  position: fixed; inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events: none; z-index: 0;
}

/* ═══════════ SCROLLBAR ═══════════ */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: var(--ink2); }
::-webkit-scrollbar-thumb { background: var(--ink4); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--cyan-dim); }

/* ═══════════ LAYOUT ═══════════ */
.shell { display: flex; min-height: 100vh; position: relative; z-index: 1; }

/* ═══════════ SIDEBAR ═══════════ */
.sidebar {
  width: var(--sb-w);
  background: var(--ink2);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  position: fixed; top: 0; left: 0; height: 100vh;
  z-index: 200;
  transition: transform .3s cubic-bezier(.4,0,.2,1);
}

/* Grid pattern on sidebar */
.sidebar::before {
  content: '';
  position: absolute; inset: 0;
  background-image:
    linear-gradient(var(--border) 1px, transparent 1px),
    linear-gradient(90deg, var(--border) 1px, transparent 1px);
  background-size: 24px 24px;
  opacity: 0.4;
  pointer-events: none;
}

.sb-brand {
  padding: 20px 18px 16px;
  border-bottom: 1px solid var(--border);
  position: relative;
  flex-shrink: 0;
}
.sb-brand-top {
  display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
}
.sb-icon {
  width: 36px; height: 36px;
  background: var(--cyan);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  position: relative;
}
.sb-icon::after {
  content: '';
  position: absolute; inset: -2px;
  border-radius: 10px;
  border: 1px solid rgba(0,212,255,.4);
}
.sb-icon i { font-size: 18px; color: #000; }
.sb-title { font-family: var(--font-head); font-size: 13px; font-weight: 700; color: var(--t1); line-height: 1.2; }
.sb-title small { display: block; font-size: 9px; color: var(--t3); font-family: var(--font-mono); font-weight: 400; letter-spacing: .04em; margin-top: 1px; }

/* Officer card */
.sb-officer {
  background: var(--cyan-dim);
  border: 1px solid var(--border2);
  border-radius: var(--r-sm);
  padding: 10px 12px;
  display: flex; align-items: center; gap: 10px;
}
.sb-officer-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, #00d4ff, #0066ff);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-head); font-size: 12px; font-weight: 700; color: #000;
  flex-shrink: 0;
}
.sb-officer-name { font-size: 12px; font-weight: 600; color: var(--t1); }
.sb-officer-role { font-size: 10px; color: var(--cyan); font-family: var(--font-mono); }
.sb-status-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--green);
  margin-left: auto; flex-shrink: 0;
  box-shadow: 0 0 6px var(--green);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%,100% { opacity: 1; } 50% { opacity: .4; }
}

/* Nav */
.sb-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
.sb-section {
  padding: 10px 18px 4px;
  font-size: 9px; letter-spacing: .12em; text-transform: uppercase;
  color: var(--t3); font-family: var(--font-mono);
}
.sb-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 18px;
  color: var(--t2); font-size: 13px; cursor: pointer;
  border-left: 2px solid transparent;
  transition: all .15s;
  text-decoration: none;
  position: relative;
}
.sb-item:hover { background: rgba(255,255,255,.03); color: var(--t1); }
.sb-item.active {
  background: var(--cyan-dim);
  color: var(--cyan);
  border-left-color: var(--cyan);
}
.sb-item i { font-size: 17px; width: 20px; text-align: center; }
.sb-badge {
  margin-left: auto;
  background: var(--red); color: #fff;
  font-size: 9px; padding: 1px 6px; border-radius: 10px;
  font-family: var(--font-mono);
}
.sb-badge.cyan { background: var(--cyan); color: #000; }

.sb-footer {
  padding: 14px 18px;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}
.sb-footer-link {
  display: flex; align-items: center; gap: 8px;
  color: var(--t3); font-size: 12px; cursor: pointer;
  transition: color .15s;
}
.sb-footer-link:hover { color: var(--red); }
.sb-footer-link i { font-size: 16px; }

/* ═══════════ MAIN ═══════════ */
.main {
  flex: 1;
  margin-left: var(--sb-w);
  display: flex; flex-direction: column;
  min-height: 100vh;
}

/* ── TOPBAR ── */
.topbar {
  position: sticky; top: 0; z-index: 100;
  height: var(--top-h);
  background: rgba(10,15,26,.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center;
  padding: 0 24px; gap: 14px;
}
.topbar-hamburger {
  display: none; background: none; border: none; cursor: pointer;
  color: var(--t2); font-size: 20px;
}
.topbar-breadcrumb {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; color: var(--t3); font-family: var(--font-mono);
}
.topbar-breadcrumb span { color: var(--cyan); }
.topbar-spacer { flex: 1; }
.topbar-search {
  display: flex; align-items: center; gap: 8px;
  background: var(--ink3); border: 1px solid var(--border);
  border-radius: var(--r-sm); padding: 7px 12px;
  width: 220px; transition: border-color .2s;
}
.topbar-search:focus-within { border-color: var(--border2); }
.topbar-search i { font-size: 15px; color: var(--t3); }
.topbar-search input {
  background: none; border: none; outline: none;
  font-family: var(--font-mono); font-size: 12px; color: var(--t1);
  width: 100%;
}
.topbar-search input::placeholder { color: var(--t3); }

.tb-actions { display: flex; align-items: center; gap: 6px; }
.tb-btn {
  width: 34px; height: 34px;
  background: var(--ink3); border: 1px solid var(--border);
  border-radius: var(--r-sm);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--t2); position: relative;
  transition: all .15s;
}
.tb-btn:hover { border-color: var(--border2); color: var(--cyan); }
.tb-btn i { font-size: 17px; }
.tb-notif { position: absolute; top: 6px; right: 6px; width: 6px; height: 6px; border-radius: 50%; background: var(--red); }

/* ── System status bar ── */
.status-bar {
  background: var(--ink2);
  border-bottom: 1px solid var(--border);
  padding: 7px 24px;
  display: flex; align-items: center; gap: 20px;
  font-family: var(--font-mono); font-size: 11px; color: var(--t3);
  overflow-x: auto;
}
.status-item { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.s-online  { background: var(--green); box-shadow: 0 0 5px var(--green); }
.s-warn    { background: var(--amber); box-shadow: 0 0 5px var(--amber); }
.s-offline { background: var(--red); box-shadow: 0 0 5px var(--red); }
.status-bar-label { color: var(--t1); }

/* ═══════════ CONTENT ═══════════ */
.content { padding: 24px; flex: 1; }

/* Page header */
.page-header { margin-bottom: 24px; }
.page-header h1 {
  font-family: var(--font-head);
  font-size: 26px; font-weight: 800;
  color: var(--t1);
  letter-spacing: -.02em;
}
.page-header h1 span { color: var(--cyan); }
.page-header p { font-size: 13px; color: var(--t2); margin-top: 4px; }
.page-header-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }

/* Section title */
.section-title {
  font-family: var(--font-head);
  font-size: 13px; font-weight: 700;
  color: var(--t1); letter-spacing: .04em; text-transform: uppercase;
  display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
}
.section-title::after {
  content: ''; flex: 1;
  height: 1px; background: var(--border);
}

/* ═══════════ CARDS ═══════════ */
.card {
  background: var(--ink2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
  position: relative;
}
.card-head {
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.card-head h2 {
  font-family: var(--font-head);
  font-size: 13px; font-weight: 700;
  color: var(--t1); letter-spacing: .02em;
  display: flex; align-items: center; gap: 8px;
}
.card-head h2 i { font-size: 16px; color: var(--cyan); }
.card-body { padding: 18px; }

/* ═══════════ STAT CARDS ═══════════ */
.stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 20px; }

.stat-card {
  background: var(--ink2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 16px;
  position: relative; overflow: hidden;
  transition: border-color .2s, transform .2s;
  cursor: default;
}
.stat-card:hover { transform: translateY(-2px); }

.stat-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
}
.sc-cyan::before   { background: linear-gradient(90deg, var(--cyan), transparent); }
.sc-green::before  { background: linear-gradient(90deg, var(--green), transparent); }
.sc-amber::before  { background: linear-gradient(90deg, var(--amber), transparent); }
.sc-red::before    { background: linear-gradient(90deg, var(--red), transparent); }
.sc-purple::before { background: linear-gradient(90deg, var(--purple), transparent); }

.stat-card:hover.sc-cyan   { border-color: rgba(0,212,255,.3); }
.stat-card:hover.sc-green  { border-color: rgba(34,197,94,.3); }
.stat-card:hover.sc-amber  { border-color: rgba(245,158,11,.3); }
.stat-card:hover.sc-red    { border-color: rgba(239,68,68,.3); }
.stat-card:hover.sc-purple { border-color: rgba(167,139,250,.3); }

.stat-icon {
  width: 38px; height: 38px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 12px;
}
.stat-icon i { font-size: 20px; }
.ic-cyan   { background: var(--cyan-dim);   color: var(--cyan); }
.ic-green  { background: var(--green-dim);  color: var(--green); }
.ic-amber  { background: var(--amber-dim);  color: var(--amber); }
.ic-red    { background: var(--red-dim);    color: var(--red); }
.ic-purple { background: var(--purple-dim); color: var(--purple); }

.stat-val  { font-family: var(--font-mono); font-size: 28px; font-weight: 500; color: var(--t1); line-height: 1; }
.stat-lbl  { font-size: 12px; color: var(--t2); margin-top: 6px; }
.stat-chip {
  display: inline-flex; align-items: center; gap: 3px;
  font-size: 10px; font-family: var(--font-mono);
  padding: 2px 7px; border-radius: 20px; margin-top: 8px;
}
.chip-up   { background: var(--green-dim); color: var(--green); }
.chip-warn { background: var(--amber-dim); color: var(--amber); }
.chip-down { background: var(--red-dim);   color: var(--red); }

/* ═══════════ GRIDS ═══════════ */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
.grid-3-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }

/* ═══════════ TABLES ═══════════ */
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th {
  text-align: left; padding: 10px 14px;
  font-size: 10px; font-weight: 600; color: var(--t3);
  background: var(--ink3);
  border-bottom: 1px solid var(--border);
  font-family: var(--font-mono); letter-spacing: .06em; text-transform: uppercase;
  white-space: nowrap;
}
.data-table td {
  padding: 11px 14px;
  border-bottom: 1px solid var(--border);
  color: var(--t1); vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover td { background: rgba(255,255,255,.02); }

/* ═══════════ BADGES ═══════════ */
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 10px; font-family: var(--font-mono); font-weight: 500;
  padding: 3px 8px; border-radius: 20px;
}
.b-cyan   { background: var(--cyan-dim);   color: var(--cyan);   border: 1px solid rgba(0,212,255,.2); }
.b-green  { background: var(--green-dim);  color: var(--green);  border: 1px solid rgba(34,197,94,.2); }
.b-amber  { background: var(--amber-dim);  color: var(--amber);  border: 1px solid rgba(245,158,11,.2); }
.b-red    { background: var(--red-dim);    color: var(--red);    border: 1px solid rgba(239,68,68,.2); }
.b-purple { background: var(--purple-dim); color: var(--purple); border: 1px solid rgba(167,139,250,.2); }
.b-gray   { background: rgba(255,255,255,.05); color: var(--t2); border: 1px solid var(--border); }

/* ═══════════ BUTTONS ═══════════ */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: var(--r-sm);
  font-family: var(--font-body); font-size: 13px; font-weight: 500;
  cursor: pointer; border: none; transition: all .15s;
}
.btn i { font-size: 16px; }
.btn:active { transform: scale(.98); }

.btn-cyan    { background: var(--cyan); color: #000; }
.btn-cyan:hover { background: #33ddff; }
.btn-ghost   { background: rgba(255,255,255,.05); color: var(--t1); border: 1px solid var(--border); }
.btn-ghost:hover { border-color: var(--border2); color: var(--cyan); }
.btn-danger  { background: var(--red-dim); color: var(--red); border: 1px solid rgba(239,68,68,.2); }
.btn-danger:hover { background: rgba(239,68,68,.2); }
.btn-sm { padding: 6px 11px; font-size: 12px; }
.btn-sm i { font-size: 14px; }
.btn-icon {
  width: 30px; height: 30px; padding: 0;
  display: inline-flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,.04); border: 1px solid var(--border);
  border-radius: 6px; cursor: pointer; color: var(--t2);
  transition: all .15s;
}
.btn-icon:hover { border-color: var(--border2); color: var(--cyan); }
.btn-icon i { font-size: 15px; }

/* ═══════════ PROGRESS BAR ═══════════ */
.prog-bar {
  height: 6px; background: var(--ink4); border-radius: 3px; overflow: hidden; margin-top: 6px;
}
.prog-fill {
  height: 100%; border-radius: 3px;
  transition: width 1s ease;
}
.pf-cyan   { background: linear-gradient(90deg, var(--cyan), #0066ff); }
.pf-green  { background: linear-gradient(90deg, var(--green), #16a34a); }
.pf-amber  { background: linear-gradient(90deg, var(--amber), #d97706); }
.pf-red    { background: linear-gradient(90deg, var(--red), #dc2626); }

/* ═══════════ MODALS ═══════════ */
.modal-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.7);
  backdrop-filter: blur(4px);
  z-index: 500;
  display: none; align-items: center; justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal {
  background: var(--ink2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  width: 520px; max-width: 95vw;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,.5);
  animation: modal-in .2s ease;
}
@keyframes modal-in { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.modal-head {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-head h2 {
  font-family: var(--font-head); font-size: 15px; font-weight: 700;
  color: var(--t1);
}
.modal-close { cursor: pointer; color: var(--t3); font-size: 20px; transition: color .15s; }
.modal-close:hover { color: var(--red); }
.modal-body { padding: 20px; }
.modal-foot {
  padding: 14px 20px;
  border-top: 1px solid var(--border);
  display: flex; justify-content: flex-end; gap: 8px;
}

/* ═══════════ FORMS ═══════════ */
.form-group { margin-bottom: 16px; }
.form-group label {
  display: block; font-size: 11px; font-weight: 600;
  color: var(--t2); margin-bottom: 6px;
  font-family: var(--font-mono); text-transform: uppercase; letter-spacing: .06em;
}
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%; padding: 9px 12px;
  background: var(--ink3); border: 1px solid var(--border);
  border-radius: var(--r-sm);
  font-family: var(--font-body); font-size: 13px; color: var(--t1);
  outline: none; transition: border-color .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--border2);
  box-shadow: 0 0 0 3px rgba(0,212,255,.06);
}
.form-group input::placeholder,
.form-group textarea::placeholder { color: var(--t3); }
.form-group select option { background: var(--ink2); }
.form-group textarea { resize: vertical; min-height: 80px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ═══════════ ALERTS ═══════════ */
.alert {
  padding: 12px 16px; border-radius: var(--r-sm);
  font-size: 13px; display: flex; align-items: flex-start; gap: 10px;
  margin-bottom: 16px;
}
.alert i { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
.alert-red    { background: var(--red-dim);   color: #fca5a5; border: 1px solid rgba(239,68,68,.2); }
.alert-amber  { background: var(--amber-dim); color: #fcd34d; border: 1px solid rgba(245,158,11,.2); }
.alert-cyan   { background: var(--cyan-dim);  color: var(--cyan); border: 1px solid rgba(0,212,255,.2); }
.alert-green  { background: var(--green-dim); color: #86efac;  border: 1px solid rgba(34,197,94,.2); }

/* ═══════════ TABS ═══════════ */
.tabs { display: flex; gap: 2px; border-bottom: 1px solid var(--border); margin-bottom: 18px; }
.tab-btn {
  padding: 10px 16px; font-size: 13px; font-weight: 500;
  color: var(--t2); cursor: pointer; border: none; background: none;
  border-bottom: 2px solid transparent; margin-bottom: -1px;
  transition: all .15s; font-family: var(--font-body);
}
.tab-btn:hover { color: var(--t1); }
.tab-btn.active { color: var(--cyan); border-bottom-color: var(--cyan); }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ═══════════ TOGGLE ═══════════ */
.toggle { position: relative; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.toggle input { display: none; }
.toggle-track {
  width: 36px; height: 20px; border-radius: 10px;
  background: var(--ink4); border: 1px solid var(--border);
  transition: background .2s;
  position: relative;
}
.toggle input:checked + .toggle-track { background: var(--cyan); border-color: var(--cyan); }
.toggle-track::after {
  content: '';
  position: absolute; top: 2px; left: 2px;
  width: 14px; height: 14px; border-radius: 50%;
  background: var(--t2); transition: transform .2s, background .2s;
}
.toggle input:checked + .toggle-track::after { transform: translateX(16px); background: #000; }
.toggle-label { font-size: 13px; color: var(--t1); }

/* ═══════════ LIST ITEM ═══════════ */
.list-item {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 0; border-bottom: 1px solid var(--border);
}
.list-item:last-child { border-bottom: none; }
.list-icon {
  width: 36px; height: 36px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.list-icon i { font-size: 18px; }
.list-info { flex: 1; min-width: 0; }
.list-title { font-size: 13px; font-weight: 500; color: var(--t1); }
.list-sub   { font-size: 11px; color: var(--t2); margin-top: 2px; font-family: var(--font-mono); }
.list-meta  { font-size: 11px; color: var(--t3); text-align: right; white-space: nowrap; }

/* ═══════════ MINI CHART (CSS bars) ═══════════ */
.mini-chart { display: flex; align-items: flex-end; gap: 4px; height: 48px; }
.mini-bar { flex: 1; border-radius: 3px 3px 0 0; transition: opacity .2s; cursor: pointer; }
.mini-bar:hover { opacity: .7; }

/* ═══════════ SERVER CARD ═══════════ */
.server-card {
  background: var(--ink3); border: 1px solid var(--border); border-radius: var(--r-sm);
  padding: 14px 16px;
}
.server-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.server-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.server-name { font-size: 13px; font-weight: 600; font-family: var(--font-mono); color: var(--t1); }
.server-ip   { font-size: 10px; color: var(--t3); font-family: var(--font-mono); margin-left: auto; }
.server-metrics { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
.server-metric { }
.sm-label { font-size: 9px; color: var(--t3); font-family: var(--font-mono); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px; }
.sm-val   { font-size: 14px; font-weight: 600; font-family: var(--font-mono); }

/* ═══════════ AVATAR ═══════════ */
.user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-head); font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.sb-footer-link i{
  font-size: 16px;
  color: #8899B8;
  margin-left: 7px;

}
.sb-footer-link a{
  font-size: 13px;
  color: #8899B8;
  text-decoration: none;
}
.sb-footer-link a:hover{
  color: white;
}
.sb-footer-link:hover{
  background: rgba(255,255,255,.03); color: var(--t1);
  padding-top: 2px;

}
/* ═══════════ MOBILE OVERLAY ═══════════ */
.sb-overlay {
  display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6);
  z-index: 199;
}

/* ═══════════ RESPONSIVE ═══════════ */
@media (max-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(2,1fr); }
  .grid-3 { grid-template-columns: 1fr 1fr; }
  .grid-3-1 { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.open { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,.5); }
  .sb-overlay.visible { display: block; }
  .main { margin-left: 0; }
  .topbar-hamburger { display: block; }
  .topbar-search { display: none; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .grid-2 { grid-template-columns: 1fr; }
  .grid-3 { grid-template-columns: 1fr; }
  .form-grid-2 { grid-template-columns: 1fr; }
  .content { padding: 16px; }
  .status-bar { gap: 12px; }
}
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="shell">

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sb-brand">
    <div class="sb-brand-top">
      <div class="sb-icon"><img src="../../images/uoa_logo.png" height="100%" width="100%"></i></div>
      <div class="sb-title">DVC · ICT Portal <small>OFFICER CONSOLE v1.0</small></div>
    </div>
    <div class="sb-officer">
      <div class="sb-officer-avatar">IO</div>
      <div>
        <div class="sb-officer-name">I. Obiora</div>
        <div class="sb-officer-role">ICT_OFFICER</div>
      </div>
      <div class="sb-status-dot" title="Online"></div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-section">Overview</div>
    <a href="#" class="sb-item active" onclick="showSection('dashboard',this)">
      <i class="ti ti-layout-dashboard"></i> Dashboard
    </a>

    <div class="sb-section">System Management</div>
    <a href="#" class="sb-item" onclick="showSection('users',this)">
      <i class="ti ti-users-group"></i> User Management <span class="sb-badge">3</span>
    </a>
    <a href="#" class="sb-item" onclick="showSection('servers',this)">
      <i class="ti ti-server"></i> Servers &amp; Network
    </a>
    <a href="#" class="sb-item" onclick="showSection('security',this)">
      <i class="ti ti-shield-lock"></i> Security &amp; Access
    </a>
    <a href="#" class="sb-item" onclick="showSection('backups',this)">
      <i class="ti ti-database-backup"></i> Backups &amp; Data
    </a>

    <div class="sb-section">Support</div>
    <a href="#" class="sb-item" onclick="showSection('tickets',this)">
      <i class="ti ti-headset"></i> Support Tickets <span class="sb-badge">5</span>
    </a>
    <a href="#" class="sb-item" onclick="showSection('audit',this)">
      <i class="ti ti-file-search"></i> Audit Logs
    </a>
    <a href="#" class="sb-item" onclick="showSection('reports',this)">
      <i class="ti ti-chart-bar"></i> Reports
    </a>

    <div class="sb-section">Configuration</div>
    <a href="#" class="sb-item" onclick="showSection('settings',this)">
      <i class="ti ti-settings"></i> System Settings
    </a>
    <a href="#" class="sb-item" onclick="showSection('notifications',this)">
      <i class="ti ti-bell-ringing"></i> Notifications <span class="sb-badge cyan">7</span>
    </a>
  </nav>

  <div class="sb-footer">
    <div class="sb-footer-link"><i class="ti ti-logout"></i><a href="<?= BASE_URL ?>/modules/auth/logout.php">Sign out</a></div>
  </div>
</aside>

<!-- Mobile overlay -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="topbar-hamburger" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
    <div class="topbar-breadcrumb">
      DVC_SYSTEM / <span id="breadcrumb">DASHBOARD</span>
    </div>
    <div class="topbar-spacer"></div>
    <div class="topbar-search">
      <i class="ti ti-search"></i>
      <input type="text" placeholder="Search users, logs, tickets...">
    </div>
    <div class="tb-actions">
      <div class="tb-btn" title="Notifications" onclick="showSection('notifications', null)">
        <i class="ti ti-bell"></i>
        <div class="tb-notif"></div>
      </div>
      <div class="tb-btn" title="Terminal"><i class="ti ti-terminal-2"></i></div>
      <div class="tb-btn" title="Refresh" onclick="animateRefresh(this)"><i class="ti ti-refresh"></i></div>
    </div>
  </header>

  <!-- SYSTEM STATUS BAR -->
  <div class="status-bar">
    <div class="status-item"><div class="status-dot s-online"></div><span class="status-bar-label">Web Server</span> ONLINE</div>
    <div class="status-item"><div class="status-dot s-online"></div><span class="status-bar-label">Database</span> ONLINE</div>
    <div class="status-item"><div class="status-dot s-warn"></div><span class="status-bar-label">Backup Server</span> DEGRADED</div>
    <div class="status-item"><div class="status-dot s-online"></div><span class="status-bar-label">Email Gateway</span> ONLINE</div>
    <div class="status-item"><div class="status-dot s-online"></div><span class="status-bar-label">SMS Gateway</span> ONLINE</div>
    <div class="status-item"><div class="status-dot s-online"></div><span class="status-bar-label">Biometric Device</span> ONLINE</div>
    <div class="status-item" style="margin-left:auto;color:var(--cyan);font-size:10px" id="clock"></div>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <!-- ══════ DASHBOARD ══════ -->
    <div id="sec-dashboard" class="section">
      <div class="page-header-row">
        <div class="page-header">
          <h1>System <span>Dashboard</span></h1>
          <p>ICT Officer console — University of Nigeria, DVC Office</p>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-ghost btn-sm"><i class="ti ti-download"></i> Export</button>
          <button class="btn btn-cyan btn-sm" onclick="showSection('users',null);openModal('addUserModal')">
            <i class="ti ti-user-plus"></i> Add User
          </button>
        </div>
      </div>

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card sc-cyan">
          <div class="stat-icon ic-cyan"><i class="ti ti-users"></i></div>
          <div class="stat-val">47</div>
          <div class="stat-lbl">Total system users</div>
          <div class="stat-chip chip-up"><i class="ti ti-arrow-up" style="font-size:9px"></i> +3 this month</div>
        </div>
        <div class="stat-card sc-green">
          <div class="stat-icon ic-green"><i class="ti ti-circle-check"></i></div>
          <div class="stat-val">44</div>
          <div class="stat-lbl">Active accounts</div>
          <div class="stat-chip chip-up">93.6% active rate</div>
        </div>
        <div class="stat-card sc-red">
          <div class="stat-icon ic-red"><i class="ti ti-headset"></i></div>
          <div class="stat-val">5</div>
          <div class="stat-lbl">Open support tickets</div>
          <div class="stat-chip chip-warn">2 high priority</div>
        </div>
        <div class="stat-card sc-amber">
          <div class="stat-icon ic-amber"><i class="ti ti-database-backup"></i></div>
          <div class="stat-val">98<span style="font-size:16px">%</span></div>
          <div class="stat-lbl">Last backup health</div>
          <div class="stat-chip chip-warn">Backup server degraded</div>
        </div>
      </div>

      <div class="grid-3-1" style="margin-bottom:20px">
        <!-- Network + Server Activity -->
        <div>
          <div class="grid-2" style="margin-bottom:16px">
            <div class="server-card">
              <div class="server-header">
                <div class="server-dot s-online"></div>
                <div class="server-name">WEB-01</div>
                <div class="server-ip">192.168.1.10</div>
              </div>
              <div class="server-metrics">
                <div class="server-metric">
                  <div class="sm-label">CPU</div>
                  <div class="sm-val" style="color:var(--cyan)">34%</div>
                  <div class="prog-bar"><div class="prog-fill pf-cyan" style="width:34%"></div></div>
                </div>
                <div class="server-metric">
                  <div class="sm-label">RAM</div>
                  <div class="sm-val" style="color:var(--amber)">71%</div>
                  <div class="prog-bar"><div class="prog-fill pf-amber" style="width:71%"></div></div>
                </div>
                <div class="server-metric">
                  <div class="sm-label">DISK</div>
                  <div class="sm-val" style="color:var(--green)">48%</div>
                  <div class="prog-bar"><div class="prog-fill pf-green" style="width:48%"></div></div>
                </div>
              </div>
            </div>
            <div class="server-card">
              <div class="server-header">
                <div class="server-dot s-online"></div>
                <div class="server-name">DB-01</div>
                <div class="server-ip">192.168.1.11</div>
              </div>
              <div class="server-metrics">
                <div class="server-metric">
                  <div class="sm-label">CPU</div>
                  <div class="sm-val" style="color:var(--green)">18%</div>
                  <div class="prog-bar"><div class="prog-fill pf-green" style="width:18%"></div></div>
                </div>
                <div class="server-metric">
                  <div class="sm-label">RAM</div>
                  <div class="sm-val" style="color:var(--green)">55%</div>
                  <div class="prog-bar"><div class="prog-fill pf-green" style="width:55%"></div></div>
                </div>
                <div class="server-metric">
                  <div class="sm-label">DISK</div>
                  <div class="sm-val" style="color:var(--amber)">79%</div>
                  <div class="prog-bar"><div class="prog-fill pf-amber" style="width:79%"></div></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Login activity chart -->
          <div class="card">
            <div class="card-head">
              <h2><i class="ti ti-chart-line"></i> Login activity — last 7 days</h2>
            </div>
            <div class="card-body" style="padding:16px">
              <div id="loginChart" style="display:flex;align-items:flex-end;gap:6px;height:80px"></div>
              <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:10px;color:var(--t3);font-family:var(--font-mono)">
                <span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span><span>Mon</span><span>Tue</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent events -->
        <div>
          <div class="card" style="height:100%">
            <div class="card-head">
              <h2><i class="ti ti-activity"></i> Recent events</h2>
              <button class="btn-icon" onclick="showSection('audit',null)"><i class="ti ti-external-link"></i></button>
            </div>
            <div class="card-body" style="padding:10px 16px">
              <div id="recentEvents"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickets + Storage -->
      <div class="grid-2">
        <div class="card">
          <div class="card-head">
            <h2><i class="ti ti-headset"></i> Open support tickets</h2>
            <button class="btn btn-ghost btn-sm" onclick="showSection('tickets',null)">View all</button>
          </div>
          <div class="card-body" style="padding:0 18px">
            <div id="dashTickets"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-database"></i> Storage overview</h2></div>
          <div class="card-body">
            <div id="storageItems"></div>
          </div>
        </div>
      </div>
    </div><!-- /dashboard -->

    <!-- ══════ USER MANAGEMENT ══════ -->
    <div id="sec-users" class="section" style="display:none">
      <div class="page-header-row">
        <div class="page-header">
          <h1>User <span>Management</span></h1>
          <p>Create, edit, deactivate accounts and assign roles</p>
        </div>
        <button class="btn btn-cyan" onclick="openModal('addUserModal')">
          <i class="ti ti-user-plus"></i> Add new user
        </button>
      </div>

      <!-- Filters -->
      <div class="card" style="margin-bottom:16px">
        <div class="card-body" style="padding:12px 18px">
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div style="position:relative;flex:1;min-width:200px">
              <i class="ti ti-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--t3);font-size:15px"></i>
              <input type="text" id="userSearch" placeholder="Search by name, email, staff ID..." style="padding-left:34px" oninput="filterUsers()">
            </div>
            <select id="roleFilter" style="min-width:160px" onchange="filterUsers()">
              <option value="">All roles</option>
              <option>DVC</option>
              <option>Executive Assistant</option>
              <option>Administrative Officer</option>
              <option>Secretary</option>
              <option>ICT Officer</option>
              <option>Records Officer</option>
              <option>HR Officer</option>
              <option>Clerical Officer</option>
            </select>
            <select id="statusFilter" onchange="filterUsers()">
              <option value="">All status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h2><i class="ti ti-users-group"></i> Staff accounts <span id="userCount" style="color:var(--t3);font-weight:400;font-size:12px;font-family:var(--font-mono)"></span></h2>
          <div style="display:flex;gap:6px">
            <button class="btn btn-ghost btn-sm"><i class="ti ti-file-export"></i> Export</button>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table" id="usersTable">
            <thead>
              <tr>
                <th>Staff</th>
                <th>Staff ID</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Last login</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersBody"></tbody>
          </table>
        </div>
      </div>
    </div><!-- /users -->

    <!-- ══════ SERVERS & NETWORK ══════ -->
    <div id="sec-servers" class="section" style="display:none">
      <div class="page-header">
        <h1>Servers &amp; <span>Network</span></h1>
        <p>Monitor hardware, network services, and infrastructure health</p>
      </div>

      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card sc-green"><div class="stat-icon ic-green"><i class="ti ti-server"></i></div><div class="stat-val">4</div><div class="stat-lbl">Servers online</div></div>
        <div class="stat-card sc-amber"><div class="stat-icon ic-amber"><i class="ti ti-wifi"></i></div><div class="stat-val">98<span style="font-size:16px">%</span></div><div class="stat-lbl">Network uptime</div></div>
        <div class="stat-card sc-cyan"><div class="stat-icon ic-cyan"><i class="ti ti-speedboat"></i></div><div class="stat-val">24<span style="font-size:16px">ms</span></div><div class="stat-lbl">Avg response time</div></div>
        <div class="stat-card sc-red"><div class="stat-icon ic-red"><i class="ti ti-alert-triangle"></i></div><div class="stat-val">1</div><div class="stat-lbl">Alerts active</div></div>
      </div>

      <div class="alert alert-amber" style="margin-bottom:16px">
        <i class="ti ti-alert-triangle"></i>
        <div><strong>BACKUP-01 degraded:</strong> Secondary backup server is reporting high disk temperature (78°C). Recommend inspection before next scheduled backup at 02:00 AM.</div>
      </div>

      <div class="grid-2" style="margin-bottom:16px">
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-server-2"></i> Server inventory</h2></div>
          <div style="padding:16px;display:flex;flex-direction:column;gap:12px" id="serverList"></div>
        </div>
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-network"></i> Network devices</h2></div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead><tr><th>Device</th><th>Type</th><th>IP</th><th>Status</th><th>Action</th></tr></thead>
              <tbody id="networkDevices"></tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h2><i class="ti ti-chart-histogram"></i> Bandwidth usage — today</h2></div>
        <div class="card-body">
          <div style="display:flex;align-items:flex-end;gap:3px;height:100px" id="bandwidthChart"></div>
          <div style="font-size:10px;color:var(--t3);font-family:var(--font-mono);margin-top:6px">00:00 → 23:59 (hourly)</div>
        </div>
      </div>
    </div><!-- /servers -->

    <!-- ══════ SECURITY & ACCESS ══════ -->
    <div id="sec-security" class="section" style="display:none">
      <div class="page-header">
        <h1>Security &amp; <span>Access</span></h1>
        <p>Role permissions, login monitoring, and access control</p>
      </div>

      <div class="alert alert-red" style="margin-bottom:16px">
        <i class="ti ti-shield-x"></i>
        <div><strong>3 failed login attempts</strong> detected for user <code style="font-family:var(--font-mono)">a.eze@university.edu.ng</code> in the last hour. Account has been temporarily locked.</div>
      </div>

      <div class="grid-2">
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-key"></i> Role permissions</h2></div>
          <div style="overflow-x:auto">
            <table class="data-table" id="rolesTable"></table>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-lock-access"></i> Recent login activity</h2></div>
          <div style="padding:0 18px" id="loginActivity"></div>
        </div>
      </div>

      <div style="margin-top:16px" class="card">
        <div class="card-head">
          <h2><i class="ti ti-settings-2"></i> Security settings</h2>
        </div>
        <div class="card-body">
          <div class="grid-2">
            <div style="display:flex;flex-direction:column;gap:16px">
              <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--ink3);border-radius:var(--r-sm)">
                <div><div style="font-weight:500">Two-factor authentication</div><div style="font-size:11px;color:var(--t2);margin-top:2px">Require 2FA for admin roles</div></div>
                <label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div></label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--ink3);border-radius:var(--r-sm)">
                <div><div style="font-weight:500">Session timeout</div><div style="font-size:11px;color:var(--t2);margin-top:2px">Auto-logout after 8 hours</div></div>
                <label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div></label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--ink3);border-radius:var(--r-sm)">
                <div><div style="font-weight:500">Failed login lockout</div><div style="font-size:11px;color:var(--t2);margin-top:2px">Lock after 5 failed attempts</div></div>
                <label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div></label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--ink3);border-radius:var(--r-sm)">
                <div><div style="font-weight:500">Audit all file access</div><div style="font-size:11px;color:var(--t2);margin-top:2px">Log every document download</div></div>
                <label class="toggle"><input type="checkbox" checked><div class="toggle-track"></div></label>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px">
              <div class="form-group">
                <label>Password minimum length</label>
                <input type="number" value="8" min="6" max="32">
              </div>
              <div class="form-group">
                <label>Password expiry (days)</label>
                <input type="number" value="90" min="30">
              </div>
              <div class="form-group">
                <label>Max concurrent sessions</label>
                <input type="number" value="2" min="1">
              </div>
              <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save security config</button>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /security -->

    <!-- ══════ BACKUPS & DATA ══════ -->
    <div id="sec-backups" class="section" style="display:none">
      <div class="page-header-row">
        <div class="page-header">
          <h1>Backups &amp; <span>Data</span></h1>
          <p>Database snapshots, scheduled backups, and recovery</p>
        </div>
        <button class="btn btn-cyan"><i class="ti ti-database-export"></i> Run backup now</button>
      </div>

      <div class="stats-grid" style="margin-bottom:20px">
        <div class="stat-card sc-green"><div class="stat-icon ic-green"><i class="ti ti-circle-check"></i></div><div class="stat-val">142</div><div class="stat-lbl">Total backups taken</div></div>
        <div class="stat-card sc-cyan"><div class="stat-icon ic-cyan"><i class="ti ti-clock"></i></div><div class="stat-val" style="font-size:18px">02:00 AM</div><div class="stat-lbl">Next scheduled backup</div></div>
        <div class="stat-card sc-amber"><div class="stat-icon ic-amber"><i class="ti ti-database"></i></div><div class="stat-val" style="font-size:18px">4.2 GB</div><div class="stat-lbl">Total backup size</div></div>
        <div class="stat-card sc-red"><div class="stat-icon ic-red"><i class="ti ti-alert-triangle"></i></div><div class="stat-val">1</div><div class="stat-lbl">Failed backups (7d)</div></div>
      </div>

      <div class="grid-2">
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-list"></i> Backup history</h2></div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead><tr><th>Date &amp; Time</th><th>Type</th><th>Size</th><th>Status</th><th>Action</th></tr></thead>
              <tbody id="backupHistory"></tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><h2><i class="ti ti-settings"></i> Backup schedule</h2></div>
          <div class="card-body">
            <div class="form-group"><label>Frequency</label>
              <select><option>Daily (02:00 AM)</option><option>Every 6 hours</option><option>Weekly</option></select>
            </div>
            <div class="form-group"><label>Retention (days)</label><input type="number" value="30"></div>
            <div class="form-group"><label>Backup destination</label>
              <select><option>Local server</option><option>Remote FTP</option><option>Cloud storage</option></select>
            </div>
            <div class="form-group"><label>Email notification to</label><input type="email" value="ict@university.edu.ng"></div>
            <div style="display:flex;gap:8px">
              <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save schedule</button>
              <button class="btn btn-ghost"><i class="ti ti-test-pipe"></i> Test backup</button>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /backups -->

    <!-- ══════ SUPPORT TICKETS ══════ -->
    <div id="sec-tickets" class="section" style="display:none">
      <div class="page-header-row">
        <div class="page-header">
          <h1>Support <span>Tickets</span></h1>
          <p>Technical assistance requests from DVC office staff</p>
        </div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-ghost btn-sm"><i class="ti ti-filter"></i> Filter</button>
          <button class="btn btn-cyan btn-sm" onclick="openModal('newTicketModal')">
            <i class="ti ti-plus"></i> New ticket
          </button>
        </div>
      </div>

      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
        <div class="stat-card sc-red"><div class="stat-icon ic-red"><i class="ti ti-urgent"></i></div><div class="stat-val">2</div><div class="stat-lbl">High priority</div></div>
        <div class="stat-card sc-amber"><div class="stat-icon ic-amber"><i class="ti ti-clock-hour-4"></i></div><div class="stat-val">3</div><div class="stat-lbl">In progress</div></div>
        <div class="stat-card sc-cyan"><div class="stat-icon ic-cyan"><i class="ti ti-list-check"></i></div><div class="stat-val">18</div><div class="stat-lbl">Resolved this month</div></div>
        <div class="stat-card sc-green"><div class="stat-icon ic-green"><i class="ti ti-star"></i></div><div class="stat-val">4.8</div><div class="stat-lbl">Avg satisfaction</div></div>
      </div>

      <div class="card">
        <div class="card-head"><h2><i class="ti ti-ticket"></i> All tickets</h2></div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead><tr><th>#ID</th><th>Title</th><th>Reporter</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody id="ticketsBody"></tbody>
          </table>
        </div>
      </div>
    </div><!-- /tickets -->

    <!-- ══════ AUDIT LOGS ══════ -->
    <div id="sec-audit" class="section" style="display:none">
      <div class="page-header">
        <h1>Audit <span>Logs</span></h1>
        <p>Complete record of all system actions and user activity</p>
      </div>

      <div class="card" style="margin-bottom:16px">
        <div class="card-body" style="padding:12px 18px">
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <select style="min-width:140px"><option>All modules</option><option>Auth</option><option>Documents</option><option>Tasks</option><option>Attendance</option><option>Users</option></select>
            <select style="min-width:140px"><option>All actions</option><option>Login</option><option>Upload</option><option>Delete</option><option>Update</option><option>Download</option></select>
            <input type="date" value="2026-05-20">
            <input type="date" value="2026-05-21">
            <button class="btn btn-cyan btn-sm"><i class="ti ti-search"></i> Search</button>
            <button class="btn btn-ghost btn-sm"><i class="ti ti-download"></i> Export CSV</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h2><i class="ti ti-file-search"></i> System audit trail</h2></div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP Address</th></tr></thead>
            <tbody id="auditBody"></tbody>
          </table>
        </div>
      </div>
    </div><!-- /audit -->

    <!-- ══════ REPORTS ══════ -->
    <div id="sec-reports" class="section" style="display:none">
      <div class="page-header">
        <h1>System <span>Reports</span></h1>
        <p>Generate and export ICT infrastructure and usage reports</p>
      </div>

      <div class="grid-3" style="margin-bottom:20px">
        <div class="card" style="cursor:pointer" onclick="openModal('genReportModal')">
          <div class="card-body" style="text-align:center;padding:28px 20px">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--cyan-dim);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
              <i class="ti ti-users" style="font-size:26px;color:var(--cyan)"></i>
            </div>
            <div style="font-family:var(--font-head);font-weight:700;margin-bottom:4px">User Activity Report</div>
            <div style="font-size:12px;color:var(--t2)">Login history, active sessions, account changes</div>
          </div>
        </div>
        <div class="card" style="cursor:pointer" onclick="openModal('genReportModal')">
          <div class="card-body" style="text-align:center;padding:28px 20px">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--green-dim);border:1px solid rgba(34,197,94,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
              <i class="ti ti-server" style="font-size:26px;color:var(--green)"></i>
            </div>
            <div style="font-family:var(--font-head);font-weight:700;margin-bottom:4px">Server Health Report</div>
            <div style="font-size:12px;color:var(--t2)">CPU, memory, disk usage over time</div>
          </div>
        </div>
        <div class="card" style="cursor:pointer" onclick="openModal('genReportModal')">
          <div class="card-body" style="text-align:center;padding:28px 20px">
            <div style="width:52px;height:52px;border-radius:12px;background:var(--amber-dim);border:1px solid rgba(245,158,11,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
              <i class="ti ti-database-backup" style="font-size:26px;color:var(--amber)"></i>
            </div>
            <div style="font-family:var(--font-head);font-weight:700;margin-bottom:4px">Backup Status Report</div>
            <div style="font-size:12px;color:var(--t2)">Backup history, failures, storage usage</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h2><i class="ti ti-history"></i> Recently generated reports</h2></div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead><tr><th>Report</th><th>Type</th><th>Generated by</th><th>Date</th><th>Format</th><th>Action</th></tr></thead>
            <tbody id="reportsBody"></tbody>
          </table>
        </div>
      </div>
    </div><!-- /reports -->

    <!-- ══════ SETTINGS ══════ -->
    <div id="sec-settings" class="section" style="display:none">
      <div class="page-header">
        <h1>System <span>Settings</span></h1>
        <p>Configure application, email, SMS, and integration settings</p>
      </div>

      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab(this,'tab-general')">General</button>
        <button class="tab-btn" onclick="switchTab(this,'tab-email')">Email (SMTP)</button>
        <button class="tab-btn" onclick="switchTab(this,'tab-sms')">SMS Gateway</button>
        <button class="tab-btn" onclick="switchTab(this,'tab-biometric')">Biometric</button>
      </div>

      <div id="tab-general" class="tab-panel active">
        <div class="card">
          <div class="card-body">
            <div class="form-grid-2">
              <div class="form-group"><label>Application name</label><input type="text" value="DVC Office Automation System"></div>
              <div class="form-group"><label>University name</label><input type="text" value="University of Nigeria"></div>
              <div class="form-group"><label>Base URL</label><input type="text" value="http://localhost/dvc_system"></div>
              <div class="form-group"><label>Timezone</label><select><option>Africa/Lagos (WAT)</option><option>UTC</option></select></div>
              <div class="form-group"><label>Work start time</label><input type="time" value="08:00"></div>
              <div class="form-group"><label>Work end time</label><input type="time" value="17:00"></div>
              <div class="form-group"><label>Late threshold (minutes)</label><input type="number" value="15"></div>
              <div class="form-group"><label>Max upload size (MB)</label><input type="number" value="20"></div>
            </div>
            <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save general settings</button>
          </div>
        </div>
      </div>

      <div id="tab-email" class="tab-panel">
        <div class="card"><div class="card-body">
          <div class="form-grid-2">
            <div class="form-group"><label>SMTP Host</label><input type="text" placeholder="smtp.gmail.com"></div>
            <div class="form-group"><label>SMTP Port</label><input type="number" value="587"></div>
            <div class="form-group"><label>SMTP Username</label><input type="email" placeholder="noreply@university.edu.ng"></div>
            <div class="form-group"><label>SMTP Password</label><input type="password" placeholder="••••••••"></div>
            <div class="form-group"><label>Encryption</label><select><option>TLS</option><option>SSL</option><option>None</option></select></div>
            <div class="form-group"><label>From name</label><input type="text" value="DVC Office System"></div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save SMTP settings</button>
            <button class="btn btn-ghost"><i class="ti ti-send"></i> Send test email</button>
          </div>
        </div></div>
      </div>

      <div id="tab-sms" class="tab-panel">
        <div class="card"><div class="card-body">
          <div class="form-grid-2">
            <div class="form-group"><label>SMS Provider</label><select><option>Twilio</option><option>Termii</option><option>Africa's Talking</option></select></div>
            <div class="form-group"><label>API Key</label><input type="password" placeholder="••••••••••••••••"></div>
            <div class="form-group"><label>Sender ID</label><input type="text" placeholder="DVCOffice"></div>
            <div class="form-group"><label>Default country code</label><input type="text" value="+234"></div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save SMS settings</button>
            <button class="btn btn-ghost"><i class="ti ti-message"></i> Send test SMS</button>
          </div>
        </div></div>
      </div>

      <div id="tab-biometric" class="tab-panel">
        <div class="card"><div class="card-body">
          <div class="alert alert-cyan" style="margin-bottom:16px">
            <i class="ti ti-info-circle"></i>
            <div>Biometric device is connected on <code style="font-family:var(--font-mono)">192.168.1.50:4500</code>. Last sync: <strong>Today, 07:58 AM</strong></div>
          </div>
          <div class="form-grid-2">
            <div class="form-group"><label>Device IP address</label><input type="text" value="192.168.1.50"></div>
            <div class="form-group"><label>Device port</label><input type="number" value="4500"></div>
            <div class="form-group"><label>Sync interval (minutes)</label><input type="number" value="5"></div>
            <div class="form-group"><label>Scan type</label><select><option>Fingerprint</option><option>Facial recognition</option><option>Thumbprint</option></select></div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-cyan"><i class="ti ti-device-floppy"></i> Save biometric settings</button>
            <button class="btn btn-ghost"><i class="ti ti-refresh"></i> Sync now</button>
            <button class="btn btn-ghost"><i class="ti ti-plug"></i> Test connection</button>
          </div>
        </div></div>
      </div>
    </div><!-- /settings -->

    <!-- ══════ NOTIFICATIONS ══════ -->
    <div id="sec-notifications" class="section" style="display:none">
      <div class="page-header">
        <h1>System <span>Notifications</span></h1>
        <p>Alerts, system messages, and broadcast announcements</p>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px" id="notifList"></div>
    </div><!-- /notifications -->

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /shell -->

<!-- ══════════════ MODALS ══════════════ -->

<!-- ADD USER -->
<div class="modal-backdrop" id="addUserModal">
  <div class="modal">
    <div class="modal-head">
      <h2><i class="ti ti-user-plus" style="color:var(--cyan);font-size:17px;vertical-align:-2px;margin-right:6px"></i> Add new user</h2>
      <span class="modal-close" onclick="closeModal('addUserModal')"><i class="ti ti-x"></i></span>
    </div>
    <div class="modal-body">
      <div class="form-grid-2">
        <div class="form-group"><label>First name *</label><input type="text" placeholder="e.g. Adaeze"></div>
        <div class="form-group"><label>Last name *</label><input type="text" placeholder="e.g. Okonkwo"></div>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Staff ID</label><input type="text" placeholder="e.g. STF/2026/001"></div>
        <div class="form-group"><label>Department</label><input type="text" placeholder="e.g. Administration"></div>
      </div>
      <div class="form-group"><label>Email address *</label><input type="email" placeholder="staff@university.edu.ng"></div>
      <div class="form-grid-2">
        <div class="form-group"><label>Phone number</label><input type="tel" placeholder="+234 801 234 5678"></div>
        <div class="form-group"><label>Gender</label><select><option value="">Select gender</option><option>Male</option><option>Female</option></select></div>
      </div>
      <div class="form-group"><label>Role / Position *</label>
        <select>
          <option value="">Select role</option>
          <option>Deputy Vice Chancellor</option>
          <option>Executive Assistant</option>
          <option>Administrative Officer</option>
          <option>Personal Assistant</option>
          <option>Secretary</option>
          <option>ICT Officer</option>
          <option>Records Officer</option>
          <option>Finance Officer</option>
          <option>HR Officer</option>
          <option>Clerical Officer</option>
          <option>Office Assistant</option>
          <option>Security Officer</option>
        </select>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Temporary password *</label><input type="password" placeholder="Min. 8 characters"></div>
        <div class="form-group"><label>Confirm password *</label><input type="password" placeholder="Repeat password"></div>
      </div>
      <div class="alert alert-cyan" style="margin:0">
        <i class="ti ti-info-circle"></i>
        <div style="font-size:12px">The user will receive a login email with instructions to change their password on first login.</div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('addUserModal')">Cancel</button>
      <button class="btn btn-cyan" onclick="closeModal('addUserModal');showToast('User created successfully','green')">
        <i class="ti ti-user-check"></i> Create user
      </button>
    </div>
  </div>
</div>

<!-- NEW TICKET -->
<div class="modal-backdrop" id="newTicketModal">
  <div class="modal">
    <div class="modal-head">
      <h2><i class="ti ti-ticket" style="color:var(--cyan);font-size:17px;vertical-align:-2px;margin-right:6px"></i> New support ticket</h2>
      <span class="modal-close" onclick="closeModal('newTicketModal')"><i class="ti ti-x"></i></span>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Title *</label><input type="text" placeholder="Brief description of the issue"></div>
      <div class="form-grid-2">
        <div class="form-group"><label>Category</label><select><option>Hardware</option><option>Software</option><option>Network</option><option>Account access</option><option>Other</option></select></div>
        <div class="form-group"><label>Priority</label><select><option>Low</option><option>Medium</option><option selected>High</option><option>Critical</option></select></div>
      </div>
      <div class="form-group"><label>Assign to</label><select><option>I. Obiora (ICT Officer)</option><option>Unassigned</option></select></div>
      <div class="form-group"><label>Description</label><textarea placeholder="Describe the issue in detail..."></textarea></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('newTicketModal')">Cancel</button>
      <button class="btn btn-cyan" onclick="closeModal('newTicketModal');showToast('Ticket created','cyan')"><i class="ti ti-send"></i> Submit ticket</button>
    </div>
  </div>
</div>

<!-- GENERATE REPORT -->
<div class="modal-backdrop" id="genReportModal">
  <div class="modal">
    <div class="modal-head">
      <h2><i class="ti ti-file-chart" style="color:var(--cyan);font-size:17px;vertical-align:-2px;margin-right:6px"></i> Generate report</h2>
      <span class="modal-close" onclick="closeModal('genReportModal')"><i class="ti ti-x"></i></span>
    </div>
    <div class="modal-body">
      <div class="form-group"><label>Report type</label><select><option>User Activity</option><option>Server Health</option><option>Backup Status</option><option>Login Audit</option></select></div>
      <div class="form-grid-2">
        <div class="form-group"><label>Start date</label><input type="date" value="2026-05-01"></div>
        <div class="form-group"><label>End date</label><input type="date" value="2026-05-21"></div>
      </div>
      <div class="form-group"><label>Export format</label><select><option>PDF</option><option>CSV</option><option>Excel</option></select></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('genReportModal')">Cancel</button>
      <button class="btn btn-cyan" onclick="closeModal('genReportModal');showToast('Report queued for generation','green')"><i class="ti ti-file-export"></i> Generate</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast" style="position:fixed;bottom:24px;right:24px;z-index:999;display:none;
  background:var(--ink2);border:1px solid var(--border);border-radius:var(--r-sm);
  padding:12px 18px;font-size:13px;display:flex;align-items:center;gap:10px;
  box-shadow:0 8px 24px rgba(0,0,0,.4);transform:translateY(20px);opacity:0;
  transition:all .3s ease;min-width:240px">
  <i id="toastIcon" class="ti ti-circle-check" style="font-size:18px"></i>
  <span id="toastMsg"></span>
</div>

<script>
// ── Data ──────────────────────────────────────────────────────────────────
const USERS = [
  {id:1,name:'Prof. A. Okonkwo',staff:'STF/001',role:'Deputy Vice Chancellor',email:'dvc@university.edu.ng',phone:'08031234567',login:'Today, 07:48 AM',status:'active'},
  {id:2,name:'E. Adaeze',staff:'STF/002',role:'Executive Assistant',email:'e.adaeze@university.edu.ng',phone:'08031234568',login:'Today, 07:52 AM',status:'active'},
  {id:3,name:'I. Obiora',staff:'STF/003',role:'ICT Officer',email:'i.obiora@university.edu.ng',phone:'08031234569',login:'Today, 08:15 AM',status:'active'},
  {id:4,name:'C. Ugwu',staff:'STF/004',role:'Secretary',email:'c.ugwu@university.edu.ng',phone:'08031234570',login:'20 May 2026',status:'active'},
  {id:5,name:'B. Nwosu',staff:'STF/005',role:'Records Officer',email:'b.nwosu@university.edu.ng',phone:'08031234571',login:'Today, 08:42 AM',status:'active'},
  {id:6,name:'A. Eze',staff:'STF/006',role:'Administrative Officer',email:'a.eze@university.edu.ng',phone:'08031234572',login:'19 May 2026',status:'inactive'},
  {id:7,name:'F. Dike',staff:'STF/007',role:'Finance Officer',email:'f.dike@university.edu.ng',phone:'08031234573',login:'Today, 08:30 AM',status:'active'},
  {id:8,name:'N. Obi',staff:'STF/008',role:'HR Officer',email:'n.obi@university.edu.ng',phone:'08031234574',login:'Today, 09:01 AM',status:'active'},
];

const TICKETS = [
  {id:'TKT-021',title:'Cannot access document module',reporter:'C. Ugwu',cat:'Software',priority:'High',status:'open',date:'21 May'},
  {id:'TKT-020',title:'Fingerprint scanner not syncing',reporter:'N. Obi',cat:'Hardware',priority:'High',status:'in_progress',date:'20 May'},
  {id:'TKT-019',title:'Email notifications not sending',reporter:'E. Adaeze',cat:'Software',priority:'Medium',status:'in_progress',date:'20 May'},
  {id:'TKT-018',title:'Password reset not working',reporter:'F. Dike',cat:'Account',priority:'Medium',status:'open',date:'19 May'},
  {id:'TKT-017',title:'Slow network on 2nd floor',reporter:'B. Nwosu',cat:'Network',priority:'Low',status:'open',date:'19 May'},
];

const AUDIT = [
  {time:'09:14:32',user:'I. Obiora',action:'login','module':'auth',desc:'Successful login',ip:'192.168.1.5'},
  {time:'09:12:01',user:'E. Adaeze',action:'upload','module':'documents',desc:'Uploaded: Budget_2026.pdf',ip:'192.168.1.8'},
  {time:'09:08:45',user:'B. Nwosu',action:'download','module':'documents',desc:'Downloaded: Senate_Minutes.pdf',ip:'192.168.1.12'},
  {time:'08:55:17',user:'N. Obi',action:'update','module':'attendance',desc:'Approved leave for C. Ugwu',ip:'192.168.1.14'},
  {time:'08:50:03',user:'A. Eze',action:'login_failed','module':'auth',desc:'Invalid password (attempt 3/5)',ip:'192.168.1.20'},
  {time:'08:42:11',user:'B. Nwosu',action:'login','module':'auth',desc:'Successful login',ip:'192.168.1.12'},
  {time:'08:30:22',user:'F. Dike',action:'login','module':'auth',desc:'Successful login',ip:'192.168.1.9'},
];

const EVENTS = [
  {icon:'ti-user-plus',color:'var(--cyan)',title:'New user created','sub':'A. Bello — Clerical Officer','time':'2m ago'},
  {icon:'ti-shield-x',color:'var(--red)',title:'Login attempt blocked','sub':'a.eze@university.edu.ng','time':'9m ago'},
  {icon:'ti-database-backup',color:'var(--amber)',title:'Backup warning','sub':'BACKUP-01 disk temp high','time':'34m ago'},
  {icon:'ti-refresh',color:'var(--green)',title:'System backup completed','sub':'4.2 GB — 98% integrity','time':'2h ago'},
  {icon:'ti-ticket',color:'var(--purple)',title:'Support ticket opened','sub':'TKT-021 — C. Ugwu','time':'3h ago'},
];

const REPORTS_DATA = [
  {name:'User Activity Report — May 2026',type:'User Activity',by:'I. Obiora',date:'21 May 2026',fmt:'PDF'},
  {name:'Server Health — Week 20',type:'Server Health',by:'I. Obiora',date:'18 May 2026',fmt:'PDF'},
  {name:'Backup Status — April 2026',type:'Backup',by:'I. Obiora',date:'01 May 2026',fmt:'CSV'},
];

const NOTIFS = [
  {icon:'ti-shield-x',color:'var(--red)',title:'Security alert: Login lockout triggered',body:'Account a.eze@university.edu.ng locked after 3 failed attempts.',time:'9 minutes ago',unread:true},
  {icon:'ti-database-backup',color:'var(--amber)',title:'Backup server degraded',body:'BACKUP-01 is reporting high disk temperature. Immediate inspection recommended.',time:'34 minutes ago',unread:true},
  {icon:'ti-ticket',color:'var(--purple)',title:'New support ticket: TKT-021',body:'C. Ugwu reported: "Cannot access document module" — High priority.',time:'1 hour ago',unread:true},
  {icon:'ti-user-plus',color:'var(--cyan)',title:'New user account request pending',body:'HR Officer N. Obi has submitted a request to onboard new clerical staff.',time:'2 hours ago',unread:false},
  {icon:'ti-circle-check',color:'var(--green)',title:'Nightly backup completed',body:'Full database backup completed successfully. 4.2 GB — integrity 98%.',time:'8 hours ago',unread:false},
  {icon:'ti-wifi',color:'var(--cyan)',title:'Network maintenance tonight',body:'Scheduled network maintenance from 11 PM – 1 AM. Brief interruptions expected.',time:'Yesterday',unread:false},
  {icon:'ti-file-export',color:'var(--green)',title:'Report ready: Server Health – Week 20',body:'Your server health report has been generated and is ready to download.',time:'3 days ago',unread:false},
];

const SERVERS_DATA = [
  {name:'WEB-01',ip:'192.168.1.10',os:'Ubuntu 22.04',role:'Web Server',status:'online',cpu:34,ram:71,disk:48},
  {name:'DB-01',ip:'192.168.1.11',os:'Ubuntu 22.04',role:'MySQL Database',status:'online',cpu:18,ram:55,disk:79},
  {name:'BACKUP-01',ip:'192.168.1.12',os:'Debian 11',role:'Backup Server',status:'warn',cpu:22,ram:40,disk:91},
  {name:'MAIL-01',ip:'192.168.1.13',os:'CentOS 7',role:'Email Gateway',status:'online',cpu:9,ram:33,disk:31},
];

const NETWORK_DEVICES = [
  {name:'Core Switch',type:'Switch',ip:'192.168.1.1',status:'online'},
  {name:'Router-01',type:'Router',ip:'192.168.1.2',status:'online'},
  {name:'AP — Main Hall',type:'Access Point',ip:'192.168.1.30',status:'online'},
  {name:'AP — 2nd Floor',type:'Access Point',ip:'192.168.1.31',status:'warn'},
  {name:'Biometric Device',type:'Attendance',ip:'192.168.1.50',status:'online'},
  {name:'IP Camera × 4',type:'CCTV NVR',ip:'192.168.1.60',status:'online'},
];

const BACKUPS = [
  {date:'21 May 2026, 02:00 AM',type:'Full',size:'4.2 GB',status:'success'},
  {date:'20 May 2026, 02:00 AM',type:'Full',size:'4.1 GB',status:'success'},
  {date:'19 May 2026, 02:00 AM',type:'Full',size:'4.1 GB',status:'success'},
  {date:'18 May 2026, 02:00 AM',type:'Full',size:'4.0 GB',status:'failed'},
  {date:'17 May 2026, 02:00 AM',type:'Full',size:'4.0 GB',status:'success'},
];

const STORAGE = [
  {label:'Database',used:79,max:100,unit:'GB',color:'pf-amber'},
  {label:'Documents',used:4.2,max:20,unit:'GB',color:'pf-cyan'},
  {label:'Backups',used:41,max:50,unit:'GB',color:'pf-red'},
  {label:'Media / Cache',used:1.8,max:10,unit:'GB',color:'pf-green'},
];

// ── Role permissions matrix ───────────────────────────────────────────────
const ROLES_MATRIX = [
  {role:'DVC',docs:'✓',tasks:'✓',users:'✓',attendance:'✓',reports:'✓',audit:'✓',settings:'✓'},
  {role:'Exec. Asst.',docs:'✓',tasks:'✓',users:'✓',attendance:'✓',reports:'✓',audit:'✓',settings:'—'},
  {role:'Admin Officer',docs:'✓',tasks:'✓',users:'✓',attendance:'✓',reports:'✓',audit:'—',settings:'—'},
  {role:'Secretary',docs:'✓',tasks:'view',users:'—',attendance:'view',reports:'—',audit:'—',settings:'—'},
  {role:'ICT Officer',docs:'✓',tasks:'✓',users:'✓',attendance:'view',reports:'✓',audit:'✓',settings:'✓'},
  {role:'HR Officer',docs:'view',tasks:'view',users:'—',attendance:'✓',reports:'✓',audit:'—',settings:'—'},
  {role:'Clerical',docs:'view',tasks:'view',users:'—',attendance:'—',reports:'—',audit:'—',settings:'—'},
];

// ── RENDER FUNCTIONS ──────────────────────────────────────────────────────

function renderUsers(list) {
  const colors = ['#185fa5','#3b6d11','#854f0b','#a32d2d','#5b21b6','#0e7490'];
  const tbody = document.getElementById('usersBody');
  tbody.innerHTML = list.map((u,i) => `
    <tr>
      <td><div style="display:flex;align-items:center;gap:10px">
        <div class="user-avatar" style="background:${colors[i%colors.length]}22;color:${colors[i%colors.length]};border:1px solid ${colors[i%colors.length]}44">
          ${u.name.split(' ').slice(-2).map(n=>n[0]).join('').toUpperCase()}
        </div>
        <div>
          <div style="font-weight:500">${u.name}</div>
          <div style="font-size:11px;color:var(--t3);font-family:var(--font-mono)">${u.staff}</div>
        </div>
      </div></td>
      <td style="font-family:var(--font-mono);font-size:11px;color:var(--t2)">${u.staff}</td>
      <td><span class="badge b-cyan">${u.role}</span></td>
      <td style="font-family:var(--font-mono);font-size:11px">${u.email}</td>
      <td style="font-size:12px;color:var(--t2)">${u.phone}</td>
      <td style="font-size:11px;color:var(--t3);font-family:var(--font-mono)">${u.login}</td>
      <td><span class="badge ${u.status==='active'?'b-green':'b-red'}">${u.status==='active'?'● Active':'○ Inactive'}</span></td>
      <td><div style="display:flex;gap:4px">
        <button class="btn-icon" title="Edit" onclick="showToast('Edit user: '+${JSON.stringify(u.name)},'cyan')"><i class="ti ti-edit"></i></button>
        <button class="btn-icon" title="Reset password" onclick="showToast('Password reset email sent','green')"><i class="ti ti-key"></i></button>
        <button class="btn-icon" title="${u.status==='active'?'Deactivate':'Activate'}"
          onclick="showToast('User ${u.status==='active'?'deactivated':'activated'}','${u.status==='active'?'amber':'green'}')">
          <i class="ti ti-${u.status==='active'?'user-off':'user-check'}"></i>
        </button>
      </div></td>
    </tr>`).join('');
  document.getElementById('userCount').textContent = `(${list.length})`;
}

function filterUsers() {
  const q   = document.getElementById('userSearch').value.toLowerCase();
  const role = document.getElementById('roleFilter').value;
  const st  = document.getElementById('statusFilter').value;
  renderUsers(USERS.filter(u =>
    (!q || u.name.toLowerCase().includes(q) || u.email.includes(q) || u.staff.includes(q)) &&
    (!role || u.role === role) &&
    (!st || u.status === st)
  ));
}

function buildLoginChart() {
  const vals = [22,18,15,6,4,27,31];
  const max  = Math.max(...vals);
  const el   = document.getElementById('loginChart');
  if(!el) return;
  el.innerHTML = vals.map((v,i) => `
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px">
      <div style="font-size:9px;color:var(--t3);font-family:var(--font-mono)">${v}</div>
      <div style="width:100%;border-radius:3px 3px 0 0;
        height:${Math.round((v/max)*60)}px;
        background:${i===6?'var(--cyan)':'var(--ink4)'};
        border:1px solid ${i===6?'rgba(0,212,255,.3)':'var(--border)'}">
      </div>
    </div>`).join('');
}

function buildBandwidthChart() {
  const el = document.getElementById('bandwidthChart');
  if(!el) return;
  const vals = [2,1,1,2,3,8,12,18,22,24,20,18,19,22,24,21,19,17,16,14,10,8,5,3];
  const max  = Math.max(...vals);
  el.innerHTML = vals.map((v,i) => `
    <div class="mini-bar" style="background:linear-gradient(180deg,var(--cyan),#0066ff);
      height:${Math.round((v/max)*100)}%;opacity:${0.4+v/max*0.6}"
      title="${String(i).padStart(2,'0')}:00 — ${v} Mbps">
    </div>`).join('');
}

function buildRecentEvents() {
  const el = document.getElementById('recentEvents');
  if(!el) return;
  el.innerHTML = EVENTS.map(e => `
    <div class="list-item">
      <div class="list-icon" style="background:${e.color}18;border:1px solid ${e.color}33">
        <i class="ti ${e.icon}" style="color:${e.color}"></i>
      </div>
      <div class="list-info">
        <div class="list-title">${e.title}</div>
        <div class="list-sub">${e.sub}</div>
      </div>
      <div class="list-meta">${e.time}</div>
    </div>`).join('');
}

function buildDashTickets() {
  const el = document.getElementById('dashTickets');
  if(!el) return;
  const pMap = {High:'b-red',Medium:'b-amber',Low:'b-gray',Critical:'b-red'};
  el.innerHTML = TICKETS.map(t => `
    <div class="list-item">
      <div class="list-info">
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-family:var(--font-mono);font-size:10px;color:var(--t3)">${t.id}</span>
          <div class="list-title">${t.title}</div>
        </div>
        <div class="list-sub">${t.reporter} · ${t.date}</div>
      </div>
      <span class="badge ${pMap[t.priority]}">${t.priority}</span>
    </div>`).join('');
}

function buildStorage() {
  const el = document.getElementById('storageItems');
  if(!el) return;
  el.innerHTML = STORAGE.map(s => `
    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
        <span style="color:var(--t1);font-weight:500">${s.label}</span>
        <span style="color:var(--t2);font-family:var(--font-mono)">${s.used} / ${s.max} ${s.unit}</span>
      </div>
      <div class="prog-bar" style="height:8px">
        <div class="prog-fill ${s.color}" style="width:${Math.round(s.used/s.max*100)}%"></div>
      </div>
    </div>`).join('');
}

function buildTickets() {
  const pMap = {High:'b-red',Medium:'b-amber',Low:'b-gray',Critical:'b-red'};
  const sMap = {open:'b-amber',in_progress:'b-cyan',resolved:'b-green',closed:'b-gray'};
  document.getElementById('ticketsBody').innerHTML = TICKETS.map(t => `
    <tr>
      <td style="font-family:var(--font-mono);font-size:11px;color:var(--cyan)">${t.id}</td>
      <td style="font-weight:500">${t.title}</td>
      <td style="color:var(--t2)">${t.reporter}</td>
      <td><span class="badge b-gray">${t.cat}</span></td>
      <td><span class="badge ${pMap[t.priority]}">${t.priority}</span></td>
      <td><span class="badge ${sMap[t.status]}">${t.status.replace('_',' ')}</span></td>
      <td style="font-size:11px;color:var(--t3);font-family:var(--font-mono)">${t.date}</td>
      <td><div style="display:flex;gap:4px">
        <button class="btn-icon" onclick="showToast('Viewing ${t.id}','cyan')"><i class="ti ti-eye"></i></button>
        <button class="btn-icon" onclick="showToast('${t.id} marked in-progress','amber')"><i class="ti ti-player-play"></i></button>
        <button class="btn-icon" onclick="showToast('${t.id} resolved','green')"><i class="ti ti-check"></i></button>
      </div></td>
    </tr>`).join('');
}

function buildAudit() {
  const aMap = {login:'b-green',upload:'b-cyan',download:'b-purple',update:'b-amber',login_failed:'b-red',delete:'b-red'};
  document.getElementById('auditBody').innerHTML = AUDIT.map(a => `
    <tr>
      <td style="font-family:var(--font-mono);font-size:11px;color:var(--t3)">${a.time}</td>
      <td style="font-weight:500">${a.user}</td>
      <td><span class="badge ${aMap[a.action]||'b-gray'}">${a.action}</span></td>
      <td><span class="badge b-gray">${a.module}</span></td>
      <td style="font-size:12px;color:var(--t2)">${a.desc}</td>
      <td style="font-family:var(--font-mono);font-size:11px;color:var(--t3)">${a.ip}</td>
    </tr>`).join('');
}

function buildReports() {
  document.getElementById('reportsBody').innerHTML = REPORTS_DATA.map(r => `
    <tr>
      <td style="font-weight:500">${r.name}</td>
      <td><span class="badge b-cyan">${r.type}</span></td>
      <td>${r.by}</td>
      <td style="font-size:11px;color:var(--t3);font-family:var(--font-mono)">${r.date}</td>
      <td><span class="badge b-gray">${r.fmt}</span></td>
      <td><button class="btn btn-ghost btn-sm" onclick="showToast('Downloading report...','cyan')"><i class="ti ti-download"></i> Download</button></td>
    </tr>`).join('');
}

function buildServerList() {
  const el = document.getElementById('serverList');
  if(!el) return;
  el.innerHTML = SERVERS_DATA.map(s => `
    <div class="server-card">
      <div class="server-header">
        <div class="server-dot s-${s.status==='online'?'online':s.status==='warn'?'warn':'offline'}"></div>
        <div style="flex:1">
          <div class="server-name">${s.name}</div>
          <div style="font-size:10px;color:var(--t3);font-family:var(--font-mono)">${s.role} · ${s.os}</div>
        </div>
        <div class="server-ip">${s.ip}</div>
      </div>
      <div class="server-metrics">
        <div class="server-metric">
          <div class="sm-label">CPU</div>
          <div class="sm-val" style="color:${s.cpu>70?'var(--red)':s.cpu>50?'var(--amber)':'var(--green)'}">${s.cpu}%</div>
          <div class="prog-bar"><div class="prog-fill ${s.cpu>70?'pf-red':s.cpu>50?'pf-amber':'pf-green'}" style="width:${s.cpu}%"></div></div>
        </div>
        <div class="server-metric">
          <div class="sm-label">RAM</div>
          <div class="sm-val" style="color:${s.ram>80?'var(--red)':s.ram>60?'var(--amber)':'var(--green)'}">${s.ram}%</div>
          <div class="prog-bar"><div class="prog-fill ${s.ram>80?'pf-red':s.ram>60?'pf-amber':'pf-green'}" style="width:${s.ram}%"></div></div>
        </div>
        <div class="server-metric">
          <div class="sm-label">DISK</div>
          <div class="sm-val" style="color:${s.disk>80?'var(--red)':s.disk>60?'var(--amber)':'var(--green)'}">${s.disk}%</div>
          <div class="prog-bar"><div class="prog-fill ${s.disk>80?'pf-red':s.disk>60?'pf-amber':'pf-green'}" style="width:${s.disk}%"></div></div>
        </div>
      </div>
    </div>`).join('');
}

function buildNetworkDevices() {
  document.getElementById('networkDevices').innerHTML = NETWORK_DEVICES.map(d => `
    <tr>
      <td style="font-weight:500">${d.name}</td>
      <td><span class="badge b-gray">${d.type}</span></td>
      <td style="font-family:var(--font-mono);font-size:11px;color:var(--t2)">${d.ip}</td>
      <td><span class="badge ${d.status==='online'?'b-green':'b-amber'}">${d.status==='online'?'● Online':'⚠ Warning'}</span></td>
      <td><button class="btn-icon" onclick="showToast('Pinging '+${JSON.stringify(d.ip)}+'...','cyan')"><i class="ti ti-ping-pong"></i></button></td>
    </tr>`).join('');
}

function buildRolesTable() {
  const el = document.getElementById('rolesTable');
  if(!el) return;
  el.innerHTML = `
    <thead><tr>
      <th>Role</th><th>Documents</th><th>Tasks</th><th>Users</th><th>Attendance</th><th>Reports</th><th>Audit</th><th>Settings</th>
    </tr></thead>
    <tbody>${ROLES_MATRIX.map(r => `
      <tr>
        <td style="font-weight:500;font-family:var(--font-mono);font-size:11px">${r.role}</td>
        ${['docs','tasks','users','attendance','reports','audit','settings'].map(k=>`
        <td style="text-align:center">
          <span style="color:${r[k]==='✓'?'var(--green)':r[k]==='—'?'var(--t3)':'var(--amber)'};font-size:15px">${r[k]}</span>
        </td>`).join('')}
      </tr>`).join('')}
    </tbody>`;
}

function buildLoginActivity() {
  const el = document.getElementById('loginActivity');
  if(!el) return;
  el.innerHTML = AUDIT.slice(0,5).map(a => `
    <div class="list-item">
      <div class="list-icon" style="background:${a.action.includes('fail')?'var(--red-dim)':'var(--green-dim)'};border:1px solid ${a.action.includes('fail')?'rgba(239,68,68,.2)':'rgba(34,197,94,.2)'}">
        <i class="ti ti-${a.action.includes('fail')?'shield-x':'login'}" style="color:${a.action.includes('fail')?'var(--red)':'var(--green)'}"></i>
      </div>
      <div class="list-info">
        <div class="list-title">${a.user}</div>
        <div class="list-sub">${a.desc}</div>
      </div>
      <div class="list-meta">${a.time}</div>
    </div>`).join('');
}

function buildBackupHistory() {
  document.getElementById('backupHistory').innerHTML = BACKUPS.map(b => `
    <tr>
      <td style="font-family:var(--font-mono);font-size:11px">${b.date}</td>
      <td><span class="badge b-gray">${b.type}</span></td>
      <td style="font-family:var(--font-mono);color:var(--t2)">${b.size}</td>
      <td><span class="badge ${b.status==='success'?'b-green':'b-red'}">${b.status}</span></td>
      <td>
        ${b.status==='success'?`<button class="btn-icon" onclick="showToast('Downloading backup...','cyan')"><i class="ti ti-download"></i></button>`:''}
        <button class="btn-icon" onclick="showToast('Restoring from backup...','amber')"><i class="ti ti-restore"></i></button>
      </td>
    </tr>`).join('');
}

function buildNotifications() {
  const el = document.getElementById('notifList');
  if(!el) return;
  el.innerHTML = NOTIFS.map(n => `
    <div class="card" style="border-color:${n.unread?'var(--border2)':'var(--border)'}">
      <div class="card-body" style="padding:14px 18px;display:flex;align-items:flex-start;gap:14px">
        <div style="width:40px;height:40px;border-radius:10px;background:${n.color}18;border:1px solid ${n.color}33;
             display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="ti ${n.icon}" style="font-size:20px;color:${n.color}"></i>
        </div>
        <div style="flex:1">
          <div style="font-weight:${n.unread?'600':'400'};margin-bottom:3px">${n.title}</div>
          <div style="font-size:12px;color:var(--t2)">${n.body}</div>
          <div style="font-size:11px;color:var(--t3);font-family:var(--font-mono);margin-top:6px">${n.time}</div>
        </div>
        ${n.unread?`<div style="width:8px;height:8px;border-radius:50%;background:var(--cyan);margin-top:6px;flex-shrink:0;box-shadow:0 0 6px var(--cyan)"></div>`:''}
      </div>
    </div>`).join('');
}

// ── Navigation ────────────────────────────────────────────────────────────
function showSection(id, linkEl) {
  document.querySelectorAll('.section').forEach(s => s.style.display='none');
  document.querySelectorAll('.sb-item').forEach(i => i.classList.remove('active'));
  const el = document.getElementById('sec-'+id);
  if(el) el.style.display='block';
  if(linkEl) linkEl.classList.add('active');
  document.getElementById('breadcrumb').textContent = id.toUpperCase().replace('-',' ');
  closeSidebar();

  // Lazy render
  if(id==='users')    renderUsers(USERS);
  if(id==='audit')    buildAudit();
  if(id==='tickets')  buildTickets();
  if(id==='reports')  buildReports();
  if(id==='servers')  { buildServerList(); buildNetworkDevices(); buildBandwidthChart(); }
  if(id==='security') { buildRolesTable(); buildLoginActivity(); }
  if(id==='backups')  buildBackupHistory();
  if(id==='notifications') buildNotifications();
  return false;
}

// ── Modals ────────────────────────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('click', e => {
  if(e.target.classList.contains('modal-backdrop')) closeModal(e.target.id);
});

// ── Tabs ──────────────────────────────────────────────────────────────────
function switchTab(btn, panelId) {
  btn.closest('.tabs').querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  btn.closest('.section').querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById(panelId).classList.add('active');
}

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(msg, type='cyan') {
  const t = document.getElementById('toast');
  const icon = document.getElementById('toastIcon');
  const icons = {cyan:'ti-info-circle',green:'ti-circle-check',amber:'ti-alert-triangle',red:'ti-alert-circle'};
  const colors = {cyan:'var(--cyan)',green:'var(--green)',amber:'var(--amber)',red:'var(--red)'};
  document.getElementById('toastMsg').textContent = msg;
  icon.className = 'ti ' + (icons[type]||'ti-info-circle');
  icon.style.color = colors[type]||'var(--cyan)';
  t.style.display='flex'; t.style.borderColor=colors[type];
  setTimeout(()=>{ t.style.opacity='1'; t.style.transform='translateY(0)'; },10);
  setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateY(20px)';
    setTimeout(()=>t.style.display='none',300); },3500);
}

// ── Sidebar mobile ────────────────────────────────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sbOverlay').classList.toggle('visible');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sbOverlay').classList.remove('visible');
}

// ── Refresh animation ─────────────────────────────────────────────────────
function animateRefresh(btn) {
  btn.querySelector('i').style.animation='spin .7s linear';
  setTimeout(()=>{ btn.querySelector('i').style.animation=''; showToast('Data refreshed','green'); },700);
}

// ── Clock ─────────────────────────────────────────────────────────────────
function updateClock() {
  const n = new Date();
  document.getElementById('clock').textContent =
    n.toLocaleDateString('en-NG',{weekday:'short',day:'2-digit',month:'short',year:'numeric'})
    + ' ' + n.toLocaleTimeString('en-NG',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}

// ── Init ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  buildLoginChart();
  buildRecentEvents();
  buildDashTickets();
  buildStorage();
  updateClock();
  setInterval(updateClock, 1000);
  // Animate progress bars
  setTimeout(()=>{
    document.querySelectorAll('.prog-fill').forEach(b=>{
      const w = b.style.width; b.style.width='0'; setTimeout(()=>b.style.width=w,50);
    });
  },100);
});

// Add spin keyframe
const s = document.createElement('style');
s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(s);
</script>
</body>
</html>