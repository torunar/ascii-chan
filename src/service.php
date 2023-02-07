<?php

declare(strict_types=1);

namespace Torunar\AsciiChan;

use DateTimeImmutable;
use SQLite3;
use SQLite3Result;

function getConnection(?string $dbPath = null): SQLite3
{
    $dbPath = $dbPath ?? __DIR__ . '/../data/storage/ascii-chan.sqlite';

    static $connections = [];
    if (!isset($connections[$dbPath])) {
        $connections[$dbPath] = new SQLite3($dbPath);
    }

    return $connections[$dbPath];
}

function getTemplate(string $name): string
{
    static $views = [];

    if (!isset($views[$name])) {
        $views[$name] = file_get_contents(__DIR__ . '/../template/' . $name . '.phtml');
    }

    return $views[$name];
}

function getTranslation(string $id, string $lang = 'en'): string
{
    static $messages = [];

    if (!isset($messages[$lang])) {
        $messages[$lang] = require_once __DIR__ . '/../data/messages.' . $lang . '.php';
    }

    return $messages[$lang][$id] ?? $id;
}

function getCurrentDate(): string
{
    static $date = null;

    if ($date === null) {
        $date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    return $date;
}

function executeQuery(string $sql, array ...$params): false|SQLite3Result
{
    $query = getConnection()->prepare($sql);
    foreach ($params as [$param, $value, $type]) {
        $query->bindValue($param, $value, $type);
    }

    return $query->execute();
}
