<?php
declare(strict_types=1);

namespace MuaathBots;

require 'vendor/autoload.php';

use MuaathBots\TestPaymentV2Bot;
use SimpleBotAPI\TelegramBot;

$BotDir = basename(__DIR__);

# Check auth
if ($_GET['token'] != getenv('PAYMENT_BOT_TOKEN'))
{
    include $_SERVER['DOCUMENT_ROOT'] . "/bots/webhook-unauthorized.php?bot={$BotDir}";
    exit;
}

# Reading the update from Telegram to the webhook
$Update = json_decode(file_get_contents('php://input'));

if (!empty($Update))
{
    # Create the Bot
    $Bot = new TelegramBot(getenv('PAYMENT_BOT_TOKEN'));
    $Bot->SetUpdatesHandler(new TestPaymentV2Bot($Bot, getenv('PAYMENT_BOT_PROVIDER_TOKEN'), getenv('PAYMENT_BOT_LOGS_CHAT_ID')));

    # Process the update
    $Bot->OnUpdate($Update);
}
else
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    include "../webhook-settings.php?token={$token}&bot={$BotDir}";
}