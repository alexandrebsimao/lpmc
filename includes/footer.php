<?php
/**
 * includes/footer.php
 * Rodapé global e Modal Universal de Compartilhamento do Via Scriptura.
 */
?>
  <!-- Modal Universal de Compartilhamento (Histórias, Capítulos e Trechos da Bíblia) -->
  <div id="vs-share-modal" class="vs-share-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="share-modal-heading">
    <div class="vs-share-backdrop" id="vs-share-backdrop"></div>
    <div class="vs-share-dialog">
      <header class="vs-share-header">
        <div>
          <span id="share-modal-type" class="section-kicker">Compartilhar Palavra</span>
          <h3 id="share-modal-heading">Compartilhar Conteúdo</h3>
        </div>
        <button type="button" id="vs-share-close" class="vs-share-close" aria-label="Fechar janela de compartilhamento">✕</button>
      </header>

      <div class="vs-share-body">
        <!-- Prévia do Conteúdo -->
        <div class="vs-share-preview">
          <div class="share-preview-top">
            <strong id="share-preview-title"></strong>
            <span id="share-preview-ref" class="share-preview-ref"></span>
          </div>
          <p id="share-preview-text" class="share-preview-text"></p>
        </div>

        <!-- Botões de Redes Sociais e Mensageiros -->
        <div class="vs-share-networks">
          <a href="#" id="share-btn-whatsapp" target="_blank" rel="noopener noreferrer" class="share-net-btn share-net--whatsapp">
            <span>WhatsApp</span>
          </a>
          <a href="#" id="share-btn-telegram" target="_blank" rel="noopener noreferrer" class="share-net-btn share-net--telegram">
            <span>Telegram</span>
          </a>
          <a href="#" id="share-btn-twitter" target="_blank" rel="noopener noreferrer" class="share-net-btn share-net--twitter">
            <span>X / Twitter</span>
          </a>
          <a href="#" id="share-btn-facebook" target="_blank" rel="noopener noreferrer" class="share-net-btn share-net--facebook">
            <span>Facebook</span>
          </a>
          <a href="#" id="share-btn-email" class="share-net-btn share-net--email">
            <span>E-mail</span>
          </a>
          <button type="button" id="share-btn-native" class="share-net-btn share-net--native" style="display: none;">
            <span>Mais Opções...</span>
          </button>
        </div>

        <!-- Ações Rápidas: Copiar Tudo, Copiar Link ou Baixar Card de Imagem -->
        <div class="vs-share-utility-row">
          <button type="button" id="share-btn-copy-full" class="btn-modern-primary">
            <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            <span>Copiar Texto + Link</span>
          </button>

          <button type="button" id="share-btn-copy-link" class="btn-modern-secondary">
            <span>Copiar Apenas Link</span>
          </button>

          <button type="button" id="share-btn-download-card" class="btn-modern-secondary" title="Gerar imagem para Instagram Stories / Status">
            <span>Baixar Card Visual (PNG)</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Instalação do Aplicativo (PWA) -->
  <div id="vs-pwa-modal" class="vs-share-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="pwa-modal-heading">
    <div class="vs-share-backdrop" id="vs-pwa-backdrop"></div>
    <div class="vs-share-dialog vs-pwa-dialog">
      <header class="vs-share-header">
        <div>
          <span class="section-kicker">Aplicativo Web Progressivo (PWA)</span>
          <h3 id="pwa-modal-heading">Instalar o Via Scriptura</h3>
        </div>
        <button type="button" id="vs-pwa-close" class="vs-share-close" aria-label="Fechar janela de instalação">✕</button>
      </header>

      <div class="vs-share-body">
        <div class="pwa-modal-hero">
          <img src="assets/icons/icon-192.png" alt="Ícone do Via Scriptura" class="pwa-modal-icon" width="64" height="64" />
          <div class="pwa-modal-copy">
            <strong>Via Scriptura no seu dispositivo</strong>
            <p>Acesse a Leitura do Dia, as 366 Histórias e a Bíblia Completa (NAA) em tela cheia e com suporte a leitura offline.</p>
          </div>
        </div>

        <div id="pwa-native-install-box" class="pwa-install-box" style="display: none;">
          <button type="button" id="pwa-confirm-install-btn" class="btn-modern-primary pwa-full-btn">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span>Instalar Agora em 1 Clique</span>
          </button>
        </div>

        <div class="pwa-steps-grid">
          <div class="pwa-step-card">
            <span class="pwa-step-badge">iPhone / iPad (Safari)</span>
            <p>Toque no botão <strong>Compartilhar</strong> na barra do Safari e selecione <strong>“Adicionar à Tela de Início”</strong>.</p>
          </div>
          <div class="pwa-step-card">
            <span class="pwa-step-badge">Android / Chrome / Edge</span>
            <p>Toque no ícone <strong>Instalar aplicativo</strong> na barra de endereços ou no menu <strong>⋮ → Instalar aplicativo</strong>.</p>
          </div>
        </div>

        <div class="pwa-offline-preload">
          <button type="button" id="pwa-cache-core-btn" class="btn-modern-secondary pwa-full-btn">
            <span>Baixar Páginas Principais para Leitura Offline</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast de Notificação -->
  <div id="vs-toast" class="vs-toast" role="status" aria-live="polite"></div>

  <!-- Rodapé Global -->
  <footer id="rodape" class="site-footer visible">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-brand-head">
          <img src="assets/icons/icon.svg" alt="Logo Via Scriptura" class="footer-brand-logo" width="38" height="38" loading="lazy" />
          <span class="footer-title">Via <em>Scriptura</em></span>
        </div>
        <p class="footer-tagline">Caminhando pelas Escrituras dia após dia com reverência, beleza e propósito.</p>
      </div>

      <div class="footer-links-group">
        <a href="index.php">Leitura do Dia</a>
        <a href="bible.php">Bíblia Completa (NAA)</a>
        <a href="bible.php?view=panorama">Panorama dos 66 Livros</a>
        <a href="stories.php">586 Histórias Bíblicas</a>
        <a href="divisions.php">Separação Bíblica (Eras & Divisões)</a>
        <a href="about.php">Sobre o Projeto</a>
      </div>

      <div class="footer-meta">
        <span class="version-pill">Nova Almeida Atualizada (NAA)</span>
        <span id="pwa-connection-pill" class="version-pill pwa-status-pill">PWA · Pronto para Uso Offline</span>
        <span class="copyright-text">© <?= date('Y') ?> Via Scriptura · Todos os direitos reservados</span>
      </div>
    </div>
  </footer>

  <!-- Barra de Navegação Inferior Fixa para Mobile (App Tab Bar) -->
  <?php $currentNav = $activeNav ?? 'home'; ?>
  <nav class="mobile-bottom-nav" aria-label="Menu inferior mobile">
    <a href="index.php" class="mobile-tab-item <?= $currentNav === 'home' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
      </svg>
      <span>Hoje</span>
    </a>
    <a href="bible.php" class="mobile-tab-item <?= $currentNav === 'bible' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
      </svg>
      <span>Bíblia</span>
    </a>
    <a href="stories.php" class="mobile-tab-item <?= $currentNav === 'stories' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
      </svg>
      <span>Histórias</span>
    </a>
    <a href="divisions.php" class="mobile-tab-item <?= $currentNav === 'divisions' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="2" fill="none" aria-hidden="true">
        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
        <polyline points="2 17 12 22 22 17"></polyline>
        <polyline points="2 12 12 17 22 12"></polyline>
      </svg>
      <span>Separação</span>
    </a>
    <button type="button" class="mobile-tab-item mobile-tab-btn" data-open-drawer="true" aria-label="Abrir menu retrátil e navegação da Bíblia">
      <svg viewBox="0 0 24 24" width="19" height="19" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" aria-hidden="true">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
      <span>Menu</span>
    </button>
  </nav>

  <script src="assets/js/app.js"></script>
</body>
</html>
