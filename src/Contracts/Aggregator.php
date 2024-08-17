<?php

namespace Bmatovu\Ussd\Contracts;

interface Aggregator
{
    /**
     * Call USSD service.
     *
     * @param array $options Simulator options
     *                       $options = [
     *                       'new_session'   => (bool)    Request type
     *                       'session_id'    => (string)  Session ID - Auto generated
     *                       'phone_number'  => (string)  Phone Number
     *                       'service_code'  => (string)  Service Code
     *                       'network_code'  => (?string) Network Code
     *                       'input'         => (?string) Current user input
     *                       'answers'       => (?string) All the user's input for this session
     *                       ]
     *
     * @return null|string successful response
     */
    public function call(string $uri, array $options): ?string;
}
