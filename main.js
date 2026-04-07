/**
 * main.js
 * Carrega dados do data.json no mesmo diretório.
 */

let DADOS = [];

function diaDoAno(data) {
  const hoje = data instanceof Date ? data : new Date(data);
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  const diferenca = hoje - inicioAno;
  const umDia = 1000 * 60 * 60 * 24;
  return Math.floor(diferenca / umDia) + 1;
}

async function carregarDadosJSON() {
  try {
    const response = await fetch('./data.json');
    if (!response.ok) {
      throw new Error(`Erro ao carregar data.json: ${response.statusText}`);
    }
    DADOS = await response.json();
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    DADOS = [];
  }
}

function formatarDataBR(data) {
  const dia = String(data.getDate()).padStart(2, '0');
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const ano = data.getFullYear();
  return `${dia}/${mes}/${ano}`;
}

function obterDiaDoQuery() {
  const params = new URLSearchParams(window.location.search);
  return params.get('day');
}

function carregarDados() {
  const hoje = new Date();
  const diaQuery = obterDiaDoQuery();
  const diaAtual = diaQuery ? String(diaQuery) : String(diaDoAno(hoje));
  
  // Busca no array pelos dados que correspondem ao dia do ano
  const pagina = DADOS.find(item => item.dia === diaAtual);
  
  if (!pagina) {
    console.warn(`Nenhum dado encontrado para o dia ${diaAtual}`);
    return {
      dia: diaAtual,
      data: formatarDataBR(hoje),
      titulo: 'Dia sem conteúdo',
      referencia: '',
      texto: 'Nenhum conteúdo disponível para este dia.',
      imagem: {}
    };
  }

  return {
    ...pagina,
    dia: String(diaAtual),
    data: pagina.data || formatarDataBR(hoje)
  };
}

function popularPagina(pagina) {
  // Título
  const tituloEl = document.getElementById('titulo');
  const titulo = String(pagina.titulo || '');
  const palavras = titulo.split(' ').filter(Boolean);
  const ultima = palavras.pop() || '';
  const primeiraParte = palavras.join(' ');
  const diaQuery = obterDiaDoQuery();

  tituloEl.innerHTML = primeiraParte ? `${primeiraParte} <em>${ultima}</em>` : ultima;

  if (diaQuery) {
    if (diaQuery > 1) {
      document.getElementById('previous-day').href = `?day=${String(Number(diaQuery) - 1)}`;
      document.getElementById('previous-day').style.display = 'inline-block';
    }

    if (diaQuery < 266) {
      document.getElementById('next-day').href = `?day=${String(Number(diaQuery) + 1)}`;
      document.getElementById('next-day').style.display = 'inline-block';
    }
  }

  // Subtítulo / referência
  document.getElementById('subtitulo').textContent = pagina.referencia || '';

  // Texto principal: aceita texto ou historia
  document.getElementById('historia').textContent = pagina.texto || pagina.historia || '';

  // Label com dia / data, se houver
  const labelEl = document.querySelector('.label');
  if (labelEl) {
    const diaData = [pagina.dia, pagina.data].filter(Boolean).join(' · ');
    labelEl.textContent = diaData || 'Descubra a palavra';
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
document.addEventListener('DOMContentLoaded', async () => {
  await carregarDadosJSON();
  const pagina = carregarDados();
  popularPagina(pagina);
});