<?php
declare(strict_types=1);

namespace MuaathBots;

require '/app/vendor/autoload.php';

use MuaathBots\RemoveInlineButtonsBot;
use SimpleBotAPI\TelegramBot;

$BotDir = basename(__DIR__);
$Token = $_GET['token'];

# Check auth
if ($Token != getenv('REMOVE_INLINE_BUTTONS_BOT_TOKEN'))
{
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-unauthorized.php?bot={$BotDir}");
    exit;
}

$Bot = new TelegramBot($Token);

# Reading the update from Telegram to the webhook
$Update = json_decode(file_get_contents('php://input'));

if (!empty($Update))
{
    # Set UpdatesHandler for the bot
    $Bot->SetUpdatesHandler(new RemoveInlineButtonsBot($Bot, getenv('REMOVE_INLINE_BUTTONS_BOT_LOGS_CHAT_ID')));

    # Process the update
    $Bot->OnUpdate($Update);
}
else
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-settings.php?token={$Token}&bot={$BotDir}");
    exit;
}