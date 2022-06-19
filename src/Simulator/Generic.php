<?php

namespace Bmatovu\Ussd\Simulator;

use Bmatovu\Ussd\Contracts\Aggregator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;

class Generic implements Aggregator
{
    public function call(string $uri, array $simulator): ?string
    {
        $params = [
            'new_session' => true === $simulator['new_session'] ? 'yes' : 'no',
            'session_id' => $simulator['session_id'],
            'network_code' => $simulator['network_code'],
            'phone_number' => $simulator['phone_number'],
            'input' => $simulator['input'],
            'service_code' => $simulator['service_code'],
        ];

        try {
            $response = (new Client())->request('POST', $uri, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => $params,
            ]);

            $body = json_decode((string) $response->getBody());

            if ('break' === $body->flow) {
                throw new \Exception($body->data);
            }
        } catch (RequestException $ex) {
            $response = $ex->getResponse();
            $body = json_decode((string) $response->getBody());
            $message = $body->message ?? $response->getReasonPhrase();

            throw new \Exception(sprintf('%s . %s', $message, $response->getStatusCode()));
        } catch (TransferException $ex) {
            throw new \Exception(sprintf('%s . %s', $ex->getMessage(), $ex->getCode()));
        }

        return $body->data;
    }
}
