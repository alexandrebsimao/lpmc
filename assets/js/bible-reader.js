/**
 * bible-reader.js
 * Módulo para carregamento e leitura da Bíblia completa (NAA) no Via Scriptura.
 */

(function () {
  'use strict';

  // Cache e estado interno
  let BOOKS_META = [];
  const BOOKS_CACHE = new Map();
  const BOOK_NAME_MAP = new Map();
  let currentBookAbbrev = 'gn';
  let currentChapter = 1;
  let currentHighlight = null; // { start: number, end: number } | null

  // Elementos do DOM
  let drawerEl = null;
  let backdropEl = null;
  let closeBtn = null;
  let bookSelect = null;
  let chapterSelect = null;
  let versesListEl = null;
  let loadingEl = null;
  let chapterTitleEl = null;
  let contextTagEl = null;
  let prevChapterBtn = null;
  let nextChapterBtn = null;
  let chapterIndicatorEl = null;

  function stripAccents(str) {
    if (!str) return '';
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
  }

  /**
   * Constrói o mapa de nomes de livros para normalizar referências como "Miquéias" e "Miqueias".
   */
  function buildBookMap() {
    BOOK_NAME_MAP.clear();
    BOOKS_META.forEach(book => {
      const norm = stripAccents(book.name);
      BOOK_NAME_MAP.set(norm, book);
      BOOK_NAME_MAP.set(book.abbrev.toLowerCase(), book);

      // Variações comuns de nomes em português
      if (norm === 'salmos') BOOK_NAME_MAP.set('salmo', book);
      if (norm === 'canticos') {
        BOOK_NAME_MAP.set('cantares', book);
        BOOK_NAME_MAP.set('cantico dos canticos', book);
        BOOK_NAME_MAP.set('canticos dos canticos', book);
      }
      if (norm === 'lamentacoes de jeremias') {
        BOOK_NAME_MAP.set('lamentacoes', book);
      }
    });

    // Desambiguação de João vs Jó
    const joaoBook = BOOKS_META.find(b => b.name === 'João');
    const joBook = BOOKS_META.find(b => b.name === 'Jó');
    if (joaoBook) {
      BOOK_NAME_MAP.set('joao', joaoBook);
      BOOK_NAME_MAP.set('joão', joaoBook);
    }
    if (joBook) {
      BOOK_NAME_MAP.set('jo', joBook);
      BOOK_NAME_MAP.set('jó', joBook);
      BOOK_NAME_MAP.set('job', joBook);
    }
  }

  /**
   * Converte uma string de referência (ex: "2 Coríntios 5:17", "João 1:1-18", "Salmo 23")
   * nos componentes necessários para abrir a Bíblia.
   */
  function parseReference(refStr) {
    if (!refStr || typeof refStr !== 'string') return null;

    const trimmed = refStr.trim();
    const match = trimmed.match(/^((?:[123]\s+)?[A-Za-zÀ-ÿ\s]+?)\s+(\d+)(?::(\d+(?:\s*-\s*\d+)?))?$/);
    if (!match) return null;

    const [_, rawBookName, capStr, verseStr] = match;
    const normBook = stripAccents(rawBookName);
    const book = BOOK_NAME_MAP.get(normBook);
    if (!book) return null;

    const chapter = parseInt(capStr, 10);
    let highlight = null;

    if (verseStr) {
      if (verseStr.includes('-')) {
        const parts = verseStr.split('-').map(s => parseInt(s.trim(), 10));
        highlight = { start: parts[0], end: parts[1] };
      } else {
        const v = parseInt(verseStr.trim(), 10);
        highlight = { start: v, end: v };
      }
    }

    return {
      book,
      chapter,
      highlight,
      rawReference: trimmed
    };
  }

  /**
   * Carrega metadados de books.json
   */
  async function loadBooksMetadata() {
    if (BOOKS_META.length > 0) return BOOKS_META;
    try {
      const res = await fetch('./data/bible/naa/books.json');
      if (!res.ok) throw new Error(`Erro ao carregar books.json: ${res.statusText}`);
      BOOKS_META = await res.json();
      buildBookMap();
      populateBookSelect();
      return BOOKS_META;
    } catch (err) {
      console.error('Erro ao carregar metadados dos livros da Bíblia:', err);
      return [];
    }
  }

  /**
   * Preenche o dropdown de livros agrupado por Testamento.
   */
  function populateBookSelect() {
    if (!bookSelect || BOOKS_META.length === 0) return;

    bookSelect.innerHTML = '';

    const vtGroup = document.createElement('optgroup');
    vtGroup.label = 'Antigo Testamento';

    const ntGroup = document.createElement('optgroup');
    ntGroup.label = 'Novo Testamento';

    BOOKS_META.forEach(b => {
      const option = document.createElement('option');
      option.value = b.abbrev;
      option.textContent = b.name;
      if (b.testament === 'VT') {
        vtGroup.appendChild(option);
      } else {
        ntGroup.appendChild(option);
      }
    });

    bookSelect.appendChild(vtGroup);
    bookSelect.appendChild(ntGroup);
  }

  /**
   * Atualiza as opções do seletor de capítulos de acordo com o livro atual.
   */
  function updateChapterSelect(totalChapters, selectedChapter) {
    if (!chapterSelect) return;
    chapterSelect.innerHTML = '';
    for (let c = 1; c <= totalChapters; c++) {
      const opt = document.createElement('option');
      opt.value = c;
      opt.textContent = `Capítulo ${c}`;
      if (c === selectedChapter) opt.selected = true;
      chapterSelect.appendChild(opt);
    }
  }

  /**
   * Busca e cacheia o arquivo JSON do livro solicitado.
   */
  async function fetchBookData(abbrev) {
    const key = abbrev.toLowerCase();
    if (BOOKS_CACHE.has(key)) {
      return BOOKS_CACHE.get(key);
    }

    const res = await fetch(`./data/bible/naa/books/${key}.json`);
    if (!res.ok) throw new Error(`Livro não encontrado: ${key}`);
    const data = await res.json();
    BOOKS_CACHE.set(key, data);
    return data;
  }

  /**
   * Renderiza o capítulo selecionado na interface do drawer.
   */
  async function renderChapter(bookAbbrev, chapterNum, highlight = null, contextLabel = '') {
    if (!versesListEl) return;

    currentBookAbbrev = bookAbbrev.toLowerCase();
    currentChapter = chapterNum;
    currentHighlight = highlight;

    // Estado de carregamento
    if (loadingEl) loadingEl.style.display = 'flex';
    versesListEl.style.display = 'none';

    try {
      const bookData = await fetchBookData(currentBookAbbrev);
      const bookMeta = BOOKS_META.find(b => b.abbrev.toLowerCase() === currentBookAbbrev);
      const totalChapters = bookData.chapters.length;

      // Validação de limites
      if (currentChapter < 1) currentChapter = 1;
      if (currentChapter > totalChapters) currentChapter = totalChapters;

      const verses = bookData.chapters[currentChapter - 1] || [];

      // Atualiza controles de cabeçalho
      if (bookSelect) bookSelect.value = currentBookAbbrev;
      updateChapterSelect(totalChapters, currentChapter);

      if (chapterTitleEl) {
        chapterTitleEl.textContent = `${bookData.name} ${currentChapter}`;
      }

      if (contextTagEl) {
        if (contextLabel) {
          contextTagEl.innerHTML = `<span class="context-pill">Passagem de hoje: <strong>${contextLabel}</strong></span>`;
          contextTagEl.style.display = 'block';
        } else {
          contextTagEl.style.display = 'none';
          contextTagEl.innerHTML = '';
        }
      }

      // Constrói versículos
      versesListEl.innerHTML = '';
      verses.forEach((verseText, idx) => {
        const verseNum = idx + 1;
        const isHighlighted = highlight && verseNum >= highlight.start && verseNum <= highlight.end;

        const verseRow = document.createElement('div');
        verseRow.className = `bible-verse${isHighlighted ? ' highlighted' : ''}`;
        verseRow.id = `verse-${verseNum}`;

        const numSpan = document.createElement('span');
        numSpan.className = 'verse-num';
        numSpan.textContent = verseNum;

        const textSpan = document.createElement('span');
        textSpan.className = 'verse-text';
        textSpan.textContent = verseText;

        verseRow.appendChild(numSpan);
        verseRow.appendChild(textSpan);
        versesListEl.appendChild(verseRow);
      });

      // Atualiza botões de navegação
      updateNavButtons(totalChapters);

      // Oculta loader e exibe versículos
      if (loadingEl) loadingEl.style.display = 'none';
      versesListEl.style.display = 'block';

      // Rola até o versículo em destaque se aplicável
      if (highlight && highlight.start) {
        setTimeout(() => {
          const targetEl = document.getElementById(`verse-${highlight.start}`);
          if (targetEl) {
            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }, 120);
      } else {
        const bodyEl = document.getElementById('bible-drawer-body');
        if (bodyEl) bodyEl.scrollTop = 0;
      }
    } catch (err) {
      console.error('Erro ao renderizar capítulo da Bíblia:', err);
      if (loadingEl) loadingEl.style.display = 'none';
      versesListEl.innerHTML = '<p class="bible-error">Não foi possível carregar este capítulo. Tente novamente.</p>';
      versesListEl.style.display = 'block';
    }
  }

  function updateNavButtons(totalChapters) {
    const currentBookIdx = BOOKS_META.findIndex(b => b.abbrev.toLowerCase() === currentBookAbbrev);

    // Botão anterior
    if (prevChapterBtn) {
      const hasPrev = currentChapter > 1 || currentBookIdx > 0;
      prevChapterBtn.disabled = !hasPrev;
    }

    // Botão próximo
    if (nextChapterBtn) {
      const hasNext = currentChapter < totalChapters || currentBookIdx < BOOKS_META.length - 1;
      nextChapterBtn.disabled = !hasNext;
    }

    // Indicador
    if (chapterIndicatorEl) {
      chapterIndicatorEl.textContent = `${currentChapter} / ${totalChapters}`;
    }
  }

  function goToPreviousChapter() {
    if (currentChapter > 1) {
      renderChapter(currentBookAbbrev, currentChapter - 1);
    } else {
      const currentBookIdx = BOOKS_META.findIndex(b => b.abbrev.toLowerCase() === currentBookAbbrev);
      if (currentBookIdx > 0) {
        const prevBook = BOOKS_META[currentBookIdx - 1];
        renderChapter(prevBook.abbrev, prevBook.chapters_count);
      }
    }
  }

  function goToNextChapter() {
    const bookMeta = BOOKS_META.find(b => b.abbrev.toLowerCase() === currentBookAbbrev);
    const totalChapters = bookMeta ? bookMeta.chapters_count : 1;

    if (currentChapter < totalChapters) {
      renderChapter(currentBookAbbrev, currentChapter + 1);
    } else {
      const currentBookIdx = BOOKS_META.findIndex(b => b.abbrev.toLowerCase() === currentBookAbbrev);
      if (currentBookIdx < BOOKS_META.length - 1) {
        const nextBook = BOOKS_META[currentBookIdx + 1];
        renderChapter(nextBook.abbrev, 1);
      }
    }
  }

  /**
   * Abre o drawer da Bíblia
   */
  async function openBibleDrawer(bookAbbrev = null, chapter = null, highlight = null, contextLabel = '') {
    await loadBooksMetadata();

    if (!drawerEl) return;

    // Se não especificado, abre com o livro atual ou o padrão
    const targetBook = bookAbbrev || currentBookAbbrev || 'gn';
    const targetChapter = chapter || currentChapter || 1;

    document.body.classList.add('bible-drawer-open');
    drawerEl.classList.add('active');
    drawerEl.setAttribute('aria-hidden', 'false');

    await renderChapter(targetBook, targetChapter, highlight, contextLabel);
  }

  /**
   * Fecha o drawer da Bíblia
   */
  function closeBibleDrawer() {
    if (!drawerEl) return;
    drawerEl.classList.remove('active');
    drawerEl.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('bible-drawer-open');
  }

  /**
   * Abre o capítulo correspondente à história ativa.
   */
  function openCurrentStoryChapter() {
    const subtitleEl = document.getElementById('subtitulo');
    const refStr = subtitleEl ? subtitleEl.textContent : '';
    const parsed = parseReference(refStr);

    if (parsed) {
      openBibleDrawer(parsed.book.abbrev, parsed.chapter, parsed.highlight, parsed.rawReference);
    } else {
      // Fallback: abre no primeiro livro
      openBibleDrawer('gn', 1, null, '');
    }
  }

  /**
   * Inicializa os event listeners do leitor bíblico.
   */
  function initEvents() {
    // Fechar drawer
    if (closeBtn) closeBtn.addEventListener('click', closeBibleDrawer);
    if (backdropEl) backdropEl.addEventListener('click', closeBibleDrawer);

    // Tecla ESC fecha
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && drawerEl && drawerEl.classList.contains('active')) {
        closeBibleDrawer();
      }
    });

    // Seletor de Livro
    if (bookSelect) {
      bookSelect.addEventListener('change', e => {
        renderChapter(e.target.value, 1, null, '');
      });
    }

    // Seletor de Capítulo
    if (chapterSelect) {
      chapterSelect.addEventListener('change', e => {
        renderChapter(currentBookAbbrev, parseInt(e.target.value, 10), null, '');
      });
    }

    // Botões de navegação de capítulo
    if (prevChapterBtn) prevChapterBtn.addEventListener('click', goToPreviousChapter);
    if (nextChapterBtn) nextChapterBtn.addEventListener('click', goToNextChapter);

    // Gatilho: Botão "Ler capítulo completo"
    const readBtn = document.getElementById('btn-read-chapter');
    if (readBtn) {
      readBtn.addEventListener('click', openCurrentStoryChapter);
    }

    // Gatilho: Clique direto na referência (subtítulo)
    const subtitleEl = document.getElementById('subtitulo');
    if (subtitleEl) {
      subtitleEl.style.cursor = 'pointer';
      subtitleEl.title = 'Clique para ler o capítulo completo na Bíblia';
      subtitleEl.addEventListener('click', openCurrentStoryChapter);
    }

    // Gatilho: Link de navegação "Bíblia Completa"
    const openBibleNavBtn = document.getElementById('open-bible-nav');
    if (openBibleNavBtn) {
      openBibleNavBtn.addEventListener('click', e => {
        e.preventDefault();
        openBibleDrawer();
      });
    }

    // Suporte a parâmetros na URL (?bible=1 ou ?book=jo&ch=3)
    const params = new URLSearchParams(window.location.search);
    if (params.get('bible') === '1' || params.has('book')) {
      const bookParam = params.get('book') || 'gn';
      const chParam = parseInt(params.get('ch') || '1', 10);
      setTimeout(() => {
        openBibleDrawer(bookParam, chParam);
      }, 500);
    }
  }

  /**
   * Configuração inicial no carregamento da página
   */
  async function initBibleReader() {
    drawerEl = document.getElementById('bible-drawer');
    backdropEl = document.getElementById('bible-drawer-backdrop');
    closeBtn = document.getElementById('bible-drawer-close');
    bookSelect = document.getElementById('bible-book-select');
    chapterSelect = document.getElementById('bible-chapter-select');
    versesListEl = document.getElementById('bible-verses-list');
    loadingEl = document.getElementById('bible-drawer-loading');
    chapterTitleEl = document.getElementById('bible-drawer-title');
    contextTagEl = document.getElementById('bible-drawer-context-tag');
    prevChapterBtn = document.getElementById('bible-prev-chapter');
    nextChapterBtn = document.getElementById('bible-next-chapter');
    chapterIndicatorEl = document.getElementById('bible-chapter-indicator');

    if (!drawerEl) return;

    initEvents();
    // Pré-carrega metadados em segundo plano
    loadBooksMetadata();
  }

  /**
   * Busca os versículos exatos diretamente da fonte do JSON da Bíblia completa (NAA).
   * Retorna o texto formatado: "1. No princípio era o Verbo... 2. Ele estava no princípio..."
   */
  async function fetchPassage(refStr) {
    await loadBooksMetadata();
    const parsed = parseReference(refStr);
    if (!parsed) return null;

    const bookData = await fetchBookData(parsed.book.abbrev);
    if (!bookData || !bookData.chapters) return null;

    const chapterVerses = bookData.chapters[parsed.chapter - 1];
    if (!chapterVerses) return null;

    if (!parsed.highlight) {
      return chapterVerses.map((v, i) => `${i + 1}. ${v}`).join(' ');
    }

    const start = parsed.highlight.start;
    const end = parsed.highlight.end;
    const selected = [];

    for (let v = start; v <= end; v++) {
      if (v >= 1 && v <= chapterVerses.length) {
        selected.push(`${v}. ${chapterVerses[v - 1]}`);
      }
    }

    return selected.join(' ');
  }

  // Exporta para escopo global
  window.BibleReader = {
    init: initBibleReader,
    open: openBibleDrawer,
    close: closeBibleDrawer,
    openCurrentStory: openCurrentStoryChapter,
    parseReference,
    fetchPassage
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBibleReader);
  } else {
    initBibleReader();
  }
})();
