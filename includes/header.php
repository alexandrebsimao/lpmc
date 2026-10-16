<?php
/**
 * includes/header.php
 * Cabeçalho global e barra de navegação moderna do Via Scriptura.
 */

$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$isHttps = ($forwardedProto === 'https') || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$detectedScheme = $isHttps ? 'https' : 'http';
$detectedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'www.viascriptura.com';
$baseAppUrl = rtrim((string)(getenv('APP_URL') ?: ($detectedScheme . '://' . $detectedHost)), '/');

$pageTitle = $pageTitle ?? 'Via Scriptura — Leitura Bíblica Diária & Bíblia Completa (NAA)';
$pageDescription = $pageDescription ?? 'Leia histórias bíblicas diárias ilustradas e a Bíblia Sagrada completa na versão Nova Almeida Atualizada (NAA).';
$pageKeywords = $pageKeywords ?? 'leitura bíblica, Bíblia completa, Nova Almeida Atualizada, NAA, histórias bíblicas, devocional diário, versículos';
$pageUrl = $pageUrl ?? ($baseAppUrl . ($_SERVER['REQUEST_URI'] ?? '/'));
$pageImage = $pageImage ?? 'https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=1200&q=80';
$activeNav = $activeNav ?? 'home';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-P8213T4K5R"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-P8213T4K5R');
  </script>

  <!-- Restaura tema salvo imediatamente para evitar flash -->
  <script>
    (function() {
      try {
        var savedTheme = localStorage.getItem('vs-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
      } catch (e) {}
    })();
  </script>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="keywords" content="<?= htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="theme-color" content="#0d0b09" id="meta-theme-color" />
  <meta name="robots" content="index, follow" />
  <meta name="language" content="Portuguese" />
  <meta name="author" content="Via Scriptura" />

  <!-- Progressive Web App (PWA) & Mobile Web App Meta -->
  <link rel="manifest" href="manifest.webmanifest" />
  <link rel="icon" type="image/svg+xml" href="assets/icons/icon.svg" />
  <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/icon-192.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/apple-touch-icon.png" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="Via Scriptura" />
  <meta name="application-name" content="Via Scriptura" />

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($pageImage, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:locale" content="pt_BR" />

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:url" content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:image" content="<?= htmlspecialchars($pageImage, ENT_QUOTES, 'UTF-8') ?>" />

  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="canonical" href="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <link rel="alternate" hreflang="pt-BR" href="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>" />

  <!-- Fontes Modernas: Plus Jakarta Sans (UI), Cormorant Garamond (Display) e Lora (Leitura Bíblica) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Lora:ital,wght@0,400;0,500;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Barra de Navegação Global Moderna -->
  <header class="site-navbar" id="site-navbar">
    <div class="navbar-inner">
      <a href="index.php" class="navbar-brand" aria-label="Via Scriptura — Início">
        <span class="brand-emblem" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
          </svg>
        </span>
        <div class="brand-copy">
          <span class="brand-text">Via <em>Scriptura</em></span>
          <span class="brand-sub">Bíblia & Devocional</span>
        </div>
      </a>

      <nav class="navbar-links" aria-label="Menu principal">
        <a href="index.php" class="nav-item <?= $activeNav === 'home' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
          </svg>
          <span>Leitura do Dia</span>
        </a>
        <a href="bible.php" class="nav-item <?= $activeNav === 'bible' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
          </svg>
          <span>Bíblia Completa</span>
        </a>
        <a href="stories.php" class="nav-item <?= $activeNav === 'stories' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
          </svg>
          <span>Histórias <small class="nav-badge">586</small></span>
        </a>
        <a href="divisions.php" class="nav-item <?= $activeNav === 'divisions' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
            <polyline points="2 17 12 22 22 17"></polyline>
            <polyline points="2 12 12 17 22 12"></polyline>
          </svg>
          <span>Separação Bíblica</span>
        </a>
        <a href="about.php" class="nav-item <?= $activeNav === 'about' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          <span>Sobre</span>
        </a>
      </nav>

      <div class="navbar-actions">
        <form action="bible.php" method="get" class="navbar-search" role="search">
          <svg class="search-icon-left" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input
            type="search"
            name="q"
            id="global-search-input"
            placeholder="Buscar versículo ou livro..."
            aria-label="Buscar versículos ou referências na Bíblia"
            value="<?= isset($_GET['q']) && $activeNav === 'bible' ? htmlspecialchars((string)$_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>"
          />
          <kbd class="search-kbd" title="Pressione / para focar">/</kbd>
        </form>

        <!-- Botão de Instalação do App (PWA) -->
        <button
          type="button"
          id="pwa-install-btn"
          class="pwa-install-btn"
          style="display: none;"
          aria-label="Instalar aplicativo Via Scriptura"
          title="Instalar aplicativo para acesso rápido e leitura offline"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span>Instalar App</span>
        </button>

        <!-- Alternador de Tema (Escuro / Sépia / Claro) -->
        <button
          type="button"
          id="theme-toggle-btn"
          class="theme-toggle-btn"
          aria-label="Alternar tema de leitura"
          title="Alternar tema (Escuro / Sépia / Claro)"
        >
          <span class="theme-dot"></span>
          <span id="theme-label-text">Escuro</span>
        </button>

        <!-- Controle de Fonte -->
        <div class="font-controls" aria-label="Tamanho da fonte">
          <button type="button" id="font-decrease" class="font-btn" aria-label="Diminuir fonte" title="Diminuir fonte">A−</button>
          <span id="font-size-display" class="font-size-display">100%</span>
          <button type="button" id="font-increase" class="font-btn" aria-label="Aumentar fonte" title="Aumentar fonte">A+</button>
        </div>
      </div>
    </div>
  </header>
