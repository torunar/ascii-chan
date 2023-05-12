<?php

declare(strict_types=1);

namespace Torunar\AsciiChan;

function showBoardList(): string
{
    $boards = getBoards();

    return showIndexPage(
        renderBoardList($boards)
    );
}

function showBoard(string $slug, int $page = 1): string
{
    $board = getBoard($slug);
    if (!$board) {
        return showErrorPage(getTranslation('board.notExists'));
    }

    $threads = getThreads($board['id'], $page);

    return showPage(
        renderBoard($board, $threads)
    );
}

function showThread(string $slug, int $id): string
{
    $thread = getThread($id);
    if (!$thread) {
        return showErrorPage(getTranslation('thread.notExists'));
    }

    $board = getBoard($slug);
    if (!$board) {
        return showErrorPage(getTranslation('board.notExists'));
    }

    $posts = getPosts($id);

    return showPage(
        renderThread($board, $thread, $posts)
    );
}

function showErrorPage(string $error): string
{
    return showPage(
        renderErrorPage($error)
    );
}

function showPage(array $response): string
{
    return strtr(
        getTemplate('page'),
        $response
    );
}

function showIndexPage(array $response): string
{
    return strtr(
        getTemplate('index'),
        $response
    );
}
