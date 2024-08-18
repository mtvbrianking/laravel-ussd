<?php

namespace Bmatovu\Ussd\Simulator;

use Bmatovu\Ussd\Contracts\Aggregator;
use Bmatovu\Ussd\Exceptions\FlowBreakException;
use GuzzleHttp\Client;

class Africastalking implements Aggregator
{
    public function call(string $uri, array $simulator): ?string
    {
        $params = [
            'sessionId' => $simulator['session_id'],
            'networkCode' => $simulator['network_code'],
            'phoneNumber' => $simulator['phone_number'],
            'text' => $simulator['answers'],
            'serviceCode' => $simulator['service_code'],
        ];

        // try {
        $response = (new Client())->request('POST', $uri, [
            'headers' => [
                'Accept' => 'text/plain',
            ],
            'form_params' => $params,
        ]);

        $body = (string) $response->getBody();

        $cmd = substr($body, 0, 3);
        $payload = substr($body, 4);
        // } catch (\Throwable $th) {
        //     $firstLine = preg_split('#\r?\n#', ltrim($th->getMessage()), 2)[0];
        //     throw new \Exception($firstLine);
        // }

        if ('END' === $cmd) {
            throw new FlowBreakException($payload);
        }

        return $payload;
    }
}
