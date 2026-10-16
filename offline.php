<?php
declare(strict_types=1);

$pageTitle = 'Modo Offline — Via Scriptura';
$pageDescription = 'Você está sem conexão no momento. Acesse suas leituras bíblicas salvas em cache no dispositivo.';
$activeNav = 'home';
$bodyClass = 'page-offline';

require __DIR__ . '/includes/header.php';
?>

<main class="offline-shell">
  <section class="offline-card">
    <div class="offline-icon-wrap" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="1" y1="1" x2="23" y2="23"></line>
        <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
        <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
        <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
        <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
        <line x1="12" y1="20" x2="12.01" y2="20"></line>
      </svg>
    </div>

    <span class="section-kicker">Modo Offline Ativo</span>
    <h1 class="offline-title">Você está sem conexão com a internet</h1>
    <p class="offline-desc">
      Esta página ou capítulo específico ainda não foi salvo no cache do seu aparelho.
      No entanto, todas as páginas e capítulos que você já visitou permanecem disponíveis para leitura offline.
    </p>

    <blockquote class="offline-verse">
      <p>“Lâmpada para os meus pés é a tua palavra e, luz para os meus caminhos.”</p>
      <cite>Salmos 119:105 (NAA)</cite>
    </blockquote>

    <div class="offline-actions">
      <button type="button" onclick="window.location.reload()" class="btn-modern-primary">
        <span>Tentar Novamente</span>
      </button>
      <a href="index.php" class="btn-modern-secondary">
        <span>Ir para Leitura do Dia</span>
      </a>
      <a href="bible.php" class="btn-modern-secondary">
        <span>Abrir Bíblia Salva</span>
      </a>
      <a href="stories.php" class="btn-modern-secondary">
        <span>366 Histórias</span>
      </a>
    </div>

    <!-- Lista automática de páginas/capítulos disponíveis no Cache Storage -->
    <div id="offline-cached-container" class="offline-cached-container" style="display: none;">
      <h2 class="offline-cached-heading">Leituras Disponíveis Offline neste Dispositivo</h2>
      <div id="offline-cached-list" class="offline-cached-list"></div>
    </div>
  </section>
</main>

<script>
  (async function loadCachedPages() {
    if (!('caches' in window)) return;
    try {
      const cacheNames = await caches.keys();
      const items = [];
      const seen = new Set();

      for (const name of cacheNames) {
        if (!name.startsWith('vs-')) continue;
        const cache = await caches.open(name);
        const requests = await cache.keys();
        for (const req of requests) {
          const url = new URL(req.url);
          if (url.origin !== location.origin) continue;
          if (
            url.pathname.endsWith('.css') ||
            url.pathname.endsWith('.js') ||
            url.pathname.endsWith('.png') ||
            url.pathname.endsWith('.svg') ||
            url.pathname.endsWith('.webmanifest') ||
            url.pathname.endsWith('offline.php') ||
            url.pathname.includes('/api/')
          ) {
            continue;
          }

          const fullPath = url.pathname + url.search;
          if (seen.has(fullPath)) continue;
          seen.add(fullPath);

          let label = 'Página Salva';
          const params = url.searchParams;
          if (url.pathname.endsWith('bible.php')) {
            const book = params.get('book');
            const cap = params.get('chapter') || '1';
            label = book ? `Bíblia: ${book.toUpperCase()} — Cap. ${cap}` : 'Bíblia Completa (NAA)';
          } else if (url.pathname === '/' || url.pathname.endsWith('index.php')) {
            const date = params.get('date');
            label = date ? `Leitura do Dia (${date})` : 'Leitura do Dia (Início)';
          } else if (url.pathname.endsWith('stories.php')) {
            label = 'Catálogo das 366 Histórias Bíblicas';
          } else if (url.pathname.endsWith('divisions.php')) {
            label = 'Separação Bíblica (Eras & Divisões)';
          } else if (url.pathname.endsWith('about.php')) {
            label = 'Sobre o Via Scriptura';
          }

          items.push({ href: fullPath, label });
        }
      }

      if (items.length > 0) {
        const container = document.getElementById('offline-cached-container');
        const list = document.getElementById('offline-cached-list');
        if (container && list) {
          list.innerHTML = items
            .slice(0, 24)
            .map(item => `<a href="${item.href}" class="offline-cached-link">${item.label}</a>`)
            .join('');
          container.style.display = 'block';
        }
      }
    } catch (e) {
      // Silencioso
    }
  })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
