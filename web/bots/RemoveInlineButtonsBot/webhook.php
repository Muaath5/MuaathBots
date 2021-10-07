<?php
declare(strict_types=1);

namespace MuaathBots;

require '/app/vendor/autoload.php';

use MuaathBots\RemoveInlineButtonsBot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$Bot = new TelegramBot($Token, new RemoveInlineButtonsBot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}