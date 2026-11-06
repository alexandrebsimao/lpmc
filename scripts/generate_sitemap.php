<?php
declare(strict_types=1);

/**
 * scripts/generate_sitemap.php
 * Gerador de sitemap.xml compatível com PHP 8.1+ e Sitemaps Protocol 0.9.
 * Pode ser executado via CLI localmente ou dentro do container Docker.
 *
 * Exemplo de uso:
 *   php scripts/generate_sitemap.php
 */

require_once dirname(__DIR__) . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

$repo = new BibleRepository();
$baseUrl = 'https://www.viascriptura.com';
$today = date('Y-m-d');
$outputFile = dirname(__DIR__) . '/sitemap.xml';

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

$urlset = $dom->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
$urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
$dom->appendChild($urlset);

$addUrl = static function (
    string $loc,
    float $priority,
    string $changefreq,
    ?string $imgUrl = null,
    ?string $imgTitle = null
) use ($dom, $urlset, $today): void {
    $urlElem = $dom->createElement('url');

    $locElem = $dom->createElement('loc');
    $locElem->appendChild($dom->createTextNode($loc));
    $urlElem->appendChild($locElem);

    $lastmodElem = $dom->createElement('lastmod', $today);
    $urlElem->appendChild($lastmodElem);

    $cfElem = $dom->createElement('changefreq', $changefreq);
    $urlElem->appendChild($cfElem);

    $prElem = $dom->createElement('priority', number_format($priority, 1, '.', ''));
    $urlElem->appendChild($prElem);

    if ($imgUrl !== null && trim($imgUrl) !== '') {
        $imgElem = $dom->createElementNS('http://www.google.com/schemas/sitemap-image/1.1', 'image:image');

        $imgLoc = $dom->createElementNS('http://www.google.com/schemas/sitemap-image/1.1', 'image:loc');
        $imgLoc->appendChild($dom->createTextNode(trim($imgUrl)));
        $imgElem->appendChild($imgLoc);

        if ($imgTitle !== null && trim($imgTitle) !== '') {
            $imgTit = $dom->createElementNS('http://www.google.com/schemas/sitemap-image/1.1', 'image:title');
            $imgTit->appendChild($dom->createTextNode(trim($imgTitle)));
            $imgElem->appendChild($imgTit);
        }

        $urlElem->appendChild($imgElem);
    }

    $urlset->appendChild($urlElem);
};

// 1. Páginas Principais
$addUrl("{$baseUrl}/", 1.0, 'daily', 'https://raw.githubusercontent.com/alexandrebsimao/via-scriptura/refs/heads/main/photo-1470252649378-9c29740c9fa8.jpeg', 'Via Scriptura — Leitura Bíblica Diária Ilustrada');
$addUrl("{$baseUrl}/bible.php", 0.9, 'weekly');
$addUrl("{$baseUrl}/stories.php", 0.9, 'weekly');
$addUrl("{$baseUrl}/divisions.php", 0.8, 'weekly');
$addUrl("{$baseUrl}/about.php", 0.6, 'monthly');
$addUrl("{$baseUrl}/privacy.php", 0.3, 'monthly');

// 2. Capítulos da Bíblia Completa (66 Livros)
$books = $repo->getBooks();
foreach ($books as $b) {
    $urlAbbrev = $b['url_abbrev'] ?? $b['abbrev'];
    $totalCh = (int)($b['chapters_count'] ?? 1);
    for ($ch = 1; $ch <= $totalCh; $ch++) {
        $loc = "{$baseUrl}/bible.php?book=" . rawurlencode($urlAbbrev) . "&ch={$ch}";
        $addUrl($loc, 0.7, 'monthly');
    }
}

// 3. Histórias Bíblicas Diárias (366 Histórias)
$dailyStories = $repo->getAllStories();
foreach ($dailyStories as $s) {
    $slug = (string)($s['slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    $loc = "{$baseUrl}/?p=" . rawurlencode($slug);
    $title = (string)($s['titulo'] ?? '');
    $ref = (string)($s['referencia'] ?? '');
    $fullTitle = $ref !== '' ? "{$title} ({$ref})" : $title;
    $imgUrl = $s['imagem']['url'] ?? null;
    $addUrl($loc, 0.8, 'monthly', $imgUrl, $fullTitle);
}

// 4. Histórias Bíblicas Canônicas Adicionais (220 Histórias)
$canonicalStories = $repo->getCanonicalExtraStories();
foreach ($canonicalStories as $cs) {
    $slug = (string)($cs['slug'] ?? '');
    if ($slug === '') {
        continue;
    }
    $loc = "{$baseUrl}/?p=" . rawurlencode($slug);
    $title = (string)($cs['titulo'] ?? '');
    $ref = (string)($cs['referencia'] ?? '');
    $fullTitle = $ref !== '' ? "{$title} ({$ref})" : $title;
    $imgUrl = $cs['imagem']['url'] ?? null;
    $addUrl($loc, 0.7, 'monthly', $imgUrl, $fullTitle);
}

$dom->save($outputFile);

$totalUrls = $urlset->getElementsByTagName('url')->length;
$sizeKb = round(filesize($outputFile) / 1024, 2);

echo "Sitemap XML gerado com sucesso em: {$outputFile}\n";
echo "Total de URLs: {$totalUrls}\n";
echo "Tamanho do arquivo: {$sizeKb} KB\n";
