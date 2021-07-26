<?php
declare(strict_types=1);

namespace MuaathBots;

require('../../../vendor/autoload.php');

use SimpleBotAPI\TelegramBot;
use MuaathBots\TestPaymentV2Bot;

# Configurations
$token = getenv('TestPayment2Bot_Token');
define('DevUsername', getenv('DevUsername'));
$bot_admins = json_decode(getenv('TestPayment2Bot_Admins'));

# Bot settings
define('SettingsFilePath', __DIR__ . '/settings.json');
$settings = json_decode(file_get_contents(SettingsFilePath));

# Create the Bot API
$Bot = new TelegramBot($token);
$Bot->SetUpdatesHandler(new TestPaymentV2Bot($Bot, getenv('TestPayment2Bot_ProviderToken'), getenv('TestPayment2Bot_LogsChatID')));


# Reading the update
$Update = json_decode(file_get_contents('php://input'));

# Check auth
$BotDir = basename(__DIR__);
if ($_GET['token'] != $token)
{
    include $_SERVER['DOCUMENT_ROOT'] . "/bots/webhook-unauthorized.php?bot={$BotDir}";
    exit;
}

if (!empty($Update))
{
    # Process the update from webhook
    $Bot->OnUpdate($Update);
}
else
{
    # Show the admin settings of the webhook, Contains: Webhook info, Delete webhook, Set webhook
    include $_SERVER['DOCUMENT_ROOT'] . "/bots/webhook-settings.php?token={$token}&bot={$BotDir}";
}