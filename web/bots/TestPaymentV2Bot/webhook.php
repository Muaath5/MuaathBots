<?php
declare(strict_types=1);

namespace MuaathBots;

require '/home/muaath/websites/muaath-bots/vendor/autoload.php';

use MuaathBots\TestPaymentV2Bot;
use SimpleBotAPI\TelegramBot;

$BotDir = basename(__DIR__);
$Token = $_GET['token'];

# Check auth
if ($Token != getenv('PAYMENT_BOT_TOKEN'))
{
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-unauthorized.php?bot={$BotDir}");
    exit;
}

$Bot = new TelegramBot(getenv('PAYMENT_BOT_TOKEN'));

# Reading the update from Telegram to the webhook
$Update = json_decode(file_get_contents('php://input'));

if (!empty($Update))
{
    # Set UpdatesHandler for the bot
    $Bot->SetUpdatesHandler(new TestPaymentV2Bot($Bot, getenv('PAYMENT_BOT_PROVIDER_TOKEN'), getenv('PAYMENT_BOT_LOGS_CHAT_ID')));

    # Process the update
    $Bot->OnUpdate($Update);
}
else
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-settings.php?token={$Token}&bot={$BotDir}");
    exit;
}