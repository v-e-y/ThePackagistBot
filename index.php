<?php

declare(strict_types=1);

require './vendor/autoload.php';

use telegramBotPackagist\PackagistService;
use telegramBotPackagist\ThePackagistBot;
use GuzzleHttp\Client as HttpClient;

// App configs
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

// Init data file
$dataFile = new SplFileObject(__DIR__ . '/src/data/lastUpdate.txt', 'r+');

// Init http client
$httpClient = new HttpClient([
    'timeout'  => 2.0
]);

// Init Bot
$bot = new ThePackagistBot($dotenv->load(), $httpClient, $dataFile);

$bot->getUpdates();

