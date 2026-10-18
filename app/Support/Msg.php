<?php

namespace App\Support;

use App\Services\Notifications\MessageTemplates;

/** Short alias: Msg::t('contracts.stage_started', ['stage' => …]) = an admin-editable notification text. */
class Msg
{
    public static function t(string $key, array $vars = []): string
    {
        return MessageTemplates::render($key, $vars);
    }
}
