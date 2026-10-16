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

if (!isset($allBooks) || !is_array($allBooks)) {
    require_once dirname(__DIR__) . '/src/BibleRepository.php';
    $headerRepo = $repo ?? new \ViaScriptura\BibleRepository();
    $allBooks = $headerRepo->getBooks();
}
$drawerActiveBook = $activeBook ?? ($chapterView['book'] ?? ($allBooks[0] ?? ['url_abbrev' => 'gn', 'name' => 'Gênesis', 'chapters_count' => 50]));
$drawerActiveBookAbbrev = (string)($drawerActiveBook['url_abbrev'] ?? 'gn');
$drawerActiveChapter = (int)($activeChapter ?? ($chapterView['chapter'] ?? 1));
$drawerActiveTotalChapters = (int)($drawerActiveBook['chapters_count'] ?? 50);
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
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
          <svg viewBox="0 0 64 64" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Raios de Luz -->
            <path d="M32 5V8M23 8L25 11M41 8L39 11M17 14L20 16M47 14L44 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.65"/>
            <!-- Cruz Sagrada -->
            <path d="M32 9V25M26 14H38" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <!-- Caminho ("Via") nas Escrituras -->
            <path d="M32 28C30.5 36 26.5 43 22 48H42C37.5 43 33.5 36 32 28Z" fill="currentColor" fill-opacity="0.22"/>
            <!-- Bíblia Sagrada Aberta -->
            <path d="M10 26H24C28.4 26 31 28.5 32 32V52C30.2 49.2 27.2 48 23 48H10V26Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M54 26H40C35.6 26 33 28.5 32 32V52C33.8 49.2 36.8 48 41 48H54V26Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <!-- Linhas de Escritura -->
            <path d="M15 33H24M15 39H25M39 33H49M39 39H49" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" opacity="0.75"/>
            <!-- Marcador Central -->
            <path d="M29 52L32 58L35 52" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
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

        <!-- Botão de Menu Retrátil Mobile (Menu + Navegação da Bíblia) -->
        <button
          type="button"
          id="mobile-menu-toggle"
          class="mobile-menu-toggle"
          aria-label="Abrir menu e navegação da Bíblia"
          aria-expanded="false"
          aria-controls="mobile-drawer"
        >
          <svg class="icon-menu-bars" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" aria-hidden="true">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <span>Menu</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Menu Retrátil Mobile & Navegação Completa da Bíblia (Drawer) -->
  <div id="mobile-drawer" class="mobile-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menu principal e navegação da Bíblia">
    <div class="mobile-drawer-backdrop" id="mobile-drawer-backdrop"></div>
    <aside class="mobile-drawer-panel">
      <!-- Topo do Menu Retrátil -->
      <div class="mobile-drawer-header">
        <div class="mobile-drawer-brand">
          <span class="brand-emblem brand-emblem--sm" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M32 9V25M26 14H38" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              <path d="M10 26H24C28.4 26 31 28.5 32 32V52C30.2 49.2 27.2 48 23 48H10V26Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M54 26H40C35.6 26 33 28.5 32 32V52C33.8 49.2 36.8 48 41 48H54V26Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <div>
            <strong>Via <em>Scriptura</em></strong>
            <span>Menu & Navegação Bíblica</span>
          </div>
        </div>
        <button type="button" id="mobile-drawer-close" class="mobile-drawer-close" aria-label="Fechar menu">✕</button>
      </div>

      <!-- Busca Rápida dentro do Menu Retrátil -->
      <div class="mobile-drawer-search">
        <form action="bible.php" method="get" class="drawer-search-form" role="search">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input
            type="search"
            name="q"
            placeholder="Buscar versículo, palavra ou referência..."
            aria-label="Buscar na Bíblia Sagrada"
            value="<?= isset($_GET['q']) && $activeNav === 'bible' ? htmlspecialchars((string)$_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>"
          />
          <button type="submit" class="drawer-search-submit">Buscar</button>
        </form>
      </div>

      <!-- Conteúdo Rolável do Menu Retrátil -->
      <div class="mobile-drawer-body">
        <!-- Seção 1: Menu de Páginas do Site (Retrátil) -->
        <details class="drawer-section" open>
          <summary class="drawer-section-summary">
            <span class="drawer-summary-title">
              <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
              </svg>
              <span>Navegação do Site</span>
            </span>
            <span class="drawer-chevron" aria-hidden="true">▾</span>
          </summary>

          <nav class="drawer-nav-list" aria-label="Páginas do Via Scriptura">
            <a href="index.php" class="drawer-nav-link <?= $activeNav === 'home' ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
              </svg>
              <span>Leitura do Dia</span>
              <small class="drawer-link-tag">366 Dias</small>
            </a>
            <a href="bible.php" class="drawer-nav-link <?= ($activeNav === 'bible' && ($_GET['view'] ?? '') !== 'panorama') ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              <span>Bíblia Completa (NAA)</span>
              <small class="drawer-link-tag">1.189 Cap.</small>
            </a>
            <a href="bible.php?view=panorama" class="drawer-nav-link <?= ($activeNav === 'bible' && ($_GET['view'] ?? '') === 'panorama') ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                <line x1="12" y1="4" x2="12" y2="20"></line>
              </svg>
              <span>Panorama dos 66 Livros</span>
              <small class="drawer-link-tag">AT & NT</small>
            </a>
            <a href="stories.php" class="drawer-nav-link <?= $activeNav === 'stories' ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
              </svg>
              <span>Histórias Bíblicas</span>
              <small class="drawer-link-tag">586</small>
            </a>
            <a href="divisions.php" class="drawer-nav-link <?= $activeNav === 'divisions' ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
              <span>Separação Bíblica</span>
              <small class="drawer-link-tag">Eras & Livros</small>
            </a>
            <a href="about.php" class="drawer-nav-link <?= $activeNav === 'about' ? 'active' : '' ?>">
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              <span>Sobre o Projeto</span>
            </a>
          </nav>
        </details>

        <!-- Seção 2: Navegação da Bíblia Sagrada (Livros e Capítulos Retráteis) -->
        <details class="drawer-section drawer-section--bible" id="drawer-bible-section" open>
          <summary class="drawer-section-summary">
            <span class="drawer-summary-title">
              <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
              </svg>
              <span>Navegação da Bíblia (66 Livros)</span>
            </span>
            <span class="drawer-chevron" aria-hidden="true">▾</span>
          </summary>

          <div class="drawer-bible-controls">
            <!-- Seletor Direto de Livro e Capítulo -->
            <div class="drawer-quick-jump">
              <div class="drawer-quick-selects">
                <select id="drawer-book-select" class="bible-select" aria-label="Selecionar Livro da Bíblia">
                  <optgroup label="Antigo Testamento (39)">
                    <?php foreach ($allBooks as $b): ?>
                      <?php if (($b['testament'] ?? 'VT') === 'VT'): ?>
                        <option
                          value="<?= htmlspecialchars((string)$b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                          data-chapters="<?= (int)$b['chapters_count'] ?>"
                          <?= $b['url_abbrev'] === $drawerActiveBookAbbrev ? 'selected' : '' ?>
                        >
                          <?= htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </optgroup>
                  <optgroup label="Novo Testamento (27)">
                    <?php foreach ($allBooks as $b): ?>
                      <?php if (($b['testament'] ?? '') === 'NT'): ?>
                        <option
                          value="<?= htmlspecialchars((string)$b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                          data-chapters="<?= (int)$b['chapters_count'] ?>"
                          <?= $b['url_abbrev'] === $drawerActiveBookAbbrev ? 'selected' : '' ?>
                        >
                          <?= htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </optgroup>
                </select>

                <select id="drawer-chapter-select" class="bible-select" aria-label="Selecionar Capítulo">
                  <?php for ($c = 1; $c <= $drawerActiveTotalChapters; $c++): ?>
                    <option value="<?= $c ?>" <?= $c === $drawerActiveChapter ? 'selected' : '' ?>>
                      Cap. <?= $c ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>

              <button type="button" id="drawer-go-bible-btn" class="btn-modern-primary drawer-go-btn">
                <span>Abrir Capítulo Selecionado</span>
                <span aria-hidden="true">→</span>
              </button>
            </div>

            <!-- Filtro de Testamento e Busca de Livro -->
            <div class="drawer-testament-tabs" role="tablist" aria-label="Filtrar livros no menu">
              <button type="button" class="drawer-testament-tab active" data-drawer-testament="ALL">Todos (66)</button>
              <button type="button" class="drawer-testament-tab" data-drawer-testament="VT">Antigo (39)</button>
              <button type="button" class="drawer-testament-tab" data-drawer-testament="NT">Novo (27)</button>
            </div>

            <input
              type="search"
              id="drawer-book-filter"
              class="sidebar-book-filter"
              placeholder="Filtrar livro (ex: Salmos, João, Romanos)..."
              aria-label="Filtrar livros da Bíblia no menu"
            />

            <!-- Acordeão de Todos os 66 Livros com Grade de Capítulos Retrátil -->
            <div class="drawer-books-accordion" id="drawer-books-accordion">
              <?php foreach ($allBooks as $b): ?>
                <?php
                  $bAbbrev = (string)$b['url_abbrev'];
                  $bChapters = (int)$b['chapters_count'];
                  $isCurrentBook = ($bAbbrev === $drawerActiveBookAbbrev);
                ?>
                <details
                  class="drawer-book-group <?= $isCurrentBook ? 'is-active-book' : '' ?>"
                  data-testament="<?= htmlspecialchars((string)($b['testament'] ?? 'VT'), ENT_QUOTES, 'UTF-8') ?>"
                  data-book-name="<?= htmlspecialchars(\ViaScriptura\BibleRepository::stripAccents((string)$b['name'] . ' ' . (string)$b['abbrev'] . ' ' . (string)$b['category_label']), ENT_QUOTES, 'UTF-8') ?>"
                  data-book-abbrev="<?= htmlspecialchars($bAbbrev, ENT_QUOTES, 'UTF-8') ?>"
                  <?= $isCurrentBook ? 'open' : '' ?>
                >
                  <summary class="drawer-book-summary">
                    <span class="drawer-book-badge"><?= htmlspecialchars(mb_strtoupper((string)$b['abbrev'], 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="drawer-book-meta">
                      <strong><?= htmlspecialchars((string)$b['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <small><?= htmlspecialchars((string)$b['category_short'], ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                    <span class="drawer-book-count"><?= $bChapters ?> cap.</span>
                    <span class="drawer-book-chevron" aria-hidden="true">▾</span>
                  </summary>

                  <div class="drawer-book-chapters">
                    <?php for ($c = 1; $c <= $bChapters; $c++): ?>
                      <a
                        href="bible.php?book=<?= urlencode($bAbbrev) ?>&ch=<?= $c ?>"
                        class="drawer-chapter-pill <?= ($isCurrentBook && $c === $drawerActiveChapter) ? 'active' : '' ?>"
                        data-drawer-book="<?= htmlspecialchars($bAbbrev, ENT_QUOTES, 'UTF-8') ?>"
                        data-drawer-ch="<?= $c ?>"
                      ><?= $c ?></a>
                    <?php endfor; ?>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          </div>
        </details>
      </div>
    </aside>
  </div>
