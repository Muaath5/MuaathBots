<?php
declare(strict_types=1);

require_once '/app/vendor/autoload.php';

use MuaathBots\TestPaymentV2Bot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$Bot = new TelegramBot(getenv('PAYMENT_BOT_TOKEN'), new TestPaymentV2Bot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}