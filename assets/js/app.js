/**
 * assets/js/app.js
 * Interatividade moderna para o Via Scriptura (PHP).
 * Inclui alternador de temas (Escuro / Sépia / Claro), narração em voz (TTS), atalhos de teclado e leitura fluida.
 */

(function () {
  'use strict';

  // ── Utilitários ──
  function stripAccents(str) {
    if (!str) return '';
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
  }

  let toastTimeout = null;
  function showToast(message) {
    const toast = document.getElementById('vs-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('visible');
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      toast.classList.remove('visible');
    }, 2800);
  }

  async function copyToClipboard(text, successMsg = 'Copiado para a área de transferência!') {
    try {
      await navigator.clipboard.writeText(text);
      showToast(successMsg);
    } catch (_) {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
      showToast(successMsg);
    }
  }

  // ── Alternador de Tema (Escuro / Sépia / Claro) ──
  const THEME_KEY = 'vs-theme';
  const THEMES = [
    { id: 'dark', label: 'Escuro' },
    { id: 'sepia', label: 'Sépia' },
    { id: 'light', label: 'Claro' }
  ];

  function getCurrentTheme() {
    return localStorage.getItem(THEME_KEY) || 'dark';
  }

  const THEME_COLORS = {
    dark: '#0d0b09',
    sepia: '#f3ead8',
    light: '#fbf9f5'
  };

  function applyTheme(themeId, notify = false) {
    const valid = THEMES.find(t => t.id === themeId) || THEMES[0];
    document.documentElement.setAttribute('data-theme', valid.id);
    localStorage.setItem(THEME_KEY, valid.id);

    const metaTheme = document.getElementById('meta-theme-color');
    if (metaTheme && THEME_COLORS[valid.id]) {
      metaTheme.setAttribute('content', THEME_COLORS[valid.id]);
    }

    const labelEl = document.getElementById('theme-label-text');
    if (labelEl) {
      labelEl.textContent = valid.label;
    }
    if (notify) {
      showToast(`Tema alterado para ${valid.label}`);
    }
  }

  function initThemeSwitcher() {
    applyTheme(getCurrentTheme(), false);
    const btn = document.getElementById('theme-toggle-btn');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const current = getCurrentTheme();
      const idx = THEMES.findIndex(t => t.id === current);
      const next = THEMES[(idx + 1) % THEMES.length];
      applyTheme(next.id, true);
    });
  }

  // ── Atalho de Teclado Global (/ ou Ctrl+K / Cmd+K para Buscar) ──
  function initKeyboardShortcuts() {
    document.addEventListener('keydown', e => {
      const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
      const isInputFocused = activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select';

      if ((e.key === '/' && !isInputFocused) || ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k')) {
        const searchInput = document.getElementById('global-search-input');
        if (searchInput) {
          e.preventDefault();
          searchInput.focus();
          searchInput.select();
        }
      }
    });
  }

  // ── Controle de Escala de Fonte ──
  const MIN_SCALE = 0.85;
  const MAX_SCALE = 1.45;
  const SCALE_STEP = 0.1;
  const FONT_SCALE_KEY = 'lpmc-font-scale';

  function getFontScale() {
    const saved = localStorage.getItem(FONT_SCALE_KEY);
    const parsed = saved ? parseFloat(saved) : 1;
    return Number.isFinite(parsed) ? parsed : 1;
  }

  function setFontScale(scale) {
    const clamped = Math.round(Math.max(MIN_SCALE, Math.min(MAX_SCALE, scale)) * 100) / 100;
    localStorage.setItem(FONT_SCALE_KEY, String(clamped));
    document.documentElement.style.setProperty('--font-scale', String(clamped));
    const display = document.getElementById('font-size-display');
    if (display) {
      display.textContent = `${Math.round(clamped * 100)}%`;
    }
  }

  function initFontControls() {
    const decreaseBtn = document.getElementById('font-decrease');
    const increaseBtn = document.getElementById('font-increase');

    if (decreaseBtn) {
      decreaseBtn.addEventListener('click', () => setFontScale(getFontScale() - SCALE_STEP));
    }
    if (increaseBtn) {
      increaseBtn.addEventListener('click', () => setFontScale(getFontScale() + SCALE_STEP));
    }
    setFontScale(getFontScale());
  }

  // ── Narração em Voz Alta (Web Speech API TTS) ──
  let isSpeaking = false;

  function stopSpeech() {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
    }
    isSpeaking = false;
    const heroLabel = document.getElementById('tts-btn-label');
    if (heroLabel) heroLabel.textContent = 'Ouvir';
    const chLabel = document.getElementById('tts-chapter-label');
    if (chLabel) chLabel.textContent = 'Ouvir';
  }

  function speakText(text, labelElement) {
    if (!('speechSynthesis' in window)) {
      showToast('Seu navegador não suporta leitura em voz alta.');
      return;
    }

    if (isSpeaking) {
      stopSpeech();
      showToast('Leitura em áudio encerrada.');
      return;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'pt-BR';
    utterance.rate = 0.96;

    utterance.onstart = () => {
      isSpeaking = true;
      if (labelElement) labelElement.textContent = 'Parar';
      showToast('Iniciando leitura em voz alta...');
    };

    utterance.onend = () => {
      stopSpeech();
    };

    utterance.onerror = () => {
      stopSpeech();
    };

    window.speechSynthesis.speak(utterance);
  }

  // ── Ações do Hero (Copiar e Ouvir) ──
  function initHeroActions() {
    const copyBtn = document.getElementById('btn-copy-passage');
    if (copyBtn) {
      copyBtn.addEventListener('click', () => {
        const text = copyBtn.getAttribute('data-copy-text') || '';
        if (text) {
          copyToClipboard(text, 'Passagem copiada!');
        }
      });
    }

    const ttsBtn = document.getElementById('btn-tts-passage');
    if (ttsBtn) {
      ttsBtn.addEventListener('click', () => {
        const text = ttsBtn.getAttribute('data-tts-text') || '';
        const label = document.getElementById('tts-btn-label');
        if (text) {
          speakText(text, label);
        }
      });
    }
  }

  // ── Abas de Modo de Visualização em bible.php ──
  function initBibleViewTabs() {
    const tabs = document.querySelectorAll('[data-switch-view]');
    if (!tabs.length) return;

    function activateTab(viewName, updateUrl = true) {
      tabs.forEach(tab => {
        const isTarget = tab.getAttribute('data-switch-view') === viewName;
        tab.classList.toggle('active', isTarget);
        tab.setAttribute('aria-selected', isTarget ? 'true' : 'false');
      });

      const panes = {
        reader: document.getElementById('bible-view-reader'),
        panorama: document.getElementById('bible-view-panorama'),
        search: document.getElementById('bible-view-search')
      };

      Object.entries(panes).forEach(([key, pane]) => {
        if (pane) {
          pane.classList.toggle('active', key === viewName);
        }
      });

      if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location.href);
        if (viewName === 'reader') {
          url.searchParams.delete('view');
        } else {
          url.searchParams.set('view', viewName);
        }
        window.history.replaceState({}, '', url.toString());
      }
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.getAttribute('data-switch-view');
        if (target) activateTab(target, true);
      });
    });

    // Filtro de livros no Panorama
    const panoramaSearch = document.getElementById('panorama-book-search');
    if (panoramaSearch) {
      panoramaSearch.addEventListener('input', e => {
        const term = stripAccents(e.target.value);
        const sections = document.querySelectorAll('.panorama-category-section');
        sections.forEach(section => {
          const cards = section.querySelectorAll('.panorama-book-card');
          let visibleCount = 0;
          cards.forEach(card => {
            const name = card.getAttribute('data-panorama-book') || '';
            const match = !term || name.includes(term);
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
          });
          section.style.display = visibleCount > 0 ? '' : 'none';
        });
      });
    }
  }

  // ── Leitor Bíblico Visível em Tela (Sidebar + Capítulos + Versículos) ──
  function initOnScreenBibleReader() {
    const versesListEl = document.getElementById('reader-verses-list');
    if (!versesListEl) return;

    const bookSelect = document.getElementById('reader-book-select');
    const chapterSelect = document.getElementById('reader-chapter-select');
    const chapterTitleEl = document.getElementById('reader-chapter-title');
    const breadcrumbEl = document.getElementById('reader-breadcrumb');
    const pillsListEl = document.getElementById('chapter-pills-list');
    const contextBannerEl = document.getElementById('reader-context-banner');
    const relatedStoriesEl = document.getElementById('reader-related-stories');
    const prevBtn = document.getElementById('reader-prev-btn');
    const nextBtn = document.getElementById('reader-next-btn');
    const indicatorEl = document.getElementById('reader-chapter-indicator');
    const sidebarFilterInput = document.getElementById('sidebar-book-filter');
    const testamentTabs = document.querySelectorAll('[data-filter-testament]');
    const modeVersesBtn = document.getElementById('btn-mode-verses');
    const modeProseBtn = document.getElementById('btn-mode-prose');
    const copyChapterBtn = document.getElementById('btn-copy-chapter');
    const ttsChapterBtn = document.getElementById('btn-tts-chapter');
    const copySelectedBtn = document.getElementById('btn-copy-selected-verses');

    let activeTestamentFilter = 'ALL';

    // Filtro da Sidebar de 66 Livros
    function applySidebarFilters() {
      const searchVal = sidebarFilterInput ? stripAccents(sidebarFilterInput.value) : '';
      const groups = document.querySelectorAll('.testament-group');

      groups.forEach(group => {
        const groupTestament = group.getAttribute('data-testament-group') || '';
        const testamentAllowed = activeTestamentFilter === 'ALL' || activeTestamentFilter === groupTestament;

        if (!testamentAllowed) {
          group.style.display = 'none';
          return;
        }

        const items = group.querySelectorAll('.sidebar-book-item');
        let visibleInGroup = 0;

        items.forEach(item => {
          const bName = item.getAttribute('data-book-name') || '';
          const bAbbrev = item.getAttribute('data-book-abbrev') || '';
          const matchesSearch = !searchVal || bName.includes(searchVal) || bAbbrev.includes(searchVal);
          item.style.display = matchesSearch ? '' : 'none';
          if (matchesSearch) visibleInGroup++;
        });

        group.style.display = visibleInGroup > 0 ? '' : 'none';
      });
    }

    if (sidebarFilterInput) {
      sidebarFilterInput.addEventListener('input', applySidebarFilters);
    }

    testamentTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        activeTestamentFilter = tab.getAttribute('data-filter-testament') || 'ALL';
        testamentTabs.forEach(t => t.classList.toggle('active', t === tab));
        applySidebarFilters();
      });
    });

    // Alternância de Modo de Leitura (Versículos em Lista vs Prosa Contínua)
    if (modeVersesBtn && modeProseBtn) {
      modeVersesBtn.addEventListener('click', () => {
        versesListEl.classList.remove('prose-mode');
        modeVersesBtn.classList.add('active');
        modeProseBtn.classList.remove('active');
      });
      modeProseBtn.addEventListener('click', () => {
        versesListEl.classList.add('prose-mode');
        modeProseBtn.classList.add('active');
        modeVersesBtn.classList.remove('active');
      });
    }

    // Seleção, cópia e compartilhamento de versículos ao clicar
    const shareChapterBtn = document.getElementById('btn-share-chapter');
    const shareSelectedBtn = document.getElementById('btn-share-selected-verses');

    function updateSelectedVersesButton() {
      const selected = versesListEl.querySelectorAll('.bible-verse.user-selected');
      const count = selected.length;
      if (copySelectedBtn) {
        if (count > 0) {
          copySelectedBtn.style.display = 'inline-flex';
          copySelectedBtn.textContent = `Copiar (${count})`;
        } else {
          copySelectedBtn.style.display = 'none';
        }
      }
      if (shareSelectedBtn) {
        if (count > 0) {
          shareSelectedBtn.style.display = 'inline-flex';
          shareSelectedBtn.textContent = `Compartilhar trecho (${count})`;
        } else {
          shareSelectedBtn.style.display = 'none';
        }
      }
    }

    versesListEl.addEventListener('click', e => {
      const verseRow = e.target.closest('.bible-verse');
      if (!verseRow) return;
      verseRow.classList.toggle('user-selected');
      updateSelectedVersesButton();
    });

    if (copySelectedBtn) {
      copySelectedBtn.addEventListener('click', () => {
        const selected = Array.from(versesListEl.querySelectorAll('.bible-verse.user-selected'));
        if (!selected.length) return;
        const bookName = versesListEl.getAttribute('data-book-name') || '';
        const chNum = versesListEl.getAttribute('data-chapter') || '';
        const lines = selected.map(el => {
          const num = el.getAttribute('data-verse') || '';
          const txt = el.querySelector('.verse-text')?.textContent || '';
          return `${num}. ${txt}`;
        });
        copyToClipboard(`${bookName} ${chNum} (NAA)\n${lines.join('\n')}`, 'Versículos selecionados copiados!');
      });
    }

    if (shareSelectedBtn) {
      shareSelectedBtn.addEventListener('click', () => {
        const selected = Array.from(versesListEl.querySelectorAll('.bible-verse.user-selected'));
        if (!selected.length) return;
        const bookName = versesListEl.getAttribute('data-book-name') || '';
        const chNum = versesListEl.getAttribute('data-chapter') || '1';
        const bookAbbrev = bookSelect ? bookSelect.value : 'gn';
        const nums = selected.map(el => parseInt(el.getAttribute('data-verse') || '1', 10));
        const minV = Math.min(...nums);
        const maxV = Math.max(...nums);
        const vLabel = minV === maxV ? `${minV}` : `${minV}-${maxV}`;
        const lines = selected.map(el => {
          const num = el.getAttribute('data-verse') || '';
          const txt = el.querySelector('.verse-text')?.textContent || '';
          return `${num}. ${txt}`;
        });
        openShareModal({
          type: 'Trecho da Bíblia',
          title: `${bookName} ${chNum}:${vLabel}`,
          ref: `${bookName} ${chNum}:${vLabel} (NAA)`,
          text: lines.join(' '),
          url: `bible.php?book=${encodeURIComponent(bookAbbrev)}&ch=${encodeURIComponent(chNum)}&hl=${encodeURIComponent(vLabel)}`
        });
      });
    }

    if (copyChapterBtn) {
      copyChapterBtn.addEventListener('click', () => {
        const allVerses = Array.from(versesListEl.querySelectorAll('.bible-verse'));
        const bookName = versesListEl.getAttribute('data-book-name') || '';
        const chNum = versesListEl.getAttribute('data-chapter') || '';
        const lines = allVerses.map(el => {
          const num = el.getAttribute('data-verse') || '';
          const txt = el.querySelector('.verse-text')?.textContent || '';
          return `${num}. ${txt}`;
        });
        copyToClipboard(`${bookName} ${chNum} (NAA)\n\n${lines.join('\n')}`, `${bookName} ${chNum} copiado!`);
      });
    }

    if (shareChapterBtn) {
      shareChapterBtn.addEventListener('click', () => {
        const allVerses = Array.from(versesListEl.querySelectorAll('.bible-verse'));
        const bookName = versesListEl.getAttribute('data-book-name') || '';
        const chNum = versesListEl.getAttribute('data-chapter') || '1';
        const bookAbbrev = bookSelect ? bookSelect.value : 'gn';
        const previewLines = allVerses.slice(0, 5).map(el => {
          const num = el.getAttribute('data-verse') || '';
          const txt = el.querySelector('.verse-text')?.textContent || '';
          return `${num}. ${txt}`;
        });
        openShareModal({
          type: 'Capítulo da Bíblia',
          title: `${bookName} ${chNum}`,
          ref: `${bookName} ${chNum} · ${allVerses.length} versículos (NAA)`,
          text: previewLines.join(' ') + (allVerses.length > 5 ? ' …' : ''),
          url: `bible.php?book=${encodeURIComponent(bookAbbrev)}&ch=${encodeURIComponent(chNum)}`
        });
      });
    }

    if (ttsChapterBtn) {
      ttsChapterBtn.addEventListener('click', () => {
        const allVerses = Array.from(versesListEl.querySelectorAll('.bible-verse'));
        const bookName = versesListEl.getAttribute('data-book-name') || '';
        const chNum = versesListEl.getAttribute('data-chapter') || '';
        const text = `${bookName}, capítulo ${chNum}. ` + allVerses.map(el => el.querySelector('.verse-text')?.textContent || '').join(' ');
        const label = document.getElementById('tts-chapter-label');
        speakText(text, label);
      });
    }

    // Carregamento fluido de capítulo via API PHP
    async function loadChapterAjax(bookAbbrev, chapterNum, pushHistory = true) {
      try {
        stopSpeech();
        versesListEl.classList.add('loading-state');
        const res = await fetch(`api/bible.php?action=chapter&book=${encodeURIComponent(bookAbbrev)}&ch=${encodeURIComponent(chapterNum)}`);
        if (!res.ok) throw new Error('Falha ao buscar capítulo');
        const json = await res.json();
        if (!json.ok || !json.data) throw new Error('Resposta inválida');

        const data = json.data;
        const book = data.book;
        const ch = data.chapter;
        const total = data.totalChapters;

        if (chapterTitleEl) {
          chapterTitleEl.textContent = `${book.name} ${ch}`;
        }
        if (breadcrumbEl) {
          const tName = book.testament === 'VT' ? 'Antigo Testamento' : 'Novo Testamento';
          breadcrumbEl.textContent = `${tName} · ${book.category_label} · Livro ${book.id} de 66`;
        }

        if (bookSelect) bookSelect.value = book.url_abbrev;
        if (chapterSelect) {
          chapterSelect.innerHTML = '';
          for (let c = 1; c <= total; c++) {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = `Capítulo ${c}`;
            if (c === ch) opt.selected = true;
            chapterSelect.appendChild(opt);
          }
        }

        document.querySelectorAll('.sidebar-book-item').forEach(item => {
          const isMatch = item.getAttribute('data-book-abbrev') === book.url_abbrev;
          item.classList.toggle('active', isMatch);
        });

        const pillsLabel = document.querySelector('.chapter-pills-label');
        if (pillsLabel) {
          pillsLabel.innerHTML = `Capítulos de <strong>${book.name}</strong> (${total}):`;
        }
        if (pillsListEl) {
          const isBiblePage = document.body.classList.contains('page-bible');
          pillsListEl.innerHTML = '';
          for (let c = 1; c <= total; c++) {
            const a = document.createElement('a');
            a.href = isBiblePage
              ? `bible.php?book=${encodeURIComponent(book.url_abbrev)}&ch=${c}`
              : `index.php?book=${encodeURIComponent(book.url_abbrev)}&ch=${c}#biblia-em-tela`;
            a.className = `chapter-pill${c === ch ? ' active' : ''}`;
            a.setAttribute('data-ch', String(c));
            a.textContent = String(c);
            pillsListEl.appendChild(a);
          }
        }

        if (contextBannerEl) {
          contextBannerEl.style.display = 'none';
          contextBannerEl.innerHTML = '';
        }

        if (relatedStoriesEl) {
          if (Array.isArray(data.relatedStories) && data.relatedStories.length > 0) {
            relatedStoriesEl.style.display = '';
            const cardsHtml = data.relatedStories.map(rs => {
              const imgHtml = rs.imagem?.url
                ? `<img src="${rs.imagem.url}" alt="" loading="lazy" />`
                : '';
              return `
                <a href="index.php?p=${encodeURIComponent(rs.slug || '')}" class="related-story-chip">
                  ${imgHtml}
                  <div class="related-story-chip-info">
                    <span class="chip-day">Dia ${rs.dia} · ${rs.referencia || ''}</span>
                    <strong>${rs.titulo || ''}</strong>
                  </div>
                </a>
              `;
            }).join('');
            relatedStoriesEl.innerHTML = `
              <div class="related-stories-label">Meditações do Via Scriptura neste capítulo:</div>
              <div class="related-stories-cards">${cardsHtml}</div>
            `;
          } else {
            relatedStoriesEl.style.display = 'none';
            relatedStoriesEl.innerHTML = '';
          }
        }

        versesListEl.setAttribute('data-book-name', book.name);
        versesListEl.setAttribute('data-chapter', String(ch));
        versesListEl.innerHTML = '';
        data.verses.forEach((vText, idx) => {
          const vNum = idx + 1;
          const row = document.createElement('div');
          row.className = 'bible-verse';
          row.id = `verse-${vNum}`;
          row.setAttribute('data-verse', String(vNum));

          const numSpan = document.createElement('span');
          numSpan.className = 'verse-num';
          numSpan.textContent = String(vNum);

          const txtSpan = document.createElement('span');
          txtSpan.className = 'verse-text';
          txtSpan.textContent = vText;

          row.appendChild(numSpan);
          row.appendChild(txtSpan);
          versesListEl.appendChild(row);
        });

        updateSelectedVersesButton();

        if (indicatorEl) {
          indicatorEl.textContent = `${book.name} · Capítulo ${ch} de ${total}`;
        }

        const isBiblePage = document.body.classList.contains('page-bible');
        if (prevBtn && data.prev) {
          prevBtn.href = isBiblePage
            ? `bible.php?book=${encodeURIComponent(data.prev.book.url_abbrev)}&ch=${data.prev.chapter}`
            : `index.php?book=${encodeURIComponent(data.prev.book.url_abbrev)}&ch=${data.prev.chapter}#biblia-em-tela`;
          prevBtn.setAttribute('data-book', data.prev.book.url_abbrev);
          prevBtn.setAttribute('data-ch', String(data.prev.chapter));
          prevBtn.innerHTML = `<span aria-hidden="true">←</span> <span>${data.prev.label}</span>`;
        }
        if (nextBtn && data.next) {
          nextBtn.href = isBiblePage
            ? `bible.php?book=${encodeURIComponent(data.next.book.url_abbrev)}&ch=${data.next.chapter}`
            : `index.php?book=${encodeURIComponent(data.next.book.url_abbrev)}&ch=${data.next.chapter}#biblia-em-tela`;
          nextBtn.setAttribute('data-book', data.next.book.url_abbrev);
          nextBtn.setAttribute('data-ch', String(data.next.chapter));
          nextBtn.innerHTML = `<span>${data.next.label}</span> <span aria-hidden="true">→</span>`;
        }

        const readerTabSpan = document.querySelector('[data-switch-view="reader"] span');
        if (readerTabSpan) {
          readerTabSpan.textContent = `Leitor em Tela (${book.name} ${ch})`;
        }

        if (pushHistory && window.history && window.history.pushState) {
          const url = new URL(window.location.href);
          url.searchParams.set('book', book.url_abbrev);
          url.searchParams.set('ch', String(ch));
          url.searchParams.delete('hl');
          url.searchParams.delete('ref');
          window.history.pushState({ book: book.url_abbrev, ch }, '', url.toString());
        }
      } catch (err) {
        console.error('Erro ao carregar capítulo:', err);
      } finally {
        versesListEl.classList.remove('loading-state');
      }
    }

    if (bookSelect) {
      bookSelect.addEventListener('change', e => {
        loadChapterAjax(e.target.value, 1, true);
      });
    }

    if (chapterSelect) {
      chapterSelect.addEventListener('change', e => {
        const currentBook = bookSelect ? bookSelect.value : 'gn';
        loadChapterAjax(currentBook, parseInt(e.target.value, 10) || 1, true);
      });
    }

    const sidebarList = document.getElementById('sidebar-books-list');
    if (sidebarList) {
      sidebarList.addEventListener('click', e => {
        const link = e.target.closest('.sidebar-book-item');
        if (!link || e.metaKey || e.ctrlKey) return;
        e.preventDefault();
        const bookAbbrev = link.getAttribute('data-book-abbrev') || 'gn';
        loadChapterAjax(bookAbbrev, 1, true);
      });
    }

    if (pillsListEl) {
      pillsListEl.addEventListener('click', e => {
        const pill = e.target.closest('.chapter-pill');
        if (!pill || e.metaKey || e.ctrlKey) return;
        e.preventDefault();
        const ch = parseInt(pill.getAttribute('data-ch') || '1', 10);
        const currentBook = bookSelect ? bookSelect.value : 'gn';
        loadChapterAjax(currentBook, ch, true);
      });
    }

    [prevBtn, nextBtn].forEach(btn => {
      if (!btn) return;
      btn.addEventListener('click', e => {
        if (e.metaKey || e.ctrlKey) return;
        const targetBook = btn.getAttribute('data-book');
        const targetCh = parseInt(btn.getAttribute('data-ch') || '0', 10);
        if (targetBook && targetCh > 0) {
          e.preventDefault();
          loadChapterAjax(targetBook, targetCh, true);
          const panelTop = document.querySelector('.bible-main-panel');
          if (panelTop) {
            panelTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      });
    });

    const firstHighlighted = versesListEl.querySelector('.bible-verse.highlighted');
    if (firstHighlighted && document.body.classList.contains('page-bible')) {
      setTimeout(() => {
        firstHighlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 250);
    }
  }

  // ── Catálogo de Histórias (stories.php) ──
  function initStoriesCatalog() {
    const grid = document.getElementById('stories-grid');
    const searchInput = document.getElementById('search-input');
    const filterButtons = document.querySelectorAll('[data-story-testament]');
    const countIndicator = document.getElementById('stories-count-indicator');
    const emptyMsg = document.getElementById('no-stories-message');

    if (!grid) return;

    let activeTestament = 'ALL';

    function filterCards() {
      const term = searchInput ? stripAccents(searchInput.value) : '';
      const cards = grid.querySelectorAll('.story-card');
      let visible = 0;

      cards.forEach(card => {
        const t = card.getAttribute('data-testament') || '';
        const s = card.getAttribute('data-search') || '';
        const matchTestament = activeTestament === 'ALL' || t === activeTestament;
        const matchSearch = !term || s.includes(term);

        if (matchTestament && matchSearch) {
          card.style.display = '';
          visible++;
        } else {
          card.style.display = 'none';
        }
      });

      if (countIndicator) {
        countIndicator.textContent = `${visible} ${visible === 1 ? 'história' : 'histórias'}`;
      }
      if (emptyMsg) {
        emptyMsg.style.display = visible === 0 ? 'block' : 'none';
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', filterCards);
    }

    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        activeTestament = btn.getAttribute('data-story-testament') || 'ALL';
        filterButtons.forEach(b => b.classList.toggle('active', b === btn));
        filterCards();
      });
    });
  }

  // ── Tela de Separação das Histórias Bíblicas (divisions.php) ──
  function initDivisionsPage() {
    const modeTabs = document.querySelectorAll('[data-div-tab]');
    const filterInput = document.getElementById('divisions-filter-input');
    if (!modeTabs.length) return;

    function switchDivTab(tabName, updateUrl = true) {
      modeTabs.forEach(btn => {
        const active = btn.getAttribute('data-div-tab') === tabName;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
      });

      const panes = {
        eras: document.getElementById('div-pane-eras'),
        divisions: document.getElementById('div-pane-divisions'),
        books: document.getElementById('div-pane-books')
      };

      Object.entries(panes).forEach(([key, pane]) => {
        if (pane) pane.classList.toggle('active', key === tabName);
      });

      if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
      }
    }

    modeTabs.forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-div-tab');
        if (target) switchDivTab(target, true);
      });
    });

    if (filterInput) {
      filterInput.addEventListener('input', e => {
        const term = stripAccents(e.target.value);
        // Filtra cards nos modos Eras e Divisões
        document.querySelectorAll('#div-pane-eras [data-separation-block], #div-pane-divisions [data-separation-block]').forEach(block => {
          const items = block.querySelectorAll('[data-sep-item]');
          let visibleCount = 0;
          items.forEach(item => {
            const data = item.getAttribute('data-sep-item') || '';
            const match = !term || data.includes(term);
            item.style.display = match ? '' : 'none';
            if (match) visibleCount++;
          });
          block.style.display = visibleCount > 0 ? '' : 'none';
        });

        // Filtra cards no modo 66 Livros
        document.querySelectorAll('#div-pane-books .book-separation-card').forEach(card => {
          const data = card.getAttribute('data-sep-item') || '';
          const match = !term || data.includes(term);
          card.style.display = match ? '' : 'none';
        });
      });
    }
  }

  // ── Modal Universal de Compartilhamento (Histórias, Capítulos e Trechos) ──
  let activeSharePayload = {
    type: 'Via Scriptura',
    title: '',
    ref: '',
    text: '',
    url: window.location.href
  };

  function resolveAbsoluteUrl(relativeOrAbsolute) {
    try {
      return new URL(relativeOrAbsolute, window.location.origin + window.location.pathname).toString();
    } catch (_) {
      return window.location.href;
    }
  }

  function openShareModal(payload) {
    const modal = document.getElementById('vs-share-modal');
    if (!modal) return;

    const fullUrl = resolveAbsoluteUrl(payload.url || window.location.href);
    const cleanText = (payload.text || '').trim();
    const shortSnippet = cleanText.length > 340 ? cleanText.slice(0, 340) + '…' : cleanText;

    activeSharePayload = {
      type: payload.type || 'Compartilhar Palavra',
      title: payload.title || 'Via Scriptura',
      ref: payload.ref || '',
      text: shortSnippet,
      url: fullUrl
    };

    const typeEl = document.getElementById('share-modal-type');
    const headingEl = document.getElementById('share-modal-heading');
    const titleEl = document.getElementById('share-preview-title');
    const refEl = document.getElementById('share-preview-ref');
    const textEl = document.getElementById('share-preview-text');

    if (typeEl) typeEl.textContent = activeSharePayload.type;
    if (headingEl) headingEl.textContent = `Compartilhar ${activeSharePayload.type}`;
    if (titleEl) titleEl.textContent = activeSharePayload.title;
    if (refEl) refEl.textContent = activeSharePayload.ref;
    if (textEl) textEl.textContent = activeSharePayload.text;

    const shareMessage = `${activeSharePayload.title}${activeSharePayload.ref ? ' — ' + activeSharePayload.ref : ''}\n\n"${activeSharePayload.text}"\n\nLeia no Via Scriptura: ${activeSharePayload.url}`;

    const waBtn = document.getElementById('share-btn-whatsapp');
    const tgBtn = document.getElementById('share-btn-telegram');
    const twBtn = document.getElementById('share-btn-twitter');
    const fbBtn = document.getElementById('share-btn-facebook');
    const emBtn = document.getElementById('share-btn-email');
    const nativeBtn = document.getElementById('share-btn-native');

    if (waBtn) waBtn.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareMessage)}`;
    if (tgBtn) tgBtn.href = `https://t.me/share/url?url=${encodeURIComponent(activeSharePayload.url)}&text=${encodeURIComponent(activeSharePayload.title + ' (' + activeSharePayload.ref + ')\n\n"' + activeSharePayload.text + '"')}`;
    if (twBtn) {
      const tweetText = `${activeSharePayload.title} (${activeSharePayload.ref}) — "${activeSharePayload.text.slice(0, 140)}…"`;
      twBtn.href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(tweetText)}&url=${encodeURIComponent(activeSharePayload.url)}`;
    }
    if (fbBtn) fbBtn.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(activeSharePayload.url)}`;
    if (emBtn) {
      emBtn.href = `mailto:?subject=${encodeURIComponent(activeSharePayload.title + ' — Via Scriptura')}&body=${encodeURIComponent(shareMessage)}`;
    }
    if (nativeBtn) {
      nativeBtn.style.display = typeof navigator.share === 'function' ? 'inline-flex' : 'none';
    }

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeShareModal() {
    const modal = document.getElementById('vs-share-modal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
  }

  function downloadShareCardPng(payload) {
    const canvas = document.createElement('canvas');
    canvas.width = 1080;
    canvas.height = 1350;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Fundo escuro contemplativo com gradiente dourado
    const grad = ctx.createLinearGradient(0, 0, 1080, 1350);
    grad.addColorStop(0, '#16120e');
    grad.addColorStop(0.5, '#0e0c0a');
    grad.addColorStop(1, '#1c1610');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, 1080, 1350);

    // Moldura dourada sutil
    ctx.strokeStyle = 'rgba(212, 175, 110, 0.45)';
    ctx.lineWidth = 3;
    ctx.strokeRect(56, 56, 968, 1238);

    // Topo: Via Scriptura
    ctx.fillStyle = '#d4af6e';
    ctx.font = '600 26px sans-serif';
    ctx.fillText('VIA SCRIPTURA · BÍBLIA SAGRADA (NAA)', 110, 145);

    // Título
    ctx.fillStyle = '#f5f2eb';
    ctx.font = '600 62px Georgia, serif';
    ctx.fillText((payload.title || '').slice(0, 32), 110, 250);

    // Referência
    ctx.fillStyle = '#d4af6e';
    ctx.font = 'italic 34px Georgia, serif';
    ctx.fillText(payload.ref || '', 110, 315);

    // Linha divisória
    ctx.fillStyle = 'rgba(212, 175, 110, 0.35)';
    ctx.fillRect(110, 355, 220, 2);

    // Texto quebrado em linhas
    ctx.fillStyle = '#f5f2eb';
    ctx.font = '38px Georgia, serif';
    const words = (`"${payload.text || ''}"`).split(/\s+/);
    let line = '';
    let y = 440;
    const maxWidth = 860;
    const lineHeight = 58;

    for (let i = 0; i < words.length; i++) {
      const testLine = line + words[i] + ' ';
      const metrics = ctx.measureText(testLine);
      if (metrics.width > maxWidth && i > 0) {
        ctx.fillText(line.trim(), 110, y);
        line = words[i] + ' ';
        y += lineHeight;
        if (y > 1140) {
          line += '...';
          break;
        }
      } else {
        line = testLine;
      }
    }
    if (line.trim()) {
      ctx.fillText(line.trim(), 110, y);
    }

    // Rodapé do Card
    ctx.fillStyle = 'rgba(212, 175, 110, 0.85)';
    ctx.font = '500 26px sans-serif';
    ctx.fillText('viascriptura.com', 110, 1230);

    const link = document.createElement('a');
    link.download = `via-scriptura-${stripAccents(payload.title || 'palavra').replace(/[^a-z0-9]+/g, '-')}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
    showToast('Card visual baixado em PNG!');
  }

  function initShareModal() {
    // Delegação global para qualquer botão .btn-share-trigger
    document.addEventListener('click', e => {
      const trigger = e.target.closest('.btn-share-trigger');
      if (!trigger) return;
      e.preventDefault();
      openShareModal({
        type: trigger.getAttribute('data-share-type') || 'Conteúdo Bíblico',
        title: trigger.getAttribute('data-share-title') || 'Via Scriptura',
        ref: trigger.getAttribute('data-share-ref') || '',
        text: trigger.getAttribute('data-share-text') || '',
        url: trigger.getAttribute('data-share-url') || window.location.href
      });
    });

    const closeBtn = document.getElementById('vs-share-close');
    const backdrop = document.getElementById('vs-share-backdrop');
    if (closeBtn) closeBtn.addEventListener('click', closeShareModal);
    if (backdrop) backdrop.addEventListener('click', closeShareModal);

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeShareModal();
    });

    const copyFullBtn = document.getElementById('share-btn-copy-full');
    if (copyFullBtn) {
      copyFullBtn.addEventListener('click', () => {
        const msg = `${activeSharePayload.title}${activeSharePayload.ref ? ' — ' + activeSharePayload.ref : ''}\n\n"${activeSharePayload.text}"\n\n${activeSharePayload.url}`;
        copyToClipboard(msg, 'Texto e link copiados para compartilhar!');
      });
    }

    const copyLinkBtn = document.getElementById('share-btn-copy-link');
    if (copyLinkBtn) {
      copyLinkBtn.addEventListener('click', () => {
        copyToClipboard(activeSharePayload.url, 'Link copiado!');
      });
    }

    const nativeBtn = document.getElementById('share-btn-native');
    if (nativeBtn) {
      nativeBtn.addEventListener('click', async () => {
        if (typeof navigator.share === 'function') {
          try {
            await navigator.share({
              title: `${activeSharePayload.title} — Via Scriptura`,
              text: `${activeSharePayload.title} (${activeSharePayload.ref}): "${activeSharePayload.text}"`,
              url: activeSharePayload.url
            });
          } catch (_) {}
        }
      });
    }

    const downloadCardBtn = document.getElementById('share-btn-download-card');
    if (downloadCardBtn) {
      downloadCardBtn.addEventListener('click', () => {
        downloadShareCardPng(activeSharePayload);
      });
    }
  }

  // ── Progressive Web App (PWA): Service Worker, Instalação e Modo Offline ──
  function initPWA() {
    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwa-install-btn');
    const pwaModal = document.getElementById('vs-pwa-modal');
    const pwaCloseBtn = document.getElementById('vs-pwa-close');
    const pwaBackdrop = document.getElementById('vs-pwa-backdrop');
    const nativeInstallBox = document.getElementById('pwa-native-install-box');
    const confirmInstallBtn = document.getElementById('pwa-confirm-install-btn');
    const cacheCoreBtn = document.getElementById('pwa-cache-core-btn');
    const connectionPill = document.getElementById('pwa-connection-pill');

    const isStandalone =
      (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
      window.navigator.standalone === true;

    // Exibe o botão "Instalar App" na navbar caso ainda não esteja em modo instalado
    if (installBtn && !isStandalone) {
      installBtn.style.display = 'inline-flex';
    }

    function openPwaModal() {
      if (!pwaModal) return;
      if (nativeInstallBox) {
        nativeInstallBox.style.display = deferredInstallPrompt ? 'block' : 'none';
      }
      pwaModal.classList.add('active');
      pwaModal.setAttribute('aria-hidden', 'false');
    }

    function closePwaModal() {
      if (!pwaModal) return;
      pwaModal.classList.remove('active');
      pwaModal.setAttribute('aria-hidden', 'true');
    }

    if (pwaCloseBtn) pwaCloseBtn.addEventListener('click', closePwaModal);
    if (pwaBackdrop) pwaBackdrop.addEventListener('click', closePwaModal);

    // Captura evento nativo de instalação (Chrome / Edge / Android / Desktop)
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredInstallPrompt = e;
      if (installBtn && !isStandalone) {
        installBtn.style.display = 'inline-flex';
      }
      if (nativeInstallBox) {
        nativeInstallBox.style.display = 'block';
      }
    });

    async function triggerNativeInstall() {
      if (!deferredInstallPrompt) {
        openPwaModal();
        return;
      }
      deferredInstallPrompt.prompt();
      try {
        const choice = await deferredInstallPrompt.userChoice;
        if (choice && choice.outcome === 'accepted') {
          showToast('Via Scriptura instalado com sucesso!');
          if (installBtn) installBtn.style.display = 'none';
          closePwaModal();
        }
      } catch (_) {}
      deferredInstallPrompt = null;
      if (nativeInstallBox) nativeInstallBox.style.display = 'none';
    }

    if (installBtn) {
      installBtn.addEventListener('click', () => {
        if (deferredInstallPrompt) {
          triggerNativeInstall();
        } else {
          openPwaModal();
        }
      });
    }

    if (confirmInstallBtn) {
      confirmInstallBtn.addEventListener('click', triggerNativeInstall);
    }

    window.addEventListener('appinstalled', () => {
      deferredInstallPrompt = null;
      if (installBtn) installBtn.style.display = 'none';
      closePwaModal();
      showToast('Aplicativo Via Scriptura instalado!');
    });

    // Botão para salvar explicitamente páginas principais e capítulo atual no cache offline
    if (cacheCoreBtn) {
      cacheCoreBtn.addEventListener('click', async () => {
        const urlsToSave = [
          '/index.php',
          '/bible.php',
          '/stories.php',
          '/divisions.php',
          '/about.php',
          window.location.pathname + window.location.search
        ];

        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
          cacheCoreBtn.disabled = true;
          cacheCoreBtn.querySelector('span').textContent = 'Salvando páginas para leitura offline...';
          navigator.serviceWorker.controller.postMessage({
            type: 'CACHE_URLS',
            urls: urlsToSave
          });
        } else {
          showToast('Cache offline já está ativo neste navegador!');
        }
      });
    }

    // Monitoramento de status Online / Offline
    function updateOnlineStatus(notify = false) {
      const online = navigator.onLine;
      document.body.classList.toggle('is-offline', !online);
      if (connectionPill) {
        connectionPill.textContent = online
          ? 'PWA · Pronto para Uso Offline'
          : 'Modo Offline Ativo (Cache Local)';
        connectionPill.classList.toggle('offline-active', !online);
      }
      if (notify) {
        showToast(
          online
            ? 'Conexão restabelecida — você está online.'
            : 'Sem conexão — Modo Offline ativo com leituras salvas.'
        );
      }
    }

    window.addEventListener('online', () => updateOnlineStatus(true));
    window.addEventListener('offline', () => updateOnlineStatus(true));
    updateOnlineStatus(false);

    // Registro do Service Worker (/sw.js)
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker
          .register('/sw.js', { scope: '/' })
          .then((registration) => {
            registration.addEventListener('updatefound', () => {
              const newWorker = registration.installing;
              if (!newWorker) return;
              newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  newWorker.postMessage({ type: 'SKIP_WAITING' });
                }
              });
            });
          })
          .catch(() => {
            // Silencioso em ambientes sem suporte a SW
          });
      });

      navigator.serviceWorker.addEventListener('message', (event) => {
        const data = event.data || {};
        if (data.type === 'CACHE_URLS_DONE') {
          if (cacheCoreBtn) {
            cacheCoreBtn.disabled = false;
            cacheCoreBtn.querySelector('span').textContent = 'Páginas Salvas para Leitura Offline ✓';
          }
          showToast(`${data.savedCount || 5} páginas salvas para leitura offline!`);
        }
      });
    }
  }

  // ── Inicialização Geral ──
  document.addEventListener('DOMContentLoaded', () => {
    initThemeSwitcher();
    initKeyboardShortcuts();
    initFontControls();
    initHeroActions();
    initBibleViewTabs();
    initOnScreenBibleReader();
    initStoriesCatalog();
    initDivisionsPage();
    initShareModal();
    initPWA();
  });
})();
