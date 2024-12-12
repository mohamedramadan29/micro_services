<?php

return [

    'supportedLocales' => [
        'ar'          => ['name' => 'Arabic',                 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_AE'],
       // 'en'          => ['name' => 'English',                'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],



    ],

    'useAcceptLanguageHeader' => true,
    'redirectDefaultLocale' => true,

    'hideDefaultLocaleInURL' => false,

    'localesOrder' => ['ar', 'en'],

    'localesMapping' => [],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),
    'urlsIgnored' => ['/skipped'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
