<?php

declare(strict_types=1);

require './vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo $_ENV['TG_BOT_API_URL'];

$token = $_ENV['TG_BOT_TOKEN'];

echo getenv('APP_NAME', false);

