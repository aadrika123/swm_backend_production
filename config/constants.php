<?php

/**
 * | Created On-11-08-2022 
 * | Created By-Anshu Kumar
 * | For Module Master defining constants
 */


return [
    "API_KEY"                 => env('API_KEY'),
    "DOC_URL"                 => env('DOC_URL'),
    "DMS_URL"                 => env('DMS_URL'),
    "PAYMENT_URL"             => env('PAYMENT_URL'),                    // ( Payment Engine )


    'PAYMENT_OFFLINE_MODE' => [
        'Cash',
        'Cheque',
        'DD',
        'Neft'
    ],
    "PAYMENT_OFFLINE_MODE" => [
        "1" => "Cash",
        "2" => "Cheque",
        "3" => "DD",
        "4" => "Neft",
        "5" => "Online"
    ],
    "PAYMENT_FOR" => 'Demand Collections',
    "MODULE_ID" => '4',
    "USER_TYPE" => [
        'Tax_Collector' => 'TC',
        'Jsk'           => 'JSK',
        'Citizen'       => 'Citizen'
    ],
];
