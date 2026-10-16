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
     * Retorna as histórias devocionais relacionadas a um livro e capítulo.
     */
    public function getStoriesForChapter(string $bookAbbrev, int $chapter): array
    {
        $stories = $this->getAllStories();
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
     */
    public function getStoryForRequest(?string $slug = null, ?string $day = null): array
    {
        $stories = $this->getAllStories();
        $dayOfYear = (int)date('z') + 1;
        $todayFormatted = date('d/m/Y');

        $selected = null;
        $selectedIndex = 0;

        if ($slug !== null && trim($slug) !== '') {
            $slugClean = trim($slug);
            foreach ($stories as $idx => $item) {
                if (($item['slug'] ?? '') === $slugClean) {
                    $selected = $item;
                    $selectedIndex = $idx;
                    break;
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

        // Sincroniza texto com a fonte oficial da Bíblia NAA
        if (!empty($selected['referencia'])) {
            $officialText = $this->getPassageText($selected['referencia']);
            if ($officialText !== null && trim($officialText) !== '') {
                $selected['texto'] = $officialText;
            }
        }

        $previous = $selectedIndex > 0 ? $stories[$selectedIndex - 1] : null;
        $next = ($selectedIndex < count($stories) - 1) ? $stories[$selectedIndex + 1] : null;

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
     * Organiza todos os 66 livros e todas as 366 histórias já definidas nas 10 Divisões Canônicas da Bíblia.
     */
    public function getStoriesSeparatedByDivision(): array
    {
        $stories = $this->getAllStories();
        $groups = $this->getBooksGroupedByCategory();

        // Indexa histórias definidas por id do livro
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
            $enrichedBooks = [];

            foreach ($group['books'] as $book) {
                $bId = (int)$book['id'];
                $bStories = $storiesByBookId[$bId] ?? [];
                $book['stories'] = $bStories;
                $book['stories_count'] = count($bStories);
                $enrichedBooks[] = $book;
                foreach ($bStories as $st) {
                    $divisionStories[] = $st;
                }
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
            // Verifica se é Advento / Nascimento / Infância de Jesus
            if (
                (($bookId === 40 || $bookId === 42) && $chapter <= 2)
                || ($bookId === 43 && $chapter === 1 && $story['highlight'] && $story['highlight']['start'] <= 18)
                || str_contains($titleNorm, 'nascimento de jesus')
                || str_contains($titleNorm, 'natal')
                || str_contains($titleNorm, 'magos')
                || str_contains($titleNorm, 'pastores')
                || str_contains($titleNorm, 'manjedoura')
                || str_contains($titleNorm, 'simeao')
                || str_contains($titleNorm, 'cantico de maria')
            ) {
                return 'advento_nascimento';
            }

            // Verifica se é Paixão, Crucificação ou Ressurreição
            if (
                ($bookId === 40 && $chapter >= 26)
                || ($bookId === 41 && $chapter >= 14)
                || ($bookId === 42 && $chapter >= 22)
                || ($bookId === 43 && $chapter >= 18)
                || str_contains($titleNorm, 'cruz')
                || str_contains($titleNorm, 'crucific')
                || str_contains($titleNorm, 'ressurreic')
                || str_contains($titleNorm, 'tumulo vazio')
                || str_contains($titleNorm, 'getsemani')
                || str_contains($titleNorm, 'ultima ceia')
                || str_contains($titleNorm, 'pilatos')
            ) {
                return 'paixao_ressurreicao';
            }

            return 'ministerio_jesus';
        }
        if ($bookId === 44) {
            return 'igreja_primitiva';
        }
        if ($bookId >= 45 && $bookId <= 65) {
            return 'epistolas_vida_crista';
        }
        return 'consumacao_apocalipse';
    }

    /**
     * Retorna todas as histórias separadas pelas 12 Grandes Eras da História Bíblica.
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
                'description' => 'A criação dos céus e da terra, o Jardim do Éden, a queda, o dilúvio de Noé e a aliança de Deus com toda a criação.',
                'stories' => [],
            ],
            'patriarcas' => [
                'order' => 2,
                'key' => 'patriarcas',
                'title' => '2. Os Patriarcas da Aliança',
                'range' => 'Gênesis 12–50',
                'testament' => 'VT',
                'description' => 'O chamado de Abraão, a promessa a Isaque, a jornada de Jacó e a providência divina na vida de José no Egito.',
                'stories' => [],
            ],
            'exodo_lei' => [
                'order' => 3,
                'key' => 'exodo_lei',
                'title' => '3. O Êxodo e a Caminhada no Deserto',
                'range' => 'Êxodo a Deuteronômio',
                'testament' => 'VT',
                'description' => 'A libertação da escravidão no Egito sob Moisés, a travessia do Mar Vermelho, os Dez Mandamentos e a fidelidade no deserto.',
                'stories' => [],
            ],
            'conquista_juizes' => [
                'order' => 4,
                'key' => 'conquista_juizes',
                'title' => '4. A Terra Prometida e os Juízes',
                'range' => 'Josué, Juízes e Rute',
                'testament' => 'VT',
                'description' => 'A conquista sob Josué, os libertadores levantados por Deus (Débora, Gideão, Sansão) e a história de redenção em Rute.',
                'stories' => [],
            ],
            'reino_israel' => [
                'order' => 5,
                'key' => 'reino_israel',
                'title' => '5. Reis, Crônicas e Restauração',
                'range' => '1 Samuel a Ester',
                'testament' => 'VT',
                'description' => 'O chamado de Samuel, o reinado de Davi, a sabedoria de Salomão, os milagres de Elias e Eliseu, e o retorno do exílio.',
                'stories' => [],
            ],
            'sabedoria_salmos' => [
                'order' => 6,
                'key' => 'sabedoria_salmos',
                'title' => '6. Sabedoria, Salmos e Adoração',
                'range' => 'Jó, Salmos, Provérbios, Eclesiastes e Cânticos',
                'testament' => 'VT',
                'description' => 'A perseverança de Jó, os cânticos de confiança e refúgio nos Salmos e os conselhos eternos da sabedoria divina.',
                'stories' => [],
            ],
            'profetas' => [
                'order' => 7,
                'key' => 'profetas',
                'title' => '7. A Voz dos Profetas e Promessas Messiânicas',
                'range' => 'Isaías a Malaquias',
                'testament' => 'VT',
                'description' => 'O anúncio do Servo Sofredor, o novo coração prometido, a fidelidade na fornalha e na cova dos leões e a esperança do Messias.',
                'stories' => [],
            ],
            'advento_nascimento' => [
                'order' => 8,
                'key' => 'advento_nascimento',
                'title' => '8. O Advento e o Nascimento de Jesus',
                'range' => 'Mateus 1–2 · Lucas 1–2 · João 1',
                'testament' => 'NT',
                'description' => 'O Verbo que se fez carne, a anunciação a Maria, o nascimento em Belém, a adoração dos pastores e magos.',
                'stories' => [],
            ],
            'ministerio_jesus' => [
                'order' => 9,
                'key' => 'ministerio_jesus',
                'title' => '9. Ministério, Ensinos e Milagres de Jesus',
                'range' => 'Mateus, Marcos, Lucas e João',
                'testament' => 'NT',
                'description' => 'O Sermão do Monte, as parábolas do Reino, curas, multiplicação dos pães e os discursos de Jesus como Bom Pastor e Luz do Mundo.',
                'stories' => [],
            ],
            'paixao_ressurreicao' => [
                'order' => 10,
                'key' => 'paixao_ressurreicao',
                'title' => '10. A Cruz e a Ressurreição Gloriosa',
                'range' => 'Narrativas da Paixão e Páscoa nos 4 Evangelhos',
                'testament' => 'NT',
                'description' => 'A Última Ceia, a oração no Getsêmani, o sacrifício redentor no Calvário e a vitória sobre a morte na manhã da ressurreição.',
                'stories' => [],
            ],
            'igreja_primitiva' => [
                'order' => 11,
                'key' => 'igreja_primitiva',
                'title' => '11. Pentecostes e a Igreja Primitiva',
                'range' => 'Atos dos Apóstolos 1–28',
                'testament' => 'NT',
                'description' => 'O derramamento do Espírito Santo, a comunhão dos primeiros cristãos, a conversão de Paulo e o Evangelho levado às nações.',
                'stories' => [],
            ],
            'epistolas_vida_crista' => [
                'order' => 12,
                'key' => 'epistolas_vida_crista',
                'title' => '12. Cartas Apostólicas, Graça e Eternidade',
                'range' => 'Romanos a Apocalipse',
                'testament' => 'NT',
                'description' => 'A vida no Espírito, o fruto do amor, a armadura de Deus, a galeria dos heróis da fé e a visão da Nova Jerusalém.',
                'stories' => [],
            ],
        ];

        $stories = $this->getAllStories();
        foreach ($stories as $story) {
            $eraKey = $this->resolveEraKeyForStory($story);
            if (!isset($eras[$eraKey])) {
                $eraKey = 'epistolas_vida_crista';
            }
            $eras[$eraKey]['stories'][] = $story;
        }

        return array_values($eras);
    }
}
