<?php
declare(strict_types=1);

$pageTitle = 'Privacidade e Cookies — Via Scriptura';
$pageDescription = 'Saiba como o Via Scriptura utiliza dados, armazenamento local e cookies analíticos.';
$pageUrl = 'https://www.viascriptura.com/privacy.php';
$activeNav = 'privacy';
$bodyClass = 'page-privacy';

require __DIR__ . '/includes/header.php';
?>

<main class="privacy-page-main">
  <article class="privacy-document">
    <header class="privacy-hero">
      <span class="section-kicker">LGPD · Transparência e controle</span>
      <h1>Política de Privacidade e Cookies</h1>
      <p>Esta política explica, de forma simples, como o Via Scriptura trata informações durante sua navegação.</p>
      <span class="privacy-updated">Última atualização: 16 de outubro de 2026</span>
    </header>

    <section>
      <h2>1. Dados e tecnologias utilizadas</h2>
      <h3>Recursos necessários e preferências locais</h3>
      <p>O site utiliza armazenamento local do navegador para guardar preferências como tema, tamanho da fonte, instalação do aplicativo e a própria escolha sobre cookies. Essas informações são necessárias para oferecer as funcionalidades solicitadas e permanecem no dispositivo do visitante.</p>

      <h3>Google Analytics — opcional</h3>
      <p>Com sua autorização, utilizamos o Google Analytics 4 para medir visitas, páginas acessadas, tipo geral de dispositivo, origem aproximada do acesso e interações com o site. A finalidade é compreender o uso agregado e melhorar conteúdo, desempenho e navegação.</p>
      <p>O Analytics permanece bloqueado até uma manifestação positiva. Sinais de publicidade, personalização de anúncios e Google Signals são desativados nesta implementação.</p>
    </section>

    <section>
      <h2>2. Base legal e compartilhamento</h2>
      <p>O tratamento analítico é realizado com base no consentimento do titular, que pode ser recusado ou revogado sem limitar o acesso ao conteúdo. Quando aceito, dados técnicos são processados pelo Google, fornecedor contratado para a medição, podendo envolver transferência internacional conforme os termos e mecanismos de proteção adotados por esse fornecedor.</p>
    </section>

    <section>
      <h2>3. Retenção</h2>
      <p>A preferência de consentimento permanece no navegador até ser alterada ou até a limpeza dos dados locais. Os dados no Google Analytics devem ser configurados para o menor período disponível compatível com a finalidade de medição. Informações agregadas podem permanecer em relatórios sem identificação direta.</p>
    </section>

    <section>
      <h2>4. Seus direitos</h2>
      <p>Nos termos da LGPD, você pode solicitar confirmação e acesso, correção, informação sobre compartilhamento, eliminação quando aplicável, oposição e revogação do consentimento. A revogação não afeta o tratamento realizado anteriormente de forma válida.</p>
    </section>

    <section>
      <h2>5. Como alterar sua escolha</h2>
      <p>Use o botão abaixo ou o link “Preferências de cookies” disponível no rodapé. Ao recusar, o site registra a preferência e tenta remover os cookies analíticos definidos por esta origem.</p>
      <button type="button" class="btn-modern-primary" id="privacy-cookie-preferences">Revisar preferências de cookies</button>
    </section>

    <section>
      <h2>6. Alterações desta política</h2>
      <p>Esta política poderá ser atualizada para refletir mudanças no site ou nas práticas de tratamento. Alterações relevantes que afetem o consentimento exigirão uma nova escolha do visitante.</p>
    </section>
  </article>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var button = document.getElementById('privacy-cookie-preferences');
    if (!button) return;
    button.addEventListener('click', function () {
      var footerButton = document.getElementById('cookie-preferences-btn');
      if (footerButton) footerButton.click();
    });
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
