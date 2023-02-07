<?php

declare(strict_types=1);

namespace Torunar\AsciiChan;

use Exception;

const DATE_FORMAT = 'd/m/Y H:i';
const MAX_POST_LENGTH = 4096;
const MAX_TITLE_LENGTH = 128;

function getBoards(): array
{
    $sql = <<<'SQL'
        SELECT
            b.id,
            b.slug,
            b.display_name
        FROM boards b
        ORDER BY b.slug
    SQL;

    $result = executeQuery($sql);
    if ($result === false) {
        return [];
    }

    $boards = [];
    while ($board = $result->fetchArray()) {
        $boards[$board['slug']] = [
            'id' => $board['id'],
            'slug' => $board['slug'],
            'displayName' => $board['display_name'],
        ];
    }

    return $boards;
}

function getBoard(string $slug): ?array
{
    $boards = getBoards();

    return $boards[$slug] ?? null;
}

function getThreads(int $boardId, int $page = 1): array
{
    $threadsPerPage = 100;

    $sql = <<<'SQL'
        SELECT
            t.id,
            t.title,
            p.text AS op_post,
            t.op_post_id,
            t.created_at,
            t.updated_at
        FROM threads t
        INNER JOIN posts p ON p.id = t.op_post_id
        WHERE t.board_id = :boardId
        ORDER BY t.updated_at DESC
        LIMIT :limit
        OFFSET :offset
    SQL;

    $result = executeQuery(
        $sql,
        ['boardId', $boardId, SQLITE3_INTEGER],
        ['limit', $threadsPerPage, SQLITE3_INTEGER],
        ['offset', $threadsPerPage * ($page - 1), SQLITE3_INTEGER],
    );
    if ($result === false) {
        return [];
    }

    $threads = [];
    while ($thread = $result->fetchArray()) {
        $threads[$thread['id']] = [
            'id' => $thread['id'],
            'title' => $thread['title'],
            'createdAt' => date(DATE_FORMAT, strtotime($thread['created_at'])),
            'updatedAt' => date(DATE_FORMAT, strtotime($thread['updated_at'])),
            'opPost' => $thread['op_post'],
            'opPostId' => $thread['op_post_id'],
        ];
    }

    return $threads;
}

function getThread(int $id): ?array
{
    $sql = <<<'SQL'
        SELECT
            t.id,
            t.title,
            p.text AS op_post,
            t.op_post_id,
            t.created_at,
            t.updated_at
        FROM threads t
        INNER JOIN posts p ON p.id = t.op_post_id
        WHERE t.id = :id
    SQL;

    $result = executeQuery($sql, ['id', $id, SQLITE3_INTEGER]);
    if ($result === false) {
        return null;
    }

    $thread = $result->fetchArray();
    if (!$thread) {
        return null;
    }

    return [
        'id' => $thread['id'],
        'title' => $thread['title'],
        'createdAt' => date(DATE_FORMAT, strtotime($thread['created_at'])),
        'updatedAt' => date(DATE_FORMAT, strtotime($thread['updated_at'])),
        'opPost' => $thread['op_post'],
        'opPostId' => $thread['op_post_id'],
    ];
}

function getPosts(int $threadId): array
{
    $sql = <<<'SQL'
        SELECT
            p.id,
            p.text,
            p.created_at AS created_at
        FROM posts p
        WHERE p.thread_id = :id
        ORDER BY p.id ASC
    SQL;

    $result = executeQuery($sql, ['id', $threadId, SQLITE3_INTEGER]);
    if ($result === false) {
        return [];
    }

    $posts = [];
    while ($post = $result->fetchArray()) {
        $posts[$post['id']] = [
            'id' => $post['id'],
            'text' => $post['text'],
            'createdAt' => date(DATE_FORMAT, strtotime($post['created_at'])),
        ];
    }

    return $posts;
}

function validatePost(array $data): void
{
    if (!getThread($data['threadId'])) {
        throw new Exception(getTranslation('thread.notExists'));
    }

    if ($data['text'] === '') {
        throw new Exception(getTranslation('post.enterPostText'));
    }
}

function validateThread(array $data): void
{
    if (!getBoard($data['slug'])) {
        throw new Exception(getTranslation('board.notExists'));
    }

    if ($data['text'] === '') {
        throw new Exception(getTranslation('post.enterPostText'));
    }
}

function addPost(array $data): int
{
    $createPostSql = <<<'SQL'
        INSERT INTO posts (thread_id, text, created_at)
        VALUES (:threadId, :text, :createdAt)  
    SQL;

    $result = executeQuery(
        $createPostSql,
        ['threadId', $data['threadId'], SQLITE3_INTEGER],
        ['text', mb_substr(trim($data['text'], PHP_EOL), 0, MAX_POST_LENGTH), SQLITE3_TEXT],
        ['createdAt', getCurrentDate(), SQLITE3_TEXT]
    );
    if ($result === false) {
        throw new Exception(getTranslation('post.notCreated'));
    }

    $postId = (getConnection())->lastInsertRowID();

    $updateThreadSql = <<<'SQL'
        UPDATE threads 
        SET updated_at = :updatedAt,
            op_post_id = (
                CASE op_post_id
                    WHEN 0 THEN :postId
                    ELSE op_post_id
                END
            )
        WHERE id = :threadId
    SQL;

    executeQuery(
        $updateThreadSql,
        ['updatedAt', getCurrentDate(), SQLITE3_TEXT],
        ['threadId', $data['threadId'], SQLITE3_INTEGER],
        ['postId', $postId, SQLITE3_INTEGER]
    );

    return $postId;
}

function addThread(array $data): int
{
    $createThreadSql = <<<'SQL'
        INSERT INTO threads (board_id, title, op_post_id, created_at, updated_at)
        VALUES (:boardId, :title, 0, :date, :date)  
    SQL;

    $board = getBoard($data['slug']);

    $result = executeQuery(
        $createThreadSql,
        ['boardId', $board['id'], SQLITE3_INTEGER],
        ['title', mb_substr(trim($data['title'], PHP_EOL), 0, MAX_TITLE_LENGTH), SQLITE3_TEXT],
        ['date', getCurrentDate(), SQLITE3_TEXT]
    );
    if ($result === false) {
        throw new Exception(getTranslation('thread.notCreated'));
    }

    $threadId = (getConnection())->lastInsertRowID();

    addPost([
        'threadId' => $threadId,
        'text' => $data['text'],
    ]);

    return $threadId;
}
