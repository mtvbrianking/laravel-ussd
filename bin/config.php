<?php

return [
    // USSD Aggregators...
    'africastalking' => [
        'provider' => '\\Bmatovu\\Ussd\\Simulator\\Africastalking',
        'uri' => 'http://localhost:8000/api/ussd/africastalking',
        'service' => '*156#',
        'network' => '6001',
    ],
];
