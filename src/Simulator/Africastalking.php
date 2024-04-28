<?php

namespace Bmatovu\Ussd\Simulator;

use Bmatovu\Ussd\Contracts\Aggregator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Str;

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

        try {
            $response = (new Client())->request('POST', $uri, [
                'headers' => [
                    'Accept' => 'text/plain',
                ],
                'form_params' => $params,
            ]);

            $body = (string) $response->getBody();

            $cmd = substr($body, 0, 3);
            $payload = substr($body, 4);

            if ('END' === $cmd) {
                throw new \Exception($payload);
            }
        } catch (\Throwable $th) {
            throw new \Exception(Str::limit($th->getMessage(), 120, '...'));
        }

        // catch (RequestException $ex) {
        //     $response = $ex->getResponse();
        //     $body = (string) $response->getBody();
        //     $message = $body ?? $response->getReasonPhrase();

        //     throw new \Exception(sprintf('%s . %s', $message, $response->getStatusCode()));
        // } catch (TransferException $ex) {
        //     throw new \Exception(sprintf('%s . %s', $ex->getMessage(), $ex->getCode()));
        // }

        return $payload;
    }
}
