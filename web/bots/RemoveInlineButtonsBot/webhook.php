<?php
declare(strict_types=1);

namespace MuaathBots;

require '/app/vendor/autoload.php';

use MuaathBots\RemoveInlineButtonsBot;
use SimpleBotAPI\BotSettings;
use SimpleBotAPI\TelegramBot;

$BotDir = basename(__DIR__);
$Token = $_REQUEST['token'];

# Check auth
if ($Token != getenv('REMOVE_INLINE_BUTTONS_BOT_TOKEN'))
{
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-unauthorized.php?bot={$BotDir}");
    exit;
}

$Bot = new TelegramBot($Token, new RemoveInlineButtonsBot(), BotSettings::Import(__DIR__ . '/settings.json'));
$Bot->OnWebhookUpdate(file_get_contents('php://input'));


if (isset($_REQUEST['s']))
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    header('Location: ' . "https://muaath-bots.herokuapp.com/bots/webhook-settings.php?token={$Token}&bot={$BotDir}");
    exit;
}