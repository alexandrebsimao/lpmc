const https = require('https');

const DATA_URL = 'https://raw.githubusercontent.com/alexandrebsimao/lpmc/refs/heads/main/data/data.json';
const MAX_SPEAK_CHARS = 7000;
const CTA_FINAL = 'Acesse: viascriptura.com';

let historiasCache = null;

function normalizeText(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

function tokenize(value) {
  return normalizeText(value)
    .split(/[^a-z0-9]+/g)
    .filter(Boolean);
}

function jaccardSimilarity(aTokens, bTokens) {
  const a = new Set(aTokens);
  const b = new Set(bTokens);
  if (a.size === 0 || b.size === 0) return 0;

  let intersection = 0;
  for (const token of a) {
    if (b.has(token)) intersection += 1;
  }
  const union = new Set([...a, ...b]).size;
  return union === 0 ? 0 : intersection / union;
}

function plainResponse(text, reprompt = null, shouldEndSession = false) {
  const response = {
    version: '1.0',
    response: {
      outputSpeech: {
        type: 'PlainText',
        text
      },
      shouldEndSession
    }
  };

  if (reprompt) {
    response.response.reprompt = {
      outputSpeech: {
        type: 'PlainText',
        text: reprompt
      }
    };
    response.response.shouldEndSession = false;
  }

  return response;
}

function fetchJson(url) {
  return new Promise((resolve, reject) => {
    const request = https.get(url, (response) => {
      if (response.statusCode >= 300 && response.statusCode < 400 && response.headers.location) {
        response.resume();
        resolve(fetchJson(response.headers.location));
        return;
      }

      if (response.statusCode !== 200) {
        reject(new Error(`Falha ao carregar JSON. Status: ${response.statusCode}`));
        response.resume();
        return;
      }

      let body = '';
      response.setEncoding('utf8');
      response.on('data', (chunk) => { body += chunk; });
      response.on('end', () => {
        try {
          resolve(JSON.parse(body));
        } catch (error) {
          reject(new Error(`JSON inválido: ${error.message}`));
        }
      });
    });

    request.setTimeout(8000, () => {
      request.destroy(new Error('Timeout ao carregar JSON remoto.'));
    });
    request.on('error', reject);
  });
}

async function carregarHistorias() {
  if (historiasCache) return historiasCache;

  try {
    const parsed = await fetchJson(DATA_URL);
    historiasCache = Array.isArray(parsed) ? parsed : [];
    return historiasCache;
  } catch (error) {
    console.error('Erro ao ler data.json remoto:', error);
    historiasCache = [];
    return historiasCache;
  }
}

function limitarTextoParaAlexa(texto) {
  const limpo = String(texto || '').replace(/\s+/g, ' ').trim();
  if (limpo.length <= MAX_SPEAK_CHARS) return limpo;
  return `${limpo.slice(0, MAX_SPEAK_CHARS - 20)}. Texto encurtado.`;
}

function removerNumeracaoVersiculos(texto) {
  return String(texto || '')
    .replace(/\b\d+\.\s*/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

async function obterLeituraDoDia() {
  const historias = await carregarHistorias();
  if (historias.length === 0) return null;

  const now = new Date();
  const start = new Date(now.getFullYear(), 0, 0);
  const diff = now - start;
  const oneDay = 1000 * 60 * 60 * 24;
  const dayOfYear = Math.floor(diff / oneDay);

  const leitura = historias.find((item) => Number(item.dia) === dayOfYear);
  if (leitura) return leitura;

  const index = (dayOfYear - 1) % historias.length;
  return historias[index];
}

async function buscarHistoria(consulta) {
  const historias = await carregarHistorias();
  const q = normalizeText(consulta);
  const qTokens = tokenize(consulta);
  if (!q) return null;

  const exact = historias.find((item) => {
    const titulo = normalizeText(item.titulo);
    const referencia = normalizeText(item.referencia);
    const slug = normalizeText(item.slug);
    return titulo === q || referencia === q || slug === q;
  });
  if (exact) return exact;

  const includesMatch = historias.find((item) => {
    const titulo = normalizeText(item.titulo);
    const referencia = normalizeText(item.referencia);
    return titulo.includes(q) || referencia.includes(q) || q.includes(titulo);
  });
  if (includesMatch) return includesMatch;

  let best = null;
  let bestScore = 0;

  for (const item of historias) {
    const tituloTokens = tokenize(item.titulo);
    const referenciaTokens = tokenize(item.referencia);
    const textoTokens = tokenize(item.texto).slice(0, 120);

    const titleScore = jaccardSimilarity(qTokens, tituloTokens);
    const refScore = jaccardSimilarity(qTokens, referenciaTokens);
    const textScore = jaccardSimilarity(qTokens, textoTokens);

    // Peso maior para título, depois referência, depois texto.
    const score = (titleScore * 0.65) + (refScore * 0.25) + (textScore * 0.10);

    if (score > bestScore) {
      bestScore = score;
      best = item;
    }
  }

  // Limiar para evitar respostas erradas quando a consulta for muito vaga.
  if (best && bestScore >= 0.18) return best;
  return null;
}

function getSessionAttributes(event) {
  return event.session?.attributes || {};
}

function withSession(response, sessionAttributes) {
  return {
    ...response,
    sessionAttributes
  };
}

exports.handler = async (event) => {
  try {
    const requestType = event?.request?.type;
    const intentName = event?.request?.intent?.name;
    const sessionAttributes = getSessionAttributes(event);

    if (requestType === 'LaunchRequest') {
      sessionAttributes.awaitingChoice = true;
      return withSession(
        plainResponse(
          'Bem-vindo ao Via Scriptura. Você quer a leitura do dia ou ouvir uma história da Bíblia?',
          'Diga: leitura do dia, ou diga o nome de uma história da Bíblia.'
        ),
        sessionAttributes
      );
    }

    if (requestType === 'IntentRequest') {
      if (intentName === 'AMAZON.YesIntent') {
        if (!sessionAttributes.awaitingChoice) {
          return withSession(
            plainResponse(
              'Você pode pedir a leitura do dia ou uma história da Bíblia.',
              'Diga: leitura do dia, ou diga o nome de uma história.'
            ),
            sessionAttributes
          );
        }

        sessionAttributes.awaitingChoice = false;
        const leitura = await obterLeituraDoDia();
        if (!leitura) {
          return withSession(plainResponse('Não consegui carregar as leituras no momento.', null, true), sessionAttributes);
        }

        const textoSemNumeracao = removerNumeracaoVersiculos(leitura.texto);
        const speakOutput = limitarTextoParaAlexa(`A leitura do dia é: ${leitura.titulo}. ${leitura.referencia}. ${textoSemNumeracao}. ${CTA_FINAL}`);
        return withSession(plainResponse(speakOutput, null, true), sessionAttributes);
      }

      if (intentName === 'AMAZON.NoIntent') {
        if (sessionAttributes.awaitingChoice) {
          sessionAttributes.awaitingChoice = false;
          return withSession(
            plainResponse(
              'Tudo bem. Qual história da Bíblia você quer ouvir?',
              'Diga o título da história, por exemplo: A criação do mundo.'
            ),
            sessionAttributes
          );
        }
        return withSession(plainResponse('Tudo bem. Quando quiser, peça a leitura do dia ou uma história da Bíblia.', null, true), sessionAttributes);
      }

      if (intentName === 'DailyReadingIntent') {
        sessionAttributes.awaitingChoice = false;
        const leitura = await obterLeituraDoDia();
        if (!leitura) {
          return withSession(plainResponse('Não consegui carregar as leituras no momento.', null, true), sessionAttributes);
        }

        const textoSemNumeracao = removerNumeracaoVersiculos(leitura.texto);
        const speakOutput = limitarTextoParaAlexa(`A leitura do dia é: ${leitura.titulo}. ${leitura.referencia}. ${textoSemNumeracao}. ${CTA_FINAL}`);
        return withSession(plainResponse(speakOutput, null, true), sessionAttributes);
      }

      if (intentName === 'BibleStoryIntent') {
        sessionAttributes.awaitingChoice = false;
        const slotValue = event?.request?.intent?.slots?.storyTitle?.value;

        if (!slotValue) {
          return withSession(
            plainResponse(
              'Qual história você quer ouvir? Diga, por exemplo: A criação do mundo.',
              'Diga o nome da história que você quer ouvir.'
            ),
            sessionAttributes
          );
        }

        const historia = await buscarHistoria(slotValue);
        if (!historia) {
          return withSession(
            plainResponse(
              `Não encontrei a história ${slotValue} no JSON do projeto. Tente outro título.`,
              'Diga o nome de outra história da Bíblia.'
            ),
            sessionAttributes
          );
        }

        const textoSemNumeracao = removerNumeracaoVersiculos(historia.texto);
        const speakOutput = limitarTextoParaAlexa(`${historia.titulo}. ${historia.referencia}. ${textoSemNumeracao}. ${CTA_FINAL}`);
        return withSession(plainResponse(speakOutput, null, true), sessionAttributes);
      }

      if (intentName === 'AMAZON.HelpIntent') {
        return withSession(
          plainResponse(
            'Você pode dizer leitura do dia, ou pedir uma história da Bíblia pelo título.',
            'Você pode dizer leitura do dia, ou pedir uma história da Bíblia pelo título.'
          ),
          sessionAttributes
        );
      }

      if (intentName === 'AMAZON.CancelIntent' || intentName === 'AMAZON.StopIntent') {
        return withSession(plainResponse('Até logo.', null, true), sessionAttributes);
      }

      if (intentName === 'AMAZON.FallbackIntent') {
        return withSession(
          plainResponse(
            'Não entendi. Diga leitura do dia, ou diga conte a história seguido do título.',
            'Exemplo: leitura do dia. Ou: conte a história A criação do mundo.'
          ),
          sessionAttributes
        );
      }
    }

    if (requestType === 'SessionEndedRequest') {
      return withSession({ version: '1.0', response: { shouldEndSession: true } }, sessionAttributes);
    }

    return withSession(
      plainResponse(
        'Não entendi sua solicitação. Você pode pedir leitura do dia ou uma história da Bíblia.',
        'Diga leitura do dia, ou diga o nome de uma história da Bíblia.'
      ),
      sessionAttributes
    );
  } catch (error) {
    console.error('Erro geral da skill:', error);
    return plainResponse(
      'Desculpe, ocorreu um erro ao processar sua solicitação.',
      'Você pode pedir a leitura do dia ou uma história da Bíblia.'
    );
  }
};
