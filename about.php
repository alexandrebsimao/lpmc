<?php
declare(strict_types=1);

$pageTitle = 'Sobre — Via Scriptura | Leitura Bíblica Diária & Bíblia Completa';
$pageDescription = 'Conheça o propósito do Via Scriptura: aproximar pessoas da Palavra de Deus com leituras bíblicas diárias, a Bíblia Sagrada completa (NAA) e ilustrações contemplativas.';
$pageUrl = 'https://www.viascriptura.com/about.php';
$activeNav = 'about';
$bodyClass = 'page-about';

require __DIR__ . '/includes/header.php';
?>

  <main id="about-section" class="about-page-main">
    <img
      class="about-bg"
      src="https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Escritor em ambiente de luz suave.png"
      alt=""
      aria-hidden="true"
    />
    <div class="about-overlay"></div>

    <section class="about-content" aria-labelledby="about-title">
      <div class="about-logo-showcase">
        <img src="assets/logo.png" alt="Logo Oficial Via Scriptura — Bíblia & Devocional" class="about-hero-logo" width="160" height="160" />
      </div>
      <p class="about-label">Nossa Missão & Propósito</p>
      <h1 id="about-title">Via <em>Scriptura</em></h1>

      <div class="about-stats-grid">
        <div class="about-stat-card">
          <span class="about-stat-num">66</span>
          <span class="about-stat-label">Livros Bíblicos</span>
        </div>
        <div class="about-stat-card">
          <span class="about-stat-num">1.189</span>
          <span class="about-stat-label">Capítulos em Tela</span>
        </div>
        <div class="about-stat-card">
          <span class="about-stat-num">31.102</span>
          <span class="about-stat-label">Versículos (NAA)</span>
        </div>
        <div class="about-stat-card">
          <span class="about-stat-num">366</span>
          <span class="about-stat-label">Meditações Diárias</span>
        </div>
      </div>

      <div class="about-text-card">
        <div class="about-text">
          <p>O <strong>Via Scriptura</strong> nasceu com o propósito de aproximar as pessoas da Palavra de Deus por meio de uma experiência de leitura mais profunda, visual e inspiradora.</p>

          <p>Reunimos <strong>366 histórias bíblicas</strong> acompanhadas de ilustrações geradas com inteligência artificial e acesso integral aos <strong>66 livros da Bíblia Sagrada na versão Nova Almeida Atualizada (NAA)</strong> diretamente em tela, criando um ambiente imersivo para meditar nas Escrituras e compreender o contexto de cada passagem.</p>

          <p>As imagens não substituem o texto bíblico, mas servem como apoio visual para tornar a leitura mais envolvente e contemplativa, enquanto o leitor bíblico integrado permite explorar capítulos inteiros, comparar divisões bíblicas e pesquisar qualquer tema nas Escrituras.</p>

          <p>O Via Scriptura é um convite para caminhar pelas Escrituras, dia após dia, com reverência, beleza e propósito.</p>
        </div>
      </div>

      <nav class="about-actions" aria-label="Navegação da página Sobre">
        <a href="index.php" class="back-link">← Leitura do Dia</a>
        <a href="bible.php" class="back-link">Abrir Bíblia Completa</a>
        <a href="stories.php" class="back-link">Ver 366 Histórias</a>
      </nav>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
