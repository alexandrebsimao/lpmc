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

  <!-- Toast de Notificação -->
  <div id="vs-toast" class="vs-toast" role="status" aria-live="polite"></div>

  <!-- Rodapé Global -->
  <footer id="rodape" class="site-footer visible">
    <div class="footer-inner">
      <div class="footer-brand">
        <span class="footer-title">Via <em>Scriptura</em></span>
        <p class="footer-tagline">Caminhando pelas Escrituras dia após dia com reverência, beleza e propósito.</p>
      </div>

      <div class="footer-links-group">
        <a href="index.php">Leitura do Dia</a>
        <a href="bible.php">Bíblia Completa (NAA)</a>
        <a href="bible.php?view=panorama">Panorama dos 66 Livros</a>
        <a href="stories.php">366 Histórias Bíblicas</a>
        <a href="divisions.php">Separação Bíblica (Eras & Divisões)</a>
        <a href="about.php">Sobre o Projeto</a>
      </div>

      <div class="footer-meta">
        <span class="version-pill">Nova Almeida Atualizada (NAA)</span>
        <span class="copyright-text">© <?= date('Y') ?> Via Scriptura · Todos os direitos reservados</span>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
