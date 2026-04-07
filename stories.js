/**
 * stories.js
 * Carrega todos os dados do data.json e exibe como grid de histórias
 */

let HISTORIAS = [];

async function carregarHistorias() {
  try {
    const response = await fetch('./data.json');
    if (!response.ok) {
      throw new Error(`Erro ao carregar data.json: ${response.statusText}`);
    }
    HISTORIAS = await response.json();
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
    HISTORIAS = [];
  }
}

function renderizarHistorias() {
  const grid = document.getElementById('stories-grid');
  
  if (HISTORIAS.length === 0) {
    grid.innerHTML = '<p class="no-stories">Nenhuma história disponível.</p>';
    return;
  }

  const historiasOrdenadas = HISTORIAS.sort((a, b) => Number(a.dia) - Number(b.dia));

  grid.innerHTML = historiasOrdenadas.map(historia => `
    <a href="index.html?page=${historia.dia}" class="story-card">
      <div class="story-image">
        <img 
          src="${historia.imagem?.url || 'https://cdn.pixabay.com/photo/2023/04/03/17/25/jesus-7897344_1280.jpg'}" 
          alt="${historia.imagem?.alt || historia.titulo}"
          loading="lazy"
        />
        <div class="story-overlay"></div>
      </div>
      <div class="story-content">
        <span class="story-day">Dia ${historia.dia}</span>
        <h3 class="story-title">${historia.titulo}</h3>
        <p class="story-reference">${historia.referencia || ''}</p>
        <p class="story-date">${historia.data || ''}</p>
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

async function inicializar() {
  await carregarHistorias();
  renderizarHistorias();
  esconderLoader();
}

document.addEventListener('DOMContentLoaded', inicializar);
