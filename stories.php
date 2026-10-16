<?php
declare(strict_types=1);

require_once __DIR__ . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();
$stories = $repo->getAllStories();

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

$pageTitle = 'Histórias Bíblicas (366 Leituras Diárias) — Via Scriptura';
$pageDescription = 'Explore nossa coleção completa de 366 histórias bíblicas ilustradas com versículos e capítulos da Bíblia Nova Almeida Atualizada (NAA).';
$pageUrl = 'https://www.viascriptura.com/stories.php';
$activeNav = 'stories';
$bodyClass = 'page-stories';

require __DIR__ . '/includes/header.php';
?>

  <section id="stories-section" class="stories-page-section">
    <div class="stories-header">
      <div class="stories-header-titles">
        <span class="section-kicker">Jornada Devocional Anual · 366 Passagens</span>
        <h1>Histórias <em>Bíblicas</em></h1>
        <p class="stories-subtitle">
          Percorra as 366 leituras bíblicas curadas do ano ou filtre por Testamento, título e referência bíblica.
        </p>
      </div>

      <div class="stories-stats-pills">
        <span class="stat-pill"><strong><?= count($stories) ?></strong> Leituras</span>
        <span class="stat-pill"><strong><?= $vtCount ?></strong> Antigo Testamento</span>
        <span class="stat-pill"><strong><?= $ntCount ?></strong> Novo Testamento</span>
        <a href="divisions.php" class="btn-modern-primary">
          <span>Ver Separação por Eras & Divisões</span>
          <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>

    <!-- Barra de Busca e Filtros -->
    <div class="stories-controls-bar">
      <div class="stories-filter-tabs" role="tablist" aria-label="Filtrar histórias por Testamento">
        <button type="button" class="filter-tab-btn active" data-story-testament="ALL">
          Todas (<?= count($stories) ?>)
        </button>
        <button type="button" class="filter-tab-btn" data-story-testament="VT">
          Antigo Testamento (<?= $vtCount ?>)
        </button>
        <button type="button" class="filter-tab-btn" data-story-testament="NT">
          Novo Testamento (<?= $ntCount ?>)
        </button>
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

    <!-- Grade de Histórias Renderizada no Servidor -->
    <div id="stories-grid" class="stories-grid">
      <?php foreach ($stories as $historia): ?>
        <?php
          $slug = (string)($historia['slug'] ?? '');
          $dia = (string)($historia['dia'] ?? '');
          $data = (string)($historia['data'] ?? '');
          $titulo = (string)($historia['titulo'] ?? '');
          $referencia = (string)($historia['referencia'] ?? '');
          $imgUrl = (string)($historia['imagem']['url'] ?? 'https://cdn.pixabay.com/photo/2023/04/03/17/25/jesus-7897344_1280.jpg');
          $imgAlt = (string)($historia['imagem']['alt'] ?? $titulo);
          $testament = (string)($historia['book']['testament'] ?? 'VT');
          $categoryShort = (string)($historia['book']['category_short'] ?? '');
          $storyUrl = $slug !== '' ? 'index.php?p=' . urlencode($slug) : 'index.php?day=' . urlencode($dia);
          $bibleUrl = (string)($historia['bible_url'] ?? 'bible.php');
          $rawExcerpt = preg_replace('/\b\d+\.\s*/', '', (string)($historia['texto'] ?? '')) ?? '';
          $excerpt = mb_strlen($rawExcerpt, 'UTF-8') > 115
              ? mb_substr($rawExcerpt, 0, 115, 'UTF-8') . '…'
              : $rawExcerpt;
          $searchIndex = BibleRepository::stripAccents($titulo . ' ' . $referencia . ' ' . $categoryShort . ' dia ' . $dia);
        ?>
        <article
          class="story-card"
          data-testament="<?= htmlspecialchars($testament, ENT_QUOTES, 'UTF-8') ?>"
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
                <span class="story-day-badge">Dia <?= htmlspecialchars($dia, ENT_QUOTES, 'UTF-8') ?><?= $data !== '' ? ' · ' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : '' ?></span>
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
                Ler Meditação
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
