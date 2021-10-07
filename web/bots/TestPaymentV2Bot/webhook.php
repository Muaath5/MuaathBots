<?php
declare(strict_types=1);

namespace MuaathBots;

require '/app/vendor/autoload.php';

use MuaathBots\TestPaymentV2Bot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$BotDir = basename(__DIR__);
$Token = $_REQUEST['token'];

# Check auth
if ($Token != getenv('PAYMENT_BOT_TOKEN'))
{
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-unauthorized.php?bot={$BotDir}");
    exit;
}

if (isset($_REQUEST['s']))
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-settings.php?token={$Token}&bot={$BotDir}");
    exit;
}

$Bot = new TelegramBot($Token, new TestPaymentV2Bot(), BotSettings::Import(__DIR__ . '/settings.json'));
if (!$Bot->OnWebhookUpdate())
{
    echo 'false';
}