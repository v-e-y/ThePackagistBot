<?php

declare(strict_types=1);

namespace telegramBotPackagist;

final class PackagistService
{
    private array $configs;
    private \GuzzleHttp\Client $httpClient;

    public function __construct(array $configs, \GuzzleHttp\Client $httpClient)
    {
        $this->configs = [
            'TG_BOT_PACKAGIST_API_TAG_SEARCH' => $configs['TG_BOT_PACKAGIST_API_TAG_SEARCH'],
            'TG_BOT_PACKAGIST_WEB_TAG_SEARCH' => $configs['TG_BOT_PACKAGIST_WEB_TAG_SEARCH']
        ];

        $this->httpClient = $httpClient;
    }

    public function getPackagistPackages(string $tag): string|array
    {
        // Second check
        if (mb_strlen($tag) < 1) {
           return 'Tag length must be equal or grater than 1';
        }

        if (mb_strlen($tag) > 64) {
            return 'The tag is to long';
        }

        $packages = $this->requestPackagesByTag($tag);

        return ($this->isPackagesExist($packages))? $packages['results'] : 'No packages for this tag';
    }

    public function getWebUrlForTag(string $tag): string
    {
        return $this->configs['TG_BOT_PACKAGIST_WEB_TAG_SEARCH'] . $tag;
    }

    private function requestPackagesByTag(string $tag): array
    {
        // TODO add response checks
        $response = $this->httpClient->get(
            $this->getUriForRequest($tag)
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getUriForRequest(string $searchTag): string
    {
        return $this->configs['TG_BOT_PACKAGIST_API_TAG_SEARCH'] . $searchTag;
    }

    private function isPackagesExist(array $requestResult): bool
    {
        return ($requestResult['total'])? true : false;
    }
}
