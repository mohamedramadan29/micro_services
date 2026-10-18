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
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://nafizha.com/auth/google/callback'),
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
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

    // Service request payments (escrow), same EasyKash account setup as AyaKids.
    // Set the callback URL in the EasyKash dashboard to https://<site>/easykash/webhook.
    // SEO (client file «تعديلات», item 05): a service account key file + the Search Console property.
    'search_console' => [
        'credentials' => env('GSC_CREDENTIALS'),
        'site_url' => env('GSC_SITE_URL'),
    ],

    // Approximate USD prices (display only; payments stay EGP).
    'fx' => [
        'url' => env('FX_RATES_URL', 'https://open.er-api.com/v6/latest/EGP'),
    ],

    // Site check (client file «اختبر موقعك مجاناً»).
    'pagespeed' => [
        'key' => env('PAGESPEED_API_KEY'),
    ],

    'easykash' => [
        'api_key'           => env('EASYKASH_API_KEY'),
        'hmac_secret'       => env('EASYKASH_HMAC_SECRET'),
        'base_url'          => env('EASYKASH_BASE_URL', 'https://back.easykash.net/api'),
        'currency'          => env('EASYKASH_CURRENCY', 'EGP'),
        'currency_label'    => env('EASYKASH_CURRENCY_LABEL', 'ج.م'),
        'payment_options'   => env('EASYKASH_PAYMENT_OPTIONS', '2,4,5,6'), // as in AyaKids
        'cash_expiry_hours' => env('EASYKASH_CASH_EXPIRY_HOURS', 24),
    ],

];
