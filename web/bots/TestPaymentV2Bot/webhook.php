<?php
declare(strict_types=1);

namespace MuaathBots;

require_once '/app/vendor/autoload.php';

use MuaathBots\TestPaymentV2Bot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$Token = getenv('PAYMENT_BOT_TOKEN');

$Bot = new TelegramBot($Token, new TestPaymentV2Bot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}