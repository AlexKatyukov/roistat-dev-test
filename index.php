<?php

include_once 'vendor/autoload.php';

use App\LogParser;

/** @var string[] $argv */

try {
    $parser = new LogParser($argv[1]);
    echo $parser->parseFile();
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
}
