<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google'=>[
        'client_id' => '328167433164-m8inkgdf53hnujim5afmi76faoj79vc6.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect' => 'https://nafizha.com/auth/google/callback',
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_APP_ID', '3841335436123346'),
        'client_secret' => env('FACEBOOK_APP_SECRET', '3c55fcaf488b519eb21714a046fba441'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', 'https://nafizha.com/auth/facebook/callback'),
    ],

    // ======================== Social Media Platforms ========================

    'facebook_social' => [
        'client_id'     => env('FACEBOOK_APP_ID', '3841335436123346'),
        'client_secret' => env('FACEBOOK_APP_SECRET', '3c55fcaf488b519eb21714a046fba441'),
        'redirect'      => env('FACEBOOK_SOCIAL_REDIRECT', 'https://nafizha.com/admin/social-media/callback/facebook'),
    ],

    'instagram' => [
        'client_id'     => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect'      => env('INSTAGRAM_REDIRECT_URI', 'https://nafizha.com/admin/social-media/callback/instagram'),
    ],

    'tiktok' => [
        'client_key'    => env('TIKTOK_CLIENT_KEY', ''),
        'client_secret' => env('TIKTOK_CLIENT_SECRET', ''),
        'redirect'      => env('TIKTOK_REDIRECT_URI', 'https://nafizha.com/admin/social-media/callback/tiktok'),
    ],

    'youtube' => [
        'client_id'     => env('YOUTUBE_CLIENT_ID', '328167433164-m8inkgdf53hnujim5afmi76faoj79vc6.apps.googleusercontent.com'),
        'client_secret' => env('YOUTUBE_CLIENT_SECRET', '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'),
        'redirect'      => env('YOUTUBE_REDIRECT_URI', 'https://nafizha.com/admin/social-media/callback/youtube'),
    ],

    'linkedin' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_REDIRECT_URI'),
    ],

    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT_URI'),
    ],

];
