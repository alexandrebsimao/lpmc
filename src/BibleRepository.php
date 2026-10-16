<?php
declare(strict_types=1);

namespace ViaScriptura;

class BibleRepository
{
    private string $dataDir;
    private array $booksMeta = [];
    private array $bookNameMap = [];
    private array $booksCache = [];
    private ?array $storiesCache = null;
    private ?array $canonicalStoriesCache = null;

    public function __construct(?string $dataDir = null)
    {
        $this->dataDir = $dataDir ?? dirname(__DIR__) . '/data';
        $this->loadBooksMetadata();
    }

    /**
     * Normaliza texto removendo acentos e convertendo para minúsculas.
     */
    public static function stripAccents(string $str): string
    {
        $str = trim($str);
        if ($str === '') {
            return '';
        }

        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'Á' => 'a', 'À' => 'a', 'Ã' => 'a', 'Â' => 'a', 'Ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ó' => 'o', 'Ò' => 'o', 'Õ' => 'o', 'Ô' => 'o', 'Ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'ç' => 'c', 'Ç' => 'c', 'ñ' => 'n', 'Ñ' => 'n',
        ];

        $normalized = strtr($str, $map);
        // Remove combining diacritical marks (caso esteja em NFD)
        $normalized = preg_replace('/[\x{0300}-\x{036f}]/u', '', $normalized) ?? $normalized;
        return mb_strtolower(trim($normalized), 'UTF-8');
    }

    /**
     * Retorna a categoria literária e grupo de um livro pelo seu ID (1 a 66).
     */
    public static function getBookCategory(int $bookId): array
    {
        if ($bookId >= 1 && $bookId <= 5) {
            return ['key' => 'pentateuco', 'label' => 'Pentateuco (Lei)', 'short' => 'Pentateuco'];
        }
        if ($bookId >= 6 && $bookId <= 17) {
            return ['key' => 'historicos_vt', 'label' => 'Livros Históricos', 'short' => 'Históricos'];
        }
        if ($bookId >= 18 && $bookId <= 22) {
            return ['key' => 'poeticos', 'label' => 'Poéticos e Sabedoria', 'short' => 'Poéticos'];
        }
        if ($bookId >= 23 && $bookId <= 27) {
            return ['key' => 'profetas_maiores', 'label' => 'Profetas Maiores', 'short' => 'Prof. Maiores'];
        }
        if ($bookId >= 28 && $bookId <= 39) {
            return ['key' => 'profetas_menores', 'label' => 'Profetas Menores', 'short' => 'Prof. Menores'];
        }
        if ($bookId >= 40 && $bookId <= 43) {
            return ['key' => 'evangelhos', 'label' => 'Evangelhos', 'short' => 'Evangelhos'];
        }
        if ($bookId === 44) {
            return ['key' => 'historico_nt', 'label' => 'Histórico (Igreja Primitiva)', 'short' => 'Histórico'];
        }
        if ($bookId >= 45 && $bookId <= 57) {
            return ['key' => 'epistolas_paulinas', 'label' => 'Epístolas Paulinas', 'short' => 'Ep. Paulinas'];
        }
        if ($bookId >= 58 && $bookId <= 65) {
            return ['key' => 'epistolas_gerais', 'label' => 'Epístolas Gerais', 'short' => 'Ep. Gerais'];
        }
        return ['key' => 'profetico_nt', 'label' => 'Profético (Revelação)', 'short' => 'Profético'];
    }

    /**
     * Carrega metadados de todos os 66 livros da Bíblia.
     */
    private function loadBooksMetadata(): void
    {
        if (!empty($this->booksMeta)) {
            return;
        }

        $booksFile = $this->dataDir . '/bible/naa/books.json';
        if (!is_file($booksFile)) {
            return;
        }

        $raw = file_get_contents($booksFile);
        $decoded = $raw ? json_decode($raw, true) : [];
        if (!is_array($decoded)) {
            return;
        }

        foreach ($decoded as $book) {
            $id = (int)($book['id'] ?? 0);
            $cat = self::getBookCategory($id);
            $book['category_key'] = $cat['key'];
            $book['category_label'] = $cat['label'];
            $book['category_short'] = $cat['short'];
            // Slug seguro para URLs (ex: jó -> job ou jó)
            $book['url_abbrev'] = ($book['abbrev'] === 'jó') ? 'job' : $book['abbrev'];
            $this->booksMeta[] = $book;
        }

        $this->buildBookMap();
    }

    private function buildBookMap(): void
    {
        $this->bookNameMap = [];

        foreach ($this->booksMeta as $book) {
            $norm = self::stripAccents($book['name']);
            $this->bookNameMap[$norm] = $book;
            $this->bookNameMap[mb_strtolower($book['abbrev'], 'UTF-8')] = $book;
            $this->bookNameMap[$book['url_abbrev']] = $book;

            if ($norm === 'salmos') {
                $this->bookNameMap['salmo'] = $book;
            }
            if ($norm === 'canticos') {
                $this->bookNameMap['cantares'] = $book;
                $this->bookNameMap['cantico dos canticos'] = $book;
                $this->bookNameMap['canticos dos canticos'] = $book;
            }
            if ($norm === 'lamentacoes de jeremias') {
                $this->bookNameMap['lamentacoes'] = $book;
            }
            if ($norm === 'atos') {
                $this->bookNameMap['atos dos apostolos'] = $book;
            }
        }

        // Desambiguação explícita entre João (jo) e Jó (jó / job)
        foreach ($this->booksMeta as $book) {
            if ($book['id'] === 43) { // João
                $this->bookNameMap['joao'] = $book;
                $this->bookNameMap['joão'] = $book;
                $this->bookNameMap['jo'] = $book;
            } elseif ($book['id'] === 18) { // Jó
                $this->bookNameMap['jó'] = $book;
                $this->bookNameMap['jo_vt'] = $book;
                $this->bookNameMap['job'] = $book;
            }
        }
    }

    public function getBooks(): array
    {
        return $this->booksMeta;
    }

    public function getBooksByTestament(): array
    {
        $vt = [];
        $nt = [];
        foreach ($this->booksMeta as $book) {
            if (($book['testament'] ?? '') === 'VT') {
                $vt[] = $book;
            } else {
                $nt[] = $book;
            }
        }
        return ['VT' => $vt, 'NT' => $nt];
    }

    public function getBooksGroupedByCategory(): array
    {
        $groups = [];
        foreach ($this->booksMeta as $book) {
            $key = $book['category_key'];
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'label' => $book['category_label'],
                    'short' => $book['category_short'],
                    'testament' => $book['testament'],
                    'books' => [],
                ];
            }
            $groups[$key]['books'][] = $book;
        }
        return array_values($groups);
    }

    public function findBook(string $identifier): ?array
    {
        $clean = trim($identifier);
        if ($clean === '') {
            return null;
        }

        $lower = mb_strtolower($clean, 'UTF-8');
        if (isset($this->bookNameMap[$lower])) {
            return $this->bookNameMap[$lower];
        }

        // Caso especial para "Jó" com acento no nome completo vs "João"
        if ($lower === 'jó' || $lower === 'jó') {
            return $this->bookNameMap['job'] ?? null;
        }

        $stripped = self::stripAccents($clean);
        if ($stripped === 'jo' && ($clean === 'Jó' || str_contains($clean, 'ó') || str_contains($clean, "o\xCC\x81"))) {
            return $this->bookNameMap['job'] ?? null;
        }

        return $this->bookNameMap[$stripped] ?? null;
    }

    /**
     * Carrega o arquivo JSON de um livro específico (com suporte a NFC/NFD para jó.json).
     */
    public function getBookData(string $abbrevOrName): ?array
    {
        $bookMeta = $this->findBook($abbrevOrName);
        if (!$bookMeta) {
            return null;
        }

        $abbrev = $bookMeta['abbrev'];
        if (isset($this->booksCache[$abbrev])) {
            return $this->booksCache[$abbrev];
        }

        $booksDir = $this->dataDir . '/bible/naa/books';
        $candidates = [
            $booksDir . '/' . $abbrev . '.json',
        ];

        if ($bookMeta['id'] === 18) {
            // Jó em NFC e NFD
            $candidates[] = $booksDir . "/j\xC3\xB3.json";
            $candidates[] = $booksDir . "/jo\xCC\x81.json";
            $candidates[] = $booksDir . "/jo_vt.json";
        }

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        // Fallback varrendo diretório caso o sistema de arquivos use outra normalização
        if (!$filePath && is_dir($booksDir)) {
            $files = scandir($booksDir) ?: [];
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
                    continue;
                }
                $stem = pathinfo($file, PATHINFO_FILENAME);
                if (self::stripAccents($stem) === self::stripAccents($abbrev)) {
                    if ($bookMeta['id'] === 18 && $stem === 'jo') {
                        continue; // 'jo.json' é João
                    }
                    $filePath = $booksDir . '/' . $file;
                    break;
                }
            }
        }

        if (!$filePath || !is_file($filePath)) {
            return null;
        }

        $raw = file_get_contents($filePath);
        $data = $raw ? json_decode($raw, true) : null;
        if (!is_array($data)) {
            return null;
        }

        $this->booksCache[$abbrev] = $data;
        return $data;
    }

    /**
     * Faz o parse de uma referência bíblica (ex: "João 1:1-18", "2 Coríntios 5:17", "Salmos 23").
     */
    public function parseReference(string $refStr): ?array
    {
        $trimmed = trim($refStr);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^((?:[123]\s+)?[\p{L}\s]+?)\s+(\d+)(?::(\d+(?:\s*-\s*\d+)?))?$/u', $trimmed, $matches)) {
            return null;
        }

        $rawBookName = trim($matches[1]);
        $chapter = (int)$matches[2];
        $verseStr = $matches[3] ?? '';

        $book = $this->findBook($rawBookName);
        if (!$book) {
            return null;
        }

        $highlight = null;
        if ($verseStr !== '') {
            if (str_contains($verseStr, '-')) {
                $parts = array_map('intval', explode('-', $verseStr));
                $highlight = [
                    'start' => max(1, $parts[0]),
                    'end' => max($parts[0], $parts[1] ?? $parts[0]),
                ];
            } else {
                $v = max(1, (int)$verseStr);
                $highlight = ['start' => $v, 'end' => $v];
            }
        }

        return [
            'book' => $book,
            'chapter' => $chapter,
            'highlight' => $highlight,
            'rawReference' => $trimmed,
        ];
    }

    /**
     * Retorna os versículos formatados de uma referência bíblica diretamente da base oficial NAA.
     */
    public function getPassageText(string $refStr): ?string
    {
        $parsed = $this->parseReference($refStr);
        if (!$parsed) {
            return null;
        }

        $bookData = $this->getBookData($parsed['book']['abbrev']);
        if (!$bookData || empty($bookData['chapters'])) {
            return null;
        }

        $chapterIdx = $parsed['chapter'] - 1;
        $chapterVerses = $bookData['chapters'][$chapterIdx] ?? null;
        if (!is_array($chapterVerses)) {
            return null;
        }

        if (!$parsed['highlight']) {
            $out = [];
            foreach ($chapterVerses as $i => $verseText) {
                $out[] = ($i + 1) . '. ' . $verseText;
            }
            return implode(' ', $out);
        }

        $start = $parsed['highlight']['start'];
        $end = $parsed['highlight']['end'];
        $selected = [];

        for ($v = $start; $v <= $end; $v++) {
            if (isset($chapterVerses[$v - 1])) {
                $selected[] = $v . '. ' . $chapterVerses[$v - 1];
            }
        }

        return !empty($selected) ? implode(' ', $selected) : null;
    }

    /**
     * Formata uma string de passagem ("1. No princípio... 2. Ele estava...") com números de versículo estilizados.
     */
    public static function formatPassageVersesHtml(string $passageText): string
    {
        $escaped = htmlspecialchars(trim($passageText), ENT_QUOTES, 'UTF-8');
        if ($escaped === '') {
            return '';
        }

        return preg_replace(
            '/(?:^|\s)(\d+)\.\s+/u',
            ' <sup class="passage-verse-num">$1</sup>',
            $escaped
        ) ?? $escaped;
    }

    /**
     * Retorna os dados completos de um capítulo para exibição em tela, incluindo navegação anterior/próxima.
     */
    public function getChapterViewData(string $bookIdentifier, int $chapter, ?string $highlightParam = null, ?string $contextLabel = null): array
    {
        $bookMeta = $this->findBook($bookIdentifier) ?? $this->booksMeta[0];
        $bookData = $this->getBookData($bookMeta['abbrev']);
        $totalChapters = (int)($bookMeta['chapters_count'] ?? count($bookData['chapters'] ?? []));
        if ($totalChapters < 1) {
            $totalChapters = 1;
        }

        $chapter = max(1, min($totalChapters, $chapter));
        $verses = $bookData['chapters'][$chapter - 1] ?? [];

        $highlight = null;
        if ($highlightParam !== null && trim($highlightParam) !== '') {
            $hlClean = trim($highlightParam);
            if (str_contains($hlClean, '-')) {
                [$s, $e] = array_map('intval', explode('-', $hlClean, 2));
                $highlight = ['start' => max(1, $s), 'end' => max($s, $e)];
            } elseif (is_numeric($hlClean)) {
                $v = max(1, (int)$hlClean);
                $highlight = ['start' => $v, 'end' => $v];
            }
        }

        // Calcula capítulo anterior e próximo (atravessando livros se necessário)
        $currentIndex = 0;
        foreach ($this->booksMeta as $idx => $b) {
            if ($b['id'] === $bookMeta['id']) {
                $currentIndex = $idx;
                break;
            }
        }

        $prevNav = null;
        if ($chapter > 1) {
            $prevNav = [
                'book' => $bookMeta,
                'chapter' => $chapter - 1,
                'label' => $bookMeta['name'] . ' ' . ($chapter - 1),
            ];
        } elseif ($currentIndex > 0) {
            $prevBook = $this->booksMeta[$currentIndex - 1];
            $prevNav = [
                'book' => $prevBook,
                'chapter' => (int)$prevBook['chapters_count'],
                'label' => $prevBook['name'] . ' ' . $prevBook['chapters_count'],
            ];
        }

        $nextNav = null;
        if ($chapter < $totalChapters) {
            $nextNav = [
                'book' => $bookMeta,
                'chapter' => $chapter + 1,
                'label' => $bookMeta['name'] . ' ' . ($chapter + 1),
            ];
        } elseif ($currentIndex < count($this->booksMeta) - 1) {
            $nextBook = $this->booksMeta[$currentIndex + 1];
            $nextNav = [
                'book' => $nextBook,
                'chapter' => 1,
                'label' => $nextBook['name'] . ' 1',
            ];
        }

        $relatedStories = $this->getStoriesForChapter($bookMeta['abbrev'], $chapter);

        return [
            'book' => $bookMeta,
            'chapter' => $chapter,
            'totalChapters' => $totalChapters,
            'verses' => $verses,
            'highlight' => $highlight,
            'contextLabel' => $contextLabel,
            'prev' => $prevNav,
            'next' => $nextNav,
            'relatedStories' => $relatedStories,
        ];
    }

    /**
     * Carrega todas as 366 histórias devocionais enriquecidas com metadados bíblicos.
     */
    public function getAllStories(): array
    {
        if ($this->storiesCache !== null) {
            return $this->storiesCache;
        }

        $file = $this->dataDir . '/data.json';
        if (!is_file($file)) {
            $this->storiesCache = [];
            return [];
        }

        $raw = file_get_contents($file);
        $stories = $raw ? json_decode($raw, true) : [];
        if (!is_array($stories)) {
            $this->storiesCache = [];
            return [];
        }

        usort($stories, static fn(array $a, array $b): int => ((int)($a['dia'] ?? 0)) <=> ((int)($b['dia'] ?? 0)));

        foreach ($stories as &$story) {
            $ref = (string)($story['referencia'] ?? '');
            $story['origin'] = 'defined';
            $parsed = $this->parseReference($ref);
            if ($parsed) {
                $story['book'] = $parsed['book'];
                $story['chapter'] = $parsed['chapter'];
                $story['highlight'] = $parsed['highlight'];
                $hlStr = '';
                if ($parsed['highlight']) {
                    $hlStr = $parsed['highlight']['start'] === $parsed['highlight']['end']
                        ? (string)$parsed['highlight']['start']
                        : $parsed['highlight']['start'] . '-' . $parsed['highlight']['end'];
                }
                $story['highlight_str'] = $hlStr;
                $story['bible_url'] = 'bible.php?book=' . urlencode($parsed['book']['url_abbrev'])
                    . '&ch=' . $parsed['chapter']
                    . ($hlStr !== '' ? '&hl=' . urlencode($hlStr) : '')
                    . '&ref=' . urlencode($ref);
            } else {
                $story['book'] = null;
                $story['chapter'] = 1;
                $story['highlight'] = null;
                $story['highlight_str'] = '';
                $story['bible_url'] = 'bible.php';
            }
        }
        unset($story);

        $this->storiesCache = $stories;
        return $this->storiesCache;
    }

    /**
     * Carrega o catálogo de histórias bíblicas canônicas adicionais (além das 366 devocionais diárias),
     * enriquecendo cada uma com o trecho oficial da Bíblia NAA e URL direta de leitura.
     */
    public function getCanonicalExtraStories(): array
    {
        if ($this->canonicalStoriesCache !== null) {
            return $this->canonicalStoriesCache;
        }

        $file = $this->dataDir . '/canonical_stories.json';
        if (!is_file($file)) {
            $this->canonicalStoriesCache = [];
            return [];
        }

        $raw = file_get_contents($file);
        $items = $raw ? json_decode($raw, true) : [];
        if (!is_array($items)) {
            $this->canonicalStoriesCache = [];
            return [];
        }

        $categoryArtwork = [
            'Origens' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg',
            'Patriarcas' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jacob_esau_meeting.png',
            'Êxodo' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_sarca_ardente.png',
            'Lei e Aliança' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_consagracao_dos_sacerdotes.png',
            'Deserto' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/balaao_e_a_jumenta.png',
            'Conquista' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_morte_de_moises.png',
            'Juízes' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Viagem sagrada sob o luar.png',
            'Redenção' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_alegria_da_salvacao.png',
            'Reino Unido' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/ai-generated-8930616_1280.png',
            'Reino Dividido' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
            'Crônicas' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_apresentacao_no_templo.png',
            'Pós-Exílio' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_no_templo_aos_doze_anos.png',
            'Sabedoria' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_cresce_em_sabedoria.png',
            'Salmos' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/a_alegria_da_salvacao.png',
            'Profetas' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/um_menino_nos_nasceu.png',
            'Exílio' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/sodoma_gomorra_1280x800.png',
            'Evangelhos' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Batismo ao pôr do sol.png',
            'Parábolas' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/jesus_cresce_em_sabedoria.png',
            'Milagres' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
            'Paixão' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Viagem sagrada sob o luar.png',
            'Ressurreição' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Cena de Natal iluminada e serena.png',
            'Igreja Primitiva' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/O pregador e a figura luminosa.png',
            'Epístolas' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg',
            'Apocalipse' => 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/Cena de Natal iluminada e serena.png',
        ];

        $enriched = [];
        foreach ($items as $idx => $item) {
            $id = (string)($item['id'] ?? ('c' . ($idx + 1)));
            $title = (string)($item['titulo'] ?? '');
            $ref = (string)($item['referencia'] ?? '');
            $cat = (string)($item['categoria'] ?? 'Narrativa Bíblica');
            $parsed = $this->parseReference($ref);

            $bookMeta = $parsed['book'] ?? null;
            $chapter = (int)($parsed['chapter'] ?? 1);
            $highlight = $parsed['highlight'] ?? null;
            $hlStr = '';
            if ($highlight) {
                $hlStr = $highlight['start'] === $highlight['end']
                    ? (string)$highlight['start']
                    : $highlight['start'] . '-' . $highlight['end'];
            }

            $bibleUrl = $bookMeta
                ? 'bible.php?book=' . urlencode($bookMeta['url_abbrev'])
                    . '&ch=' . $chapter
                    . ($hlStr !== '' ? '&hl=' . urlencode($hlStr) : '')
                    . '&ref=' . urlencode($ref)
                : 'bible.php';

            // Extrai os primeiros versículos reais da Bíblia NAA para resumo e compartilhamento
            $passageText = '';
            if ($bookMeta) {
                $bookData = $this->getBookData($bookMeta['abbrev']);
                $chapterVerses = $bookData['chapters'][$chapter - 1] ?? [];
                $startV = $highlight ? max(1, (int)$highlight['start']) : 1;
                $endV = $highlight ? min(count($chapterVerses), (int)$highlight['end']) : min(count($chapterVerses), 4);
                $maxV = min($endV, $startV + 3);
                $snippetParts = [];
                for ($v = $startV; $v <= $maxV; $v++) {
                    if (isset($chapterVerses[$v - 1])) {
                        $snippetParts[] = $v . '. ' . $chapterVerses[$v - 1];
                    }
                }
                $passageText = implode(' ', $snippetParts);
            }

            $slug = 'canonical_' . $id;
            $imgUrl = $categoryArtwork[$cat] ?? 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg';

            $enriched[] = [
                'id' => $id,
                'dia' => null,
                'data' => $cat,
                'titulo' => $title,
                'referencia' => $ref,
                'categoria' => $cat,
                'texto' => $passageText,
                'imagem' => [
                    'url' => $imgUrl,
                    'alt' => "Representação visual de '{$title}', baseada em {$ref} (NAA)."
                ],
                'slug' => $slug,
                'origin' => 'canonical',
                'book' => $bookMeta,
                'chapter' => $chapter,
                'highlight' => $highlight,
                'highlight_str' => $hlStr,
                'bible_url' => $bibleUrl,
            ];
        }

        $this->canonicalStoriesCache = $enriched;
        return $this->canonicalStoriesCache;
    }

    /**
     * Retorna todas as histórias bíblicas combinadas (366 já definidas + histórias canônicas adicionais),
     * ordenadas pela ordem canônica dos livros e capítulos.
     */
    public function getAllBiblicalStoriesCombined(): array
    {
        $defined = $this->getAllStories();
        $canonical = $this->getCanonicalExtraStories();
        $combined = array_merge($defined, $canonical);

        usort($combined, static function (array $a, array $b): int {
            $bA = (int)($a['book']['id'] ?? 999);
            $bB = (int)($b['book']['id'] ?? 999);
            if ($bA !== $bB) {
                return $bA <=> $bB;
            }
            $cA = (int)($a['chapter'] ?? 1);
            $cB = (int)($b['chapter'] ?? 1);
            if ($cA !== $cB) {
                return $cA <=> $cB;
            }
            $vA = (int)($a['highlight']['start'] ?? 1);
            $vB = (int)($b['highlight']['start'] ?? 1);
            return $vA <=> $vB;
        });

        return $combined;
    }

    /**
     * Retorna as histórias (definidas + canônicas) relacionadas a um livro e capítulo.
     */
    public function getStoriesForChapter(string $bookAbbrev, int $chapter): array
    {
        $stories = $this->getAllBiblicalStoriesCombined();
        $matches = [];
        foreach ($stories as $story) {
            if (
                isset($story['book']['abbrev'])
                && $story['book']['abbrev'] === $bookAbbrev
                && (int)($story['chapter'] ?? 0) === $chapter
            ) {
                $matches[] = $story;
            }
        }
        return $matches;
    }

    /**
     * Resolve a história ativa com base nos parâmetros (?p=slug, ?day=N ou dia atual do ano).
     * REGRA DAS HISTÓRIAS DIÁRIAS: A rotação diária automática (Dia 1 a 366) utiliza exclusivamente
     * as 366 histórias existentes em data/data.json. Caso o usuário clique em uma história canônica
     * extra (?p=canonical_cXXX), ela também pode ser lida na tela principal sem alterar o ciclo diário.
     */
    public function getStoryForRequest(?string $slug = null, ?string $day = null): array
    {
        $stories = $this->getAllStories(); // Estritamente as 366 histórias diárias existentes
        $dayOfYear = (int)date('z') + 1;
        $todayFormatted = date('d/m/Y');

        $selected = null;
        $selectedIndex = 0;
        $isCanonicalExtra = false;

        if ($slug !== null && trim($slug) !== '') {
            $slugClean = trim($slug);
            foreach ($stories as $idx => $item) {
                if (($item['slug'] ?? '') === $slugClean) {
                    $selected = $item;
                    $selectedIndex = $idx;
                    break;
                }
            }

            // Se o slug for de uma história canônica adicional (ex: canonical_c001), permite leitura completa
            if (!$selected && str_starts_with($slugClean, 'canonical_')) {
                $canonicalStories = $this->getCanonicalExtraStories();
                foreach ($canonicalStories as $cIdx => $cItem) {
                    if (($cItem['slug'] ?? '') === $slugClean) {
                        $selected = $cItem;
                        $selectedIndex = $cIdx;
                        $isCanonicalExtra = true;
                        break;
                    }
                }
            }
        }

        if (!$selected && $day !== null && trim($day) !== '') {
            $dayClean = (string)(int)trim($day);
            foreach ($stories as $idx => $item) {
                if ((string)($item['dia'] ?? '') === $dayClean) {
                    $selected = $item;
                    $selectedIndex = $idx;
                    break;
                }
            }
        }

        // Regra diária: busca sempre nas 366 histórias existentes pelo dia do ano (1..366)
        if (!$selected) {
            $targetDay = (string)$dayOfYear;
            foreach ($stories as $idx => $item) {
                if ((string)($item['dia'] ?? '') === $targetDay) {
                    $selected = $item;
                    $selectedIndex = $idx;
                    break;
                }
            }
        }

        if (!$selected && !empty($stories)) {
            $selected = $stories[0];
            $selectedIndex = 0;
        }

        if (!$selected) {
            return [
                'story' => [
                    'dia' => (string)$dayOfYear,
                    'data' => $todayFormatted,
                    'titulo' => 'Leitura do Dia',
                    'referencia' => 'João 1:1-18',
                    'texto' => '',
                    'imagem' => ['url' => '', 'alt' => ''],
                    'slug' => '',
                ],
                'previous' => null,
                'next' => null,
                'chapterView' => $this->getChapterViewData('gn', 1),
            ];
        }

        // Sincroniza texto completo da passagem com a fonte oficial da Bíblia NAA
        if (!empty($selected['referencia'])) {
            $officialText = $this->getPassageText($selected['referencia']);
            if ($officialText !== null && trim($officialText) !== '') {
                $selected['texto'] = $officialText;
            }
        }

        if ($isCanonicalExtra) {
            $canonicalList = $this->getCanonicalExtraStories();
            $previous = $selectedIndex > 0 ? $canonicalList[$selectedIndex - 1] : null;
            $next = ($selectedIndex < count($canonicalList) - 1) ? $canonicalList[$selectedIndex + 1] : null;
        } else {
            $previous = $selectedIndex > 0 ? $stories[$selectedIndex - 1] : null;
            $next = ($selectedIndex < count($stories) - 1) ? $stories[$selectedIndex + 1] : null;
        }

        $bookAbbrev = $selected['book']['abbrev'] ?? 'gn';
        $chapterNum = (int)($selected['chapter'] ?? 1);
        $hlStr = $selected['highlight_str'] ?? null;

        $chapterView = $this->getChapterViewData(
            $bookAbbrev,
            $chapterNum,
            $hlStr,
            $selected['referencia'] ?? null
        );

        return [
            'story' => $selected,
            'previous' => $previous,
            'next' => $next,
            'chapterView' => $chapterView,
            'totalStories' => count($stories),
        ];
    }

    /**
     * Busca textual em toda a Bíblia NAA (66 livros, 1.189 capítulos, 31.102 versículos).
     */
    public function searchBible(string $query, ?string $testament = null, ?string $bookFilter = null, int $limit = 120): array
    {
        $q = trim($query);
        if (mb_strlen($q, 'UTF-8') < 2) {
            return ['query' => $q, 'total' => 0, 'results' => []];
        }

        // Verifica se a busca é diretamente uma referência bíblica (ex: "João 3:16" ou "Salmo 23")
        $parsedRef = $this->parseReference($q);
        $results = [];
        $totalMatches = 0;

        $qNorm = self::stripAccents($q);
        $targetBook = $bookFilter ? $this->findBook($bookFilter) : null;

        foreach ($this->booksMeta as $bookMeta) {
            if ($testament && in_array($testament, ['VT', 'NT'], true) && $bookMeta['testament'] !== $testament) {
                continue;
            }
            if ($targetBook && $bookMeta['id'] !== $targetBook['id']) {
                continue;
            }

            $bookData = $this->getBookData($bookMeta['abbrev']);
            if (!$bookData || empty($bookData['chapters'])) {
                continue;
            }

            foreach ($bookData['chapters'] as $cIdx => $verses) {
                $chapterNum = $cIdx + 1;
                foreach ($verses as $vIdx => $verseText) {
                    $verseNum = $vIdx + 1;
                    $verseNorm = self::stripAccents($verseText);

                    if (str_contains($verseNorm, $qNorm)) {
                        $totalMatches++;
                        if (count($results) < $limit) {
                            $results[] = [
                                'book' => $bookMeta,
                                'chapter' => $chapterNum,
                                'verse' => $verseNum,
                                'text' => $verseText,
                                'reference' => $bookMeta['name'] . ' ' . $chapterNum . ':' . $verseNum,
                                'url' => 'bible.php?book=' . urlencode($bookMeta['url_abbrev']) . '&ch=' . $chapterNum . '&hl=' . $verseNum,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'query' => $q,
            'directReference' => $parsedRef,
            'total' => $totalMatches,
            'limit' => $limit,
            'results' => $results,
        ];
    }

    /**
     * Descrições editoriais das 10 divisões canônicas das Escrituras.
     */
    public static function getDivisionMeta(string $key): array
    {
        $meta = [
            'pentateuco' => [
                'subtitle' => 'A Lei e as Origens (Torá)',
                'description' => 'Da criação do universo e chamado dos patriarcas à libertação do Egito e formação da aliança no Sinai.',
            ],
            'historicos_vt' => [
                'subtitle' => 'A História de Israel',
                'description' => 'A entrada em Canaã, a era dos Juízes, o reinado de Davi e Salomão, o exílio babilônico e a reconstrução de Jerusalém.',
            ],
            'poeticos' => [
                'subtitle' => 'Adoração, Sabedoria e Clamor',
                'description' => 'Cânticos de louvor, orações profundas nos Salmos, reflexões sobre o sofrimento em Jó e provérbios para uma vida íntegra.',
            ],
            'profetas_maiores' => [
                'subtitle' => 'Juízo, Consolo e Promessa Messiânica',
                'description' => 'Profecias grandiosas de Isaías, Jeremias, Ezequiel e Daniel apontando para a fidelidade de Deus e a vinda do Redentor.',
            ],
            'profetas_menores' => [
                'subtitle' => 'Chamado ao Arrependimento e Esperança',
                'description' => 'Doze livros proféticos que convocam o povo à justiça, misericórdia e aguardam o amanhecer do Sol da Justiça.',
            ],
            'evangelhos' => [
                'subtitle' => 'A Vida, Ministério, Morte e Ressurreição de Jesus',
                'description' => 'Os quatro testemunhos sobre Jesus Cristo — o Verbo encarnado, Seus ensinos, parábolas, milagres e obra redentora na cruz.',
            ],
            'historico_nt' => [
                'subtitle' => 'O Nascimento e Expansão da Igreja',
                'description' => 'A descida do Espírito Santo no Pentecostes, a coragem dos apóstolos e as viagens missionárias até Roma.',
            ],
            'epistolas_paulinas' => [
                'subtitle' => 'Doutrina da Graça e Vida em Cristo',
                'description' => 'Cartas do apóstolo Paulo às igrejas e líderes ensinando sobre justificação pela fé, amor, unidade e santificação.',
            ],
            'epistolas_gerais' => [
                'subtitle' => 'Perseverança, Fé Prática e Amor Fraternal',
                'description' => 'Exortações de Hebreus, Tiago, Pedro, João e Judas para permanecer firme na verdade e viver o amor cristão.',
            ],
            'profetico_nt' => [
                'subtitle' => 'A Vitória Final do Cordeiro e a Nova Jerusalém',
                'description' => 'A revelação gloriosa de Jesus Cristo triunfante, a consumação da história e a promessa de novos céus e nova terra.',
            ],
        ];

        return $meta[$key] ?? ['subtitle' => 'Escrituras Sagradas', 'description' => ''];
    }

    /**
     * Organiza todos os 66 livros e todas as histórias bíblicas (366 já definidas + 220 canônicas adicionais)
     * nas 10 Divisões Canônicas da Bíblia.
     */
    public function getStoriesSeparatedByDivision(): array
    {
        $stories = $this->getAllBiblicalStoriesCombined();
        $groups = $this->getBooksGroupedByCategory();

        // Indexa histórias por id do livro
        $storiesByBookId = [];
        foreach ($stories as $story) {
            $bId = (int)($story['book']['id'] ?? 0);
            if ($bId > 0) {
                $storiesByBookId[$bId][] = $story;
            }
        }

        $result = [];
        foreach ($groups as $group) {
            $key = $group['key'];
            $meta = self::getDivisionMeta($key);
            $divisionStories = [];
            $definedCount = 0;
            $canonicalCount = 0;
            $enrichedBooks = [];

            foreach ($group['books'] as $book) {
                $bId = (int)$book['id'];
                $bStories = $storiesByBookId[$bId] ?? [];
                $bDefined = 0;
                $bCanonical = 0;
                foreach ($bStories as $st) {
                    if (($st['origin'] ?? 'defined') === 'defined') {
                        $bDefined++;
                        $definedCount++;
                    } else {
                        $bCanonical++;
                        $canonicalCount++;
                    }
                    $divisionStories[] = $st;
                }
                $book['stories'] = $bStories;
                $book['stories_count'] = count($bStories);
                $book['defined_count'] = $bDefined;
                $book['canonical_count'] = $bCanonical;
                $enrichedBooks[] = $book;
            }

            $result[] = [
                'key' => $key,
                'label' => $group['label'],
                'short' => $group['short'],
                'testament' => $group['testament'],
                'subtitle' => $meta['subtitle'],
                'description' => $meta['description'],
                'books' => $enrichedBooks,
                'stories' => $divisionStories,
                'stories_count' => count($divisionStories),
                'defined_count' => $definedCount,
                'canonical_count' => $canonicalCount,
            ];
        }

        return $result;
    }

    /**
     * Classifica uma história dentro de uma das 12 Grandes Eras da Narrativa Bíblica.
     */
    private function resolveEraKeyForStory(array $story): string
    {
        $bookId = (int)($story['book']['id'] ?? 0);
        $chapter = (int)($story['chapter'] ?? 1);
        $titleNorm = self::stripAccents((string)($story['titulo'] ?? ''));

        if ($bookId === 1) { // Gênesis
            return $chapter <= 11 ? 'criacao_primordios' : 'patriarcas';
        }
        if ($bookId >= 2 && $bookId <= 5) {
            return 'exodo_lei';
        }
        if ($bookId >= 6 && $bookId <= 8) {
            return 'conquista_juizes';
        }
        if ($bookId >= 9 && $bookId <= 17) {
            return 'reino_israel';
        }
        if ($bookId >= 18 && $bookId <= 22) {
            return 'sabedoria_salmos';
        }
        if ($bookId >= 23 && $bookId <= 39) {
            return 'profetas';
        }
        if ($bookId >= 40 && $bookId <= 43) { // Evangelhos
            if (
                (($bookId === 40 || $bookId === 42) && $chapter <= 2)
                || ($bookId === 43 && $chapter === 1 && $story['highlight'] && $story['highlight']['start'] <= 18)
                || str_contains($titleNorm, 'nascimento de jesus')
                || str_contains($titleNorm, 'genealogia de jesus')
                || str_contains($titleNorm, 'natal')
                || str_contains($titleNorm, 'magos')
                || str_contains($titleNorm, 'pastores')
                || str_contains($titleNorm, 'manjedoura')
                || str_contains($titleNorm, 'simeao')
                || str_contains($titleNorm, 'cantico de maria')
            ) {
                return 'advento_nascimento';
            }

            if (
                ($bookId === 40 && $chapter >= 26)
                || ($bookId === 41 && $chapter >= 14)
                || ($bookId === 42 && $chapter >= 22)
                || ($bookId === 43 && $chapter >= 13 && ($chapter >= 17 || $chapter === 13))
                || str_contains($titleNorm, 'cruz')
                || str_contains($titleNorm, 'crucific')
                || str_contains($titleNorm, 'ressurreic')
                || str_contains($titleNorm, 'tumulo vazio')
                || str_contains($titleNorm, 'getsemani')
                || str_contains($titleNorm, 'ultima ceia')
                || str_contains($titleNorm, 'pilatos')
                || str_contains($titleNorm, 'lava os pes')
                || str_contains($titleNorm, 'tome')
                || str_contains($titleNorm, 'tiberiades')
            ) {
                return 'paixao_ressurreicao';
            }

            return 'ministerio_jesus';
        }
        if ($bookId === 44) {
            return 'igreja_primitiva';
        }
        return 'epistolas_vida_crista';
    }

    /**
     * Retorna todas as histórias (366 definidas + 220 canônicas adicionais) separadas pelas 12 Grandes Eras da História Bíblica.
     */
    public function getStoriesSeparatedByEra(): array
    {
        $eras = [
            'criacao_primordios' => [
                'order' => 1,
                'key' => 'criacao_primordios',
                'title' => '1. A Criação e os Primórdios',
                'range' => 'Gênesis 1–11',
                'testament' => 'VT',
                'description' => 'A criação dos céus e da terra, o Jardim do Éden, a queda, Enoque, o dilúvio de Noé, a aliança e a Torre de Babel.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'patriarcas' => [
                'order' => 2,
                'key' => 'patriarcas',
                'title' => '2. Os Patriarcas da Aliança',
                'range' => 'Gênesis 12–50',
                'testament' => 'VT',
                'description' => 'O chamado de Abraão, Melquisedeque, a promessa a Isaque, a luta de Jacó no Jaboque e a providência divina na vida de José.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'exodo_lei' => [
                'order' => 3,
                'key' => 'exodo_lei',
                'title' => '3. O Êxodo e a Caminhada no Deserto',
                'range' => 'Êxodo a Deuteronômio',
                'testament' => 'VT',
                'description' => 'A libertação do Egito sob Moisés, as pragas, o Mar Vermelho, os Dez Mandamentos, o Tabernáculo e a jornada no deserto.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'conquista_juizes' => [
                'order' => 4,
                'key' => 'conquista_juizes',
                'title' => '4. A Terra Prometida e os Juízes',
                'range' => 'Josué, Juízes e Rute',
                'testament' => 'VT',
                'description' => 'A conquista de Canaã sob Josué, o pacto em Siquém, os juízes libertadores (Débora, Gideão, Jefté, Sansão) e a redenção em Rute.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'reino_israel' => [
                'order' => 5,
                'key' => 'reino_israel',
                'title' => '5. Reis, Crônicas e Restauração',
                'range' => '1 Samuel a Ester',
                'testament' => 'VT',
                'description' => 'O chamado de Samuel, o reinado de Davi e Salomão, os milagres de Elias e Eliseu, as reformas de Ezequias e Josias, e o retorno com Esdras, Neemias e Ester.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'sabedoria_salmos' => [
                'order' => 6,
                'key' => 'sabedoria_salmos',
                'title' => '6. Sabedoria, Salmos e Adoração',
                'range' => 'Jó, Salmos, Provérbios, Eclesiastes e Cânticos',
                'testament' => 'VT',
                'description' => 'A perseverança e restauração de Jó, os cânticos de refúgio nos Salmos, a Mulher Virtuosa e os conselhos eternos da sabedoria.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'profetas' => [
                'order' => 7,
                'key' => 'profetas',
                'title' => '7. A Voz dos Profetas e Promessas Messiânicas',
                'range' => 'Isaías a Malaquias',
                'testament' => 'VT',
                'description' => 'O anúncio de Emanuel e do Servo Sofredor, o novo coração em Ezequiel, a fidelidade de Daniel na Babilônia e a voz dos doze profetas menores.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'advento_nascimento' => [
                'order' => 8,
                'key' => 'advento_nascimento',
                'title' => '8. O Advento e o Nascimento de Jesus',
                'range' => 'Mateus 1–2 · Lucas 1–2 · João 1',
                'testament' => 'NT',
                'description' => 'O Verbo que se fez carne, a anunciação a Maria e José, o nascimento em Belém, a visita dos pastores, Simeão e os magos.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'ministerio_jesus' => [
                'order' => 9,
                'key' => 'ministerio_jesus',
                'title' => '9. Ministério, Ensinos, Parábolas e Milagres de Jesus',
                'range' => 'Mateus, Marcos, Lucas e João',
                'testament' => 'NT',
                'description' => 'O Sermão do Monte, todas as parábolas do Reino, curas, multiplicação dos pães e os discursos de Jesus como Pão da Vida e Bom Pastor.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'paixao_ressurreicao' => [
                'order' => 10,
                'key' => 'paixao_ressurreicao',
                'title' => '10. A Cruz e a Ressurreição Gloriosa',
                'range' => 'Narrativas da Paixão e Páscoa nos 4 Evangelhos',
                'testament' => 'NT',
                'description' => 'O lava-pés, a Última Ceia, a Oração Sacerdotal, o Getsêmani, o sacrifício redentor no Calvário e as aparições do Cristo Ressurreto.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'igreja_primitiva' => [
                'order' => 11,
                'key' => 'igreja_primitiva',
                'title' => '11. Pentecostes e a Igreja Primitiva',
                'range' => 'Atos dos Apóstolos 1–28',
                'testament' => 'NT',
                'description' => 'O derramamento do Espírito Santo, Estêvão, Filipe, a conversão de Saulo e as viagens missionárias até Roma.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
            'epistolas_vida_crista' => [
                'order' => 12,
                'key' => 'epistolas_vida_crista',
                'title' => '12. Cartas Apostólicas, Graça e Apocalipse',
                'range' => 'Romanos a Apocalipse',
                'testament' => 'NT',
                'description' => 'A justificação pela fé, o hino ao amor, a armadura de Deus, a galeria da fé em Hebreus, as epístolas gerais e o triunfo do Cordeiro em Apocalipse.',
                'stories' => [],
                'defined_count' => 0,
                'canonical_count' => 0,
            ],
        ];

        $stories = $this->getAllBiblicalStoriesCombined();
        foreach ($stories as $story) {
            $eraKey = $this->resolveEraKeyForStory($story);
            if (!isset($eras[$eraKey])) {
                $eraKey = 'epistolas_vida_crista';
            }
            $eras[$eraKey]['stories'][] = $story;
            if (($story['origin'] ?? 'defined') === 'defined') {
                $eras[$eraKey]['defined_count']++;
            } else {
                $eras[$eraKey]['canonical_count']++;
            }
        }

        return array_values($eras);
    }
}
