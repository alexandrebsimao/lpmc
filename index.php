<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();

$slugParam = isset($_GET['p']) ? (string)$_GET['p'] : null;
$dayParam = $_GET['day'] ?? $_GET['page'] ?? null;
$dayParam = $dayParam !== null ? (string)$dayParam : null;

$resolved = $repo->getStoryForRequest($slugParam, $dayParam);
$story = $resolved['story'];
$previous = $resolved['previous'];
$next = $resolved['next'];
$chapterView = $resolved['chapterView'];
$allBooks = $repo->getBooks();
$booksByTestament = $repo->getBooksByTestament();

if (!empty($_GET['book'])) {
    $customBook = (string)$_GET['book'];
    $customCh = max(1, (int)($_GET['ch'] ?? 1));
    $customHl = isset($_GET['hl']) ? (string)$_GET['hl'] : null;
    $chapterView = $repo->getChapterViewData($customBook, $customCh, $customHl, null);
}

$rawTitle = trim((string)($story['titulo'] ?? 'Leitura do Dia'));
$words = preg_split('/\s+/u', $rawTitle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$lastWord = array_pop($words) ?? '';
$firstPart = implode(' ', $words);
$formattedTitleHtml = $firstPart !== ''
    ? htmlspecialchars($firstPart, ENT_QUOTES, 'UTF-8') . ' <em>' . htmlspecialchars($lastWord, ENT_QUOTES, 'UTF-8') . '</em>'
    : '<em>' . htmlspecialchars($lastWord, ENT_QUOTES, 'UTF-8') . '</em>';

$bgUrl = $story['imagem']['url'] ?? 'https://cdn.pixabay.com/photo/2023/04/03/17/25/jesus-7897344_1280.jpg';
$bgAlt = $story['imagem']['alt'] ?? $rawTitle;
$referencia = (string)($story['referencia'] ?? '');
$textoPassagem = (string)($story['texto'] ?? $story['historia'] ?? '');
$formattedPassageHtml = BibleRepository::formatPassageVersesHtml($textoPassagem);
$diaNumero = (int)($story['dia'] ?? 1);
$dataLabel = (string)($story['data'] ?? '');
$yearProgress = max(1, min(100, (int)round(($diaNumero / 366) * 100)));

$pageTitle = $rawTitle . ($referencia ? " ({$referencia})" : '') . ' — Via Scriptura';
$pageDescription = mb_substr(strip_tags($textoPassagem), 0, 155, 'UTF-8') . '...';
$pageUrl = 'https://www.viascriptura.com/' . (!empty($story['slug']) ? '?p=' . urlencode((string)$story['slug']) : '');
$pageImage = $bgUrl;
$activeNav = 'home';
$bodyClass = 'page-home';

$activeBook = $chapterView['book'];
$activeChapter = $chapterView['chapter'];
$totalChapters = $chapterView['totalChapters'];
$verses = $chapterView['verses'];
$highlight = $chapterView['highlight'];

require __DIR__ . '/includes/header.php';
?>

  <!-- Hero Editorial Moderno (Split Bento Showcase) -->
  <section id="hero" class="modern-hero">
    <!-- Fundo Atmosférico Suave -->
    <div class="hero-ambient">
      <img
        id="bg-image"
        class="loaded"
        src="<?= htmlspecialchars($bgUrl, ENT_QUOTES, 'UTF-8') ?>"
        alt=""
        aria-hidden="true"
      />
      <div class="hero-ambient-overlay"></div>
    </div>

    <div class="hero-container">
      <div class="hero-showcase-grid">

        <!-- Coluna Visual: Arte Sacra em Moldura Moderna + Progresso Anual -->
        <div class="hero-visual-col">
          <div class="hero-artwork-frame">
            <img
              src="<?= htmlspecialchars($bgUrl, ENT_QUOTES, 'UTF-8') ?>"
              alt="<?= htmlspecialchars($bgAlt, ENT_QUOTES, 'UTF-8') ?>"
              class="hero-artwork-img"
            />
            <div class="hero-artwork-gradient"></div>

            <div class="hero-artwork-top-badges">
              <span class="badge-glass badge-glass--gold">
                Dia <?= $diaNumero ?> de 366<?= $dataLabel !== '' ? ' · ' . htmlspecialchars($dataLabel, ENT_QUOTES, 'UTF-8') : '' ?>
              </span>
              <?php if (!empty($story['book'])): ?>
                <span class="badge-glass">
                  <?= ($story['book']['testament'] ?? 'VT') === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
                </span>
              <?php endif; ?>
            </div>

            <div class="hero-artwork-footer">
              <div class="progress-meta">
                <span>Jornada Devocional Anual</span>
                <strong><?= $yearProgress ?>% concluído</strong>
              </div>
              <div class="progress-track" role="progressbar" aria-valuenow="<?= $diaNumero ?>" aria-valuemin="1" aria-valuemax="366">
                <div class="progress-fill" style="width: <?= $yearProgress ?>%;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Coluna Editorial: Título, Referência, Ações e Texto Bíblico Formatado -->
        <div class="hero-reading-col">
          <div class="reading-card-surface">
            <div class="reading-card-eyebrow">
              <span class="eyebrow-dot"></span>
              <span>Meditação Diária · Versão NAA</span>
              <?php if (!empty($story['book']['category_label'])): ?>
                <span class="eyebrow-sep">•</span>
                <span><?= htmlspecialchars($story['book']['category_label'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php endif; ?>
            </div>

            <h1 id="titulo" class="modern-hero-title"><?= $formattedTitleHtml ?></h1>

            <div class="modern-reference-bar">
              <a href="#biblia-em-tela" class="reference-pill" id="subtitulo" title="Ver o capítulo completo abaixo na tela">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                  <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                  <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                <span><?= htmlspecialchars($referencia, ENT_QUOTES, 'UTF-8') ?></span>
              </a>

              <div class="hero-quick-actions">
                <button
                  type="button"
                  class="action-chip"
                  id="btn-tts-passage"
                  data-tts-text="<?= htmlspecialchars($rawTitle . '. ' . $referencia . '. ' . preg_replace('/\b\d+\.\s*/', '', $textoPassagem), ENT_QUOTES, 'UTF-8') ?>"
                  title="Ouvir leitura em voz alta"
                >
                  <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                  </svg>
                  <span id="tts-btn-label">Ouvir</span>
                </button>

                <button
                  type="button"
                  class="action-chip"
                  id="btn-copy-passage"
                  data-copy-text="<?= htmlspecialchars($rawTitle . ' (' . $referencia . ' - NAA): ' . $textoPassagem, ENT_QUOTES, 'UTF-8') ?>"
                  title="Copiar passagem"
                >
                  <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                  <span>Copiar</span>
                </button>

                <button
                  type="button"
                  class="action-chip btn-share-trigger"
                  data-share-type="Meditação Diária"
                  data-share-title="<?= htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8') ?>"
                  data-share-ref="<?= htmlspecialchars($referencia . ' (NAA)', ENT_QUOTES, 'UTF-8') ?>"
                  data-share-text="<?= htmlspecialchars(preg_replace('/\b\d+\.\s*/', '', $textoPassagem) ?? $textoPassagem, ENT_QUOTES, 'UTF-8') ?>"
                  data-share-url="<?= htmlspecialchars(!empty($story['slug']) ? 'index.php?p=' . urlencode((string)$story['slug']) : 'index.php', ENT_QUOTES, 'UTF-8') ?>"
                  title="Compartilhar esta meditação e passagem"
                >
                  <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
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

            <!-- Passagem Bíblica com Versículos Estilizados -->
            <div class="modern-passage-body" id="historia">
              <?= $formattedPassageHtml ?>
            </div>

            <!-- Botões de Ação Primários -->
            <div class="reading-card-cta-row">
              <a href="#biblia-em-tela" id="btn-read-chapter" class="btn-modern-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <polyline points="19 12 12 19 5 12"></polyline>
                </svg>
                <span>Ver Capítulo Completo na Tela (<?= htmlspecialchars($activeBook['name'] . ' ' . $activeChapter, ENT_QUOTES, 'UTF-8') ?>)</span>
              </a>

              <a href="<?= htmlspecialchars($story['bible_url'] ?? 'bible.php', ENT_QUOTES, 'UTF-8') ?>" class="btn-modern-secondary">
                <span>Abrir Painel de Estudo Bíblico</span>
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- Navegação Bento entre Histórias (Anterior / Próxima + Atalhos) -->
      <nav class="modern-story-nav-bar" aria-label="Navegação da jornada diária">
        <div class="bento-nav-grid">
          <?php if ($previous): ?>
            <a
              href="index.php?p=<?= urlencode((string)$previous['slug']) ?>"
              id="previous-day"
              class="bento-nav-card bento-nav-card--prev"
              rel="prev"
            >
              <span class="bento-nav-arrow" aria-hidden="true">←</span>
              <?php if (!empty($previous['imagem']['url'])): ?>
                <img src="<?= htmlspecialchars((string)$previous['imagem']['url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="bento-nav-thumb" loading="lazy" />
              <?php endif; ?>
              <div class="bento-nav-meta">
                <span class="nav-kicker">Dia <?= htmlspecialchars((string)$previous['dia'], ENT_QUOTES, 'UTF-8') ?> · Leitura Anterior</span>
                <strong class="nav-title"><?= htmlspecialchars((string)$previous['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="nav-ref"><?= htmlspecialchars((string)($previous['referencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </a>
          <?php else: ?>
            <div class="bento-nav-empty">Início da jornada anual (Dia 1)</div>
          <?php endif; ?>

          <?php if ($next): ?>
            <a
              href="index.php?p=<?= urlencode((string)$next['slug']) ?>"
              id="next-day"
              class="bento-nav-card bento-nav-card--next"
              rel="next"
            >
              <div class="bento-nav-meta">
                <span class="nav-kicker">Dia <?= htmlspecialchars((string)$next['dia'], ENT_QUOTES, 'UTF-8') ?> · Próxima Leitura</span>
                <strong class="nav-title"><?= htmlspecialchars((string)$next['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="nav-ref"><?= htmlspecialchars((string)($next['referencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <?php if (!empty($next['imagem']['url'])): ?>
                <img src="<?= htmlspecialchars((string)$next['imagem']['url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="bento-nav-thumb" loading="lazy" />
              <?php endif; ?>
              <span class="bento-nav-arrow" aria-hidden="true">→</span>
            </a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </section>

  <!-- =========================================================================
       SEÇÃO DA BÍBLIA COMPLETA VISÍVEL EM TELA (Design Moderno Integrado)
       ========================================================================= -->
  <section id="biblia-em-tela" class="onscreen-bible-section" aria-label="Leitor da Bíblia Sagrada em Tela">
    <div class="onscreen-bible-container">

      <div class="onscreen-bible-top">
        <div class="onscreen-bible-heading">
          <span class="section-kicker">Bíblia Sagrada Integrada em Tela · 66 Livros</span>
          <h2 class="section-title">Escrituras Sagradas <em>em Contexto</em></h2>
          <p class="section-subtitle">
            Leia o capítulo integral de hoje ou navegue imediatamente por todos os 66 livros e 1.189 capítulos sem sair da tela.
          </p>
        </div>

        <div class="onscreen-bible-quick-actions">
          <a href="bible.php?book=<?= urlencode($activeBook['url_abbrev']) ?>&ch=<?= $activeChapter ?>" class="btn-modern-primary">
            <span>Modo Estudo em Tela Cheia</span>
            <span aria-hidden="true">→</span>
          </a>
          <a href="bible.php?view=panorama" class="btn-modern-secondary">
            <span>Panorama dos 66 Livros</span>
          </a>
        </div>
      </div>

      <!-- Workspace de Leitura Visível em Tela -->
      <div class="bible-workspace bible-workspace--embedded" id="home-bible-workspace">

        <!-- Coluna Lateral: Todos os 66 Livros Visíveis -->
        <aside class="bible-sidebar" aria-label="Livros da Bíblia">
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
                placeholder="Filtrar livro (ex: Salmos, João)..."
                aria-label="Filtrar livros da Bíblia"
              />
            </div>
          </div>

          <div class="sidebar-books-list" id="sidebar-books-list">
            <?php foreach (['VT' => 'Antigo Testamento', 'NT' => 'Novo Testamento'] as $tCode => $tName): ?>
              <div class="testament-group" data-testament-group="<?= $tCode ?>">
                <div class="testament-group-title">
                  <span><?= $tName ?></span>
                  <small><?= count($booksByTestament[$tCode]) ?> livros</small>
                </div>
                <?php foreach ($booksByTestament[$tCode] as $b): ?>
                  <?php $isCurrentBook = ($b['id'] === $activeBook['id']); ?>
                  <a
                    href="index.php?<?= !empty($story['slug']) ? 'p=' . urlencode((string)$story['slug']) . '&' : '' ?>book=<?= urlencode($b['url_abbrev']) ?>&ch=1#biblia-em-tela"
                    class="sidebar-book-item <?= $isCurrentBook ? 'active' : '' ?>"
                    data-book-abbrev="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>"
                    data-book-name="<?= htmlspecialchars(BibleRepository::stripAccents($b['name']), ENT_QUOTES, 'UTF-8') ?>"
                    data-testament="<?= $tCode ?>"
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

        <!-- Painel Principal: Grade de Capítulos + Texto Completo do Capítulo -->
        <div class="bible-main-panel">

          <div class="bible-panel-header">
            <div class="bible-current-info">
              <span class="bible-breadcrumb" id="reader-breadcrumb">
                <?= $activeBook['testament'] === 'VT' ? 'Antigo Testamento' : 'Novo Testamento' ?>
                · <?= htmlspecialchars($activeBook['category_label'], ENT_QUOTES, 'UTF-8') ?>
                · Livro <?= (int)$activeBook['id'] ?> de 66
              </span>
              <div class="bible-title-row">
                <h3 id="reader-chapter-title" class="reader-chapter-title">
                  <?= htmlspecialchars($activeBook['name'] . ' ' . $activeChapter, ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <span class="bible-version-badge" title="Nova Almeida Atualizada">NAA</span>
              </div>
            </div>

            <div class="bible-header-selectors">
              <select id="reader-book-select" class="bible-select" aria-label="Selecionar Livro">
                <optgroup label="Antigo Testamento">
                  <?php foreach ($booksByTestament['VT'] as $b): ?>
                    <option value="<?= htmlspecialchars($b['url_abbrev'], ENT_QUOTES, 'UTF-8') ?>" <?= $b['id'] === $activeBook['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
                <optgroup label="Novo Testamento">
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
                <button type="button" class="layout-toggle-btn active" id="btn-mode-verses">Versículos</button>
                <button type="button" class="layout-toggle-btn" id="btn-mode-prose">Prosa</button>
              </div>

              <button type="button" class="btn-copy-chapter" id="btn-share-chapter" title="Compartilhar este capítulo">
                Compartilhar Capítulo
              </button>
            </div>
          </div>

          <!-- Grade de Todos os Capítulos do Livro Ativo -->
          <div class="chapter-pills-bar" id="reader-chapter-pills">
            <span class="chapter-pills-label">
              Capítulos de <strong><?= htmlspecialchars($activeBook['name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= $totalChapters ?>):
            </span>
            <div class="chapter-pills-list" id="chapter-pills-list">
              <?php for ($c = 1; $c <= $totalChapters; $c++): ?>
                <a
                  href="index.php?<?= !empty($story['slug']) ? 'p=' . urlencode((string)$story['slug']) . '&' : '' ?>book=<?= urlencode($activeBook['url_abbrev']) ?>&ch=<?= $c ?>#biblia-em-tela"
                  class="chapter-pill <?= $c === $activeChapter ? 'active' : '' ?>"
                  data-ch="<?= $c ?>"
                ><?= $c ?></a>
              <?php endfor; ?>
            </div>
          </div>

          <!-- Tag de Contexto da Passagem do Dia -->
          <div id="reader-context-banner" class="reader-context-banner" <?= empty($chapterView['contextLabel']) ? 'style="display:none;"' : '' ?>>
            <?php if (!empty($chapterView['contextLabel'])): ?>
              <span class="context-pill">
                Passagem em destaque da meditação de hoje: <strong><?= htmlspecialchars((string)$chapterView['contextLabel'], ENT_QUOTES, 'UTF-8') ?></strong>
              </span>
            <?php endif; ?>
          </div>

          <div class="verse-interaction-bar">
            <span><?= count($verses) ?> versículos · Clique em qualquer versículo para destacar, copiar ou compartilhar</span>
            <div class="verse-selected-actions">
              <button type="button" id="btn-copy-selected-verses" class="btn-copy-selected" style="display:none;">
                Copiar selecionados
              </button>
              <button type="button" id="btn-share-selected-verses" class="btn-copy-selected btn-share-selected" style="display:none;">
                Compartilhar trecho
              </button>
            </div>
          </div>

          <!-- Lista Completa de Versículos do Capítulo -->
          <div class="reader-verses-canvas" id="reader-verses-list" data-book-name="<?= htmlspecialchars($activeBook['name'], ENT_QUOTES, 'UTF-8') ?>" data-chapter="<?= $activeChapter ?>">
            <?php foreach ($verses as $idx => $verseText): ?>
              <?php
                $vNum = $idx + 1;
                $isHighlighted = $highlight && $vNum >= $highlight['start'] && $vNum <= $highlight['end'];
              ?>
              <div
                class="bible-verse <?= $isHighlighted ? 'highlighted' : '' ?>"
                id="verse-<?= $vNum ?>"
                data-verse="<?= $vNum ?>"
                title="Clique para destacar ou copiar este versículo"
              >
                <span class="verse-num"><?= $vNum ?></span>
                <span class="verse-text"><?= htmlspecialchars($verseText, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Rodapé de Navegação de Capítulos -->
          <div class="reader-footer-nav">
            <?php if ($chapterView['prev']): ?>
              <a
                href="index.php?<?= !empty($story['slug']) ? 'p=' . urlencode((string)$story['slug']) . '&' : '' ?>book=<?= urlencode($chapterView['prev']['book']['url_abbrev']) ?>&ch=<?= $chapterView['prev']['chapter'] ?>#biblia-em-tela"
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
                href="index.php?<?= !empty($story['slug']) ? 'p=' . urlencode((string)$story['slug']) . '&' : '' ?>book=<?= urlencode($chapterView['next']['book']['url_abbrev']) ?>&ch=<?= $chapterView['next']['chapter'] ?>#biblia-em-tela"
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

    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
