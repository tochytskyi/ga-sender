<?php

declare(strict_types=1);

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$data = [
    'v' => 1,
    't' => 'event',
    'tid' => getenv('TRACK_ID') ?? '',
    'uid' => 'test-user',
    'ec' => 'currency',
    'ea' => 'rate_update',
    'el' => 'usd-uah',
];

$client = new Client();

while (true) {
    echo getTime() . '. Find rate...' . PHP_EOL;

    $currencyRateResponse = $client->request(
        'GET',
        'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
        [
            'query' => [
                'json' => 1,
                'valcode' => 'USD',
            ]
        ]
    );

    $rate = parseRate($currencyRateResponse->getBody()->getContents());

    if ($rate ?? false) {
        $data['ev'] = $data['cm1'] = $rate;

        $gaResponse = $client->request(
            'POST',
            'https://www.google-analytics.com/collect',
            [
                'query' => $data,
            ]
        );

        echo getTime() . ". Current rate is {$data['ev']}" . PHP_EOL;
    } else {
        echo getTime() . '. Skip due to error' . PHP_EOL;
    }

    sleep(60); //1 min
    //sleep(24 * 60 * 60); // 1day
}

function getTime(): string
{
    return (new DateTime())->format('Y-m-d h:i:s');
}

function parseRate(string $response): ?int
{
    $encodedResponse = json_decode($response, true);
    $rate = $encodedResponse[0]['rate'] ?? null;

    if ($rate === null) {
        return null;
    }

    return (int)(round($rate, 2) * 100);
}
