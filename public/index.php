<?php

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;
use function Torunar\AsciiChan\addPost;
use function Torunar\AsciiChan\addThread;
use function Torunar\AsciiChan\showBoard;
use function Torunar\AsciiChan\showBoardList;
use function Torunar\AsciiChan\showErrorPage;
use function Torunar\AsciiChan\showThread;
use function Torunar\AsciiChan\validatePost;
use function Torunar\AsciiChan\validateThread;

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = simpleDispatcher(static function (RouteCollector $r) {
    $r->get('/', fn() => showBoardList());
    $r->get('/{slug}/[{page:\d+}/]', fn(string $slug, int $page = 1) => showBoard($slug, $page));
    $r->get('/{slug}/thread/{id:\d+}/', fn(string $slug, int $id) => showThread($slug, $id));
    $r->post('/{slug}/thread/{id:\d+}/reply/', static function (string $slug, int $id) {
        try {
            $data = ['threadId' => $id] + $_REQUEST + ['text' => ''];
            validatePost($data);

            $postId = addPost($data);
            header("Location: /{$slug}/thread/{$id}/#{$postId}");
            return '';
        } catch (Throwable $exception) {
            return showErrorPage($exception->getMessage());
        }
    });
    $r->post('/{slug}/reply/', static function (string $slug) {
        try {
            $data = ['slug' => $slug] + $_REQUEST + ['text' => '', 'title' => ''];
            validateThread($data);

            $threadId = addThread($data);
            header("Location: /{$slug}/thread/{$threadId}/");
            return '';
        } catch (Throwable $exception) {
            return showErrorPage($exception->getMessage());
        }
    });
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        echo $handler(...$vars);
        break;
    default:
        echo showErrorPage('404');
}
