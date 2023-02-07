<?php

declare(strict_types=1);

namespace Torunar\AsciiChan;

const NAME = 'ASCII.chan';
const MAX_POST_PREVIEW_LENGTH = 512;

function renderErrorPage(string $error): array
{
    $pageContent = strtr(
        getTemplate('error'),
        [
            '[error]' => $error,
        ]
    );

    $header = strtr(
        '<a href="/">[name]</a> :: [errorText]',
        [
            '[name]' => NAME,
            '[errorText]' => getTranslation('error'),
        ]
    );

    return [
        '[title]' => strip_tags($header),
        '[header]' => $header,
        '[content]' => $pageContent,
    ];
}

function renderBoardList(array $boards): array
{
    $pageContent = '';
    foreach ($boards as $board) {
        $pageContent .= strtr(
            getTemplate('board'),
            [
                '[slug]' => $board['slug'],
                '[displayName]' => $board['displayName'],
            ]
        );
    }

    return [
        '[title]' => NAME,
        '[header]' => NAME,
        '[content]' => $pageContent,
    ];
}

function renderBoard(array $board, array $threads): array
{
    $pageContent = '';
    foreach ($threads as $thread) {
        $pageContent .= strtr(
            getTemplate('thread'),
            [
                '[board]' => $board['slug'],
                '[postUrl]' => strtr(
                    '/[board]/thread/[id]/',
                    [
                        '[board]' => $board['slug'],
                        '[id]' => $thread['id'],
                    ]
                ),
                '[id]' => $thread['opPostId'],
                '[postedAtText]' => getTranslation('thread.posted'),
                '[postedAt]' => $thread['createdAt'],
                '[updatedAtText]' => getTranslation('thread.updated'),
                '[updatedAt]' => $thread['updatedAt'],
                '[title]' => htmlspecialchars($thread['title']),
                '[post]' => htmlspecialchars(trimTextToWidth($thread['opPost'], MAX_POST_PREVIEW_LENGTH)),
            ]
        );
    }

    $pageContent .= strtr(
        getTemplate('thread-form'),
        [
            '[board]' => $board['slug'],
            '[newThreadText]' => getTranslation('board.newThread'),
            '[titleText]' => getTranslation('thread.title'),
            '[postText]' => getTranslation('post.text'),
            '[createText]' => getTranslation('thread.create'),
        ]
    );

    $header = strtr(
        '<a href="/">[name]</a> :: /[board]/',
        [
            '[name]' => NAME,
            '[board]' => $board['slug'],
        ]
    );

    return [
        '[title]' => strip_tags($header),
        '[header]' => $header,
        '[content]' => $pageContent,
    ];
}

function renderThread(array $board, array $thread, array $posts): array
{
    $pageContent = '';
    $isOpPostRendered = false;
    foreach ($posts as $post) {
        $pageContent .= strtr(
            getTemplate($isOpPostRendered ? 'post' : 'thread'),
            [
                '[board]' => $board['slug'],
                '[postUrl]' => strtr(
                    '#[id]',
                    [
                        '[id]' => $post['id'],
                    ]
                ),
                '[id]' => $post['id'],
                '[postedAtText]' => getTranslation('thread.posted'),
                '[postedAt]' => $post['createdAt'],
                '[updatedAtText]' => getTranslation('thread.updated'),
                '[updatedAt]' => $thread['updatedAt'],
                '[title]' => htmlspecialchars($thread['title']),
                '[post]' => markupUrls(htmlspecialchars($post['text'])),
            ]
        );
        $isOpPostRendered = true;
    }

    $pageContent .= strtr(
        getTemplate('post-form'),
        [
            '[board]' => $board['slug'],
            '[threadId]' => $thread['id'],
            '[newPostText]' => getTranslation('thread.newPost'),
            '[replyText]' => getTranslation('thread.reply'),
            '[postText]' => getTranslation('post.text'),
        ]
    );

    $header = strtr(
        '<a href="/">[name]</a> :: <a href="/[board]/">/[board]/</a> :: [threadText] #[id]',
        [
            '[name]' => NAME,
            '[board]' => $board['slug'],
            '[threadText]' => getTranslation('thread'),
            '[id]' => $thread['id'],
        ]
    );

    return [
        '[title]' => strip_tags($header),
        '[header]' => $header,
        '[content]' => $pageContent,
    ];
}
