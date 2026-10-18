<?php

namespace App\Support;

/**
 * International phone numbers for WhatsApp links: «00…» becomes «+…», and a number
 * must start with its country code (a local «010…» would give a broken wa.me link).
 */
class Phone
{
    /** Same idea as User::WHATSAPP_RULE, allowing brackets. */
    public const RULE = 'regex:/^\+?[1-9][0-9\s\-()]{7,24}$/';

    public const MESSAGE = 'اكتب الرقم مع رمز الدولة، مثل +201001234567';

    public static function normalize(mixed $number): ?string
    {
        if (! is_string($number)) {
            return null; // arrays and the like fail validation afterwards
        }
        $number = trim($number);

        return str_starts_with($number, '00') ? '+' . substr($number, 2) : $number;
    }
}
