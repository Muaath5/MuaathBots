<?php

require_once '/app/vendor/autoload.php';

use MuaathBots\CreateFreeBot;
use SimpleBotAPI\TelegramBot;
use SimpleBotAPI\BotSettings;

$Bot = new TelegramBot(getenv('CREATE_FREE_BOT_TOKEN'), new CreateFreeBot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}