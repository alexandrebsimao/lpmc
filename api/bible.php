<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/BibleRepository.php';

use ViaScriptura\BibleRepository;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$repo = new BibleRepository();
$action = $_GET['action'] ?? 'chapter';

try {
    switch ($action) {
        case 'books':
            echo json_encode([
                'ok' => true,
                'books' => $repo->getBooks(),
                'groups' => $repo->getBooksGroupedByCategory(),
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'chapter':
            $book = (string)($_GET['book'] ?? 'gn');
            $ch = (int)($_GET['ch'] ?? 1);
            $hl = isset($_GET['hl']) ? (string)$_GET['hl'] : null;
            $ref = isset($_GET['ref']) ? (string)$_GET['ref'] : null;

            $viewData = $repo->getChapterViewData($book, $ch, $hl, $ref);
            echo json_encode([
                'ok' => true,
                'data' => $viewData,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'search':
            $q = (string)($_GET['q'] ?? '');
            $testament = isset($_GET['testament']) && $_GET['testament'] !== '' ? (string)$_GET['testament'] : null;
            $book = isset($_GET['book']) && $_GET['book'] !== '' ? (string)$_GET['book'] : null;
            $limit = max(10, min(300, (int)($_GET['limit'] ?? 120)));

            $searchData = $repo->searchBible($q, $testament, $book, $limit);
            echo json_encode([
                'ok' => true,
                'data' => $searchData,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'passage':
            $ref = (string)($_GET['ref'] ?? '');
            $parsed = $repo->parseReference($ref);
            $text = $repo->getPassageText($ref);
            echo json_encode([
                'ok' => $text !== null,
                'reference' => $ref,
                'parsed' => $parsed,
                'text' => $text,
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Ação inválida.'], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Erro interno ao processar requisição bíblica.',
    ], JSON_UNESCAPED_UNICODE);
}
