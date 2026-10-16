<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();

$bookParam = (string)($_GET['book'] ?? 'gn');
$chParam = max(1, (int)($_GET['ch'] ?? 1));
$hlParam = isset($_GET['hl']) ? (string)$_GET['hl'] : null;
$refParam = isset($_GET['ref']) ? (string)$_GET['ref'] : null;
$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$searchTestament = isset($_GET['testament']) ? trim((string)$_GET['testament']) : '';
$searchBook = isset($_GET['sbook']) ? trim((string)$_GET['sbook']) : '';

// Determina a aba ativa ('reader' | 'panorama' | 'search')
$viewMode = (string)($_GET['view'] ?? 'reader');
if ($searchQuery !== '') {
    $viewMode = 'search';
}

// Se o usuário digitou uma referência exata na busca (ex: "João 3:16" ou "Salmo 23"), oferece atalho direto
$searchResults = null;
if ($searchQuery !== '') {
    $searchResults = $repo->searchBible(
        $searchQuery,
        $searchTestament !== '' ? $searchTestament : null,
        $searchBook !== '' ? $searchBook : null,
        150
    );
}

$chapterView = $repo->getChapterViewData($bookParam, $chParam, $hlParam, $refParam);
$activeBook = $chapterView['book'];
$activeChapter = $chapterView['chapter'];
$totalChapters = $chapterView['totalChapters'];
$verses = $chapterView['verses'];
$highlight = $chapterView['highlight'];
$relatedStories = $chapterView['relatedStories'];

$allBooks = $repo->getBooks();
$booksByTestament = $repo->getBooksByTestament();
$booksGrouped = $repo->getBooksGroupedByCategory();

$pageTitle = $searchQuery !== ''
    ? "Busca: \"{$searchQuery}\" na Bíblia Sagrada (NAA) — Via Scriptura"
    : ($viewMode === 'panorama'
        ? 'Panorama Completo dos 66 Livros da Bíblia (NAA) — Via Scriptura'
        : "{$activeBook['name']} {$activeChapter} — Bíblia Sagrada Completa (NAA) | Via Scriptura");

$pageDescription = !empty($verses)
    ? "{$activeBook['name']} {$activeChapter}:1 — " . mb_substr($verses[0], 0, 140, 'UTF-8') . '...'
    : 'Leia a Bíblia Sagrada completa na versão Nova Almeida Atualizada (NAA) com todos os 66 livros e 1.189 capítulos visíveis em tela.';
$pageUrl = 'https://www.viascriptura.com/bible.php?book=' . urlencode($activeBook['url_abbrev']) . '&ch=' . $activeChapter;
$activeNav = 'bible';
$bodyClass = 'page-bible';

