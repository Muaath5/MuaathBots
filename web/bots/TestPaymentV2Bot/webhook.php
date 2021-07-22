<?php
declare(strict_types=1);

# Configurations
$token = getenv('TestPayment2Bot_Token');
define('DevUsername', getenv('DevUsername'));
$bot_admins = json_decode(getenv('TestPayment2Bot_Admins'));

# Bot settings
define('SettingsFilePath', __DIR__ . '/settings.json');
$settings = json_decode(file_get_contents(SettingsFilePath));

# A Telegram Bot library
include $_SERVER['DOCUMENT_ROOT'] . '/bot-api/TelegramBotAPI.php';
include __DIR__ . '/bot.php';

// Create new bot with token
$Bot = new TelegramBot($token);
// Create updates handler to receive updates
$BotUpdatesHandler = new TestPaymentV2Bot($Bot, getenv('TestPayment2Bot_ProviderToken'), getenv('TestPayment2Bot_LogsChatID'));
// Set it as this bot handler
$Bot->SetUpdatesHandler($BotUpdatesHandler);


# Reading the update
$update = json_decode(file_get_contents('php://input'));

# Check auth
define('BotDirectory', basename(__DIR__));
define('Token', $token);
if ($_GET['token'] != $token)
{
    var_dump(include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-unauthorized.php');
    exit;
}

unset($token);

if (!empty($update))
{
    var_dump($Bot->OnUpdate($update));
}
else
{
    var_dump(include $_SERVER['DOCUMENT_ROOT'] . '/bots/webhook-settings.php');
}