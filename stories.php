<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();
$definedStories = $repo->getAllStories();
$canonicalStories = $repo->getCanonicalExtraStories();
$stories = array_merge($definedStories, $canonicalStories);

$vtCount = 0;
$ntCount = 0;
foreach ($stories as $s) {
    $t = $s['book']['testament'] ?? '';
    if ($t === 'VT') {
        $vtCount++;
    } elseif ($t === 'NT') {
        $ntCount++;
    }
}

$pageTitle = 'Todas as Histórias Bíblicas (' . count($stories) . ' Passagens) — Via Scriptura';
$pageDescription = 'Explore nossa coleção completa de ' . count($stories) . ' histórias bíblicas (incluindo as 366 leituras diárias do ano e +220 narrativas canônicas) na versão Nova Almeida Atualizada (NAA).';
$pageUrl = 'https://www.viascriptura.com/stories.php';
$activeNav = 'stories';
$bodyClass = 'page-stories';

require __DIR__ . '/includes/header.php';
?>

  <section id="stories-section" class="stories-page-section">
    <div class="stories-header">
      <div class="stories-header-titles">
        <span class="section-kicker">Acervo Integral das Escrituras · <?= count($stories) ?> Passagens</span>
        <h1>Histórias <em>Bíblicas</em></h1>
        <p class="stories-subtitle">
          Explore todas as <strong><?= count($stories) ?> histórias bíblicas</strong> em uma única tela — incluindo as <strong><?= count($definedStories) ?> leituras diárias do ano</strong> e <strong>+<?= count($canonicalStories) ?> histórias canônicas</strong> de todos os 66 livros da Bíblia.
        </p>
      </div>

      <div class="stories-stats-pills">
        <span class="stat-pill"><strong><?= count($stories) ?></strong> Total de Histórias</span>
        <span class="stat-pill"><strong><?= count($definedStories) ?></strong> Leituras Diárias</span>
        <span class="stat-pill"><strong>+<?= count($canonicalStories) ?></strong> Outras Histórias</span>
        <span class="stat-pill"><strong><?= $vtCount ?></strong> AT · <strong><?= $ntCount ?></strong> NT</span>
        <a href="divisions.php" class="btn-modern-primary">
          <span>Ver Separação por Eras & Divisões</span>
          <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>

    <!-- Barra de Busca e Filtros Combinados -->
    <div class="stories-controls-bar">
      <div class="stories-filters-group">
        <div class="stories-filter-tabs" role="tablist" aria-label="Filtrar histórias por coleção">
          <button type="button" class="filter-tab-btn active" data-story-origin="ALL">
            Todas (<?= count($stories) ?>)
          </button>
          <button type="button" class="filter-tab-btn" data-story-origin="defined">
            Leituras Diárias (<?= count($definedStories) ?>)
          </button>
          <button type="button" class="filter-tab-btn" data-story-origin="canonical">
            Outras Histórias (+<?= count($canonicalStories) ?>)
          </button>
        </div>

        <div class="stories-filter-tabs" role="tablist" aria-label="Filtrar histórias por Testamento">
          <button type="button" class="filter-tab-btn active" data-story-testament="ALL">
            Todos Testamentos
          </button>
          <button type="button" class="filter-tab-btn" data-story-testament="VT">
            Antigo Testamento (<?= $vtCount ?>)
          </button>
          <button type="button" class="filter-tab-btn" data-story-testament="NT">
            Novo Testamento (<?= $ntCount ?>)
          </button>
        </div>
      </div>

      <div class="stories-search-box">
        <input
          type="search"
          id="search-input"
          class="search-input"
          placeholder="Buscar por título, livro ou referência (ex: Davi, Salmo 23, João)..."
          autocomplete="off"
          aria-label="Buscar histórias bíblicas"
        />
        <span id="stories-count-indicator" class="stories-count-indicator"><?= count($stories) ?> histórias</span>
      </div>
    </div>

    <!-- Grade Unificada com Todas as 586 Histórias Renderizadas no Servidor -->
    <div id="stories-grid" class="stories-grid">
      <?php foreach ($stories as $historia): ?>
        <?php
          $origin = (string)($historia['origin'] ?? 'defined');
          $isDefined = ($origin === 'defined');
          $slug = (string)($historia['slug'] ?? '');
          $dia = (string)($historia['dia'] ?? '');
          $data = (string)($historia['data'] ?? '');
          $categoria = (string)($historia['categoria'] ?? '');
          $titulo = (string)($historia['titulo'] ?? '');
          $referencia = (string)($historia['referencia'] ?? '');
          $imgUrl = (string)($historia['imagem']['url'] ?? 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg');
          $imgAlt = (string)($historia['imagem']['alt'] ?? $titulo);
          $testament = (string)($historia['book']['testament'] ?? 'VT');
          $bookName = (string)($historia['book']['name'] ?? '');
          $categoryShort = (string)($historia['book']['category_short'] ?? $categoria);
          $storyUrl = $slug !== '' ? 'index.php?p=' . urlencode($slug) : ($isDefined ? 'index.php?day=' . urlencode($dia) : (string)($historia['bible_url'] ?? 'bible.php'));
          $bibleUrl = (string)($historia['bible_url'] ?? 'bible.php');
          $rawExcerpt = preg_replace('/\b\d+\.\s*/', '', (string)($historia['texto'] ?? '')) ?? '';
          $excerpt = mb_strlen($rawExcerpt, 'UTF-8') > 115
              ? mb_substr($rawExcerpt, 0, 115, 'UTF-8') . '…'
              : $rawExcerpt;
          $searchIndex = BibleRepository::stripAccents(
              $titulo . ' ' . $referencia . ' ' . $bookName . ' ' . $categoryShort . ' ' . ($isDefined ? 'dia ' . $dia . ' diaria' : $categoria . ' canonica')
          );
        ?>
        <article
          class="story-card <?= $isDefined ? 'story-card--daily' : 'story-card--canonical' ?>"
          data-testament="<?= htmlspecialchars($testament, ENT_QUOTES, 'UTF-8') ?>"
          data-origin="<?= htmlspecialchars($origin, ENT_QUOTES, 'UTF-8') ?>"
          data-search="<?= htmlspecialchars($searchIndex, ENT_QUOTES, 'UTF-8') ?>"
        >
          <a href="<?= htmlspecialchars($storyUrl, ENT_QUOTES, 'UTF-8') ?>" class="story-card-image-link">
            <div class="story-image">
              <img
                src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
                loading="lazy"
              />
              <div class="story-overlay"></div>
              <div class="story-badges-top">
                <?php if ($isDefined): ?>
                  <span class="story-day-badge">Dia <?= htmlspecialchars($dia, ENT_QUOTES, 'UTF-8') ?><?= $data !== '' ? ' · ' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : '' ?></span>
                <?php else: ?>
                  <span class="story-day-badge story-day-badge--canonical">História Bíblica<?= $categoria !== '' ? ' · ' . htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') : '' ?></span>
                <?php endif; ?>
                <?php if ($categoryShort !== ''): ?>
                  <span class="story-cat-badge"><?= htmlspecialchars($categoryShort, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </div>
          </a>

          <div class="story-content">
            <p class="story-reference"><?= htmlspecialchars($referencia, ENT_QUOTES, 'UTF-8') ?></p>
            <h3 class="story-title">
              <a href="<?= htmlspecialchars($storyUrl, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
              </a>
            </h3>
            <?php if ($excerpt !== ''): ?>
              <p class="story-excerpt"><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="story-card-actions">
              <a href="<?= htmlspecialchars($storyUrl, ENT_QUOTES, 'UTF-8') ?>" class="story-action-primary">
                Ler História
              </a>
              <a href="<?= htmlspecialchars($bibleUrl, ENT_QUOTES, 'UTF-8') ?>" class="story-action-secondary" title="Ler o capítulo completo na Bíblia">
                Capítulo
              </a>
              <button
                type="button"
                class="sep-share-btn btn-share-trigger"
                data-share-type="História Bíblica"
                data-share-title="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>"
                data-share-ref="<?= htmlspecialchars($referencia . ' (NAA)', ENT_QUOTES, 'UTF-8') ?>"
                data-share-text="<?= htmlspecialchars($rawExcerpt, ENT_QUOTES, 'UTF-8') ?>"
                data-share-url="<?= htmlspecialchars($storyUrl, ENT_QUOTES, 'UTF-8') ?>"
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

    <div id="no-stories-message" class="no-stories" style="display: none;">
      Nenhuma história encontrada para o filtro selecionado.
    </div>
  </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
