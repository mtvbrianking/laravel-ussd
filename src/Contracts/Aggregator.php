<?php

namespace Bmatovu\Ussd\Contracts;

use Symfony\Component\Console\Output\OutputInterface;

interface Aggregator
{
    /**
     * Call USSD service.
     *
     * @param string $uri
     * @param array $data Input params from the simulator
     *    $params = [
     *        'new_session'   => (bool)    Request type
     *        'session_id'    => (string)  Session ID - Auto generated
     *        'phone_number'  => (string)  Phone Number
     *        'service_code'  => (string)  Service Code
     *        'network_code'  => (?string) Network Code
     *        'input'         => (?string) Current user input
     *        'answers'       => (?string) All the user's input for this session
     *    ]
     * @param OutputFormatterInterface $output
     *
     * @return string|null Successful response.
     */
    public function call(string $uri, array $params, OutputInterface $output): ?string;
}
