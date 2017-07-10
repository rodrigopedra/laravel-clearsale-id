<?php

return [
    'environment' => env( 'CLEARSALEID_ENVIRONMENT', 'production' ), // 'production' or 'sandbox'
    'debug'       => env( 'CLEARSALEID_DEBUG', true ),
    'entity_code' => env( 'CLEARSALEID_ENTITY_CODE', '' ),
    'appid'       => env( 'CLEARSALEID_APPID', '' ),
];
