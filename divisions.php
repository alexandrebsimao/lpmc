<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();

$eras = $repo->getStoriesSeparatedByEra();
$divisions = $repo->getStoriesSeparatedByDivision();
$definedStories = $repo->getAllStories();
$canonicalStories = $repo->getCanonicalExtraStories();
$totalCombinedCount = count($definedStories) + count($canonicalStories);

// Modo ativo: 'eras' (12 Grandes Eras Cronológicas) | 'divisions' (10 Divisões Canônicas) | 'books' (66 Livros)
$activeTab = (string)($_GET['tab'] ?? 'eras');
if (!in_array($activeTab, ['eras', 'divisions', 'books'], true)) {
    $activeTab = 'eras';
}

$pageTitle = 'Separação de Todas as Histórias Bíblicas (586 Histórias em 12 Eras e 66 Livros) — Via Scriptura';
$pageDescription = 'Explore a separação completa de todas as 586 histórias bíblicas (as 366 devocionais já definidas + 220 histórias canônicas adicionais) organizadas pelas 12 Grandes Eras, 10 Divisões e 66 Livros.';
$pageUrl = 'https://www.viascriptura.com/divisions.php';
$activeNav = 'divisions';
$bodyClass = 'page-divisions';

require __DIR__ . '/includes/header.php';
?>

  <main class="divisions-page-main">

    <!-- Hero da Tela de Separação de Histórias Bíblicas -->
    <section class="divisions-hero">
      <div class="divisions-hero-inner">
        <div class="divisions-hero-copy">
          <span class="section-kicker">Mapa Estrutural & Cronológico Completo das Escrituras</span>
          <h1>Separação de Todas as Histórias <em>Bíblicas</em></h1>
          <p class="divisions-lead">
            Navegue pelo catálogo integral de <strong><?= $totalCombinedCount ?> histórias bíblicas</strong> — unindo as <strong><?= count($definedStories) ?> histórias devocionais já definidas</strong> e <strong>+<?= count($canonicalStories) ?> histórias canônicas adicionais</strong> — cobrindo 100% dos <strong>66 Livros</strong>, <strong>10 Divisões Literárias</strong> e <strong>12 Grandes Eras da Redenção</strong>.
          </p>
        </div>

        <div class="divisions-stats-row">
          <div class="div-stat-box">
            <strong><?= $totalCombinedCount ?></strong>
            <span>Total de Histórias</span>
          </div>
          <div class="div-stat-box">
            <strong><?= count($definedStories) ?></strong>
            <span>Já Definidas (366 Dias)</span>
          </div>
          <div class="div-stat-box">
            <strong>+<?= count($canonicalStories) ?></strong>
            <span>Histórias Canônicas</span>
          </div>
          <div class="div-stat-box">
            <strong>66/66</strong>
            <span>Livros Mapeados</span>
          </div>
        </div>

        <!-- Barra de Controle: Abas de Separação + Filtro de Origem + Busca Instantânea -->
        <div class="divisions-toolbar">
          <div class="divisions-mode-tabs" role="tablist" aria-label="Critério de separação das histórias">
            <button
              type="button"
              class="div-mode-tab <?= $activeTab === 'eras' ? 'active' : '' ?>"
              data-div-tab="eras"
              role="tab"
              aria-selected="<?= $activeTab === 'eras' ? 'true' : 'false' ?>"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>12 Grandes Eras Cronológicas</span>
            </button>

            <button
              type="button"
              class="div-mode-tab <?= $activeTab === 'divisions' ? 'active' : '' ?>"
              data-div-tab="divisions"
              role="tab"
              aria-selected="<?= $activeTab === 'divisions' ? 'true' : 'false' ?>"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
              <span>10 Divisões Literárias da Bíblia</span>
            </button>

            <button
              type="button"
              class="div-mode-tab <?= $activeTab === 'books' ? 'active' : '' ?>"
              data-div-tab="books"
              role="tab"
              aria-selected="<?= $activeTab === 'books' ? 'true' : 'false' ?>"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              <span>Por Livro da Bíblia (66 Livros)</span>
            </button>
          </div>

          <!-- Filtro por Tipo de História (Todas / 366 Definidas / +220 Canônicas) -->
          <div class="divisions-origin-filters" role="group" aria-label="Filtrar por tipo de história">
            <button type="button" class="origin-filter-btn active" data-origin-filter="all">
              Todas (<?= $totalCombinedCount ?>)
            </button>
            <button type="button" class="origin-filter-btn" data-origin-filter="defined">
              366 Já Definidas
            </button>
            <button type="button" class="origin-filter-btn" data-origin-filter="canonical">
              +<?= count($canonicalStories) ?> Além das 366
            </button>
          </div>

          <div class="divisions-search-wrap">
            <input
              type="search"
              id="divisions-filter-input"
              class="search-input"
              placeholder="Filtrar história, era, livro ou referência..."
              aria-label="Filtrar histórias na tela de separação"
            />
          </div>
        </div>
      </div>
    </section>

    <div class="divisions-container">

      <!-- =====================================================================
           MODO 1: SEPARAÇÃO PELAS 12 GRANDES ERAS CRONOLÓGICAS DA BÍBLIA
           ===================================================================== -->
      <section
        id="div-pane-eras"
        class="div-pane <?= $activeTab === 'eras' ? 'active' : '' ?>"
        aria-label="Histórias separadas pelas 12 Grandes Eras Bíblicas"
      >
        <!-- Índice Rápido da Linha do Tempo -->
        <nav class="eras-timeline-nav" aria-label="Atalhos para as 12 Eras Bíblicas">
          <?php foreach ($eras as $era): ?>
            <a href="#era-<?= htmlspecialchars($era['key'], ENT_QUOTES, 'UTF-8') ?>" class="era-Jump-pill">
              <span class="era-jump-num"><?= (int)$era['order'] ?></span>
              <span class="era-jump-name"><?= htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $era['title']) ?? $era['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= count($era['stories']) ?></small>
            </a>
          <?php endforeach; ?>
        </nav>

        <?php foreach ($eras as $era): ?>
          <div
            id="era-<?= htmlspecialchars($era['key'], ENT_QUOTES, 'UTF-8') ?>"
            class="separation-block"
            data-separation-block
          >
            <div class="separation-block-header">
              <div class="separation-block-titles">
                <div class="separation-badges">
                  <span class="panorama-testament-badge">
                    <?= $era['testament'] === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
                  </span>
                  <span class="separation-range-pill"><?= htmlspecialchars($era['range'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="separation-count-pill"><?= count($era['stories']) ?> histórias (<?= (int)$era['defined_count'] ?> definidas · +<?= (int)$era['canonical_count'] ?> canônicas)</span>
                </div>
                <h2><?= htmlspecialchars($era['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($era['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>

            <div class="separated-stories-grid">
              <?php foreach ($era['stories'] as $st): ?>
                <?php
                  $origin = (string)($st['origin'] ?? 'defined');
                  $isDefined = $origin === 'defined';
                  $stSlug = (string)($st['slug'] ?? '');
                  $stDia = (string)($st['dia'] ?? '');
                  $stCat = (string)($st['categoria'] ?? 'Canônica');
                  $stTitulo = (string)($st['titulo'] ?? '');
                  $stRef = (string)($st['referencia'] ?? '');
                  $stTexto = (string)($st['texto'] ?? '');
                  $stImg = (string)($st['imagem']['url'] ?? '');
                  $stBibleUrl = (string)($st['bible_url'] ?? 'bible.php');
                  $stUrl = $isDefined
                      ? ($stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia))
                      : $stBibleUrl;
                  $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                  $stExcerpt = mb_strlen($stCleanText, 'UTF-8') > 105 ? mb_substr($stCleanText, 0, 105, 'UTF-8') . '…' : $stCleanText;
                  $filterData = BibleRepository::stripAccents($stTitulo . ' ' . $stRef . ' ' . $era['title'] . ' ' . ($isDefined ? 'dia ' . $stDia . ' definida' : $stCat . ' canonica adicional'));
                ?>
                <article
                  class="sep-story-card <?= $isDefined ? 'sep-story-card--defined' : 'sep-story-card--canonical' ?>"
                  data-sep-item="<?= htmlspecialchars($filterData, ENT_QUOTES, 'UTF-8') ?>"
                  data-story-origin="<?= htmlspecialchars($origin, ENT_QUOTES, 'UTF-8') ?>"
                >
                  <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-story-thumb-wrap <?= $isDefined ? '' : 'sep-thumb--canonical' ?>">
                    <?php if ($isDefined && $stImg !== ''): ?>
                      <img src="<?= htmlspecialchars($stImg, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
                      <span class="sep-day-tag">Dia <?= htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                      <div class="canonical-emblem" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="1.6" fill="none">
                          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                        <span><?= htmlspecialchars((string)($st['book']['abbrev'] ?? 'NAA'), ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                      <span class="sep-day-tag sep-day-tag--canonical"><?= htmlspecialchars($stCat, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                  </a>

                  <div class="sep-story-body">
                    <div class="sep-story-meta-line">
                      <span class="sep-story-ref"><?= htmlspecialchars($stRef, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="sep-origin-pill <?= $isDefined ? 'origin-pill--defined' : 'origin-pill--canonical' ?>">
                        <?= $isDefined ? '366 Definida' : 'Canônica Extra' ?>
                      </span>
                    </div>
                    <h3>
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                    <p class="sep-story-excerpt"><?= htmlspecialchars($stExcerpt, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="sep-story-actions">
                      <?php if ($isDefined): ?>
                        <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler História</a>
                        <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-secondary">Capítulo</a>
                      <?php else: ?>
                        <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler Passagem na Bíblia</a>
                      <?php endif; ?>
                      <button
                        type="button"
                        class="sep-share-btn btn-share-trigger"
                        data-share-type="História Bíblica"
                        data-share-title="<?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>"
                        data-share-ref="<?= htmlspecialchars($stRef . ' (NAA)', ENT_QUOTES, 'UTF-8') ?>"
                        data-share-text="<?= htmlspecialchars($stCleanText, ENT_QUOTES, 'UTF-8') ?>"
                        data-share-url="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>"
                        title="Compartilhar esta história"
                      >
                        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                          <circle cx="18" cy="5" r="3"></circle>
                          <circle cx="6" cy="12" r="3"></circle>
                          <circle cx="18" cy="19" r="3"></circle>
                          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                          <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        <span>Compartilhar</span>
                      </button>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <!-- =====================================================================
           MODO 2: SEPARAÇÃO PELAS 10 DIVISÕES LITERÁRIAS CANÔNICAS DA BÍBLIA
           ===================================================================== -->
      <section
        id="div-pane-divisions"
        class="div-pane <?= $activeTab === 'divisions' ? 'active' : '' ?>"
        aria-label="Histórias separadas pelas 10 Divisões Literárias da Bíblia"
      >
        <nav class="eras-timeline-nav" aria-label="Atalhos para as 10 Divisões Canônicas">
          <?php foreach ($divisions as $div): ?>
            <a href="#div-<?= htmlspecialchars($div['key'], ENT_QUOTES, 'UTF-8') ?>" class="era-Jump-pill">
              <span class="era-jump-name"><?= htmlspecialchars($div['label'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= (int)$div['stories_count'] ?></small>
            </a>
          <?php endforeach; ?>
        </nav>

        <?php foreach ($divisions as $div): ?>
          <div
            id="div-<?= htmlspecialchars($div['key'], ENT_QUOTES, 'UTF-8') ?>"
            class="separation-block"
            data-separation-block
          >
            <div class="separation-block-header">
              <div class="separation-block-titles">
                <div class="separation-badges">
                  <span class="panorama-testament-badge">
                    <?= $div['testament'] === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
                  </span>
                  <span class="separation-range-pill"><?= count($div['books']) ?> <?= count($div['books']) === 1 ? 'livro' : 'livros' ?></span>
                  <span class="separation-count-pill"><?= (int)$div['stories_count'] ?> histórias (<?= (int)$div['defined_count'] ?> definidas · +<?= (int)$div['canonical_count'] ?> canônicas)</span>
                </div>
                <h2><?= htmlspecialchars($div['label'], ENT_QUOTES, 'UTF-8') ?> — <em><?= htmlspecialchars($div['subtitle'], ENT_QUOTES, 'UTF-8') ?></em></h2>
                <p><?= htmlspecialchars($div['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>

              <!-- Pílulas dos Livros que compõem esta Divisão -->
              <div class="division-books-chips">
                <?php foreach ($div['books'] as $b): ?>
                  <a
                    href="bible.php?book=<?= urlencode($b['url_abbrev']) ?>&ch=1"
                    class="division-book-chip"
                    title="Abrir <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?> na Bíblia Completa"
                  >
                    <strong><?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= (int)$b['stories_count'] ?> hist. · <?= (int)$b['chapters_count'] ?> cap.</span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="separated-stories-grid">
              <?php foreach ($div['stories'] as $st): ?>
                <?php
                  $origin = (string)($st['origin'] ?? 'defined');
                  $isDefined = $origin === 'defined';
                  $stSlug = (string)($st['slug'] ?? '');
                  $stDia = (string)($st['dia'] ?? '');
                  $stCat = (string)($st['categoria'] ?? 'Canônica');
                  $stTitulo = (string)($st['titulo'] ?? '');
                  $stRef = (string)($st['referencia'] ?? '');
                  $stTexto = (string)($st['texto'] ?? '');
                  $stImg = (string)($st['imagem']['url'] ?? '');
                  $stBibleUrl = (string)($st['bible_url'] ?? 'bible.php');
                  $stUrl = $isDefined
                      ? ($stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia))
                      : $stBibleUrl;
                  $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                  $stExcerpt = mb_strlen($stCleanText, 'UTF-8') > 105 ? mb_substr($stCleanText, 0, 105, 'UTF-8') . '…' : $stCleanText;
                  $filterData = BibleRepository::stripAccents($stTitulo . ' ' . $stRef . ' ' . $div['label'] . ' ' . ($isDefined ? 'dia ' . $stDia . ' definida' : $stCat . ' canonica adicional'));
                ?>
                <article
                  class="sep-story-card <?= $isDefined ? 'sep-story-card--defined' : 'sep-story-card--canonical' ?>"
                  data-sep-item="<?= htmlspecialchars($filterData, ENT_QUOTES, 'UTF-8') ?>"
                  data-story-origin="<?= htmlspecialchars($origin, ENT_QUOTES, 'UTF-8') ?>"
                >
                  <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-story-thumb-wrap <?= $isDefined ? '' : 'sep-thumb--canonical' ?>">
                    <?php if ($isDefined && $stImg !== ''): ?>
                      <img src="<?= htmlspecialchars($stImg, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
                      <span class="sep-day-tag">Dia <?= htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                      <div class="canonical-emblem" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="1.6" fill="none">
                          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                        <span><?= htmlspecialchars((string)($st['book']['abbrev'] ?? 'NAA'), ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                      <span class="sep-day-tag sep-day-tag--canonical"><?= htmlspecialchars($stCat, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                  </a>

                  <div class="sep-story-body">
                    <div class="sep-story-meta-line">
                      <span class="sep-story-ref"><?= htmlspecialchars($stRef, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="sep-origin-pill <?= $isDefined ? 'origin-pill--defined' : 'origin-pill--canonical' ?>">
                        <?= $isDefined ? '366 Definida' : 'Canônica Extra' ?>
                      </span>
                    </div>
                    <h3>
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                    <p class="sep-story-excerpt"><?= htmlspecialchars($stExcerpt, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="sep-story-actions">
                      <?php if ($isDefined): ?>
                        <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler História</a>
                        <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-secondary">Capítulo</a>
                      <?php else: ?>
                        <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler Passagem na Bíblia</a>
                      <?php endif; ?>
                      <button
                        type="button"
                        class="sep-share-btn btn-share-trigger"
                        data-share-type="História Bíblica"
                        data-share-title="<?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>"
                        data-share-ref="<?= htmlspecialchars($stRef . ' (NAA)', ENT_QUOTES, 'UTF-8') ?>"
                        data-share-text="<?= htmlspecialchars($stCleanText, ENT_QUOTES, 'UTF-8') ?>"
                        data-share-url="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>"
                      >
                        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                          <circle cx="18" cy="5" r="3"></circle>
                          <circle cx="6" cy="12" r="3"></circle>
                          <circle cx="18" cy="19" r="3"></circle>
                          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                          <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        <span>Compartilhar</span>
                      </button>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <!-- =====================================================================
           MODO 3: SEPARAÇÃO POR TODOS OS 66 LIVROS DA BÍBLIA
           ===================================================================== -->
      <section
        id="div-pane-books"
        class="div-pane <?= $activeTab === 'books' ? 'active' : '' ?>"
        aria-label="Histórias separadas por todos os 66 livros da Bíblia"
      >
        <div class="books-separation-grid">
          <?php foreach ($divisions as $div): ?>
            <?php foreach ($div['books'] as $book): ?>
              <?php
                $bookFilterStr = BibleRepository::stripAccents($book['name'] . ' ' . $div['label']);
                foreach ($book['stories'] as $bs) {
                    $bookFilterStr .= ' ' . BibleRepository::stripAccents(($bs['titulo'] ?? '') . ' ' . ($bs['referencia'] ?? ''));
                }
              ?>
              <div
                class="book-separation-card"
                data-separation-block
                data-sep-item="<?= htmlspecialchars($bookFilterStr, ENT_QUOTES, 'UTF-8') ?>"
              >
                <div class="book-sep-card-header">
                  <div>
                    <span class="book-sep-division-tag">
                      <?= $book['testament'] === 'VT' ? 'AT' : 'NT' ?> · <?= htmlspecialchars($div['short'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <h3>
                      <a href="bible.php?book=<?= urlencode($book['url_abbrev']) ?>&ch=1">
                        <?= htmlspecialchars($book['name'], ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                  </div>

                  <div class="book-sep-badges">
                    <span class="separation-count-pill"><?= (int)$book['stories_count'] ?> <?= $book['stories_count'] === 1 ? 'história' : 'histórias' ?></span>
                    <a href="bible.php?book=<?= urlencode($book['url_abbrev']) ?>&ch=1" class="book-open-bible-link">
                      Ler <?= (int)$book['chapters_count'] ?> cap. →
                    </a>
                  </div>
                </div>

                <?php if (!empty($book['stories'])): ?>
                  <ul class="book-stories-list">
                    <?php foreach ($book['stories'] as $st): ?>
                      <?php
                        $origin = (string)($st['origin'] ?? 'defined');
                        $isDefined = $origin === 'defined';
                        $stSlug = (string)($st['slug'] ?? '');
                        $stDia = (string)($st['dia'] ?? '');
                        $stTitulo = (string)($st['titulo'] ?? '');
                        $stRef = (string)($st['referencia'] ?? '');
                        $stTexto = (string)($st['texto'] ?? '');
                        $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                        $stBibleUrl = (string)($st['bible_url'] ?? 'bible.php');
                        $stUrl = $isDefined
                            ? ($stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia))
                            : $stBibleUrl;
                        $rowFilter = BibleRepository::stripAccents($stTitulo . ' ' . $stRef . ' ' . $book['name']);
                      ?>
                      <li
                        class="book-story-row"
                        data-sep-item="<?= htmlspecialchars($rowFilter, ENT_QUOTES, 'UTF-8') ?>"
                        data-story-origin="<?= htmlspecialchars($origin, ENT_QUOTES, 'UTF-8') ?>"
                      >
                        <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="book-story-row-link">
                          <span class="row-day-pill <?= $isDefined ? '' : 'row-day-pill--canonical' ?>">
                            <?= $isDefined ? 'Dia ' . htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') : 'Bíblia' ?>
                          </span>
                          <strong class="row-story-title"><?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?></strong>
                          <span class="row-story-ref"><?= htmlspecialchars($stRef, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                        <button
                          type="button"
                          class="row-share-icon-btn btn-share-trigger"
                          data-share-type="História Bíblica"
                          data-share-title="<?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>"
                          data-share-ref="<?= htmlspecialchars($stRef . ' (NAA)', ENT_QUOTES, 'UTF-8') ?>"
                          data-share-text="<?= htmlspecialchars($stCleanText, ENT_QUOTES, 'UTF-8') ?>"
                          data-share-url="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>"
                          title="Compartilhar <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>"
                          aria-label="Compartilhar <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>"
                        >
                          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                          </svg>
                        </button>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      </section>

    </div>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
