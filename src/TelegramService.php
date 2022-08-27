<?php

declare(strict_types=1);

namespace telegramBotPackagist;

final class TelegramService
{
    private array $botConfigs;

    public function setConfigs(array $configs): void
    {
        $this->botConfigs = $configs;
    }

    public function botGo(\Psr\Http\Client\ClientInterface $httpClient)
    {
        // get update

        // filter message

        // send request to the packagist service
        // receive answer from P.Service

        // 

        return $this;
    }

    // get update


    // filter message

    // send message to packagist service
    // get response from packagist
}