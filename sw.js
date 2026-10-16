/**
 * Via Scriptura — Service Worker (PWA)
 * Suporte a instalação nativa, leitura offline de capítulos e histórias bíblicas,
 * e estratégia híbrida (Network-First para páginas PHP + Stale-While-Revalidate para ativos/dados).
 */

const CACHE_VERSION = 'v1.0.2';
const STATIC_CACHE = `vs-static-${CACHE_VERSION}`;
const PAGES_CACHE = `vs-pages-${CACHE_VERSION}`;
const DATA_CACHE = `vs-data-${CACHE_VERSION}`;

const APP_SHELL_ASSETS = [
  '/offline.php',
  '/manifest.webmanifest',
  '/assets/css/style.css',
  '/assets/js/app.js',
  '/assets/js/consent.js',
  '/assets/icons/icon.svg',
  '/assets/icons/logo.svg',
  '/assets/icons/icon-maskable.svg',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  '/assets/icons/icon-maskable-512.png',
  '/assets/icons/apple-touch-icon.png'
];

const CORE_PAGES = [
  '/',
  '/index.php',
  '/bible.php',
  '/stories.php',
  '/divisions.php',
  '/about.php',
  '/privacy.php'
];

// Instalação: pré-armazena o App Shell e páginas principais
self.addEventListener('install', (event) => {
  event.waitUntil(
    (async () => {
      const staticCache = await caches.open(STATIC_CACHE);
      await staticCache.addAll(APP_SHELL_ASSETS);

      const pagesCache = await caches.open(PAGES_CACHE);
      await Promise.allSettled(
        CORE_PAGES.map(async (url) => {
          try {
            const response = await fetch(url, { credentials: 'same-origin' });
            if (response && response.ok) {
              await pagesCache.put(url, response.clone());
            }
          } catch (_) {
            // Ignora falha individual no pré-cache inicial
          }
        })
      );

      await self.skipWaiting();
    })()
  );
});

// Ativação: remove caches antigos e assume controle imediato das abas
self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const validCaches = new Set([STATIC_CACHE, PAGES_CACHE, DATA_CACHE]);
      const keys = await caches.keys();
      await Promise.all(
        keys.map((key) => {
          if (key.startsWith('vs-') && !validCaches.has(key)) {
            return caches.delete(key);
          }
          return Promise.resolve();
        })
      );
      await self.clients.claim();
    })()
  );
});

// Interceptação de requisições
self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  // Ignora scripts de telemetria/analytics externos
  if (
    url.hostname.includes('googletagmanager.com') ||
    url.hostname.includes('google-analytics.com')
  ) {
    return;
  }

  // 1. Fontes externas (Google Fonts): Cache-First com atualização em background
  if (
    url.hostname.includes('fonts.googleapis.com') ||
    url.hostname.includes('fonts.gstatic.com')
  ) {
    event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
    return;
  }

  // Apenas recursos da mesma origem a partir daqui
  if (url.origin !== self.location.origin) {
    return;
  }

  // 2. API da Bíblia e JSONs de livros/histórias: Stale-While-Revalidate em DATA_CACHE
  if (
    url.pathname.startsWith('/api/') ||
    url.pathname.startsWith('/data/') ||
    url.pathname.endsWith('.json')
  ) {
    event.respondWith(staleWhileRevalidate(request, DATA_CACHE));
    return;
  }

  // 3. Ativos estáticos (CSS, JS, ícones, imagens, manifest): Stale-While-Revalidate em STATIC_CACHE
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.ico') ||
    url.pathname.endsWith('.webmanifest')
  ) {
    event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
    return;
  }

  // 4. Navegação de páginas HTML / PHP: Network-First com fallback para cache e offline.php
  const acceptHeader = request.headers.get('Accept') || '';
  if (request.mode === 'navigate' || acceptHeader.includes('text/html')) {
    event.respondWith(networkFirstPage(request));
    return;
  }

  // Padrão para demais requisições locais
  event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
});

/**
 * Estratégia Network-First para páginas PHP:
 * Tenta buscar da rede e atualiza o cache de páginas; se estiver offline,
 * devolve a página/capítulo salvo no cache ou a página offline.php.
 */
async function networkFirstPage(request) {
  const pagesCache = await caches.open(PAGES_CACHE);
  const url = new URL(request.url);

  // Remove parâmetro ?source=pwa para unificar chave de cache da home
  const normalizedUrl = new URL(request.url);
  if (normalizedUrl.searchParams.get('source') === 'pwa') {
    normalizedUrl.searchParams.delete('source');
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.ok) {
      pagesCache.put(request, networkResponse.clone());
      if (normalizedUrl.href !== request.url) {
        pagesCache.put(normalizedUrl.href, networkResponse.clone());
      }
    }
    return networkResponse;
  } catch (_) {
    // Sem rede: tenta correspondência exata (com query string, ex: ?book=gn&chapter=1)
    const cachedExact =
      (await pagesCache.match(request)) ||
      (await pagesCache.match(normalizedUrl.href));
    if (cachedExact) {
      return cachedExact;
    }

    // Se for a raiz /, tenta /index.php
    if (url.pathname === '/' || url.pathname === '/index.php') {
      const cachedHome =
        (await pagesCache.match('/index.php')) ||
        (await pagesCache.match('/'));
      if (cachedHome) {
        return cachedHome;
      }
    }

    // Fallback final: página dedicada Modo Offline
    const staticCache = await caches.open(STATIC_CACHE);
    const offlineFallback = await staticCache.match('/offline.php');
    if (offlineFallback) {
      return offlineFallback;
    }

    return new Response(
      '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Offline — Via Scriptura</title></head><body style="background:#0d0b09;color:#f3ede2;font-family:sans-serif;padding:2rem;text-align:center;"><h1>Modo Offline</h1><p>Você está sem conexão no momento.</p><a href="/index.php" style="color:#d8b26e;">Voltar ao Início</a></body></html>',
      { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}

/**
 * Estratégia Stale-While-Revalidate para ativos e dados bíblicos:
 * Retorna imediatamente do cache se existir e atualiza em segundo plano.
 */
async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);

  const networkPromise = fetch(request)
    .then((networkResponse) => {
      if (networkResponse && (networkResponse.ok || networkResponse.type === 'opaque')) {
        cache.put(request, networkResponse.clone());
      }
      return networkResponse;
    })
    .catch(() => null);

  if (cachedResponse) {
    return cachedResponse;
  }

  const networkResponse = await networkPromise;
  if (networkResponse) {
    return networkResponse;
  }

  return new Response('', { status: 504, statusText: 'Offline' });
}

// Comunicação com a interface (atualização imediata e salvamento explícito de páginas para leitura offline)
self.addEventListener('message', (event) => {
  const data = event.data || {};

  if (data.type === 'SKIP_WAITING') {
    self.skipWaiting();
    return;
  }

  if (data.type === 'CACHE_URLS' && Array.isArray(data.urls)) {
    event.waitUntil(
      (async () => {
        const pagesCache = await caches.open(PAGES_CACHE);
        let savedCount = 0;
        for (const targetUrl of data.urls) {
          try {
            const response = await fetch(targetUrl, { credentials: 'same-origin' });
            if (response && response.ok) {
              await pagesCache.put(targetUrl, response.clone());
              savedCount++;
            }
          } catch (_) {
            // Ignora erro de rede individual
          }
        }
        if (event.source) {
          event.source.postMessage({
            type: 'CACHE_URLS_DONE',
            savedCount
          });
        }
      })()
    );
  }
});
