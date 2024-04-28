<?php

namespace Bmatovu\Ussd\Simulator;

use Bmatovu\Ussd\Contracts\Aggregator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Str;

/**
 * Comviva HTTP Pull Flares API
 * Should work for MTN and Airtel
 */
class Comviva implements Aggregator
{
    public function call(string $uri, array $simulator): ?string
    {
        try {
            $response = (new Client())->request('POST', $uri, [
                'headers' => [
                    'Accept' => 'text/xml',
                    'Content-Type' => 'text/xml; charset=UTF8',
                ],
                'body' => $this->buildXml([
                    'name' => 'request',
                    'attributes' => [
                        'type' => 'pull',
                    ],
                ], [
                    'sessionId' => $simulator['session_id'],
                    'transactionId' => null,
                    'msisdn' => $simulator['phone_number'],
                    'newRequest' => $simulator['new_session'] ? 1 : 0,
                    'flowState' => $simulator['new_session'] ? 'FD' : 'FE',
                    'subscriberInput' => $simulator['input'], // $simulator['new_session'] ? substr($simulator['input'], 4) : $simulator['input'],
                ]),
            ]);

            $body = (string) $response->getBody();

            $body = new \SimpleXMLElement($body);

            $flow = $body->freeflow->freeflowState->__toString();

            $applicationResponse = $body->applicationResponse->__toString();

            if ($flow == 'FB') {
                throw new \Exception($applicationResponse);
            }
        } catch (\Throwable $th) {
            throw new \Exception(Str::limit($th->getMessage(), 120, '...'));
        }

        // } catch (RequestException $ex) {
        //     $response = $ex->getResponse();
        //     $body = (string) $response->getBody();
        //     $message = $body ?? $response->getReasonPhrase();

        //     throw new \Exception(sprintf('%s . %s', $message, $response->getStatusCode()));
        // } catch (TransferException $ex) {
        //     throw new \Exception(sprintf('%s . %s', $ex->getMessage(), $ex->getCode()));
        // }

        return $applicationResponse;
    }

    protected function buildXml(string|array $root, array $elements): string
    {
        $domDoc = new \DOMDocument('1.0', 'UTF-8');
        $domDoc->xmlStandalone = true;
        $domDoc->preserveWhiteSpace = false;
        $domDoc->formatOutput = true;

        if (is_string($root)) {
            $rootEl = $domDoc->createElement($root);
        } else {
            $rootEl = $domDoc->createElement($root['name']);
            foreach ($root['attributes'] ?? [] as $key => $value) {
                $rootEl->setAttribute($key, (string) $value);
            }
        }

        foreach ($elements as $key => $value) {
            if (!is_array($value)) {
                $elem = $domDoc->createElement($key, (string) $value);
            } else {
                $elem = $domDoc->createElement($key);
                foreach ($value as $key => $value) {
                    $subElem = $domDoc->createElement($key, (string) $value);
                    $elem->appendChild($subElem);
                }
            }
            $rootEl->appendChild($elem);
        }

        $domDoc->appendChild($rootEl);

        return $domDoc->saveXML();
    }
}
