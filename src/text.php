<?php

declare(strict_types=1);

namespace Torunar\AsciiChan;

function trimTextToWidth(string $string, int $width, string $ellipsis = '…'): string
{
    if (mb_strlen($string) <= $width) {
        return $string;
    }

    return mb_substr($string, 0, $width - mb_strlen($ellipsis)) . $ellipsis;
}

function markupUrls(string $text): string
{
    return preg_replace(
        '/(&gt;){2,} ?([0-9]+)/iu',
        '<a href="#$2">$0</a>',
        $text
    );
}
