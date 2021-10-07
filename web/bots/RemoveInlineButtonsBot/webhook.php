<?php
declare(strict_types=1);

namespace MuaathBots;

require_once '/app/vendor/autoload.php';

use MuaathBots\RemoveInlineButtonsBot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$Bot = new TelegramBot(getenv('REMOVE_INLINE_BUTTONS_BOT_TOKEN'), new RemoveInlineButtonsBot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}