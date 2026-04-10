/**
 * stories.js
 * Carrega todos os dados do data.json e exibe como grid de histórias
 */

let HISTORIAS = [];
let HISTORIAS_FILTRADAS = [];

async function carregarHistorias() {
  try {
    const response = await fetch('./data/data.json');
    if (!response.ok) {
      throw new Error(`Erro ao carregar data.json: ${response.statusText}`);
    }
    HISTORIAS = await response.json();
    HISTORIAS_FILTRADAS = HISTORIAS;
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    HISTORIAS = [];
    HISTORIAS_FILTRADAS = [];
  }
}

function filtrarHistoriasPorTitulo(termo) {
  if (!termo.trim()) {
    HISTORIAS_FILTRADAS = HISTORIAS;
  } else {
    const termoLower = termo.toLowerCase();
    HISTORIAS_FILTRADAS = HISTORIAS.filter(historia => 
      historia.titulo.toLowerCase().includes(termoLower) || historia.referencia?.toLowerCase().includes(termoLower)
    );
  }
  renderizarHistorias();
}

function renderizarHistorias() {
  const grid = document.getElementById('stories-grid');
  
  if (HISTORIAS_FILTRADAS.length === 0) {
    grid.innerHTML = '<p class="no-stories">Nenhuma história encontrada.</p>';
    return;
  }

  const historiasOrdenadas = HISTORIAS_FILTRADAS.sort((a, b) => Number(a.dia) - Number(b.dia));

  grid.innerHTML = historiasOrdenadas.map(historia => `
    <a href="/?page=${historia.dia}" class="story-card">
      <div class="story-image">
        <img 
          src="${historia.imagem?.url || 'https://cdn.pixabay.com/photo/2023/04/03/17/25/jesus-7897344_1280.jpg'}" 
          alt="${historia.imagem?.alt || historia.titulo}"
          loading="lazy"
        />
        <div class="story-overlay"></div>
      </div>
      <div class="story-content">
        <h3 class="story-title">${historia.titulo}</h3>
        <p class="story-reference">${historia.referencia || ''}</p>
      </div>
    </a>
  `).join('');
}

function esconderLoader() {
  const loader = document.getElementById('loader');
  if (loader) {
    loader.style.display = 'none';
  }
}

function configurarBusca() {
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', (event) => {
      filtrarHistoriasPorTitulo(event.target.value);
    });
  }
}

async function inicializar() {
  await carregarHistorias();
  renderizarHistorias();
  configurarBusca();
  esconderLoader();
}

document.addEventListener('DOMContentLoaded', inicializar);