require __DIR__ . '/includes/header.php';
?>

  <main class="bible-page-main">
    <!-- Cabeçalho da Página da Bíblia Completa -->
    <section class="bible-hero-bar">
      <div class="bible-hero-inner">
        <div class="bible-hero-titles">
          <span class="section-kicker">Nova Almeida Atualizada (NAA) · 66 Livros · 1.189 Capítulos</span>
          <h1>Bíblia <em>Sagrada</em></h1>
        </div>

        <!-- Abas de Modo de Visualização em Tela -->
        <div class="bible-view-tabs" role="tablist" aria-label="Modos de visualização da Bíblia">
          <button
            type="button"
            class="bible-view-tab <?= $viewMode === 'reader' ? 'active' : '' ?>"
            data-switch-view="reader"
            role="tab"
            aria-selected="<?= $viewMode === 'reader' ? 'true' : 'false' ?>"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.8" fill="none" aria-hidden="true">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
              <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            <span>Leitor em Tela (<?= htmlspecialchars($activeBook['name'] . ' ' . $activeChapter, ENT_QUOTES, 'UTF-8') ?>)</span>
          </button>

          <button
            type="button"
            class="bible-view-tab <?= $viewMode === 'panorama' ? 'active' : '' ?>"
            data-switch-view="panorama"
            role="tab"
            aria-selected="<?= $viewMode === 'panorama' ? 'true' : 'false' ?>"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.8" fill="none" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Panorama dos 66 Livros</span>
          </button>

          <button
            type="button"
            class="bible-view-tab <?= $viewMode === 'search' ? 'active' : '' ?>"
            data-switch-view="search"
            role="tab"
            aria-selected="<?= $viewMode === 'search' ? 'true' : 'false' ?>"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.8" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <span>Busca nas Escrituras<?= $searchResults ? ' (' . (int)$searchResults['total'] . ')' : '' ?></span>
          </button>
        </div>
      </div>
    </section>

    <div class="bible-page-container">

      <!-- =====================================================================
           ABA 1: LEITOR COMPLETO EM TELA (Livros na lateral + Capítulo na tela)
           ===================================================================== -->
      <section
        id="bible-view-reader"
        class="bible-view-pane <?= $viewMode === 'reader' ? 'active' : '' ?>"
        aria-label="Leitura de Livros e Capítulos"
      >
        <div class="bible-workspace" id="full-bible-workspace">

          <!-- Coluna Esquerda: Todos os 66 Livros Categorizados -->
          <aside class="bible-sidebar" aria-label="Índice dos 66 Livros da Bíblia">
            <div class="sidebar-header">
              <div class="testament-tabs" role="tablist">
                <button type="button" class="testament-tab active" data-filter-testament="ALL">Todos (66)</button>
                <button type="button" class="testament-tab" data-filter-testament="VT">Antigo (39)</button>
                <button type="button" class="testament-tab" data-filter-testament="NT">Novo (27)</button>
              </div>
              <div class="sidebar-search-wrap">
                <input
                  type="search"
                  id="sidebar-book-filter"
                  class="sidebar-book-filter"
                  placeholder="Filtrar livro (ex: Gênesis, Salmos)..."
                  aria-label="Filtrar livros pelo nome"
                />
              </div>
            </div>

            <div class="sidebar-books-list" id="sidebar-books-list">
              <?php foreach ($booksGrouped as $group): ?>
                <div class="testament-group" data-testament-group="<?= htmlspecialchars($group['testament'], ENT_QUOTES, 'UTF-8') ?>">
                  <div class="testament-group-title">
                    <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small><?= $group['testament'] ?></small>
                  </div>
                  <?php foreach ($group['books'] as $b): ?>
                    <?php $isCurrent = ($b['id'] === $activeBook['id']); ?>
                    <a
                      href="bible.php?book=<?= urlencode($b['url_abbrev']) ?>&ch=1"
                      class="sidebar-book-item <?= $isCurrent ? 'active' : '' ?>"
                      data-book-abbrev="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                      data-book-name="<?= htmlspecialchars(BibleRepository::stripAccents($b['name']), ENT_QUOTES, 'UTF-8') ?>"
                      data-testament="<?= htmlspecialchars($b['testament'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                      <div class="book-item-left">
                        <span class="book-abbrev-tag"><?= htmlspecialchars(mb_strtoupper($b['abbrev'], 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="book-item-name"><?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                      <span class="book-item-meta"><?= (int)$b['chapters_count'] ?> cap</span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </aside>

          <!-- Coluna Principal: Grade de Capítulos + Leitura Integral do Capítulo -->
          <div class="bible-main-panel">

            <!-- Cabeçalho do Capítulo Ativo -->
            <div class="bible-panel-header">
              <div class="bible-current-info">
                <span class="bible-breadcrumb" id="reader-breadcrumb">
                  <?= $activeBook['testament'] === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
                  · <?= htmlspecialchars($activeBook['category_label'], ENT_QUOTES, 'UTF-8') ?>
                  · Livro <?= (int)$activeBook['id'] ?> de 66
                </span>
                <div class="bible-title-row">
                  <h2 id="reader-chapter-title" class="reader-chapter-title">
                    <?= htmlspecialchars($activeBook['name'] . ' ' . $activeChapter, ENT_QUOTES, 'UTF-8') ?>
                  </h2>
                  <span class="bible-version-badge" title="Nova Almeida Atualizada">NAA</span>
                </div>
              </div>

              <!-- Controles de Seleção e Modo de Exibição -->
              <div class="bible-header-selectors">
                <select id="reader-book-select" class="bible-select" aria-label="Selecionar Livro">
                  <optgroup label="Antigo Testamento (39)">
                    <?php foreach ($booksByTestament['VT'] as $b): ?>
                      <option value="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>" <?= $b['id'] === $activeBook['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </optgroup>
                  <optgroup label="Novo Testamento (27)">
                    <?php foreach ($booksByTestament['NT'] as $b): ?>
                      <option value="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>" <?= $b['id'] === $activeBook['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </optgroup>
                </select>

                <select id="reader-chapter-select" class="bible-select" aria-label="Selecionar Capítulo">
                  <?php for ($c = 1; $c <= $totalChapters; $c++): ?>
                    <option value="<?= $c ?>" <?= $c === $activeChapter ? 'selected' : '' ?>>Capítulo <?= $c ?></option>
                  <?php endfor; ?>
                </select>

                <div class="reading-layout-toggle" role="group" aria-label="Formato de leitura">
                  <button type="button" class="layout-toggle-btn active" id="btn-mode-verses" title="Exibir versículo por linha">
                    Versículos
                  </button>
                  <button type="button" class="layout-toggle-btn" id="btn-mode-prose" title="Exibir em parágrafo contínuo">
                    Prosa
                  </button>
                </div>

                <button type="button" class="btn-copy-chapter" id="btn-tts-chapter" title="Ouvir capítulo em voz alta">
                  <span id="tts-chapter-label">Ouvir</span>
                </button>

                <button type="button" class="btn-copy-chapter" id="btn-copy-chapter" title="Copiar capítulo inteiro">
                  Copiar
                </button>

                <button type="button" class="btn-copy-chapter" id="btn-share-chapter" title="Compartilhar este capítulo">
                  Compartilhar
                </button>
              </div>
            </div>

            <!-- Barra Visual com Todos os Capítulos do Livro Ativo -->
            <div class="chapter-pills-bar" id="reader-chapter-pills">
              <div class="chapter-pills-header">
                <span class="chapter-pills-label">
                  Capítulos de <strong><?= htmlspecialchars($activeBook['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= $totalChapters ?>):
                </span>
              </div>
              <div class="chapter-pills-list" id="chapter-pills-list">
                <?php for ($c = 1; $c <= $totalChapters; $c++): ?>
                  <a
                    href="bible.php?book=<?= urlencode($activeBook['url_abbrev']) ?>&ch=<?= $c ?>"
                    class="chapter-pill <?= $c === $activeChapter ? 'active' : '' ?>"
                    data-ch="<?= $c ?>"
                  ><?= $c ?></a>
                <?php endfor; ?>
              </div>
            </div>

            <!-- Banner de Passagem em Destaque (se veio de uma referência) -->
            <div id="reader-context-banner" class="reader-context-banner" <?= empty($chapterView['contextLabel']) ? 'style="display:none;"' : '' ?>>
              <?php if (!empty($chapterView['contextLabel'])): ?>
                <span class="context-pill">
                  Passagem destacada: <strong><?= htmlspecialchars((string)$chapterView['contextLabel'], ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
              <?php endif; ?>
            </div>

            <!-- Meditações Diárias Relacionadas a Este Capítulo -->
            <div id="reader-related-stories" class="reader-related-stories" <?= empty($relatedStories) ? 'style="display:none;"' : '' ?>>
              <?php if (!empty($relatedStories)): ?>
                <div class="related-stories-label">Meditações do Via Scriptura neste capítulo:</div>
                <div class="related-stories-cards">
                  <?php foreach ($relatedStories as $rs): ?>
                    <a href="index.php?p=<?= urlencode((string)$rs['slug']) ?>" class="related-story-chip">
                      <?php if (!empty($rs['imagem']['url'])): ?>
                        <img src="<?= htmlspecialchars((string)$rs['imagem']['url'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" />
                      <?php endif; ?>
                      <div class="related-story-chip-info">
                        <?php $chipPrefix = !empty($rs['dia']) ? 'Dia ' . htmlspecialchars((string)$rs['dia'], ENT_QUOTES, 'UTF-8') : 'História Bíblica'; ?>
                        <span class="chip-day"><?= $chipPrefix ?> · <?= htmlspecialchars((string)$rs['referencia'], ENT_QUOTES, 'UTF-8') ?></span>
                        <strong><?= htmlspecialchars((string)$rs['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Dica de Interação -->
            <div class="verse-interaction-bar">
              <span><?= count($verses) ?> versículos neste capítulo · Clique em qualquer versículo para destacar, copiar ou compartilhar</span>
              <div class="verse-selected-actions">
                <button type="button" id="btn-copy-selected-verses" class="btn-copy-selected" style="display:none;">
                  Copiar selecionados
                </button>
                <button type="button" id="btn-share-selected-verses" class="btn-copy-selected btn-share-selected" style="display:none;">
                  Compartilhar trecho
                </button>
              </div>
            </div>

            <!-- Texto Integral dos Versículos Visível em Tela -->
            <div
              class="reader-verses-canvas"
              id="reader-verses-list"
              data-book-name="<?= htmlspecialchars($activeBook['name'], ENT_QUOTES, 'UTF-8') ?>"
              data-chapter="<?= $activeChapter ?>"
            >
              <?php foreach ($verses as $idx => $verseText): ?>
                <?php
                  $vNum = $idx + 1;
                  $isHighlighted = $highlight && $vNum >= $highlight['start'] && $vNum <= $highlight['end'];
                ?>
                <div
                  class="bible-verse <?= $isHighlighted ? 'highlighted' : '' ?>"
                  id="verse-<?= $vNum ?>"
                  data-verse="<?= $vNum ?>"
                >
                  <span class="verse-num"><?= $vNum ?></span>
                  <span class="verse-text"><?= htmlspecialchars($verseText, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Navegação Inferior entre Capítulos -->
            <div class="reader-footer-nav">
              <?php if ($chapterView['prev']): ?>
                <a
                  href="bible.php?book=<?= urlencode($chapterView['prev']['book']['url_abbrev']) ?>&ch=<?= $chapterView['prev']['chapter'] ?>"
                  id="reader-prev-btn"
                  class="bible-nav-btn"
                  data-book="<?= htmlspecialchars($chapterView['prev']['book']['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                  data-ch="<?= $chapterView['prev']['chapter'] ?>"
                >
                  <span aria-hidden="true">←</span>
                  <span><?= htmlspecialchars($chapterView['prev']['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
              <?php else: ?>
                <button type="button" class="bible-nav-btn" disabled>← Início</button>
              <?php endif; ?>

              <span id="reader-chapter-indicator" class="bible-chapter-indicator">
                <?= htmlspecialchars($activeBook['name'], ENT_QUOTES, 'UTF-8') ?> · Capítulo <?= $activeChapter ?> de <?= $totalChapters ?>
              </span>

              <?php if ($chapterView['next']): ?>
                <a
                  href="bible.php?book=<?= urlencode($chapterView['next']['book']['url_abbrev']) ?>&ch=<?= $chapterView['next']['chapter'] ?>"
                  id="reader-next-btn"
                  class="bible-nav-btn"
                  data-book="<?= htmlspecialchars($chapterView['next']['book']['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                  data-ch="<?= $chapterView['next']['chapter'] ?>"
                >
                  <span><?= htmlspecialchars($chapterView['next']['label'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span aria-hidden="true">→</span>
                </a>
              <?php else: ?>
                <button type="button" class="bible-nav-btn" disabled>Fim →</button>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </section>

      <!-- =====================================================================
           ABA 2: PANORAMA COMPLETO DOS 66 LIVROS E SEUS CAPÍTULOS EM TELA
           ===================================================================== -->
      <section
        id="bible-view-panorama"
        class="bible-view-pane <?= $viewMode === 'panorama' ? 'active' : '' ?>"
        aria-label="Panorama Completo de Todos os 66 Livros da Bíblia"
      >
        <div class="panorama-intro">
          <div>
            <h2>Todos os 66 Livros da <em>Bíblia</em></h2>
            <p>Explore a estrutura completa do Antigo e do Novo Testamento. Clique em qualquer livro ou diretamente no número do capítulo desejado.</p>
          </div>
          <div class="panorama-filter-bar">
            <input
              type="search"
              id="panorama-book-search"
              class="search-input panorama-search-input"
              placeholder="Buscar livro (ex: Isaías, Romanos, Apocalipse)..."
              aria-label="Filtrar livros no panorama"
            />
          </div>
        </div>

        <?php foreach ($booksGrouped as $group): ?>
          <div class="panorama-category-section">
            <div class="panorama-category-header">
              <span class="panorama-testament-badge <?= $group['testament'] === 'VT' ? 'vt' : 'nt' ?>">
                <?= $group['testament'] === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
              </span>
              <h3><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></h3>
              <span class="panorama-book-count"><?= count($group['books']) ?> <?= count($group['books']) === 1 ? 'livro' : 'livros' ?></span>
            </div>

            <div class="panorama-books-grid">
              <?php foreach ($group['books'] as $b): ?>
                <div
                  class="panorama-book-card"
                  data-panorama-book="<?= htmlspecialchars(BibleRepository::stripAccents($b['name']), ENT_QUOTES, 'UTF-8') ?>"
                >
                  <div class="panorama-book-card-top">
                    <div>
                      <span class="panorama-book-num">Livro #<?= (int)$b['id'] ?></span>
                      <h4>
                        <a href="bible.php?book=<?= urlencode($b['url_abbrev']) ?>&ch=1">
                          <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </h4>
                    </div>
                    <span class="panorama-chapters-badge"><?= (int)$b['chapters_count'] ?> cap.</span>
                  </div>

                  <div class="panorama-chapters-mini-grid">
                    <?php
                      $maxPreview = min(30, (int)$b['chapters_count']);
                      for ($c = 1; $c <= $maxPreview; $c++):
                    ?>
                      <a
                        href="bible.php?book=<?= urlencode($b['url_abbrev']) ?>&ch=<?= $c ?>"
                        class="mini-ch-link"
                        title="Ler <?= htmlspecialchars($b['name'] . ' ' . $c, ENT_QUOTES, 'UTF-8') ?>"
                      ><?= $c ?></a>
                    <?php endfor; ?>
                    <?php if ((int)$b['chapters_count'] > $maxPreview): ?>
                      <a
                        href="bible.php?book=<?= urlencode($b['url_abbrev']) ?>&ch=1"
                        class="mini-ch-link mini-ch-link--more"
                        title="Ver todos os <?= (int)$b['chapters_count'] ?> capítulos"
                      >+<?= (int)$b['chapters_count'] - $maxPreview ?></a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <!-- =====================================================================
           ABA 3: BUSCA TEXTUAL COMPLETA NAS ESCRITURAS
           ===================================================================== -->
      <section
        id="bible-view-search"
        class="bible-view-pane <?= $viewMode === 'search' ? 'active' : '' ?>"
        aria-label="Busca Textual na Bíblia"
      >
        <div class="bible-search-panel">
          <div class="bible-search-header">
            <h2>Pesquisar nas <em>Escrituras</em></h2>
            <p>Busque qualquer palavra, expressão ou referência bíblica nos 31.102 versículos da Nova Almeida Atualizada (NAA).</p>
          </div>

          <form action="bible.php" method="get" class="bible-search-form">
            <input type="hidden" name="view" value="search" />
            <div class="search-form-row">
              <input
                type="search"
                name="q"
                id="bible-full-search-input"
                class="search-input"
                placeholder="Ex: graça, bom pastor, João 3:16, Salmo 23..."
                value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"
                required
              />

              <select name="testament" class="bible-select">
                <option value="">Toda a Bíblia</option>
                <option value="VT" <?= $searchTestament === 'VT' ? 'selected' : '' ?>>Antigo Testamento</option>
                <option value="NT" <?= $searchTestament === 'NT' ? 'selected' : '' ?>>Novo Testamento</option>
              </select>

              <select name="sbook" class="bible-select">
                <option value="">Todos os 66 Livros</option>
                <?php foreach ($allBooks as $b): ?>
                  <option value="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>" <?= $searchBook === $b['url_abbrev'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <button type="submit" class="btn-gold-solid">Pesquisar</button>
            </div>
          </form>

          <?php if ($searchResults !== null): ?>
            <div class="search-results-container">
              <?php if (!empty($searchResults['directReference'])): ?>
                <?php
                  $dr = $searchResults['directReference'];
                  $drHl = $dr['highlight']
                      ? ($dr['highlight']['start'] === $dr['highlight']['end']
                          ? (string)$dr['highlight']['start']
                          : $dr['highlight']['start'] . '-' . $dr['highlight']['end'])
                      : '';
                ?>
                <div class="direct-reference-card">
                  <div>
                    <span class="section-kicker">Referência Bíblica Encontrada</span>
                    <h3><?= htmlspecialchars($dr['rawReference'], ENT_QUOTES, 'UTF-8') ?></h3>
                  </div>
                  <a
                    href="bible.php?book=<?= urlencode($dr['book']['url_abbrev']) ?>&ch=<?= (int)$dr['chapter'] ?><?= $drHl !== '' ? '&hl=' . urlencode($drHl) : '' ?>"
                    class="btn-gold-solid"
                  >
                    Abrir <?= htmlspecialchars($dr['book']['name'] . ' ' . $dr['chapter'], ENT_QUOTES, 'UTF-8') ?> →
                  </a>
                </div>
              <?php endif; ?>

              <div class="search-summary">
                Encontradas <strong><?= (int)$searchResults['total'] ?></strong> ocorrências para
                <em>"<?= htmlspecialchars($searchResults['query'], ENT_QUOTES, 'UTF-8') ?>"</em>
                <?php if ($searchResults['total'] > count($searchResults['results'])): ?>
                  (exibindo as primeiras <?= count($searchResults['results']) ?>)
                <?php endif; ?>
              </div>

              <?php if (empty($searchResults['results'])): ?>
                <div class="no-stories">
                  Nenhum versículo encontrado para este termo. Tente outra palavra ou verifique a ortografia.
                </div>
              <?php else: ?>
                <div class="search-results-list">
                  <?php foreach ($searchResults['results'] as $item): ?>
                    <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" class="search-result-card">
                      <div class="search-result-top">
                        <span class="search-result-ref"><?= htmlspecialchars($item['reference'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="search-result-cat"><?= htmlspecialchars($item['book']['category_short'], ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                      <p class="search-result-text"><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></p>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <!-- Sugestões Rápidas de Temas e Passagens Clássicas -->
            <div class="search-suggestions-box">
              <h3>Passagens e Temas Clássicos</h3>
              <div class="search-suggestions-grid">
                <a href="bible.php?book=gn&ch=1" class="suggestion-pill">Gênesis 1 · A Criação</a>
                <a href="bible.php?book=sl&ch=23" class="suggestion-pill">Salmos 23 · O Bom Pastor</a>
                <a href="bible.php?book=sl&ch=91" class="suggestion-pill">Salmos 91 · Refúgio e Proteção</a>
                <a href="bible.php?book=is&ch=53" class="suggestion-pill">Isaías 53 · O Servo Sofredor</a>
                <a href="bible.php?book=mt&ch=5" class="suggestion-pill">Mateus 5 · Sermão do Monte</a>
                <a href="bible.php?book=jo&ch=1" class="suggestion-pill">João 1 · O Verbo se fez Carne</a>
                <a href="bible.php?book=rm&ch=8" class="suggestion-pill">Romanos 8 · Vida no Espírito</a>
                <a href="bible.php?book=1co&ch=13" class="suggestion-pill">1 Coríntios 13 · A Excelência do Amor</a>
                <a href="bible.php?book=hb&ch=11" class="suggestion-pill">Hebreus 11 · Os Heróis da Fé</a>
                <a href="bible.php?book=ap&ch=21" class="suggestion-pill">Apocalipse 21 · Novo Céu e Nova Terra</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>

    </div>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
