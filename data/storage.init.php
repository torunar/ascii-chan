<?php

declare(strict_types=1);

use function Torunar\AsciiChan\executeQuery;

require_once __DIR__ . '/../vendor/autoload.php';

$schemaPath = __DIR__ . '/../data/storage.schema.sql';
$rootPath = realpath(__DIR__ . '/../') . '/';
printf("Applying schema from %s\n", str_replace($rootPath, '', realpath($schemaPath)));

$schema = file_get_contents($schemaPath);

$queries = array_filter(array_map('trim', explode(';', $schema)));
foreach ($queries as $i => $query) {
    $queryReadable = implode("\n> ", explode("\n", $query));
    printf("Executing query %d/%d:\n> %s\n", $i + 1, count($queries), $queryReadable);
    executeQuery($query);
    echo("-- OK\n\n");
}
