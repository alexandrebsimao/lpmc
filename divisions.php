<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();

$eras = $repo->getStoriesSeparatedByEra();
$divisions = $repo->getStoriesSeparatedByDivision();
$allStories = $repo->getAllStories();
$allBooks = $repo->getBooks();

// Modo ativo: 'eras' (12 Grandes Eras Cronológicas) | 'divisions' (10 Divisões Canônicas) | 'books' (66 Livros)
$activeTab = (string)($_GET['tab'] ?? 'eras');
if (!in_array($activeTab, ['eras', 'divisions', 'books'], true)) {
    $activeTab = 'eras';
}

// Contabiliza quantos dos 66 livros possuem histórias devocionais definidas
$booksWithStoriesCount = 0;
foreach ($divisions as $div) {
    foreach ($div['books'] as $b) {
        if ($b['stories_count'] > 0) {
            $booksWithStoriesCount++;
        }
    }
}

$pageTitle = 'Separação das Histórias Bíblicas (Eras, Divisões e Livros) — Via Scriptura';
$pageDescription = 'Explore a separação completa de todas as histórias bíblicas organizadas pelas 12 Grandes Eras Cronológicas, 10 Divisões Canônicas e pelos 66 Livros das Escrituras.';
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
          <span class="section-kicker">Mapa Estrutural & Cronológico das Escrituras</span>
          <h1>Separação das Histórias <em>Bíblicas</em></h1>
          <p class="divisions-lead">
            Navegue por todas as passagens e pelas <strong><?= count($allStories) ?> histórias já definidas</strong> no Via Scriptura, estruturadas pelas <strong>12 Grandes Eras da História da Redenção</strong>, pelas <strong>10 Divisões Canônicas</strong> e pelos <strong>66 Livros da Bíblia</strong>.
          </p>
        </div>

        <div class="divisions-stats-row">
          <div class="div-stat-box">
            <strong>12</strong>
            <span>Eras Bíblicas</span>
          </div>
          <div class="div-stat-box">
            <strong>10</strong>
            <span>Divisões Canônicas</span>
          </div>
          <div class="div-stat-box">
            <strong>66</strong>
            <span>Livros Sagrados</span>
          </div>
          <div class="div-stat-box">
            <strong><?= count($allStories) ?></strong>
            <span>Histórias Definidas</span>
          </div>
        </div>

        <!-- Barra de Controle: Abas de Separação + Filtro Instantâneo -->
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
                  <span class="separation-count-pill"><?= count($era['stories']) ?> histórias definidas</span>
                </div>
                <h2><?= htmlspecialchars($era['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($era['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>

            <div class="separated-stories-grid">
              <?php foreach ($era['stories'] as $st): ?>
                <?php
                  $stSlug = (string)($st['slug'] ?? '');
                  $stDia = (string)($st['dia'] ?? '');
                  $stTitulo = (string)($st['titulo'] ?? '');
                  $stRef = (string)($st['referencia'] ?? '');
                  $stTexto = (string)($st['texto'] ?? '');
                  $stImg = (string)($st['imagem']['url'] ?? '');
                  $stUrl = $stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia);
                  $stBibleUrl = (string)($st['bible_url'] ?? 'bible.php');
                  $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                  $stExcerpt = mb_strlen($stCleanText, 'UTF-8') > 95 ? mb_substr($stCleanText, 0, 95, 'UTF-8') . '…' : $stCleanText;
                  $filterData = BibleRepository::stripAccents($stTitulo . ' ' . $stRef . ' ' . $era['title'] . ' dia ' . $stDia);
                ?>
                <article class="sep-story-card" data-sep-item="<?= htmlspecialchars($filterData, ENT_QUOTES, 'UTF-8') ?>">
                  <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-story-thumb-wrap">
                    <?php if ($stImg !== ''): ?>
                      <img src="<?= htmlspecialchars($stImg, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
                    <?php endif; ?>
                    <span class="sep-day-tag">Dia <?= htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') ?></span>
                  </a>

                  <div class="sep-story-body">
                    <span class="sep-story-ref"><?= htmlspecialchars($stRef, ENT_QUOTES, 'UTF-8') ?></span>
                    <h3>
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                    <p class="sep-story-excerpt"><?= htmlspecialchars($stExcerpt, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="sep-story-actions">
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler História</a>
                      <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-secondary">Capítulo</a>
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
                  <span class="separation-count-pill"><?= (int)$div['stories_count'] ?> histórias definidas</span>
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
                  $stSlug = (string)($st['slug'] ?? '');
                  $stDia = (string)($st['dia'] ?? '');
                  $stTitulo = (string)($st['titulo'] ?? '');
                  $stRef = (string)($st['referencia'] ?? '');
                  $stTexto = (string)($st['texto'] ?? '');
                  $stImg = (string)($st['imagem']['url'] ?? '');
                  $stUrl = $stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia);
                  $stBibleUrl = (string)($st['bible_url'] ?? 'bible.php');
                  $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                  $stExcerpt = mb_strlen($stCleanText, 'UTF-8') > 95 ? mb_substr($stCleanText, 0, 95, 'UTF-8') . '…' : $stCleanText;
                  $filterData = BibleRepository::stripAccents($stTitulo . ' ' . $stRef . ' ' . $div['label'] . ' dia ' . $stDia);
                ?>
                <article class="sep-story-card" data-sep-item="<?= htmlspecialchars($filterData, ENT_QUOTES, 'UTF-8') ?>">
                  <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-story-thumb-wrap">
                    <?php if ($stImg !== ''): ?>
                      <img src="<?= htmlspecialchars($stImg, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
                    <?php endif; ?>
                    <span class="sep-day-tag">Dia <?= htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') ?></span>
                  </a>

                  <div class="sep-story-body">
                    <span class="sep-story-ref"><?= htmlspecialchars($stRef, ENT_QUOTES, 'UTF-8') ?></span>
                    <h3>
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($stTitulo, ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </h3>
                    <p class="sep-story-excerpt"><?= htmlspecialchars($stExcerpt, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="sep-story-actions">
                      <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-primary">Ler História</a>
                      <a href="<?= htmlspecialchars($stBibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="sep-link-secondary">Capítulo</a>
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
                        $stSlug = (string)($st['slug'] ?? '');
                        $stDia = (string)($st['dia'] ?? '');
                        $stTitulo = (string)($st['titulo'] ?? '');
                        $stRef = (string)($st['referencia'] ?? '');
                        $stTexto = (string)($st['texto'] ?? '');
                        $stCleanText = preg_replace('/\b\d+\.\s*/', '', $stTexto) ?? $stTexto;
                        $stUrl = $stSlug !== '' ? 'index.php?p=' . urlencode($stSlug) : 'index.php?day=' . urlencode($stDia);
                      ?>
                      <li class="book-story-row">
                        <a href="<?= htmlspecialchars($stUrl, ENT_QUOTES, 'UTF-8') ?>" class="book-story-row-link">
                          <span class="row-day-pill">Dia <?= htmlspecialchars($stDia, ENT_QUOTES, 'UTF-8') ?></span>
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
                <?php else: ?>
                  <div class="book-no-defined-story">
                    <span>Livro completo disponível na Bíblia em tela (<?= (int)$book['chapters_count'] ?> <?= $book['chapters_count'] === 1 ? 'capítulo' : 'capítulos' ?>).</span>
                    <a href="bible.php?book=<?= urlencode($book['url_abbrev']) ?>&ch=1">Ler livro completo →</a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      </section>

    </div>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
