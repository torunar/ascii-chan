<?php

declare(strict_types=1);

use function Torunar\AsciiChan\executeQuery;
use function Torunar\AsciiChan\getBoard;
use function Torunar\AsciiChan\getConnection;
use function Torunar\AsciiChan\getThread;

function createBoard(string $slug, string $displayName): int
{
    $sql = <<<'SQL'
        INSERT INTO boards (slug, display_name)
        VALUES (:slug, :displayName)
    SQL;

    executeQuery(
        $sql,
        ['slug', $slug, SQLITE3_TEXT],
        ['displayName', $displayName, SQLITE3_TEXT]
    );

    return (getConnection())->lastInsertRowID();
}

function removeBoard(string $slug): ?int
{
    $board = getBoard($slug);
    if (!$board) {
        return null;
    }

    $postsSql = <<<'SQL'
        DELETE FROM posts
        WHERE thread_id IN (
            SELECT id
            FROM threads
            WHERE board_id = :boardId
        )
    SQL;

    $threadsSql = <<<'SQL'
        DELETE FROM threads
        WHERE board_id = :boardId
    SQL;

    $boardSql = <<<'SQL'
        DELETE FROM boards
        WHERE id = :boardId
    SQL;

    executeQuery($postsSql, ['boardId', $board['id'], SQLITE3_INTEGER]);
    executeQuery($threadsSql, ['boardId', $board['id'], SQLITE3_INTEGER]);
    executeQuery($boardSql, ['boardId', $board['id'], SQLITE3_INTEGER]);

    return $board['id'];
}

function removeThread(int $id): ?int
{
    $thread = getThread($id);
    if (!$thread) {
        return null;
    }

    $postsSql = <<<'SQL'
        DELETE FROM posts
        WHERE thread_id = :threadId
    SQL;

    $threadSql = <<<'SQL'
        DELETE FROM threads
        WHERE id = :threadId
    SQL;

    executeQuery($postsSql, ['threadId', $id, SQLITE3_INTEGER]);
    executeQuery($threadSql, ['threadId', $id, SQLITE3_INTEGER]);

    return $id;
}
