<?php

declare(strict_types=1);

namespace telegramBotPackagist;

use Dotenv\Parser\Value;
use SplFileObject;
use \GuzzleHttp\Client;
use telegramBotPackagist\PackagistService;

final class ThePackagistBot
{
    private PackagistService $packagistService;
    private array $telegramBotConfigs;
    private Client $httpClient;
    private SplFileObject $dataFile;

    public function __construct(
        array $configs,
        Client $httpClient,
        SplFileObject $dataFile
    )
    {
        $this->packagistService = new PackagistService($configs, $httpClient);
        $this->telegramBotConfigs = $this->setBotConfigs($configs);
        $this->httpClient = $httpClient;
        $this->dataFile = $dataFile;
    }

    public function getUpdates()
    {
        $messagesFromBot = $this->httpClient->get(
            $this->telegramBotConfigs['TG_BOT_GET_UPDATES_COMMAND_URL'] . '?offset=' . $this->getOffset()
        );

        $messagesFromBotArray = json_decode($messagesFromBot->getBody()->getContents(), true);

        if (!$messagesFromBotArray['ok'] && empty($messagesFromBotArray['result'])) {
            die;
        }

        return $this->sendResponseToChats($messagesFromBotArray['result']);
    }

    /*
    private function filterResponseFromBot(\GuzzleHttp\Psr7\Response $response): array|bool
    {
        $messages = array_map(function ($message) {
            if ($message['message']['text']) {
            }
            return $message;
        }, $resultArray['result']);
        print_r($messages);
        die;
        return $resultArray['result'];
    }
    */

    private function sendResponseToChats(array $messagesForResponse)
    {
        foreach ($messagesForResponse as $messageNumber => $message) {
            

            if (!array_key_exists('text', $message['message'])) {
                continue;
            }

            $this->httpClient->get(
                $this->telegramBotConfigs['TG_BOT_GET_SEND_MESSAGE_URL'] . '?' .
                http_build_query(
                    [
                        'chat_id' => $message['message']['chat']['id'],
                        'text' => $this->getMessageForResponse($message['message']['text']),
                        'parse_mode' => $this->telegramBotConfigs['TG_BOT_PARSE_MODE']
                    ]
                )
            );

            if ($messageNumber === array_key_last($messagesForResponse)) {
                $this->writeOffset($message['update_id']);
            }
        }
    }


    private function getMessageForResponse(string $tag): string|array
    {
        if ($tag === $this->telegramBotConfigs['TG_BOT_START_COMMAND']) {
            return $this->telegramBotConfigs['TG_BOT_WELCOME_MESSAGE'];
        }

        $message = $this->packagistService->getPackagistPackages($tag);

        if (gettype($message) === 'string') {
           return $message;
        }

        $messageForSend = '';
        $messageSchema = '<strong>Name:</strong> %s'
        . PHP_EOL .
        '<strong>Description:</strong> <em>%s</em>'
        . PHP_EOL .
        '<strong>Packagist:</strong> %s'
        . PHP_EOL .
        '<strong>Repository:</strong> %s'
        . PHP_EOL .
        '<strong>Downloads/Favers:</strong> %s/%s'
        . PHP_EOL
        . PHP_EOL;

        foreach (array_slice($message, 0, 10) as $key => $value) {
            $messageForSend .= sprintf(
                $messageSchema, 
                $value['name'], 
                $value['description'], 
                $value['url'], 
                $value['repository'], 
                $value['downloads'], 
                $value['favers']
            );

            if ($key === array_key_last(array_slice($message, 0, 10))) {
                $messageForSend .= '<strong>See all results:</strong> ' . $this->packagistService->getWebUrlForTag($tag);
            }
        }
        
        return $messageForSend;
    }

    private function getOffset(): int
    {
        return (int)$this->dataFile->fgets();
    }

    private function writeOffset(int $offset):void
    {
        $offset += 1;
        $this->dataFile->ftruncate(0);
        $this->dataFile->rewind();
        $this->dataFile->fwrite((string)$offset);
    }

    private function setBotConfigs(array $appConfigs): array
    {
        return [
            'TG_BOT_NAME' => $appConfigs['TG_BOT_NAME'],
            'TG_BOT_TOKEN'=> $appConfigs['TG_BOT_TOKEN'],
            'TG_BOT_API_HOST' => $appConfigs['TG_BOT_API_HOST'],
            'TG_BOT_API_URL' => $appConfigs['TG_BOT_API_URL'],
            'TG_BOT_START_COMMAND' => $appConfigs['TG_BOT_START_COMMAND'],
            'TG_BOT_WELCOME_MESSAGE' => $appConfigs['TG_BOT_WELCOME_MESSAGE'],
            'TG_BOT_PARSE_MODE' => $appConfigs['TG_BOT_PARSE_MODE'],
            'TG_BOT_GET_UPDATES_COMMAND' => $appConfigs['TG_BOT_GET_UPDATES_COMMAND'],
            'TG_BOT_GET_UPDATES_COMMAND_URL' => $appConfigs['TG_BOT_GET_UPDATES_COMMAND_URL'],
            'TG_BOT_GET_SEND_MESSAGE_COMMAND' => $appConfigs['TG_BOT_GET_SEND_MESSAGE_COMMAND'],
            'TG_BOT_GET_SEND_MESSAGE_URL' => $appConfigs['TG_BOT_GET_SEND_MESSAGE_URL']
        ];
    }

}
