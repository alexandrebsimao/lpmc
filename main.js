/**
 * main.js
 * Dados embutidos diretamente no JS — sem fetch, funciona ao abrir
 * o index.html localmente (file://) sem precisar de servidor.
 *
 * Para alterar conteúdo, edite o objeto DADOS abaixo.
 */

const DADOS = [
    {
        dia: "1",
        data: "03/04/2026",
        titulo: "A crucificação de Jesus",
        referencia: "João 19:16-30",
        texto: "16. Então, Pilatos o entregou para ser crucificado. 17. Tomaram eles, pois, a Jesus; e ele próprio, carregando a sua cruz, saiu para o lugar chamado Calvário, Gólgota em hebraico, 18. onde o crucificaram e com ele outros dois, um de cada lado, e Jesus no meio. 19. Pilatos escreveu também um título e o colocou no cimo da cruz; o que estava escrito era: JESUS NAZARENO, O REI DOS JUDEUS. 20. Muitos judeus leram este título, porque o lugar em que Jesus fora crucificado era perto da cidade; e estava escrito em hebraico, latim e grego. 21. Os principais sacerdotes diziam a Pilatos: Não escrevas: Rei dos judeus, e sim que ele disse: Sou o rei dos judeus. 22. Respondeu Pilatos: O que escrevi escrevi. 23. Os soldados, pois, quando crucificaram Jesus, tomaram-lhe as vestes e fizeram quatro partes, para cada soldado uma parte; e pegaram também a túnica. A túnica, porém, era sem costura, toda tecida de alto a baixo. 24. Disseram, pois, uns aos outros: Não a rasguemos, mas lancemos sortes sobre ela para ver a quem caberá — para se cumprir a Escritura: Repartiram entre si as minhas vestes e sobre a minha túnica lançaram sortes. Assim, pois, o fizeram os soldados. 25. E junto à cruz estavam a mãe de Jesus, e a irmã dela, e Maria, mulher de Clopas, e Maria Madalena. 26. Vendo Jesus sua mãe e junto a ela o discípulo amado, disse: Mulher, eis aí teu filho. 27. Depois, disse ao discípulo: Eis aí tua mãe. Dessa hora em diante, o discípulo a tomou para casa. 28. Depois, vendo Jesus que tudo já estava consumado, para se cumprir a Escritura, disse: Tenho sede! 29. Estava ali um vaso cheio de vinagre. Embeberam de vinagre uma esponja e, fixando-a num caniço de hissopo, lha chegaram à boca. 30. Quando, pois, Jesus tomou o vinagre, disse: Está consumado! E, inclinando a cabeça, rendeu o espírito.",
        imagem: {
          url: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80",
          alt: "Montanhas ao amanhecer com névoa"
      }
    }
  ];

function diaDoAno(data) {
  const hoje = data instanceof Date ? data : new Date(data);
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  const diferenca = hoje - inicioAno;
  const umDia = 1000 * 60 * 60 * 24;
  return Math.floor(diferenca / umDia) + 1;
}

function formatarDataBR(data) {
  const dia = String(data.getDate()).padStart(2, '0');
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const ano = data.getFullYear();
  return `${dia}/${mes}/${ano}`;
}

function carregarDados() {
  const hoje = new Date();
  const pagina = DADOS[diaDoAno(hoje)];

  return {
    ...pagina,
    dia: String(diaDoAno(hoje)),
    data: formatarDataBR(hoje)
  };
}

function popularPagina(pagina) {
  // Título
  const tituloEl = document.getElementById('titulo');
  const titulo = String(pagina.titulo || '');
  const palavras = titulo.split(' ').filter(Boolean);
  const ultima = palavras.pop() || '';
  const primeiraParte = palavras.join(' ');
  tituloEl.innerHTML = primeiraParte ? `${primeiraParte} <em>${ultima}</em>` : ultima;

  // Subtítulo / referência
  document.getElementById('subtitulo').textContent = pagina.referencia || '';

  // Texto principal: aceita texto ou historia
  document.getElementById('historia').textContent = pagina.texto || pagina.historia || '';

  // Label com dia / data, se houver
  const labelEl = document.querySelector('.label');
  if (labelEl) {
    const diaData = [pagina.dia, pagina.data].filter(Boolean).join(' · ');
    labelEl.textContent = diaData || 'Descubra o mundo';
  }

  // Botão CTA
  const ctaEl = document.getElementById('cta');
  if (ctaEl && pagina.cta) {
    ctaEl.querySelector('span').textContent = pagina.cta;
  }

  // Rodapé
  const rodapeEl = document.getElementById('rodape');
  if (rodapeEl && pagina.rodape) {
    rodapeEl.textContent = pagina.rodape;
  }

  // Imagem de fundo
  const img = document.getElementById('bg-image');
  const imagem = pagina.imagem || {};
  img.alt = imagem.alt || '';
  img.src = imagem.url || '';

  if (imagem.url) {
    img.addEventListener('load', () => {
      img.classList.add('loaded');
      revelarConteudo();
    });
  } else {
    revelarConteudo();
  }

  // Fallback: revela mesmo que a imagem demore
  setTimeout(revelarConteudo, 3000);
}

function revelarConteudo() {
  document.getElementById('loader').classList.add('hidden');
  document.querySelector('.side-line').classList.add('visible');
  document.querySelector('.content').classList.add('visible');
  document.getElementById('rodape').classList.add('visible');
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
  const pagina = carregarDados();
  popularPagina(pagina);
});